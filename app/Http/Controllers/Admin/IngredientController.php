<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Models\User; // for chefs


class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::orderBy('name')->paginate(15);
        return view('admin.ingredients.index', compact('ingredients'));
    }

    public function create()
{
    $chefs = User::where('role', 'chef')->get();
    return view('admin.ingredients.create', compact('chefs'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'chef_id' => 'nullable|exists:users,id'

        ]);

        Ingredient::create($request->all());

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Ingredient added successfully.');
    }

    public function show(Ingredient $ingredient)
    {
        return view('admin.ingredients.show', compact('ingredient'));
    }

    public function edit(Ingredient $ingredient)
    {
        $chefs = User::where('role', 'chef')->get();
        return view('admin.ingredients.edit', compact('ingredient', 'chefs'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name,' . $ingredient->id,
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'chef_id' => 'nullable|exists:users,id'

        ]);

        $ingredient->update($request->all());

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Ingredient updated successfully.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Ingredient deleted successfully.');
    }
}
