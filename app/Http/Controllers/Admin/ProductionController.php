<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index()
    {
        // Show ALL chefs’ productions
        $productions = Production::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.productions.index', compact('productions'));
    }

    public function create()
    {
        $chefs = User::where('role', 'chef')->get();
        $ingredients = Ingredient::orderBy('name')->get();

        return view('admin.productions.create', compact('chefs', 'ingredients'));
    }

public function store(Request $request)
{
    $request->validate([
        'chef_id'         => 'required|exists:users,id',
        'production_date' => 'required|date',
        'flour_bags'      => 'required|numeric|min:0',
        'outputs'         => 'nullable|array',
        'ingredients'     => 'nullable|array',
    ]);

    // Normalize outputs with defaults
    $outputs = array_merge([
        'buns'           => 0,
        'small_breads'   => 0,
        'big_breads'     => 0,
        'donuts'         => 0,
        'half_cakes'     => 0,
        'block_cakes'    => 0,
        'slab_cakes'     => 0,
        'birthday_cakes' => 0,
    ], $request->input('outputs', []));

    // Ensure integers
    foreach ($outputs as $k => $v) {
        $outputs[$k] = (int) $v;
    }

    $flourBags = (int) $request->flour_bags;

    // Variance check (buns only)
    $hasVariance = false;
    $notes = null;
    if ($outputs['buns'] < ($flourBags * config('bakery_flour.yield_min_per_bag.buns', 150))) {
        $hasVariance = true;
        $notes = "Buns below expected yield (150 per bag minimum).";
    }

    // Calculate total production value using config prices
    $productPrices = config('bakery_products');
    $totalValue = 0;
    foreach ($outputs as $product => $qty) {
        $price = $productPrices[$product] ?? 0;
        $totalValue += $qty * $price;
    }

    // Create production record
    $production = Production::create([
        'user_id'        => $request->chef_id,
        'production_date'=> $request->production_date,
        'flour_bags'     => $flourBags,
        'buns'           => $outputs['buns'],
        'small_breads'   => $outputs['small_breads'],
        'big_breads'     => $outputs['big_breads'],
        'donuts'         => $outputs['donuts'],
        'half_cakes'     => $outputs['half_cakes'],
        'block_cakes'    => $outputs['block_cakes'],
        'slab_cakes'     => $outputs['slab_cakes'],
        'birthday_cakes' => $outputs['birthday_cakes'],
        'total_value'    => $totalValue,
        'has_variance'   => $hasVariance,
        'variance_notes' => $notes,
    ]);

    // Save ingredient usage & deduct stock
    $ingredients = $request->input('ingredients', []);
    foreach ($ingredients as $id => $qty) {
        $qty = floatval($qty);
        if ($qty <= 0) continue;

        $ingredient = Ingredient::find($id);
        if (!$ingredient) continue;

        $cost = $ingredient->unit_cost * $qty;

        $production->ingredientUsages()->create([
            'ingredient_id' => $id,
            'quantity'      => $qty,
            'unit'          => $ingredient->unit,
            'cost'          => $cost,
        ]);

        $ingredient->update([
            'stock' => max(0, $ingredient->stock - $qty)
        ]);
    }

    return redirect()->route('admin.productions.index')
        ->with('success', 'Production recorded successfully.');
}



    public function show(Production $production)
    {
        $production->load('user', 'ingredientUsages.ingredient');
        return view('admin.productions.show', compact('production'));
    }

    public function destroy(Production $production)
    {
        $production->delete();
        return back()->with('success', 'Production deleted.');
    }
}
