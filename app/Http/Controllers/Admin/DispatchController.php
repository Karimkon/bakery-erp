<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
 
    public function index()
{
    $dispatches = Dispatch::with('driver', 'items')->latest('dispatch_date')->paginate(20);

    // Transform EACH item in the current page
    foreach ($dispatches as $d) {
    $d->total_sales_value = $d->items->sum(fn($i) => $i->sold_qty * $i->unit_price);
    $d->cash_received     = $d->items->sum(fn($i) => $i->sold_cash * $i->unit_price);
    $d->balance_due       = $d->total_sales_value - $d->cash_received;
}

    return view('admin.dispatches.index', compact('dispatches'));
}


    public function create()
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $products = config('bakery_products'); // key => price

        return view('admin.dispatches.create', compact('drivers', 'products'));
    }

    public function store(Request $request)
    {
        $products = config('bakery_products');

        $request->validate([
            'driver_id'      => ['required','exists:users,id'],
            'dispatch_date'  => ['required','date'],
            'items'          => ['required','array'],
        ]);

        // store() — after $request->validate(...)
        $exists = Dispatch::where('driver_id', $request->driver_id)
                        ->where('dispatch_date', $request->dispatch_date)
                        ->exists();

        if ($exists) {
            return back()
                ->withErrors(['dispatch_date' => 'A dispatch for this driver on that date already exists. Edit the existing dispatch instead.'])
                ->withInput();
        }

        // verify selected user is actually a driver
        $isDriver = User::where('id', $request->driver_id)->where('role','driver')->exists();
        if (!$isDriver) {
            return back()->withErrors(['driver_id' => 'Selected user is not a driver'])->withInput();
        }

        // Helper: compute opening from previous day record
        $openings = $this->computeOpenings(
            (int)$request->driver_id,
            $request->dispatch_date
        );

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

            // 💡 here: only cash sales count as "cash received"
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

        $balanceDue = $totalSalesValue - $cashReceived;

        // ✅ compute commissions once, after building all lines
        $commissionTotal = $this->computeCommissionStuff($lines, $totalSalesValue);


        DB::transaction(function () use ($request, $lines, $totalItemsSold, $totalSalesValue, $commissionTotal, $cashReceived, $balanceDue) {
        $dispatch = Dispatch::create([
            'driver_id'         => $request->driver_id,
            'dispatch_date'     => $request->dispatch_date,
            'notes'             => $request->notes,
            'total_items_sold'  => $totalItemsSold,
            'total_sales_value' => $totalSalesValue,
            'commission_total'  => $commissionTotal,
            'cash_received'     => $cashReceived,
            'balance_due'       => $balanceDue,
        ]);


            foreach ($lines as $row) {
                $row['dispatch_id'] = $dispatch->id;
                DispatchItem::create($row);
            }
        });

        return redirect()
            ->route('admin.dispatches.index')
            ->with('success', 'Dispatch saved successfully.');
    }

    public function show(Dispatch $dispatch)
    {
        $dispatch->load('driver', 'items');

        return view('admin.dispatches.show', compact('dispatch'));
    }

    protected function computeOpenings(int $driverId, string $date): array
    {
        $prev = Dispatch::where('driver_id', $driverId)
            ->where('dispatch_date', '<', $date)
            ->orderByDesc('dispatch_date')
            ->first();

        if (!$prev) {
            $open = [];
            foreach (config('bakery_products') as $product => $price) {
                $open[$product] = 0;
            }
            return $open;
        }

        $open = [];
        $prev->load('items');
        foreach ($prev->items as $item) {
            $open[$item->product] = (int) $item->remaining_qty;
        }

        foreach (array_keys(config('bakery_products')) as $product) {
            if (!array_key_exists($product, $open)) {
                $open[$product] = 0;
            }
        }

        return $open;
    }

    public function edit(Dispatch $dispatch)
{
    $dispatch->load('items');

    $drivers  = User::where('role', 'driver')->orderBy('name')->get();
    $products = config('bakery_products');

    return view('admin.dispatches.edit', compact('dispatch','drivers','products'));
}

