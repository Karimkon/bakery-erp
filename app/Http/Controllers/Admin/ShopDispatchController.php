<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopStock;
use Illuminate\Http\Request;

class ShopDispatchController extends Controller
{
    /**
     * Show all dispatch records for the shop.
     */
    public function index()
    {
        $stocks = ShopStock::where('shop_name', 'Kampala Main Shop')
            ->orderBy('product_type')
            ->paginate(20);

        return view('admin.shop_dispatch.index', compact('stocks'));
    }

    /**
     * Show dispatch form.
     */
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

        return view('admin.shop_dispatch.create', compact('products'));
    }

    /**
     * Store a new dispatch entry (adds stock to the shop).
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_type' => 'required|string',
            'quantity'     => 'required|integer|min:1',
        ]);

        $stock = ShopStock::firstOrNew([
            'shop_name'    => 'Kampala Main Shop',
            'product_type' => $request->product_type,
        ]);

        if (!$stock->exists) {
            $stock->opening_stock = 0;
            $stock->sold = 0;
            $stock->remaining = 0;
        }

        $stock->dispatched += $request->quantity;
        $stock->remaining  += $request->quantity;
        $stock->save();

        return redirect()
            ->route('admin.shop-dispatch.index')
            ->with('success', "{$request->quantity} {$request->product_type} dispatched to Kampala Shop successfully.");
    }

    /**
     * Edit stock record (if admin wants to adjust).
     */
    public function edit(ShopStock $shopStock)
    {
        $products = [
            'buns','small_breads','big_breads','donuts',
            'half_cakes','block_cakes','slab_cakes','birthday_cakes'
        ];

        return view('admin.shop_dispatch.edit', compact('shopStock','products'));
    }

    /**
     * Update stock record.
     */
    public function update(Request $request, ShopStock $shopStock)
    {
        $request->validate([
            'product_type' => 'required|string',
            'opening_stock' => 'nullable|integer|min:0',
            'dispatched'    => 'nullable|integer|min:0',
            'sold'          => 'nullable|integer|min:0',
            'remaining'     => 'nullable|integer|min:0',
        ]);

        $shopStock->update($request->only(['product_type','opening_stock','dispatched','sold','remaining']));

        return redirect()
            ->route('admin.shop-dispatch.index')
            ->with('success', "Shop stock updated for {$shopStock->product_type}");
    }

    /**
     * Delete stock record (if needed).
     */
    public function destroy(ShopStock $shopStock)
    {
        $shopStock->delete();

        return redirect()
            ->route('admin.shop-dispatch.index')
            ->with('success', "Stock record deleted successfully.");
    }
}
