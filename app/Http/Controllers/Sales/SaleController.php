<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\ShopStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('sales.sales.index', compact('sales'));
    }

    public function create()
    {
        $products = [
            'buns'          => 'Buns',
            'small_breads'  => 'Small Breads',
            'big_breads'    => 'Big Breads',
            'donuts'        => 'Donuts',
            'half_cakes'    => 'Half Cakes',
            'block_cakes'   => 'Block Cakes',
            'slab_cakes'    => 'Slab Cakes',
            'birthday_cakes'=> 'Birthday Cakes',
        ];

        $stocks = \App\Models\ShopStock::where('shop_name','Kampala Main Shop')->get()
        ->keyBy('product_type'); // quick lookup by product_type

        return view('sales.sales.create', compact('products','stocks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_type'   => 'required|string',
            'quantity'       => 'required|integer|min:1',
            'unit_price'     => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,momo',
            'notes'          => 'nullable|string|max:255',
        ]);

        $stock = ShopStock::where('shop_name', 'Kampala Main Shop')
            ->where('product_type', $request->product_type)
            ->first();

        if (!$stock || $stock->remaining < $request->quantity) {
            if ($request->ajax()) {
                return response()->json(['error' => "Not enough stock. Available: " . ($stock->remaining ?? 0)], 422);
            }
            return back()->with('error', "Not enough stock. Available: " . ($stock->remaining ?? 0));
        }

        // Deduct from stock
        $stock->sold      += $request->quantity;
        $stock->remaining -= $request->quantity;
        $stock->save();

        // Save sale
        $validated['total_price'] = $validated['unit_price'] * $validated['quantity'];
        $validated['user_id']     = Auth::id();

        $sale = Sale::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success'   => "Sale of {$sale->quantity} {$sale->product_type} recorded.",
                'remaining' => $stock->remaining,
                'product'   => $sale->product_type,
            ]);
        }

        return redirect()->route('sales.sales.index')
            ->with('success', "Sale recorded. Remaining stock: {$stock->remaining}");
    }


    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);
        return view('sales.sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $this->authorize('update', $sale);

        $products = [
            'buns'          => 'Buns',
            'small_breads'  => 'Small Breads',
            'big_breads'    => 'Big Breads',
            'donuts'        => 'Donuts',
            'half_cakes'    => 'Half Cakes',
            'block_cakes'   => 'Block Cakes',
            'slab_cakes'    => 'Slab Cakes',
            'birthday_cakes'=> 'Birthday Cakes',
        ];

        return view('sales.sales.edit', compact('sale', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $this->authorize('update', $sale);

        $validated = $request->validate([
            'product_type'   => 'required|string',
            'quantity'       => 'required|integer|min:1',
            'unit_price'     => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,momo',
            'notes'          => 'nullable|string|max:255',
        ]);

        // Update totals
        $validated['total_price'] = $validated['unit_price'] * $validated['quantity'];
        $sale->update($validated);

        return redirect()
            ->route('sales.sales.index')
            ->with('success', "Sale updated successfully.");
    }

    public function destroy(Sale $sale)
    {
        $this->authorize('delete', $sale);

        // Optionally return stock if sale deleted
        $stock = ShopStock::where('shop_name','Kampala Main Shop')
            ->where('product_type',$sale->product_type)
            ->first();

        if ($stock) {
            $stock->sold      -= $sale->quantity;
            $stock->remaining += $sale->quantity;
            $stock->save();
        }

        $sale->delete();

        return redirect()
            ->route('sales.sales.index')
            ->with('success', 'Sale deleted and stock restored.');
    }
}