public function update(Request $request, Dispatch $dispatch)
{
    $products = config('bakery_products');

    $request->validate([
        'driver_id'      => ['required','exists:users,id'],
        'dispatch_date'  => ['required','date'],
        'items'          => ['required','array'],
    ]);

    if (!User::where('id',$request->driver_id)->where('role','driver')->exists()) {
        return back()->withErrors(['driver_id'=>'Selected user is not a driver'])->withInput();
    }

    $lines = [];
    $totalItemsSold = 0;
    $totalSalesValue = 0.0;
    $cashReceived = 0.0;

    foreach ($products as $product => $price) {
        $opening    = (int) data_get($request->items, "$product.opening_stock", 0);
        $dispatched = (int) data_get($request->items, "$product.dispatched_qty", 0);
        $soldCash   = (int) data_get($request->items, "$product.sold_cash", 0);
        $soldCredit = (int) data_get($request->items, "$product.sold_credit", 0);
        $sold       = $soldCash + $soldCredit;

        $available = $opening + $dispatched;
        if ($sold > $available) {
            return back()
                ->withErrors(["items.$product.sold_cash" => "Sold ($sold) cannot exceed Opening+Dispatched ($available) for $product"])
                ->withInput();
        }

        $remaining = $available - $sold;
        $unitPrice = (float) $price;
        $lineTotal = $sold * $unitPrice;
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
    $balanceDue = $totalSalesValue - $cashReceived;

    $commissionTotal = $this->computeCommissionStuff($lines, $totalSalesValue);

    DB::transaction(function () use ($request, $dispatch, $lines, $totalItemsSold, $totalSalesValue, $commissionTotal, $cashReceived, $balanceDue) {
        $dispatch->update([
            'driver_id'         => $request->driver_id,
            'dispatch_date'     => $request->dispatch_date,
            'notes'             => $request->notes,
            'total_items_sold'  => $totalItemsSold,
            'total_sales_value' => $totalSalesValue,
            'commission_total'  => $commissionTotal,
            'cash_received'     => $cashReceived,
            'balance_due'       => $balanceDue,
        ]);



        // delete old rows and replace with updated
        $dispatch->items()->delete();
        foreach ($lines as $row) {
            $row['dispatch_id'] = $dispatch->id;
            \App\Models\DispatchItem::create($row);
        }
    });

    return redirect()->route('admin.dispatches.show',$dispatch->id)
        ->with('success','Dispatch updated successfully.');
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




    protected function computeCommissionStuff(array &$lines, float $totalSalesValue): float
{
    // fallbacks so it never crashes if config is missing
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
    $basis     = config('commissions.threshold_basis', 'available'); // available|dispatched|sold

    // 1) compute basis value (UGX)
    $basisValue = 0.0;
    foreach ($lines as $row) {
        $unit = (float) $row['unit_price'];
        $opening    = (int) ($row['opening_stock'] ?? 0);
        $dispatched = (int) ($row['dispatched_qty'] ?? 0);
        $sold       = (int) ($row['sold_qty'] ?? 0);

        $qtyForBasis = match ($basis) {
            'dispatched' => $dispatched,
            'sold'       => $sold,
            default      => $opening + $dispatched, // 'available'
        };

        $basisValue += $qtyForBasis * $unit;
    }

    // 2) decide full vs half rate
    $multiplier = ($basisValue >= $threshold) ? 1.0 : 0.5;

    // 3) per-line commission AND return total
    $commissionTotal = 0.0;
    foreach ($lines as &$row) {
        $rate = (float) ($rates[$row['product']] ?? 0);
        $perPiece = $rate * $multiplier;
        $row['commission'] = round($row['sold_qty'] * $perPiece, 2);
        $commissionTotal  += $row['commission'];
    }
    unset($row);

    return round($commissionTotal, 2);
}

}
