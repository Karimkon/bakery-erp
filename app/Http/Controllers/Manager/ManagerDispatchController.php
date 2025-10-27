<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\DriverExpense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BankDeposit;
use Illuminate\Support\Facades\Storage; 
use App\Models\DriverBackDebtTransaction;

class ManagerDispatchController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 20;
        $searchDriver = $request->input('driver');

        $query = Dispatch::with('driver', 'items')
            ->orderBy('dispatch_date', 'desc')
            ->orderBy('dispatch_no', 'desc');

        if ($searchDriver) {
            $query->whereHas('driver', fn($q) =>
                $q->where('name', 'like', "%$searchDriver%")
            );
        }

        $allDispatches = $query->get();

        $driverLatest = [];
        foreach ($allDispatches as $dispatch) {
            if (!isset($driverLatest[$dispatch->driver_id])) {
                $driverLatest[$dispatch->driver_id] = $dispatch;
            }
        }

        foreach ($driverLatest as $driverId => $dispatch) {
            $remainingInventoryValue = 0;
            $creditSalesValue = 0;
            
            foreach ($dispatch->items as $item) {
                $remainingInventoryValue += $item->remaining_qty * $item->unit_price;
                $creditSalesValue += $item->sold_credit * $item->unit_price;
            }
            
            $dispatch->balance_due = $remainingInventoryValue + $creditSalesValue;
        }

        $dispatches = collect($driverLatest)->values();

        $page = $request->input('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $dispatches->forPage($page, $perPage)->values(),
            $dispatches->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('manager.dispatches.index', [
            'dispatches'   => $paginated,
            'searchDriver' => $searchDriver,
        ]);
    }

    public function create()
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $products = config('bakery_products');

        return view('manager.dispatches.create', compact('drivers', 'products'));
    }

    public function store(Request $request)
    {
        $products = config('bakery_products');

        $request->validate([
            'driver_id'      => ['required', 'exists:users,id'],
            'dispatch_date'  => ['required', 'date'],
            'items'          => ['required', 'array'],
            'driver_signature' => ['nullable', 'string'],
        ]);

        if (!User::where('id', $request->driver_id)->where('role', 'driver')->exists()) {
            return back()->withErrors(['driver_id' => 'Selected user is not a driver'])->withInput();
        }

        $nextNo = (Dispatch::where('driver_id', $request->driver_id)
            ->where('dispatch_date', $request->dispatch_date)
            ->max('dispatch_no') ?? 0) + 1;

        $openings = $this->computeOpenings((int)$request->driver_id, $request->dispatch_date);

        $lines = [];
        $totalItemsSold = $totalSalesValue = $cashReceived = 0.0;

        foreach ($products as $product => $price) {
            $dispatched  = (int) data_get($request->items, "$product.dispatched_qty", 0);
            $soldCash    = (int) data_get($request->items, "$product.sold_cash", 0);
            $soldCredit  = (int) data_get($request->items, "$product.sold_credit", 0);
            $sold        = $soldCash + $soldCredit;
            $opening     = (int) ($openings[$product] ?? 0);
            $available   = $opening + $dispatched;

            if ($dispatched > $available) {
                return back()->withErrors(["items.$product.dispatched_qty" =>
                    "Cannot dispatch more ($dispatched) than available in bakery stock ($available) for $product."])->withInput();
            }

            $remaining = $available - $sold;
            $unitPrice = (float) $price;
            $lineTotal = $sold * $unitPrice;
            $cashReceived += $soldCash * $unitPrice;
            $totalItemsSold += $sold;
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

        $balanceDue = collect($lines)->sum(fn($line) => $line['remaining_qty'] * $line['unit_price']);
        $commissionTotal = $this->computeCommissionStuff($lines, $totalSalesValue);

        DB::transaction(function () use ($request, $lines, $totalItemsSold, $totalSalesValue, $commissionTotal, $cashReceived, $balanceDue, $nextNo) {
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
            ]);

            foreach ($lines as $row) {
                $row['dispatch_id'] = $dispatch->id;
                DispatchItem::create($row);

                $stock = \App\Models\BakeryStock::where('product', $row['product'])->first();
                if ($stock) {
                    if ($row['dispatched_qty'] > $stock->quantity) {
                        throw new \Exception("Not enough bakery stock for {$row['product']}");
                    }
                    $stock->decrement('quantity', $row['dispatched_qty']);
                }
            }
        });

        return redirect()->route('manager.dispatches.index')->with('success', 'Dispatch saved successfully.');
    }

    public function show(Dispatch $dispatch)
    {
        $dispatch->load('driver', 'items');
        return view('manager.dispatches.show', compact('dispatch'));
    }

    public function openings($driverId, $date)
    {
        $driver = User::find($driverId);
        if (!$driver || $driver->role !== 'driver') {
            return response()->json(['success' => false, 'error' => 'Invalid driver'], 422);
        }

        $openings = $this->computeOpenings($driver->id, $date);

        return response()->json(['success' => true, 'openings' => $openings]);
    }

    public function edit($id)
    {
        $dispatch = Dispatch::with('items', 'driver')->findOrFail($id);
        $drivers = User::where('role', 'driver')->get();
        $products = config('bakery_products');
        $openings = $this->computeOpenings((int)$dispatch->driver_id, $dispatch->dispatch_date->toDateString(), $dispatch->id);

        return view('manager.dispatches.edit', compact('dispatch', 'drivers', 'products', 'openings'));
    }


    /**
     * ✅ NEW: List of drivers who should NEVER have back debt
     */
    protected function getExcludedBackDebtDrivers()
    {
        return [
            'Ariah Nabadda',
            'Nakato Kampala'
            // Add more names here if needed
        ];
    }

    /**
     * ✅ NEW: Check if driver is excluded from back debt
     */
    protected function shouldExcludeFromBackDebt($driverId)
    {
        $driver = User::find($driverId);
        if (!$driver) return false;
        
        $excludedDrivers = $this->getExcludedBackDebtDrivers();
        return in_array($driver->name, $excludedDrivers);
    }


