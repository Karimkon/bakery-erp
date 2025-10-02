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

    // Get all dispatches with driver & items
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

    $dispatches = collect($driverLatest)->values();

    // Manual pagination
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
        $drivers = User::where('role','driver')->orderBy('name')->get();
        $products = config('bakery_products');

        return view('manager.dispatches.create', compact('drivers','products'));
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
            'driver_signature'  => $request->driver_signature, 
            'total_items_sold'  => $totalItemsSold,
            'total_sales_value' => $totalSalesValue,
            'commission_total'  => $commissionTotal,
            'cash_received' => $cashReceived, 
            'balance_due'       => $balanceDue,

        ]);

            foreach ($lines as $row) {
                $row['dispatch_id'] = $dispatch->id;
                DispatchItem::create($row);

                // ✅ Deduct bakery stock
                $stock = \App\Models\BakeryStock::where('product', $row['product'])->first();
                if ($stock) {
                    if ($row['dispatched_qty'] > $stock->quantity) {
                        throw new \Exception("Not enough bakery stock for {$row['product']}");
                    }
                    $stock->decrement('quantity', $row['dispatched_qty']);
                }
            }

        });

        return redirect()
            ->route('manager.dispatches.index')
            ->with('success', 'Dispatch saved successfully.');
    }

    public function show(Dispatch $dispatch)
    {
        $dispatch->load('driver','items');
        return view('manager.dispatches.show', compact('dispatch'));
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

    // Get the most recent dispatch BEFORE this date
    $lastDispatch = Dispatch::where('driver_id', $driverId)
        ->where('dispatch_date', '<', $date)  // strictly before the date
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



    // 🚫 No edit, update, destroy here for managers
}
