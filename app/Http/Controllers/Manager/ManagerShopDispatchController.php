<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ShopStock;
use Illuminate\Http\Request;
use App\Models\BakeryStock;
use Illuminate\Support\Facades\DB;

class ManagerShopDispatchController extends Controller
{
    public function index()
    {
        $stocks = ShopStock::where('shop_name', 'Bakery Main Shop')
            ->orderBy('product_type')
            ->paginate(20);

        return view('manager.shop_dispatch.index', compact('stocks'));
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
            'birthday_cakes50'=> 'Birthday Cakes 50,000',
            'birthday_cakes30'=> 'Birthday Cakes 30,000',
            'quarter_breads'=> 'Quarter Breads',
            'mandazis'      => 'Mandazis',
        ];

        return view('manager.shop_dispatch.create', compact('products'));
    }

public function store(Request $request)
{
    $request->validate([
        'product_type' => 'required|string',
        'quantity'     => 'required|integer|min:1',
    ]);

    DB::beginTransaction();
    try {
        // 1️⃣ Get bakery stock for that product
        $bakeryStock = BakeryStock::where('product', $request->product_type)->first();

        if (!$bakeryStock) {
            throw new \Exception("No bakery stock record found for {$request->product_type}. Please add it first.");
        }

        // 2️⃣ Ensure enough stock exists
        if ($bakeryStock->quantity < $request->quantity) {
            throw new \Exception("Not enough bakery stock for {$request->product_type}. Available: {$bakeryStock->quantity}, trying to dispatch: {$request->quantity}");
        }

        // 3️⃣ Deduct from bakery stock
        $bakeryStock->decrement('quantity', $request->quantity);

        // 4️⃣ Update or create shop stock record
        $shopStock = ShopStock::firstOrNew([
            'shop_name'    => 'Bakery Main Shop',
            'product_type' => $request->product_type,
        ]);

        if (!$shopStock->exists) {
            $shopStock->opening_stock = 0;
            $shopStock->sold = 0;
            $shopStock->remaining = 0;
            $shopStock->dispatched = 0;
        }

        $shopStock->dispatched += $request->quantity;
        $shopStock->remaining  += $request->quantity;
        $shopStock->save();

        DB::commit();

        return redirect()
            ->route('manager.shop-dispatch.index')
            ->with('success', "{$request->quantity} {$request->product_type} dispatched to Bakery Shop successfully. Bakery stock updated.");
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()
            ->back()
            ->with('error', $e->getMessage());
    }
}


    public function edit(ShopStock $shopStock)
    {
        $products = [
            'buns','small_breads','big_breads','donuts',
            'half_cakes','block_cakes','slab_cakes','birthday_cakes'
        ];

        return view('manager.shop_dispatch.edit', compact('shopStock','products'));
    }

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
            ->route('manager.shop-dispatch.index')
            ->with('success', "Shop stock updated for {$shopStock->product_type}");
    }

    public function destroy(ShopStock $shopStock)
    {
        $shopStock->delete();

        return redirect()
            ->route('manager.shop-dispatch.index')
            ->with('success', "Stock record deleted successfully.");
    }
}