public function update(Request $request, $id)
    {
        $dispatch = Dispatch::with('items', 'driver', 'expenses')->findOrFail($id);
        $products = config('bakery_products');

        $request->validate([
            'items' => ['required', 'array'],
            'notes' => ['nullable', 'string'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'driver_signature' => ['nullable', 'string'],
            'expenses' => ['nullable', 'array'],
            'expenses.*.expense_type' => ['required_with:expenses.*.amount', 'string'],
            'expenses.*.amount' => ['required_with:expenses.*.expense_type', 'numeric', 'min:0'],
            'expenses.*.description' => ['nullable', 'string', 'max:500'],
            'expenses.*.receipt' => ['nullable', 'image', 'max:2048'],
        ]);

        $backDebtAdjustment = 0;
        $successMessage = 'Dispatch updated successfully.';

        // ✅ CHECK: Skip back debt for excluded drivers
        $isExcludedFromBackDebt = $this->shouldExcludeFromBackDebt($dispatch->driver_id);

        DB::transaction(function () use ($dispatch, $request, $products, &$backDebtAdjustment, &$successMessage, $isExcludedFromBackDebt) {
            // Store OLD values for back debt calculation
            $oldCashReceived = $dispatch->cash_received;
            $oldExpectedAfterDeductions = $dispatch->expected_cash_after_deductions;
            $oldDriverBackDebt = $dispatch->driver->back_debt;

            // Calculate NEW financial values
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

                $newSoldCash = (int) ($data['sold_cash'] ?? 0);
                $newSoldCredit = (int) ($data['sold_credit'] ?? 0);
                $maxSold = $item->opening_stock + $item->dispatched_qty;
                $totalSold = $newSoldCash + $newSoldCredit;

                if ($totalSold > $maxSold) {
                    throw new \Exception("Total sold ($totalSold) cannot exceed available stock ($maxSold) for $product");
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
                    'product' => $product,
                    'opening_stock' => $item->opening_stock,
                    'dispatched_qty' => $item->dispatched_qty,
                    'sold_qty' => $totalSold,
                    'unit_price' => $unitPrice,
                ];

                $item->update([
                    'sold_cash' => $newSoldCash,
                    'sold_credit' => $newSoldCredit,
                    'sold_qty' => $totalSold,
                    'remaining_qty' => $remaining,
                    'line_total' => $lineTotal,
                ]);
            }

            // Calculate commissions
            $commissionTotal = $this->computeCommissionStuff($itemsForCommission, $totalSalesValue);

            // Process expenses
            $totalDriverExpenses = $this->processExpenses($request, $dispatch);

            // Calculate expected cash after deductions
            $expectedAfterDeductions = $calculatedCashReceived - $commissionTotal - $totalDriverExpenses;

            // Get actual cash received
            $actualCashReceived = $this->getActualCashReceived($request, $expectedAfterDeductions, $dispatch);

            // ✅ MODIFIED: Only calculate back debt for NON-excluded drivers
            if (!$isExcludedFromBackDebt && $this->shouldAdjustBackDebt(
                $oldCashReceived, 
                $actualCashReceived, 
                $oldExpectedAfterDeductions, 
                $expectedAfterDeductions
            )) {
                $backDebtAdjustment = $this->calculateBackDebtAdjustment(
                    $oldCashReceived,
                    $oldExpectedAfterDeductions,
                    $actualCashReceived,
                    $expectedAfterDeductions,
                );

                if ($backDebtAdjustment != 0) {
                    $successMessage = 'Dispatch updated successfully. Back debt automatically adjusted.';
                }
            }

            // Update driver's back debt ONLY if adjustment is needed AND driver is NOT excluded
            $driver = User::find($dispatch->driver_id);
            $newBackDebt = $isExcludedFromBackDebt ? 0 : ($oldDriverBackDebt + $backDebtAdjustment);
            
            if (!$isExcludedFromBackDebt && $backDebtAdjustment != 0) {
                $driver->back_debt = $newBackDebt;
                $driver->save();

                // Record back debt transaction
                $this->recordBackDebtTransaction(
                    $driver->id,
                    $dispatch->id,
                    $oldDriverBackDebt,
                    $backDebtAdjustment,
                    $newBackDebt,
                    'dispatch_update',
                    "Dispatch #{$dispatch->dispatch_no} adjustment"
                );

                \Log::info("Back debt automatically adjusted", [
                    'driver_id' => $driver->id,
                    'dispatch_id' => $dispatch->id,
                    'old_back_debt' => $oldDriverBackDebt,
                    'adjustment' => $backDebtAdjustment,
                    'new_back_debt' => $newBackDebt
                ]);
            }

            // ✅ MODIFIED: Balance due calculation excludes back debt for specific drivers
            $balanceDue = $remainingInventoryValue + $creditSalesValue + ($isExcludedFromBackDebt ? 0 : $newBackDebt);

            // Update dispatch record
            $dispatch->update([
                'notes' => $request->notes,
                'driver_signature' => $request->driver_signature,
                'total_items_sold' => $totalItemsSold,
                'total_sales_value' => $totalSalesValue,
                'commission_total' => $commissionTotal,
                'driver_expenses' => $totalDriverExpenses,
                'cash_received' => $actualCashReceived,
                'expected_cash_after_deductions' => $expectedAfterDeductions,
                'balance_due' => $balanceDue,
            ]);
        });

        return redirect()->route('manager.dispatches.index')->with('success', $successMessage);
    }


