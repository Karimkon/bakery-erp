<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Models\User; // for chefs
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\StockHistory;


class ManagerIngredientController extends Controller
{
    public function index(Request $request)
    {
        // Start a query with chef relationship
        $query = Ingredient::with('chef');

        // Filter by chef if provided
        if ($request->chef_id) {
            $query->where('chef_id', $request->chef_id);
        }

        // Filter by ingredient name if provided
        if ($request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Paginate with query string so filters persist
        $ingredients = $query->orderBy('name')->paginate(15)->withQueryString();

        // Get all chefs for the filter dropdown
        $chefs = User::where('role', 'chef')->get();

        return view('manager.ingredients.index', compact('ingredients', 'chefs'));
    }


    public function create()
    {
        $chefs = User::where('role', 'chef')->get();
        return view('manager.ingredients.create', compact('chefs'));
    }

    public function store(Request $request)
    {
        $request->validate([
    'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('ingredients')->where(function ($query) use ($request) {
            // Only enforce per-chef uniqueness
            if ($request->chef_id) {
                return $query->where('chef_id', $request->chef_id);
            }
            return $query->whereNull('chef_id');
        }),
    ],
    'unit'      => 'required|string|max:50',
    'unit_cost' => 'required|numeric|min:0',
    'stock'     => 'nullable|numeric|min:0',
    'chef_id'   => 'nullable|exists:users,id'
]);


        $ingredient = Ingredient::create($request->all());

// Record stock addition if initial stock > 0
if ($request->stock > 0) {
    StockHistory::create([
        'ingredient_id' => $ingredient->id,
        'chef_id'       => $ingredient->chef_id,
        'quantity_added'=> $request->stock,
        'quantity_changed'=> $request->stock,
        'added_by'      => auth()->id(),
    ]);

    // Reset modal flag for admin
    session()->forget('seen_stock_modal');
}


        return redirect()->route('manager.ingredients.index')
            ->with('success', 'Ingredient added successfully, Notification Sent to Admin.');
    }

    public function show(Ingredient $ingredient)
    {
        return view('manager.ingredients.show', compact('ingredient'));
    }

    public function edit(Ingredient $ingredient)
    {
        $chefs = User::where('role', 'chef')->get();
        return view('manager.ingredients.edit', compact('ingredient', 'chefs'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => [
    'required',
    'string',
    'max:255',
    Rule::unique('ingredients')
        ->where(function ($query) use ($request, $ingredient) {
            if ($request->chef_id) {
                return $query->where('chef_id', $request->chef_id);
            }
            return $query->whereNull('chef_id');
        })
        ->ignore($ingredient->id),
],

            'unit'      => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'stock'     => 'nullable|numeric|min:0',
            'chef_id'   => 'nullable|exists:users,id',
        ]);

       $oldStock = $ingredient->stock;
        $ingredient->update($validated);

        $addedStock = $ingredient->stock - $oldStock;
        if ($addedStock > 0) {
            StockHistory::create([
                'ingredient_id' => $ingredient->id,
                'chef_id'       => $ingredient->chef_id,
                'quantity_added'=> $addedStock,
                'quantity_changed'=> $addedStock,
                'added_by'      => auth()->id(),
            ]);

            // Reset modal flag for admin
            session()->forget('seen_stock_modal');
        }

        return redirect()->route('manager.ingredients.index')
            ->with('success', 'Ingredient updated successfully.');
    }





    public function overview(Request $request)
    {
        // --- Filters ---
        $chefId = $request->chef_id;
        $ingredientName = $request->ingredient_name;
        $minStock = $request->min_stock;
        $maxStock = $request->max_stock;

        // Base query
        $query = Ingredient::query();

        if ($chefId) {
            $query->where('chef_id', $chefId);
        }

        if ($ingredientName) {
            $query->where('name', $ingredientName);
        }

        if ($minStock !== null) {
            $query->where('stock', '>=', $minStock);
        }

        if ($maxStock !== null) {
            $query->where('stock', '<=', $maxStock);
        }

        // --- Aggregated overview ---
        $overview = $query
            ->select(
                'name',
                'unit',
                DB::raw('SUM(stock) as total_qty'),
                DB::raw('AVG(unit_cost) as avg_cost'),
                DB::raw('SUM(stock * unit_cost) as total_value'),
                DB::raw('COUNT(DISTINCT chef_id) as chef_count'),
                DB::raw('MAX(updated_at) as last_updated')
            )
            ->groupBy('name', 'unit')
            ->orderBy('name')
            ->get();

        // --- Summary Cards Data ---
        $summary = [
            'total_items' => Ingredient::distinct('name')->count(),
            'total_stock_value' => Ingredient::sum(DB::raw('stock * unit_cost')),
            'low_stock' => Ingredient::where('stock', '<', 5)->count(),
            'total_chefs' => User::where('role', 'chef')->count(),
        ];

        // For the summary per-ingredient cards (like Sugar, Flour, etc)
        $totals = Ingredient::select('name', DB::raw('SUM(stock) as total_qty'), 'unit')
            ->groupBy('name', 'unit')
            ->get();

        $chefs = User::where('role', 'chef')->get();
        $ingredientNames = Ingredient::distinct()->pluck('name');

        return view('manager.ingredients.overview', compact('overview', 'summary', 'totals', 'chefs', 'ingredientNames'));
    }



    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return redirect()->route('manager.ingredients.index')
            ->with('success', 'Ingredient deleted successfully.');
    }
}
