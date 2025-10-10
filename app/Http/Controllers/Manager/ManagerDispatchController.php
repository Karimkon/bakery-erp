<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    // Keep only latest dispatch per driver
    $driverLatest = [];
    foreach ($allDispatches as $dispatch) {
        if (!isset($driverLatest[$dispatch->driver_id])) {
            $driverLatest[$dispatch->driver_id] = $dispatch;
        }
    }

    // Compute accurate balance_due for each dispatch
    foreach ($driverLatest as $driverId => $dispatch) {
        $remainingInventoryValue = 0;
        $creditSalesValue = 0;
        
        foreach ($dispatch->items as $item) {
            $remainingInventoryValue += $item->remaining_qty * $item->unit_price;
            $creditSalesValue += $item->sold_credit * $item->unit_price;
        }
        
        // Balance due = Remaining inventory value + Credit sales value
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

            if ($sold > $available) {
                return back()->withErrors(["items.$product.sold_cash" =>
                    "Sold ($sold) cannot exceed Opening+Dispatched ($available) for $product"])->withInput();
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

        $threshold = (float) config('commissions.threshold', 1_000_000);
        $basis = config('commissions.threshold_basis', 'available'); // available|dispatched|sold

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


public function edit($id)
{
    $dispatch = Dispatch::with('items','driver')->findOrFail($id);

    // Only get users with role 'driver'
    $drivers = User::where('role', 'driver')->get();

    $products = config('bakery_products'); // <-- fixed
    $openings = $this->computeOpenings((int)$dispatch->driver_id, $dispatch->dispatch_date, $dispatch->id);

    return view('manager.dispatches.edit', compact('dispatch','drivers','products','openings'));
}


public function update(Request $request, $id)
{
    $dispatch = Dispatch::with('items', 'driver')->findOrFail($id);
    $products = config('bakery_products');

    $request->validate([
        'items' => ['required', 'array'],
        'notes' => ['nullable', 'string'],
        'cash_received' => ['nullable', 'numeric', 'min:0'],
        'driver_signature' => ['nullable', 'string'],
    ]);

    DB::transaction(function () use ($dispatch, $request, $products) {
        $totalItemsSold = 0;
        $totalSalesValue = 0;
        $calculatedCashReceived = 0;
        $creditSalesValue = 0;
        $remainingInventoryValue = 0;

        foreach ($request->items as $product => $data) {
            if (!array_key_exists($product, $products)) continue;

            $item = $dispatch->items->firstWhere('product', $product);
            if (!$item) continue;

            $newSoldCash   = (int) ($data['sold_cash'] ?? 0);
            $newSoldCredit = (int) ($data['sold_credit'] ?? 0);

            $maxSold = $item->opening_stock + $item->dispatched_qty;
            $totalSold = $newSoldCash + $newSoldCredit;

            if ($totalSold > $maxSold) {
                throw new \Exception("Total sold ($totalSold) cannot exceed available stock ($maxSold) for $product");
            }

            $remaining = $maxSold - $totalSold;
            $unitPrice = $products[$product];
            $lineTotal = $totalSold * $unitPrice;

            $item->update([
                'sold_cash'     => $newSoldCash,
                'sold_credit'   => $newSoldCredit,
                'sold_qty'      => $totalSold,
                'remaining_qty' => $remaining,
                'line_total'    => $lineTotal,
            ]);

            $totalItemsSold += $totalSold;
            $totalSalesValue += $lineTotal;
            $calculatedCashReceived += $newSoldCash * $unitPrice;
            $creditSalesValue += $newSoldCredit * $unitPrice;
            $remainingInventoryValue += $remaining * $unitPrice;
        }

        // Use actual cash received from manager, or calculated if not provided
        $actualCashReceived = $request->input('cash_received');
        if ($actualCashReceived === null || $actualCashReceived === '') {
            $actualCashReceived = $calculatedCashReceived;
        }

        // Balance due = Remaining inventory value + Credit sales value
        $balanceDue = $remainingInventoryValue + $creditSalesValue;

        // Compute commission
        $itemsArray = $dispatch->items->toArray();
        $commissionTotal = $this->computeCommissionStuff($itemsArray, $totalSalesValue);

        // Update driver's back debt if they paid more than their credit sales
        $driver = $dispatch->driver;
        if ($actualCashReceived > $creditSalesValue) {
            $overPayment = $actualCashReceived - $creditSalesValue;
            $driver->back_debt = max(0, $driver->back_debt - $overPayment);
            $driver->save();
        }

        $dispatch->update([
            'notes'             => $request->notes,
            'driver_signature'  => $request->driver_signature,
            'total_items_sold'  => $totalItemsSold,
            'total_sales_value' => $totalSalesValue,
            'cash_received'     => $actualCashReceived,
            'balance_due'       => $balanceDue,
            'commission_total'  => $commissionTotal,
        ]);
    });

    return redirect()->route('manager.dispatches.index')
                     ->with('success', 'Dispatch updated successfully.');
}



}