/**
 * Calculate back debt adjustment based on cash differences
 */
protected function calculateBackDebtAdjustment(
    $oldCashReceived, 
    $oldExpectedAfterDeductions, 
    $newActualCashReceived, 
    $newExpectedAfterDeductions
) {
    // SIMPLEST FIX: Only track ACTUAL cash differences
    // Ignore expenses and commissions completely
    
    return $oldCashReceived - $newActualCashReceived;
}

/**
 * Record back debt transaction for audit trail
 */
protected function recordBackDebtTransaction(
    $driverId, 
    $dispatchId, 
    $previousBalance, 
    $amountChanged, 
    $newBalance, 
    $transactionType, 
    $description
) {
    \App\Models\DriverBackDebtTransaction::create([
        'driver_id' => $driverId,
        'dispatch_id' => $dispatchId,
        'previous_balance' => $previousBalance,
        'amount_changed' => $amountChanged,
        'new_balance' => $newBalance,
        'transaction_type' => $transactionType,
        'description' => $description,
        'recorded_by' => auth()->id(),
    ]);
}

/**
 * Process expenses (extracted from your existing code)
 */
protected function processExpenses(Request $request, Dispatch $dispatch)
{
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
                'dispatch_id' => $dispatch->id,
                'driver_id' => $dispatch->driver_id,
                'expense_type' => $expenseData['expense_type'],
                'amount' => $amount,
                'description' => $expenseData['description'] ?? null,
            ];

            // Handle receipt upload (your existing code)
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

    // Delete removed expenses
    $dispatch->expenses()
        ->whereNotIn('id', $existingExpenseIds)
        ->each(function ($expense) {
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $expense->delete();
        });

    return $totalDriverExpenses;
}



