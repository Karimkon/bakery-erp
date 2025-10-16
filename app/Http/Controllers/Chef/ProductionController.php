<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Ingredient;
use App\Models\StockHistory; // ✅ Add this import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionController extends Controller
{
    public function index()
    {
        // Only THIS chef's productions
        $productions = Production::where('user_id', Auth::id())
            ->latest()->paginate(15);

        return view('chef.productions.index', compact('productions'));
    }

    public function create()
    {
        $ingredients = Ingredient::where('chef_id', Auth::id())
                            ->orderBy('name')
                            ->get();
        $products = config('bakery_products');
        return view('chef.productions.create', compact('ingredients', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'production_date' => 'required|date',
            'flour_kgs'       => 'required|numeric|min:0',
            'outputs'         => 'required|array',
            'outputs.*'       => 'nullable|integer|min:0',
            'ingredients'     => 'nullable|array',
            'ingredients.*'   => 'nullable|numeric|min:0',
        ]);

        $prices   = config('bakery_products');
        $yieldMin = config('bakery_yield.yield_min_per_bag', []);
        $flourEq  = config('bakery_yield.flour_equiv_bags_per_unit', []);

        $flourKgs = (float) $request->flour_kgs;
        $flourBags = $flourKgs / 50;
        $outputs   = $request->input('outputs', []);

        // Calculate total production value
        $totalValue = 0;
        foreach ($prices as $product => $price) {
            $qty = (int) ($outputs[$product] ?? 0);
            $totalValue += $qty * (int) $price;
        }

        // Variance logic
        $hasVariance = false;
        $notes = '';

        if (isset($outputs['buns'], $yieldMin['buns'])) {
            $minBuns = $flourBags * $yieldMin['buns'];
            if ((int)$outputs['buns'] < $minBuns) {
                $hasVariance = true;
                $notes .= "Buns below minimum yield ({$outputs['buns']} < {$minBuns}). ";
            }
        }

        $impliedBags = 0.0;
        foreach ($outputs as $product => $qty) {
            $eq = (float) ($flourEq[$product] ?? 0);
            if ($eq > 0) $impliedBags += ((int)$qty) * $eq;
        }

        if ($impliedBags > $flourBags + 0.01) {
            $hasVariance = true;
            $notes .= "Over flour: outputs imply ~" . number_format($impliedBags, 2) . " bags > recorded {$flourBags}. ";
        }

        // Save Production and Deduct Ingredients
        \DB::transaction(function () use ($request, $outputs, $totalValue, $hasVariance, $notes, $flourKgs) {
            $production = \App\Models\Production::create([
                'user_id'         => \Auth::id(),
                'production_date' => $request->production_date,
                'flour_bags'      => $flourKgs / 50,
                'total_value'     => $totalValue,
                'has_variance'    => $hasVariance ? 1 : 0,
                'variance_notes'  => trim($notes) ?: null,
                'buns'            => (int)($outputs['buns'] ?? 0),
                'small_breads'    => (int)($outputs['small_breads'] ?? 0),
                'big_breads'      => (int)($outputs['big_breads'] ?? 0),
                'donuts'          => (int)($outputs['donuts'] ?? 0),
                'half_cakes'      => (int)($outputs['half_cakes'] ?? 0),
                'block_cakes'     => (int)($outputs['block_cakes'] ?? 0),
                'slab_cakes'      => (int)($outputs['slab_cakes'] ?? 0),
                'birthday_cakes'  => (int)($outputs['birthday_cakes'] ?? 0),
            ]);

            // ✅✅✅ ENHANCED: Deduct ingredient usage and track in history
            $ingredientInputs = $request->input('ingredients', []);
            foreach ($ingredientInputs as $id => $qtyKg) {
                $qtyKg = (float) $qtyKg;
                if ($qtyKg <= 0) continue;

                $ingredient = \App\Models\Ingredient::findOrFail($id);

                if ($qtyKg > (float) $ingredient->stock) {
                    throw new \Exception("Not enough stock for {$ingredient->name}. Available: {$ingredient->stock} {$ingredient->unit}");
                }

                $cost = $qtyKg * (float) $ingredient->unit_cost;

                // Save ingredient usage record
                $production->ingredientUsages()->create([
                    'ingredient_id' => $ingredient->id,
                    'quantity'      => $qtyKg,
                    'unit'          => $ingredient->unit,
                    'cost'          => $cost,
                ]);

                // ✅ TRACK STOCK BEFORE DEDUCTING
                $stockBefore = (float) $ingredient->stock;
                
                // Deduct from chef's ingredient stock
                $ingredient->decrement('stock', $qtyKg);
                
                // Refresh to get updated stock
                $ingredient->refresh();
                $stockAfter = (float) $ingredient->stock;

                // ✅✅✅ RECORD IN STOCK HISTORY
                StockHistory::create([
                    'ingredient_id'   => $ingredient->id,
                    'chef_id'         => $ingredient->chef_id,
                    'production_id'   => $production->id,
                    'quantity_changed'=> -$qtyKg, // Negative because it's usage
                    'quantity_before' => $stockBefore,
                    'quantity_after'  => $stockAfter,
                    'transaction_type'=> 'usage',
                    'added_by'        => \Auth::id(),
                    'notes'           => "Used {$qtyKg} {$ingredient->unit} of {$ingredient->name} in production on " . 
                                        \Carbon\Carbon::parse($request->production_date)->format('d M Y')
                ]);

                // ✅ Reset modal flag so admin sees the notification
                session()->forget('seen_stock_modal');
            }

            // Update bakery stock per product
            foreach ($outputs as $product => $qty) {
                $qty = (int) $qty;

                if ($qty <= 0) continue;

                $validProducts = [
                    'buns', 'small_breads', 'big_breads', 'donuts',
                    'half_cakes', 'block_cakes', 'slab_cakes', 'birthday_cakes30k', 'quarter_breads', 
                    'birthday_cakes50k', 'mandazis', 'musiba_tayi', 'scornes', 'chapatys', 
                    'toasted_bread', 'spring_donuts', 'cream_donuts', 'cinnamon_rolls'
                ];

                if (!in_array($product, $validProducts)) continue;

                $stock = \App\Models\BakeryStock::firstOrCreate(
                    ['product' => $product],
                    ['quantity' => 0]
                );

                \Log::info("Updating stock for {$product}", [
                    'previous' => $stock->quantity,
                    'added' => $qty
                ]);

                $stock->increment('quantity', $qty);
            }
        });

        return redirect()->route('chef.productions.index')
            ->with('success', 'Production recorded successfully. Ingredient usage tracked.');
    }

    public function show(Production $production)
    {
        if ($production->user_id !== Auth::id()) {
            abort(403);
        }

        $production->load('ingredientUsages.ingredient');
        return view('chef.productions.show', compact('production'));
    }
}