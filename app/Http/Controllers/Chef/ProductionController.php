<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionController extends Controller
{
    public function index()
    {
        // Only THIS chef’s productions
        $productions = Production::where('user_id', Auth::id())
            ->latest()->paginate(15);

        return view('chef.productions.index', compact('productions'));
    }

    public function create()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        $products = config('bakery_products'); // 👈 defined in config/bakery_products.php
        return view('chef.productions.create', compact('ingredients', 'products'));
    }

    public function store(Request $request)
{
    // 1) Validate inputs
    $request->validate([
        'production_date' => 'required|date',
        'flour_bags'      => 'required|numeric|min:0',
        'outputs'         => 'required|array',
        'outputs.*'       => 'nullable|integer|min:0',
        'ingredients'     => 'nullable|array',
        'ingredients.*'   => 'nullable|numeric|min:0',
    ]);

    $prices   = config('bakery_products'); // prices you already have
    $yieldMin = config('bakery_yield.yield_min_per_bag', []);
    $flourEq  = config('bakery_yield.flour_equiv_bags_per_unit', []);

    $flourBags = (float) $request->flour_bags;
    $outputs   = $request->input('outputs', []);

    // 2) Total production value
    $totalValue = 0;
    foreach ($prices as $product => $price) {
        $qty = (int) ($outputs[$product] ?? 0);
        $totalValue += ($qty * (int) $price);
    }

    // 3) Variance logic
    $hasVariance = false;
    $notes = '';

    // (a) Minimum yield: buns >= 150 * bags (lower bound only; more is allowed)
    if (isset($outputs['buns'], $yieldMin['buns'])) {
        $minBuns = $flourBags * $yieldMin['buns'];
        if ((int)$outputs['buns'] < $minBuns) {
            $hasVariance = true;
            $notes .= "Buns below minimum yield ({$outputs['buns']} < {$minBuns}). ";
        }
    }

    // (b) Flour equivalence: outputs should NOT imply more flour than recorded
    //     (This is not a cap on “how much above 150”; it simply prevents claiming
    //      unrealistic output with too little flour recorded.)
    $impliedBags = 0.0;
    foreach ($outputs as $product => $qty) {
        $eq = (float) ($flourEq[$product] ?? 0);
        if ($eq > 0) {
            $impliedBags += ((int)$qty) * $eq;
        }
    }
    // Small tolerance to avoid float rounding noise
    if ($impliedBags > $flourBags + 0.01) {
        $hasVariance = true;
        $notes .= "Over flour: outputs imply ~" . number_format($impliedBags, 2) . " bags > recorded {$flourBags}. ";
    }

    // 4) Create the production + ingredient usages (atomic)
    \DB::transaction(function () use ($request, $outputs, $totalValue, $hasVariance, $notes) {
        // Save production
        $production = Production::create([
            'user_id'         => \Auth::id(),
            'production_date' => $request->production_date,
            'flour_bags'      => $request->flour_bags,
            'total_value'     => $totalValue,
            'has_variance'    => $hasVariance ? 1 : 0,
            'variance_notes'  => trim($notes) ?: null,

            // Keep your existing columns in schema:
            'buns'            => (int)($outputs['buns'] ?? 0),
            'small_breads'    => (int)($outputs['small_breads'] ?? 0),
            'big_breads'      => (int)($outputs['big_breads'] ?? 0),
            'donuts'          => (int)($outputs['donuts'] ?? 0),
            'half_cakes'      => (int)($outputs['half_cakes'] ?? 0),
            'block_cakes'     => (int)($outputs['block_cakes'] ?? 0),
            'slab_cakes'      => (int)($outputs['slab_cakes'] ?? 0),
            'birthday_cakes'  => (int)($outputs['birthday_cakes'] ?? 0),
        ]);

        // Ingredients come as: ingredients[ingredient_id] = quantity
        $ingredientInputs = $request->input('ingredients', []);
        if (!empty($ingredientInputs)) {
            foreach ($ingredientInputs as $id => $qty) {
                $qty = (float) $qty;
                if ($qty <= 0) continue;

                $ingredient = \App\Models\Ingredient::findOrFail($id);

                // Guard: prevent using more than in stock
                if ($qty > (float) $ingredient->stock) {
                    throw new \Exception("Not enough stock for {$ingredient->name}. Available: {$ingredient->stock} {$ingredient->unit}");
                }

                $cost = $qty * (float) $ingredient->unit_cost;

                $production->ingredientUsages()->create([
                    'ingredient_id' => $ingredient->id,
                    'quantity'      => $qty,
                    'unit'          => $ingredient->unit,
                    'cost'          => $cost,
                ]);

                // Deduct stock
                $ingredient->decrement('stock', $qty);
            }
        }
    });

    return redirect()->route('chef.productions.index')
        ->with('success', 'Production recorded successfully.');
}
    public function show(Production $production)
    {
        // Ensure this chef can only see their own records
        if ($production->user_id !== Auth::id()) {
            abort(403);
        }

        $production->load('ingredientUsages.ingredient');
        return view('chef.productions.show', compact('production'));
    }

}