/**
 * ✅ SIMPLE: No restrictions on cash received - trust the manager
 */
protected function getActualCashReceived(Request $request, $expectedAfterDeductions, $dispatch)
{
    $actualCashInput = $request->input('cash_received');
    
    // If no cash entered, use expected amount
    if ($actualCashInput === null || $actualCashInput === '' || (float)$actualCashInput == 0) {
        return $expectedAfterDeductions;
    }
    
    $actualCashReceived = (float) $actualCashInput;
    
    // Only basic validation - no negative amounts
    if ($actualCashReceived < 0) {
        throw new \Exception("Cash received cannot be negative");
    }
    
    // ✅ No upper limit - trust the manager knows what they're doing
    return $actualCashReceived;
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
            'big_breads' => 200,
            'small_breads' => 100,
            'buns' => 200,
            'donuts' => 100,
            'half_cakes' => 100,
            'block_cakes' => 200,
            'slab_cakes' => 200,
            'birthday_cakes' => 200,
        ]);

        $threshold = (float) config('commissions.threshold', 1000000);
        $basis = config('commissions.threshold_basis', 'available');

        $basisValue = 0.0;
        foreach ($lines as $row) {
            $unit = (float) $row['unit_price'];
            $opening = (int) ($row['opening_stock'] ?? 0);
            $dispatched = (int) ($row['dispatched_qty'] ?? 0);
            $sold = (int) ($row['sold_qty'] ?? 0);
            
            $qtyForBasis = match ($basis) {
                'dispatched' => $dispatched,
                'sold' => $sold,
                default => $opening + $dispatched,
            };
            
            $basisValue += $qtyForBasis * $unit;
        }

        $multiplier = ($basisValue >= $threshold) ? 1.0 : 0.5;

        $commissionTotal = 0.0;
        foreach ($lines as &$row) {
            $rate = (float) ($rates[$row['product']] ?? 0);
            $perPiece = $rate * $multiplier;
            $row['commission'] = round($row['sold_qty'] * $perPiece, 2);
            $commissionTotal += $row['commission'];
        }

        return round($commissionTotal, 2);
    }

    public function history($driverId)
{
    $driver = \App\Models\User::findOrFail($driverId);

    $dispatches = \App\Models\Dispatch::where('driver_id', $driverId)
        ->with('items')
        ->latest()
        ->paginate(10);

    return view('manager.dispatches.history', compact('driver', 'dispatches'));
}

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

    return view('manager.dispatches.financial-report', compact('drivers', 'reportData'));
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

    return view('manager.dispatches.financial-details', compact(
        'driver', 'dispatches', 'deposits', 'totals', 'dateFrom', 'dateTo'
    ));
}

/**
 * ✅ Validate if back debt adjustment is actually needed
 */
protected function shouldAdjustBackDebt($oldCashReceived, $newActualCashReceived, $oldExpectedAfterDeductions, $newExpectedAfterDeductions)
{
    // If cash received didn't change AND expected amount didn't change significantly, no adjustment needed
    $cashChanged = abs($oldCashReceived - $newActualCashReceived) > 1; // More than 1 UGX difference
    $expectedChanged = abs($oldExpectedAfterDeductions - $newExpectedAfterDeductions) > 1;
    
    // Only adjust if cash received changed OR expected amount changed significantly
    return $cashChanged || $expectedChanged;
}

public function backDebtHistory($driverId)
{
    $driver = User::findOrFail($driverId);
    $history = \App\Models\DriverBackDebtTransaction::with(['dispatch', 'recordedBy'])
        ->where('driver_id', $driverId)
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    return view('manager.dispatches.back_debt_history', compact('driver', 'history'));
}

}