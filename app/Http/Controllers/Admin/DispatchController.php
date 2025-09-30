<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class DispatchController extends Controller
{
 public function index(Request $request)
{
    $perPage = 20;
    $searchDriver = $request->input('driver');

    // 1️⃣ Get all dispatches with driver & items
    $query = Dispatch::with('driver', 'items')->orderBy('dispatch_date', 'desc')->orderBy('dispatch_no', 'desc');

    if ($searchDriver) {
        $query->whereHas('driver', fn($q) => $q->where('name', 'like', "%$searchDriver%"));
    }

    $allDispatches = $query->get();

    // 2️⃣ Keep only latest dispatch per driver
    $driverLatest = [];
    foreach ($allDispatches as $dispatch) {
        if (!isset($driverLatest[$dispatch->driver_id])) {
            $driverLatest[$dispatch->driver_id] = $dispatch;
        }
    }

    $dispatches = collect($driverLatest)->values();

    // 3️⃣ Compute Balance Due (Unsold Goods) per dispatch
    $dispatches->transform(function ($d) {
        $remainingInventoryValue = $d->items->sum(fn($i) => $i->remaining_qty * $i->unit_price);
        $d->balanceDue = $remainingInventoryValue;
        return $d;
    });

    // 4️⃣ Manual pagination
    $page = $request->input('page', 1);
    $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
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

        // verify selected user is actually a driver
        if (!User::where('id', $request->driver_id)->where('role','driver')->exists()) {
            return back()->withErrors(['driver_id' => 'Selected user is not a driver'])->withInput();
        }
        // determine next dispatch_no for this driver on this date
        $nextNo = (Dispatch::where('driver_id', $request->driver_id)
             ->where('dispatch_date', $request->dispatch_date)
             ->max('dispatch_no') ?? 0) + 1;


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
        $balanceDue = 0;
        foreach ($lines as $line) {
            $balanceDue += $line['remaining_qty'] * $line['unit_price'];
        }

        // ✅ compute commissions once, after building all lines
        $commissionTotal = $this->computeCommissionStuff($lines, $totalSalesValue);


        DB::transaction(function () use ($request, $lines, $totalItemsSold, $totalSalesValue, $commissionTotal, $cashReceived, $balanceDue, $nextNo) {
        $dispatch = Dispatch::create([
            'driver_id'         => $request->driver_id,
            'dispatch_date'     => $request->dispatch_date,
            'dispatch_no'       => $nextNo, 
            'notes'             => $request->notes,
            'total_items_sold'  => $totalItemsSold,
            'total_sales_value' => $totalSalesValue,
            'commission_total'  => $commissionTotal,
            'cash_received' => $cashReceived, 
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

 protected function computeOpenings(int $driverId, string $date, ?int $currentDispatchId = null): array
{
    $products = array_keys(config('bakery_products'));
    $openings = array_fill_keys($products, 0);

    // Only get the **most recent dispatch before this date**
    $lastDispatch = Dispatch::where('driver_id', $driverId)
        ->where('dispatch_date', '<=', $date)
        ->when($currentDispatchId, fn($q) => $q->where('id', '<>', $currentDispatchId))
        ->latest('dispatch_date')
        ->latest('dispatch_no')
        ->first();

    if ($lastDispatch) {
        $lastDispatch->load('items');
        foreach ($lastDispatch->items as $item) {
            $openings[$item->product] = $item->remaining_qty;
        }
    }

    return $openings;
}


    public function edit(Dispatch $dispatch)
{
    $openings = [];
    foreach (array_keys(config('bakery_products')) as $product) {
        $row = $dispatch->items->firstWhere('product', $product);
        $openings[$product] = $row?->opening_stock ?? 0;
    }


    $dispatch->load('items');

    $drivers  = User::where('role', 'driver')->orderBy('name')->get();
    $products = config('bakery_products');

    return view('admin.dispatches.edit', compact('dispatch','drivers','products','openings'));
}

public function update(Request $request, Dispatch $dispatch)
{
    $products = config('bakery_products');

    $request->validate([
        'driver_id'     => ['required','exists:users,id'],
        'dispatch_date' => ['required','date'],
        'items'         => ['required','array'],
        'cash_received' => ['nullable','numeric','min:0'],
        'balance_due'   => ['nullable','numeric'],
    ]);

    // verify driver
    if (!User::where('id',$request->driver_id)->where('role','driver')->exists()) {
        return back()->withErrors(['driver_id'=>'Selected user is not a driver'])->withInput();
    }

    DB::transaction(function() use ($request, $dispatch, $products) {

        // 1️⃣ Recompute openings from all previous dispatches
        $openings = $this->computeOpenings($request->driver_id, $request->dispatch_date, $dispatch->id);

        $lines = [];
        $totalItemsSold  = 0;
        $totalSalesValue = 0;
        $cashReceived    = 0;

        foreach ($products as $product => $price) {
            $opening    = (int) ($openings[$product] ?? 0);
            $dispatched = (int) data_get($request->items, "$product.dispatched_qty", 0);
            $soldCash   = (int) data_get($request->items, "$product.sold_cash", 0);
            $soldCredit = (int) data_get($request->items, "$product.sold_credit", 0);
            $sold       = $soldCash + $soldCredit;

            $available = $opening + $dispatched;
            if ($sold > $available) {
                throw new \Exception("Sold ($sold) cannot exceed Opening+Dispatched ($available) for $product");
            }

            $remaining = $available - $sold;
            $unitPrice = (float) $price;
            $lineTotal = $sold * $unitPrice;

            $cashReceived += $soldCash * $unitPrice; // ✅ cash from sold_cash only
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

        // 2️⃣ Compute commissions
        $commissionTotal = $this->computeCommissionStuff($lines, $totalSalesValue);


         // ✅ FIXED: Balance Due = Value of remaining goods with driver  
        $balanceDue = 0;
        foreach ($lines as $line) {
            $balanceDue += $line['remaining_qty'] * $line['unit_price'];
        }
        
            // Use form input for cash_received if provided, otherwise use calculated value
        $finalCashReceived = $request->filled('cash_received') 
            ? (float) $request->cash_received 
            : $cashReceived;


        $dispatch->update([
            'driver_id'         => $request->driver_id,
            'dispatch_date'     => $request->dispatch_date,
            'notes'             => $request->notes,
            'total_items_sold'  => $totalItemsSold,
            'total_sales_value' => $totalSalesValue,
            'commission_total'  => $commissionTotal,
            'cash_received'     => $cashReceived, // ✅ recomputed
            'balance_due'       => $balanceDue,   // ✅ recomputed
        ]);

        // 4️⃣ Replace old items (avoid duplicates)
        $dispatch->items()->delete();
        foreach ($lines as $row) {
            $row['dispatch_id'] = $dispatch->id;
            DispatchItem::create($row);
        }

    }); // end transaction

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