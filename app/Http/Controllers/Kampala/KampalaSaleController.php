<?php

namespace App\Http\Controllers\Kampala;

use App\Http\Controllers\Controller;
use App\Models\KampalaSale;
use App\Models\KampalaStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KampalaSaleController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->kampalaShop;
        $sales = KampalaSale::with('user')
            ->where('shop_id', $shop->id)
            ->latest()
            ->paginate(20);

        return view('kampala.sales.index', compact('sales'));
    }

    public function create()
    {
        $shop = Auth::user()->kampalaShop;
        $stocks = KampalaStock::where('shop_id', $shop->id)
            ->where('remaining', '>', 0)
            ->get()
            ->keyBy('product_type');

        $products = config('bakery_products');
        
        return view('kampala.sales.create', compact('stocks', 'products'));
    }

    public function store(Request $request)
    {
        $shop = Auth::user()->kampalaShop;

        $request->validate([
            'product_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,mobile_money',
            'notes' => 'nullable|string|max:255',
        ]);

        // Check stock
        $stock = KampalaStock::where('shop_id', $shop->id)
            ->where('product_type', $request->product_type)
            ->first();

        if (!$stock || $stock->remaining < $request->quantity) {
            return back()->with('error', 'Insufficient stock. Available: ' . ($stock->remaining ?? 0));
        }

        $unitPrice = $stock->unit_price;
        $totalPrice = $request->quantity * $unitPrice;

        try {
            DB::transaction(function () use ($shop, $request, $unitPrice, $totalPrice, $stock) {
                // Create sale
                KampalaSale::create([
                    'shop_id' => $shop->id,
                    'user_id' => Auth::id(),
                    'product_type' => $request->product_type,
                    'quantity' => $request->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'payment_method' => $request->payment_method,
                    'notes' => $request->notes,
                ]);

                // Update stock
                $stock->updateOnSale($request->quantity);
            });

            return redirect()->route('kampala.sales.index')
                ->with('success', 'Sale recorded successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error recording sale: ' . $e->getMessage());
        }
    }
}