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
            'buns'             => 'nullable|numeric|min:0',
            'small_breads'     => 'nullable|numeric|min:0',
            'big_breads'       => 'nullable|numeric|min:0',
            'donuts'           => 'nullable|numeric|min:0',
            'half_cakes'       => 'nullable|numeric|min:0',
            'block_cakes'      => 'nullable|numeric|min:0',
            'slab_cakes'       => 'nullable|numeric|min:0',
            'birthday_cakes'   => 'nullable|numeric|min:0',
            'ingredients'      => 'required|array',
        ]);

        // Variance check for buns
        $hasVariance = false;
        $notes = null;
        if ($request->buns < ($request->flour_bags * 150)) {
            $hasVariance = true;
            $notes = "Buns below expected yield (150 per bag minimum).";
        }

        // Calculate total production value (sales side)
        $totalValue =
            ($request->buns * 500) +
            ($request->small_breads * 1000) +
            ($request->big_breads * 2000) +
            ($request->donuts * 800) +
            ($request->half_cakes * 8000) +
            ($request->block_cakes * 12000) +
            ($request->slab_cakes * 20000) +
            ($request->birthday_cakes * 30000);

        // Create production record
        $production = Production::create([
            'user_id'        => $request->chef_id,
            'production_date'=> $request->production_date,
            'flour_bags'     => $request->flour_bags,
            'buns'           => $request->buns,
            'small_breads'   => $request->small_breads,
            'big_breads'     => $request->big_breads,
            'donuts'         => $request->donuts,
            'half_cakes'     => $request->half_cakes,
            'block_cakes'    => $request->block_cakes,
            'slab_cakes'     => $request->slab_cakes,
            'birthday_cakes' => $request->birthday_cakes,
            'total_value'    => $totalValue,
            'has_variance'   => $hasVariance,
            'variance_notes' => $notes,
        ]);

        // Save ingredient usage + deduct stock
        foreach ($request->ingredients as $id => $qty) {
            if ($qty > 0) {
                $ingredient = Ingredient::findOrFail($id);
                $cost = $ingredient->current_price_per_unit * $qty;

                $production->ingredientUsages()->create([
                    'ingredient_id' => $id,
                    'quantity'      => $qty,
                    'unit'          => $ingredient->unit,
                    'cost'          => $cost,
                ]);

                // Decrement stock
                $ingredient->decrement('stock', $qty);
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
