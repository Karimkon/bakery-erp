<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\DriverExpense;
use App\Models\User;
use App\Models\BankDeposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 20;
        $searchDriver = $request->input('driver');

        // Get all dispatches with driver & items
        $query = Dispatch::with('driver', 'items', 'expenses')
            ->orderBy('dispatch_date', 'desc')
            ->orderBy('dispatch_no', 'desc');

        if ($searchDriver) {
            $query->whereHas('driver', fn($q) => $q->where('name', 'like', "%$searchDriver%"));
        }

        $allDispatches = $query->get();

        // Keep only latest dispatch per driver
        $driverLatest = [];
        foreach ($allDispatches as $dispatch) {
            if (!isset($driverLatest[$dispatch->driver_id])) {
                $driverLatest[$dispatch->driver_id] = $dispatch;
            }
        }

        $dispatches = collect($driverLatest)->values();

        // Compute Balance Due (Unsold Goods + Credit Sales + Back Debt) per dispatch
        $dispatches->transform(function ($d) {
            $remainingInventoryValue = $d->items->sum(fn($i) => $i->remaining_qty * $i->unit_price);
            $creditSalesValue = $d->items->sum(fn($i) => $i->sold_credit * $i->unit_price);
            $driverBackDebt = $d->driver?->back_debt ?? 0;

            $d->balanceDue = $remainingInventoryValue + $creditSalesValue + $driverBackDebt;
            return $d;
        });

        // Manual pagination
        $page = $request->input('page', 1);
        $paginated = new LengthAwarePaginator(
            $dispatches->forPage($page, $perPage)->values(),
            $dispatches->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.dispatches.index', [
            'dispatches'   => $paginated,
            'searchDriver' => $searchDriver
        ]);
    }

    public function create()
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $products = config('bakery_products');

        return view('admin.dispatches.create', compact('drivers', 'products'));
    }

    public function store(Request $request)
    {
        $products = config('bakery_products');

        $request->validate([
            'driver_id'      => ['required','exists:users,id'],
            'dispatch_date'  => ['required','date'],
            'items'          => ['required','array'],
            'driver_signature' => ['nullable','string'],
        ]);

        // Verify selected user is actually a driver
        if (!User::where('id', $request->driver_id)->where('role','driver')->exists()) {
            return back()->withErrors(['driver_id' => 'Selected user is not a driver'])->withInput();
        }

        // Compute opening from previous day record
        $openings = $this->computeOpenings((int)$request->driver_id, $request->dispatch_date);

        $lines = [];
        $totalItemsSold = 0;
        $totalSalesValue = 0.0;
        $cashReceived = 0.0;

        foreach ($products as $product => $price) {
            $dispatched  = (int) data_get($request->items, "$product.dispatched_qty", 0);
            $soldCash    = (int) data_get($request->items, "$product.sold_cash", 0);
            $soldCredit  = (int) data_get($request->items, "$product.sold_credit", 0);
            $sold        = $soldCash + $soldCredit;
            $opening     = (int) ($openings[$product] ?? 0);

            $available = $opening + $dispatched;
            if ($sold > $available) {
                return back()
                    ->withErrors(["items.$product.sold_cash" => "Sold ($sold) cannot exceed Opening+Dispatched ($available) for $product"])
                    ->withInput();
            }

            $remaining = $available - $sold;
            $unitPrice = (float) $price;
            $lineTotal = $sold * $unitPrice;

            // Only cash sales count as "cash received"
            $cashReceived += $soldCash * $unitPrice;

            $totalItemsSold  += $sold;
            $totalSalesValue += $lineTotal;

            $lines[] = [
                'product'        => $product,
                'opening_stock'  => $opening,
                'dispatched_qty' => $dispatched,
                'sold_cash'      => $soldCash,
                'sold_credit'    => $soldCredit,
                'sold_qty'       => $sold,
                'remaining_qty'  => $remaining,
                'unit_price'     => $unitPrice,
                'line_total'     => $lineTotal,
            ];
        }

        $balanceDue = 0;
        foreach ($lines as $line) {
            $balanceDue += $line['remaining_qty'] * $line['unit_price'];
        }

        // Compute commissions
        $commissionTotal = $this->computeCommissionStuff($lines, $totalSalesValue);

        DB::transaction(function () use ($request, $lines, $totalItemsSold, $totalSalesValue, $commissionTotal, $cashReceived, $balanceDue) {
         
            $nextNo = Dispatch::where('driver_id', $request->driver_id)
                ->where('dispatch_date', $request->dispatch_date)
                ->lockForUpdate()
                ->max('dispatch_no') + 1;

            $dispatch = Dispatch::create([
                'driver_id'         => $request->driver_id,
                'dispatch_date'     => $request->dispatch_date,
                'dispatch_no'       => $nextNo, 
                'notes'             => $request->notes,
                'driver_signature'  => $request->driver_signature, 
                'total_items_sold'  => $totalItemsSold,
                'total_sales_value' => $totalSalesValue,
                'commission_total'  => $commissionTotal,
                'cash_received'     => $cashReceived, 
                'balance_due'       => $balanceDue,
                'expected_cash_after_deductions' => $cashReceived - $commissionTotal, // Add this field
            ]);

            foreach ($lines as $row) {
                $row['dispatch_id'] = $dispatch->id;
                DispatchItem::create($row);

                // Deduct bakery stock
                $stock = \App\Models\BakeryStock::where('product', $row['product'])->first();
                if (!$stock && $row['dispatched_qty'] > 0) {
                    throw new \Exception("No stock record found for {$row['product']} — cannot dispatch non-existent stock.");
                }
                if ($row['dispatched_qty'] > $stock->quantity) {
                    throw new \Exception("Not enough bakery stock for {$row['product']}");
                }
                $stock->decrement('quantity', $row['dispatched_qty']);
            }
        });

        return redirect()
            ->route('admin.dispatches.index')
            ->with('success', 'Dispatch saved successfully.');
    }

    public function show(Dispatch $dispatch)
    {
        $dispatch->load('driver', 'items', 'expenses');
        return view('admin.dispatches.show', compact('dispatch'));
    }

    public function edit($id)
    {
        $dispatch = Dispatch::with('items', 'driver', 'expenses')->findOrFail($id);
        $drivers = User::where('role', 'driver')->get();
        $products = config('bakery_products');
        $openings = $this->computeOpenings((int)$dispatch->driver_id, $dispatch->dispatch_date->toDateString(), $dispatch->id);

        return view('admin.dispatches.edit', compact('dispatch', 'drivers', 'products', 'openings'));
    }

   public function update(Request $request, $id)
{
    $dispatch = Dispatch::with('items', 'driver', 'expenses')->findOrFail($id);
    $products = config('bakery_products');

    $request->validate([
        'items' => ['required', 'array'],
        'items.*.dispatched_qty' => ['nullable', 'integer', 'min:0'],
        'items.*.sold_cash' => ['nullable', 'integer', 'min:0'],
        'items.*.sold_credit' => ['nullable', 'integer', 'min:0'],
        'notes' => ['nullable', 'string'],
        'cash_received' => ['nullable', 'numeric', 'min:0'],
        'driver_signature' => ['nullable', 'string'],
        'expenses' => ['nullable', 'array'],
        'expenses.*.expense_type' => ['required_with:expenses.*.amount', 'string'],
        'expenses.*.amount' => ['required_with:expenses.*.expense_type', 'numeric', 'min:0'],
        'expenses.*.description' => ['nullable', 'string', 'max:500'],
        'expenses.*.receipt' => ['nullable', 'image', 'max:2048'],
        'back_debt' => ['nullable', 'numeric', 'min:0'],
        'back_debt_hidden' => ['nullable', 'numeric', 'min:0'],
    ], [
        'cash_received.min' => 'Cash received cannot be negative.',
        'expenses.*.amount.min' => 'Expense amounts cannot be negative.',
    ]);

    DB::transaction(function () use ($dispatch, $request, $products) {
        // STEP 1: Update Driver's Back Debt if changed
        $driver = User::find($dispatch->driver_id);
        $newBackDebt = (float) $request->input('back_debt', $request->input('back_debt_hidden', $driver->back_debt));

        // Always update back debt with the form value
        $driver->back_debt = $newBackDebt;
        $driver->save();

        // Reload the driver relationship to get updated back debt
        $dispatch->load('driver');
        $driver = $dispatch->driver;

        // STEP 2: Update Dispatch Items (INCLUDING DISPATCHED QTY)
        $totalItemsSold = 0;
        $totalSalesValue = 0;
        $calculatedCashReceived = 0;
        $creditSalesValue = 0;
        $remainingInventoryValue = 0;
        $itemsForCommission = [];

        foreach ($request->items as $product => $data) {
            if (!array_key_exists($product, $products)) continue;

            $item = $dispatch->items->firstWhere('product', $product);
            if (!$item) continue;

            // Get new dispatched quantity from form
            $newDispatchedQty = (int) ($data['dispatched_qty'] ?? $item->dispatched_qty);
            $oldDispatchedQty = $item->dispatched_qty;
            
            $newSoldCash   = (int) ($data['sold_cash'] ?? 0);
            $newSoldCredit = (int) ($data['sold_credit'] ?? 0);
            
            // Calculate max sold with NEW dispatched quantity
            $maxSold = $item->opening_stock + $newDispatchedQty;
            $totalSold = $newSoldCash + $newSoldCredit;

            if ($totalSold > $maxSold) {
                throw new \Exception("Total sold ($totalSold) cannot exceed available stock ($maxSold) for $product");
            }

            // Handle stock adjustment if dispatched quantity changed
            if ($newDispatchedQty != $oldDispatchedQty) {
                $stock = \App\Models\BakeryStock::where('product', $product)->first();
                
                if (!$stock) {
                    throw new \Exception("No stock record found for $product");
                }

                $qtyDifference = $newDispatchedQty - $oldDispatchedQty;

                // If increasing dispatch, check if we have enough stock
                if ($qtyDifference > 0) {
                    if ($stock->quantity < $qtyDifference) {
                        throw new \Exception("Not enough bakery stock for $product. Available: {$stock->quantity}, Needed: {$qtyDifference}");
                    }
                    // Deduct additional stock
                    $stock->decrement('quantity', $qtyDifference);
                } else {
                    // Restore stock (dispatched quantity was reduced)
                    $stock->increment('quantity', abs($qtyDifference));
                }
            }

            $remaining = $maxSold - $totalSold;
            $unitPrice = $products[$product];
            $lineTotal = $totalSold * $unitPrice;

            $totalItemsSold += $totalSold;
            $totalSalesValue += $lineTotal;
            $calculatedCashReceived += $newSoldCash * $unitPrice;
            $creditSalesValue += $newSoldCredit * $unitPrice;
            $remainingInventoryValue += $remaining * $unitPrice;

            $itemsForCommission[] = [
                'product'        => $product,
                'opening_stock'  => $item->opening_stock,
                'dispatched_qty' => $newDispatchedQty,
                'sold_qty'       => $totalSold,
                'unit_price'     => $unitPrice,
            ];

            // Update item with new dispatched quantity
            $item->update([
                'dispatched_qty' => $newDispatchedQty,
                'sold_cash'      => $newSoldCash,
                'sold_credit'    => $newSoldCredit,
                'sold_qty'       => $totalSold,
                'remaining_qty'  => $remaining,
                'line_total'     => $lineTotal,
            ]);
        }

        // STEP 3: Calculate Commissions
        $commissionTotal = $this->computeCommissionStuff($itemsForCommission, $totalSalesValue);

        foreach ($itemsForCommission as $itemData) {
            $item = $dispatch->items->firstWhere('product', $itemData['product']);
            if ($item) {
                $item->update(['commission' => $itemData['commission'] ?? 0]);
            }
        }

        // STEP 4: Process Driver Expenses
        $totalDriverExpenses = 0;
        $existingExpenseIds = [];

        if ($request->has('expenses') && is_array($request->expenses)) {
            foreach ($request->expenses as $expenseData) {
                if (empty($expenseData['expense_type']) || empty($expenseData['amount'])) {
                    continue;
                }

                $amount = (float) $expenseData['amount'];
                $totalDriverExpenses += $amount;

                $expenseRecord = [
                    'dispatch_id'   => $dispatch->id,
                    'driver_id'     => $dispatch->driver_id,
                    'expense_type'  => $expenseData['expense_type'],
                    'amount'        => $amount,
                    'description'   => $expenseData['description'] ?? null,
                ];

                if (isset($expenseData['receipt']) && $expenseData['receipt']) {
                    $file = $expenseData['receipt'];
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('receipts', $filename, 'public');
                    $expenseRecord['receipt_image'] = $path;
                }

                if (!empty($expenseData['id'])) {
                    $expense = DriverExpense::find($expenseData['id']);
                    if ($expense && $expense->dispatch_id == $dispatch->id) {
                        if (isset($expenseRecord['receipt_image']) && $expense->receipt_image) {
                            Storage::disk('public')->delete($expense->receipt_image);
                        }
                        $expense->update($expenseRecord);
                        $existingExpenseIds[] = $expense->id;
                    }
                } else {
                    $newExpense = DriverExpense::create($expenseRecord);
                    $existingExpenseIds[] = $newExpense->id;
                }
            }
        }

        $dispatch->expenses()
            ->whereNotIn('id', $existingExpenseIds)
            ->each(function ($expense) {
                if ($expense->receipt_image) {
                    Storage::disk('public')->delete($expense->receipt_image);
                }
                $expense->delete();
            });

        // STEP 5: Financial Calculations
        $expectedAfterDeductions = $calculatedCashReceived - $commissionTotal - $totalDriverExpenses;
        
        // Get actual cash from form - if empty, use expected
        $actualCashInput = $request->input('cash_received');
        
        if ($actualCashInput === null || $actualCashInput === '' || (float)$actualCashInput == 0) {
            $actualCashReceived = $expectedAfterDeductions;
        } else {
            $actualCashReceived = (float) $actualCashInput;
        }

        // STEP 6: Calculate Balance Due
        $balanceDue = $remainingInventoryValue + $creditSalesValue + $driver->back_debt;

        // STEP 7: Update Dispatch Record
        $dispatch->update([
            'notes'                          => $request->notes,
            'driver_signature'               => $request->driver_signature,
            'total_items_sold'               => $totalItemsSold,
            'total_sales_value'              => $totalSalesValue,
            'commission_total'               => $commissionTotal,
            'driver_expenses'                => $totalDriverExpenses,
            'cash_received'                  => $actualCashReceived,
            'expected_cash_after_deductions' => $expectedAfterDeductions,
            'balance_due'                    => $balanceDue,
        ]);
    });

    return redirect()->route('admin.dispatches.index')
                    ->with('success', 'Dispatch updated successfully.');
}
    public function openings($driverId, $date)
    {
        $driver = User::find($driverId);
        if (!$driver || $driver->role !== 'driver') {
            return response()->json(['success'=>false,'error'=>'Invalid driver'],422);
        }

        $openings = $this->computeOpenings($driver->id, $date);

        return response()->json([
            'success'  => true,
            'openings' => $openings
        ]);
    }

    protected function computeOpenings(int $driverId, string $date, ?int $currentDispatchId = null): array
    {
        $products = array_keys(config('bakery_products'));
        $openings = array_fill_keys($products, 0);

        $lastDispatch = Dispatch::where('driver_id', $driverId)
            ->where('dispatch_date', '<=', $date)
            ->when($currentDispatchId, fn($q) => $q->where('id', '<>', $currentDispatchId))
            ->orderBy('dispatch_date', 'desc')
            ->orderBy('dispatch_no', 'desc')
            ->with('items')
            ->first();

        if ($lastDispatch) {
            foreach ($lastDispatch->items as $item) {
                $openings[$item->product] = $item->remaining_qty;
            }
        }

        return $openings;
    }

    protected function computeCommissionStuff(array &$lines, float $totalSalesValue): float
    {
        $rates = config('commissions.rates', [
            'big_breads'     => 200,
            'small_breads'   => 100,
            'buns'           => 200,
            'donuts'         => 100,
            'half_cakes'     => 100,
            'block_cakes'    => 200,
            'slab_cakes'     => 200,
            'birthday_cakes' => 200,
        ]);
        
        $threshold = (float) config('commissions.threshold', 1_000_000);
        $basis     = config('commissions.threshold_basis', 'available');

        // Compute basis value (UGX)
        $basisValue = 0.0;
        foreach ($lines as $row) {
            $unit = (float) $row['unit_price'];
            $opening    = (int) ($row['opening_stock'] ?? 0);
            $dispatched = (int) ($row['dispatched_qty'] ?? 0);
            $sold       = (int) ($row['sold_qty'] ?? 0);

            $qtyForBasis = match ($basis) {
                'dispatched' => $dispatched,
                'sold'       => $sold,
                default      => $opening + $dispatched,
            };

            $basisValue += $qtyForBasis * $unit;
        }

        // Decide full vs half rate
        $multiplier = ($basisValue >= $threshold) ? 1.0 : 0.5;

        // Per-line commission AND return total
        $commissionTotal = 0.0;
        foreach ($lines as &$row) {
            $rate = (float) ($rates[$row['product']] ?? 0);
            $perPiece = $rate * $multiplier;
            $row['commission'] = round($row['sold_qty'] * $perPiece, 2);
            $commissionTotal  += $row['commission'];
        }

        return round($commissionTotal, 2);
    }

    // Add these new methods for financial reports
    public function financialReport(Request $request)
    {
        $driverId = $request->input('driver_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $reportData = [];
        
        if ($driverId && $dateFrom && $dateTo) {
            // Get total sales and ACTUAL cash received from dispatches
            $salesData = Dispatch::where('driver_id', $driverId)
                ->whereBetween('dispatch_date', [$dateFrom, $dateTo])
                ->select(
                    DB::raw('SUM(total_sales_value) as total_sales'),
                    DB::raw('SUM(cash_received) as total_actual_cash_received'),
                    DB::raw('SUM(commission_total) as total_commission'),
                    DB::raw('SUM(expected_cash_after_deductions) as total_expected_after_deductions')
                )
                ->first();

            // Get total driver expenses from driver_expenses table
            $expensesData = DriverExpense::where('driver_id', $driverId)
                ->whereHas('dispatch', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('dispatch_date', [$dateFrom, $dateTo]);
                })
                ->select(DB::raw('SUM(amount) as total_expenses'))
                ->first();

            // Get total bank deposits
            $depositsData = BankDeposit::where('user_id', $driverId)
                ->whereBetween('deposit_date', [$dateFrom, $dateTo])
                ->select(DB::raw('SUM(amount) as total_deposits'))
                ->first();

            $driver = User::find($driverId);

            $totalActualCash = $salesData->total_actual_cash_received ?? 0;
            $totalCommission = $salesData->total_commission ?? 0;
            $totalExpenses = $expensesData->total_expenses ?? 0;
            $totalExpectedAfterDeductions = $salesData->total_expected_after_deductions ?? 0;
            
            // If expected_after_deductions is not available, calculate it
            if ($totalExpectedAfterDeductions == 0) {
                $totalExpectedAfterDeductions = $totalActualCash - $totalCommission - $totalExpenses;
            }
            
            $totalDeposits = $depositsData->total_deposits ?? 0;
            $shortageExcess = $totalExpectedAfterDeductions - $totalDeposits;

            $reportData = [
                'driver' => $driver,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'total_sales' => $salesData->total_sales ?? 0,
                'total_actual_cash_received' => $totalActualCash,
                'total_commission' => $totalCommission,
                'total_expenses' => $totalExpenses,
                'total_expected_after_deductions' => $totalExpectedAfterDeductions,
                'total_deposits' => $totalDeposits,
                'shortage_excess' => $shortageExcess
            ];
        }

        return view('admin.dispatches.financial-report', compact('drivers', 'reportData'));
    }

    public function financialDetails(Request $request, $driverId)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $driver = User::findOrFail($driverId);
        
        if (!$driver || $driver->role !== 'driver') {
            return redirect()->back()->with('error', 'Invalid driver selected.');
        }

        // Get all dispatches in date range
        $dispatches = Dispatch::where('driver_id', $driverId)
            ->whereBetween('dispatch_date', [$dateFrom, $dateTo])
            ->with(['items', 'expenses'])
            ->orderBy('dispatch_date', 'desc')
            ->orderBy('dispatch_no', 'desc')
            ->get();

        // Get all bank deposits in date range
        $deposits = BankDeposit::where('user_id', $driverId)
            ->whereBetween('deposit_date', [$dateFrom, $dateTo])
            ->orderBy('deposit_date', 'desc')
            ->get();

        // Calculate totals
        $totals = [
            'total_sales_value' => $dispatches->sum('total_sales_value'),
            'total_cash_received' => $dispatches->sum('cash_received'),
            'total_commission' => $dispatches->sum('commission_total'),
            'total_expenses' => $dispatches->sum(function($dispatch) {
                return $dispatch->expenses->sum('amount');
            }),
            'total_expected_after_deductions' => $dispatches->sum('expected_cash_after_deductions'),
            'total_deposits' => $deposits->sum('amount'),
            'total_balance_due' => $dispatches->sum('balance_due'),
            'driver_back_debt' => $driver->back_debt,
        ];

        $totals['calculated_shortage'] = $totals['total_expected_after_deductions'] - $totals['total_deposits'];

        return view('admin.dispatches.financial-details', compact(
            'driver', 'dispatches', 'deposits', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    public function history($driverId)
    {
        $driver = User::findOrFail($driverId);
        $dispatches = Dispatch::where('driver_id', $driverId)
            ->with('items', 'expenses')
            ->latest()
            ->paginate(10);

        return view('admin.dispatches.history', compact('driver', 'dispatches'));
    }

    public function backDebtHistory($driverId)
{
    $driver = User::findOrFail($driverId);
    $history = \App\Models\DriverBackDebtTransaction::with(['dispatch', 'recordedBy'])
        ->where('driver_id', $driverId)
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    return view('admin.dispatches.back_debt_history', compact('driver', 'history'));
}
}