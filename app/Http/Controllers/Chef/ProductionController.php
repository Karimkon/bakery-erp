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
        $ingredients = Ingredient::where('chef_id', Auth::id())
                            ->orderBy('name')
                            ->get();
        $products = config('bakery_products'); // 👈 defined in config/bakery_products.php
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
    $flourBags = $flourKgs / 50; // 👈 convert internally (1 bag = 50 kg)
    $outputs   = $request->input('outputs', []);

    // 1️⃣ Calculate total production value
    $totalValue = 0;
    foreach ($prices as $product => $price) {
        $qty = (int) ($outputs[$product] ?? 0);
        $totalValue += $qty * (int) $price;
    }

    // 2️⃣ Variance logic (unchanged)
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

    // 3️⃣ Save Production and Deduct Ingredients
    \DB::transaction(function () use ($request, $outputs, $totalValue, $hasVariance, $notes, $flourKgs) {
        $production = \App\Models\Production::create([
            'user_id'         => \Auth::id(),
            'production_date' => $request->production_date,
            'flour_bags'      => $flourKgs / 50, // store still in bags internally
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

        // Deduct ingredient usage in kilograms
        $ingredientInputs = $request->input('ingredients', []);
        foreach ($ingredientInputs as $id => $qtyKg) {
            $qtyKg = (float) $qtyKg;
            if ($qtyKg <= 0) continue;

            $ingredient = \App\Models\Ingredient::findOrFail($id);

            if ($qtyKg > (float) $ingredient->stock) {
                throw new \Exception("Not enough stock for {$ingredient->name}. Available: {$ingredient->stock} {$ingredient->unit}");
            }

            $cost = $qtyKg * (float) $ingredient->unit_cost;

            $production->ingredientUsages()->create([
                'ingredient_id' => $ingredient->id,
                'quantity'      => $qtyKg,
                'unit'          => $ingredient->unit,
                'cost'          => $cost,
            ]);

            // Deduct from chef's ingredient stock directly
            $ingredient->decrement('stock', $qtyKg);
        }

        // Update bakery stock per product
        // ✅ Update bakery stock per product (fixed logic)
foreach ($outputs as $product => $qty) {
    $qty = (int) $qty;

    // Skip invalid or zero quantities
    if ($qty <= 0) continue;

    // Match only valid product names from your database
    $validProducts = [
        'buns', 'small_breads', 'big_breads', 'donuts',
        'half_cakes', 'block_cakes', 'slab_cakes', 'birthday_cakes30k', 'quarter_breads', 'birthday_cakes50k', 
        'mandazis', 'musiba_tayi', 'scornes', 'chapatys', 'toasted_bread', 'spring_donuts', 'cream_donuts', 'cinnamon_rolls'
    ];

    // If the product name doesn't match any in stock table, skip
    if (!in_array($product, $validProducts)) continue;

    // Create or update bakery stock correctly
    $stock = \App\Models\BakeryStock::firstOrCreate(
        ['product' => $product],
        ['quantity' => 0]
    );

    // Log debug info temporarily (you can remove later)
    \Log::info("Updating stock for {$product}", [
        'previous' => $stock->quantity,
        'added' => $qty
    ]);

    // Increment stock correctly
    $stock->increment('quantity', $qty);
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
