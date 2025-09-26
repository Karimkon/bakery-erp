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
        'chef_id'          => 'required|exists:users,id',
        'production_date'  => 'required|date',
        'flour_bags'       => 'required|numeric|min:0',
        // outputs is an array of product => qty (optional)
        'outputs'          => 'nullable|array',
        'ingredients'      => 'nullable|array',
    ]);

    // normalize outputs with defaults (so we always have numeric values)
    $outputs = array_merge([
        'buns' => 0,
        'small_breads' => 0,
        'big_breads' => 0,
        'donuts' => 0,
        'half_cakes' => 0,
        'block_cakes' => 0,
        'slab_cakes' => 0,
        'birthday_cakes' => 0,
    ], $request->input('outputs', []));

    // ensure numeric ints
    foreach ($outputs as $k => $v) {
        $outputs[$k] = (int) $v;
    }

    $flourBags = (int) $request->flour_bags;
    // Variance check for buns (use default 0 if missing)
    $hasVariance = false;
    $notes = null;
    if ($outputs['buns'] < ($flourBags * 150)) {
        $hasVariance = true;
        $notes = "Buns below expected yield (150 per bag minimum).";
    }

    // Calculate total production value (sales side) — use the outputs array
    $totalValue =
        ($outputs['buns'] * 500) +
        ($outputs['small_breads'] * 1000) +
        ($outputs['big_breads'] * 2000) +
        ($outputs['donuts'] * 800) +
        ($outputs['half_cakes'] * 8000) +
        ($outputs['block_cakes'] * 12000) +
        ($outputs['slab_cakes'] * 20000) +
        ($outputs['birthday_cakes'] * 30000);

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

    // Save ingredient usage + deduct stock (if any)
    $ingredients = $request->input('ingredients', []);
    foreach ($ingredients as $id => $qty) {
        $qty = floatval($qty);
        if ($qty > 0) {
            $ingredient = Ingredient::find($id);
            if (!$ingredient) continue; // skip unknown ids gracefully

            $cost = $ingredient->current_price_per_unit * $qty;

            $production->ingredientUsages()->create([
                'ingredient_id' => $id,
                'quantity'      => $qty,
                'unit'          => $ingredient->unit,
                'cost'          => $cost,
            ]);

            // Decrement stock but ensure stock doesn't go negative
            $newStock = max(0, $ingredient->stock - $qty);
            $ingredient->update(['stock' => $newStock]);
        }
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
