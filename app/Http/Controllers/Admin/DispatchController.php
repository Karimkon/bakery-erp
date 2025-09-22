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
        $dispatches = Dispatch::with('driver')
            ->latest('dispatch_date')
            ->paginate(20);

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

        DB::transaction(function () use ($request, $lines, $totalItemsSold, $totalSalesValue) {
            $dispatch = Dispatch::create([
                'driver_id'         => $request->driver_id,
                'dispatch_date'     => $request->dispatch_date,
                'notes'             => $request->notes,
                'total_items_sold'  => $totalItemsSold,
                'total_sales_value' => $totalSalesValue,
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

    DB::transaction(function () use ($request, $dispatch, $lines, $totalItemsSold, $totalSalesValue) {
        $dispatch->update([
            'driver_id'         => $request->driver_id,
            'dispatch_date'     => $request->dispatch_date,
            'notes'             => $request->notes,
            'total_items_sold'  => $totalItemsSold,
            'total_sales_value' => $totalSalesValue,
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

    public function openings(User $driver, $date)
    {
        if ($driver->role !== 'driver') {
            return response()->json(['error' => 'Invalid driver'], 422);
        }

        $openings = $this->computeOpenings($driver->id, $date);
        return response()->json($openings);
    }


}
