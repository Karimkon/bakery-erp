<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\ShopStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
    $configProducts = config('bakery_products', []);

    // Fetch all stocks for the bakery shop
    $stocks = ShopStock::where('shop_name', 'Bakery Main Shop')
        ->orderBy('product_type')
        ->get()
        ->keyBy('product_type'); // This uses product_type as the key

    // Build product list from config and merge with stock data
    $products = [];
    foreach ($configProducts as $key => $price) {
        $label = ucwords(str_replace('_', ' ', $key));
        $products[$key] = $label;
        
        // Ensure the stock entry exists for this product type
        if (!$stocks->has($key)) {
            // Create a dummy stock entry for products that don't exist in stock yet
            $stocks[$key] = (object)[
                'product_type' => $key,
                'unit_price' => $price, // Use price from config
                'remaining' => 0,
                'sold' => 0
            ];
        } else {
            // Update the unit_price from config if it's different
            $stocks[$key]->unit_price = $price;
        }
    }

    return view('sales.sales.create', compact('products', 'stocks', 'configProducts'));
}

    public function store(Request $request)
    {
        $validated = $request->validate(array(
            'product_type'   => 'required|string',
            'quantity'       => 'required|integer|min:1',
            'unit_price'     => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,momo',
            'notes'          => 'nullable|string|max:255',
        ));

        $stock = ShopStock::where('shop_name', 'Bakery Main Shop')
            ->where('product_type', $request->product_type)
            ->first();

        $available = 0;
        if ($stock) {
            $available = $stock->remaining;
        }

        if (!$stock || $stock->remaining < $request->quantity) {
            if ($request->ajax()) {
                return response()->json(array(
                    'error' => 'Not enough stock. Available: ' . $available
                ), 422);
            }
            return back()->with('error', 'Not enough stock. Available: ' . $available);
        }

        $stock->sold = $stock->sold + $request->quantity;
        $stock->remaining = $stock->remaining - $request->quantity;
        $stock->save();

        $validated['total_price'] = $validated['unit_price'] * $validated['quantity'];
        $validated['user_id'] = Auth::id();

        $sale = Sale::create($validated);

        if ($request->ajax()) {
            return response()->json(array(
                'success'   => 'Sale of ' . $sale->quantity . ' ' . $sale->product_type . ' recorded.',
                'remaining' => $stock->remaining,
                'product'   => $sale->product_type,
                'id'        => $sale->id,
            ));
        }

        return redirect()->route('sales.sales.index')
            ->with('success', 'Sale recorded. Remaining stock: ' . $stock->remaining);
    }

    public function receipt($id)
    {
        $sale = Sale::with('user')->findOrFail($id);
        return view('sales.sales.receipt', compact('sale'));
    }

    public function dailySummary()
    {
        $userId = auth()->id();
        $today = now()->toDateString();

        $sales = Sale::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->get();

        $totalCash = 0;
        $totalMomo = 0;
        $totalAll = 0;

        foreach ($sales as $sale) {
            if ($sale->payment_method == 'cash') {
                $totalCash = $totalCash + $sale->total_price;
            }
            if ($sale->payment_method == 'momo') {
                $totalMomo = $totalMomo + $sale->total_price;
            }
            $totalAll = $totalAll + $sale->total_price;
        }

        return view('sales.sales.summary', compact('sales', 'totalCash', 'totalMomo', 'totalAll', 'today'));
    }

    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);
        return view('sales.sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $this->authorize('update', $sale);

        $configProducts = config('products');
        if (!$configProducts || !is_array($configProducts)) {
            $configProducts = array();
        }

        $stocks = ShopStock::where('shop_name', 'Bakery Main Shop')
            ->orderBy('product_type')
            ->get()
            ->keyBy('product_type');

        $products = array();
        foreach ($stocks as $key => $stock) {
            $label = ucwords(str_replace('_', ' ', $key));
            $products[$key] = $label;
        }

        return view('sales.sales.edit', compact('sale', 'products', 'stocks'));
    }

    public function update(Request $request, Sale $sale)
    {
        $this->authorize('update', $sale);

        $validated = $request->validate(array(
            'product_type'   => 'required|string',
            'quantity'       => 'required|integer|min:1',
            'unit_price'     => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,momo',
            'notes'          => 'nullable|string|max:255',
        ));

        $validated['total_price'] = $validated['unit_price'] * $validated['quantity'];
        $sale->update($validated);

        return redirect()
            ->route('sales.sales.index')
            ->with('success', 'Sale updated successfully.');
    }

    public function destroy(Sale $sale)
    {
        $this->authorize('delete', $sale);

        $stock = ShopStock::where('shop_name', 'Bakery Main Shop')
            ->where('product_type', $sale->product_type)
            ->first();

        if ($stock) {
            $stock->sold = $stock->sold - $sale->quantity;
            $stock->remaining = $stock->remaining + $sale->quantity;
            $stock->save();
        }

        $sale->delete();

        return redirect()
            ->route('sales.sales.index')
            ->with('success', 'Sale deleted and stock restored.');
    }
}