<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\KampalaShop;
use App\Models\KampalaDispatch;
use App\Models\KampalaStock;
use App\Models\BakeryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KampalaDispatchController extends Controller
{
    public function index(Request $request)
{
    $query = KampalaDispatch::with(['shop', 'manager'])
        ->latest();

    // Filter by shop
    if ($request->has('shop') && $request->shop) {
        $query->where('shop_id', $request->shop);
    }

    // Filter by status
    if ($request->has('status') && $request->status) {
        $query->where('status', $request->status);
    }

    // Filter by date range
    if ($request->has('from') && $request->from) {
        $query->whereDate('dispatch_date', '>=', $request->from);
    }

    if ($request->has('to') && $request->to) {
        $query->whereDate('dispatch_date', '<=', $request->to);
    }

    $dispatches = $query->paginate(20);
    $shops = KampalaShop::where('status', 'active')->get();

    return view('manager.kampala-dispatches.index', compact('dispatches', 'shops'));
}

    public function create()
    {
        $shops = KampalaShop::where('status', 'active')->get();
        $products = config('bakery_products');
        $bakeryStocks = BakeryStock::all()->keyBy('product');

        return view('manager.kampala-dispatches.create', compact('shops', 'products', 'bakeryStocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:kampala_shops,id',
            'dispatch_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_type' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Generate dispatch number
                $dispatchNo = 'KMP-' . date('Ymd') . '-' . str_pad(KampalaDispatch::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);

                // Calculate totals
                $totalItems = 0;
                $totalValue = 0;
                $products = config('bakery_products');

                foreach ($request->items as $item) {
                    $totalItems += $item['quantity'];
                    $totalValue += $item['quantity'] * ($products[$item['product_type']] ?? 0);
                }

                // Create dispatch
                $dispatch = KampalaDispatch::create([
                    'shop_id' => $request->shop_id,
                    'manager_id' => auth()->id(),
                    'dispatch_date' => $request->dispatch_date,
                    'dispatch_no' => $dispatchNo,
                    'total_items' => $totalItems,
                    'total_value' => $totalValue,
                    'notes' => $request->notes,
                    'status' => 'pending',
                ]);

                // Create dispatch items
                foreach ($request->items as $item) {
                    $unitPrice = $products[$item['product_type']] ?? 0;
                    $dispatch->items()->create([
                        'product_type' => $item['product_type'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'total_price' => $item['quantity'] * $unitPrice,
                        'notes' => $item['notes'] ?? null,
                    ]);

                    // Deduct from bakery stock
                    $bakeryStock = BakeryStock::where('product', $item['product_type'])->first();
                    if ($bakeryStock) {
                        if ($bakeryStock->quantity < $item['quantity']) {
                            throw new \Exception("Insufficient stock for {$item['product_type']}. Available: {$bakeryStock->quantity}");
                        }
                        $bakeryStock->decrement('quantity', $item['quantity']);
                    }
                }
            });

            return redirect()->route('manager.kampala-dispatches.index')
                ->with('success', 'Dispatch created successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error creating dispatch: ' . $e->getMessage());
        }
    }

    public function show(KampalaDispatch $kampalaDispatch)
    {
        $kampalaDispatch->load(['shop', 'manager', 'items', 'receiver']);
        return view('manager.kampala-dispatches.show', compact('kampalaDispatch'));
    }
}