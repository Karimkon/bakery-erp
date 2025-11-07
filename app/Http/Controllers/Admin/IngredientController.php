<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::orderBy('name')->paginate(15);
        return view('admin.ingredients.index', compact('ingredients'));
    }

   public function overview(Request $request)
{
    $chefId = $request->chef_id;
    $ingredientName = $request->ingredient_name;
    $minStock = $request->min_stock;
    $maxStock = $request->max_stock;
    $dateFilter = $request->date_filter;

    $query = Ingredient::query();

    if ($chefId) $query->where('chef_id', $chefId);
    if ($ingredientName) $query->where('name', $ingredientName);
    if ($minStock !== null) $query->where('stock', '>=', $minStock);
    if ($maxStock !== null) $query->where('stock', '<=', $maxStock);
    
    // Add date filter - shows ingredients that existed on that date
    if ($dateFilter) {
        $query->whereDate('created_at', '<=', $dateFilter)
              ->where(function($q) use ($dateFilter) {
                  $q->whereDate('updated_at', '>=', $dateFilter)
                    ->orWhereNull('updated_at');
              });
    }

    $overview = $query
        ->select(
            'name', 'unit',
            DB::raw('SUM(stock) as total_qty'),
            DB::raw('AVG(unit_cost) as avg_cost'),
            DB::raw('SUM(stock * unit_cost) as total_value'),
            DB::raw('COUNT(DISTINCT chef_id) as chef_count'),
            DB::raw('MAX(updated_at) as last_updated')
        )
        ->groupBy('name', 'unit')
        ->orderBy('name')
        ->get();

    $summary = [
        'total_items' => $query->distinct('name')->count('name'),
        'total_stock_value' => $overview->sum('total_value'),
        'low_stock' => $query->clone()->where('stock', '<', 5)->count(),
        'total_chefs' => User::where('role', 'chef')->count(),
    ];

    $totals = $query->clone()
        ->select('name', DB::raw('SUM(stock) as total_qty'), 'unit')
        ->groupBy('name', 'unit')
        ->get();

    $chefs = User::where('role', 'chef')->get();
    $ingredientNames = Ingredient::distinct()->pluck('name');

    return view('admin.ingredients.overview', compact(
        'overview', 'summary', 'totals', 'chefs', 'ingredientNames', 'dateFilter'
    ));
}
    public function create()
    {
        $chefs = User::where('role', 'chef')->get();
        return view('admin.ingredients.create', compact('chefs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('ingredients')->where(function ($query) use ($request) {
                    if ($request->chef_id) {
                        return $query->where('chef_id', $request->chef_id);
                    }
                    return $query->whereNull('chef_id');
                }),
            ],
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'chef_id' => 'nullable|exists:users,id'
        ]);

        $ingredient = Ingredient::create($request->all());

        // Record initial stock if any
        if ($request->stock > 0) {
            StockHistory::create([
                'ingredient_id' => $ingredient->id,
                'chef_id' => $ingredient->chef_id,
                'quantity_changed' => $request->stock,
                'quantity_before' => 0,
                'quantity_after' => $request->stock,
                'transaction_type' => 'addition',
                'added_by' => auth()->id(),
                'notes' => 'Initial stock creation'
            ]);

            session()->forget('seen_stock_modal');
        }

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
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('ingredients')
                    ->where(function ($query) use ($request, $ingredient) {
                        if ($request->chef_id) {
                            return $query->where('chef_id', $request->chef_id);
                        }
                        return $query->whereNull('chef_id');
                    })
                    ->ignore($ingredient->id),
            ],
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'chef_id' => 'nullable|exists:users,id'
        ]);

        $oldStock = $ingredient->stock;
        $newStock = $request->stock ?? 0;
        $stockChange = $newStock - $oldStock;

        $ingredient->update($request->all());

        // Track stock changes
        if ($stockChange != 0) {
            StockHistory::create([
                'ingredient_id' => $ingredient->id,
                'chef_id' => $ingredient->chef_id,
                'quantity_changed' => $stockChange,
                'quantity_before' => $oldStock,
                'quantity_after' => $newStock,
                'transaction_type' => $stockChange > 0 ? 'addition' : 'adjustment',
                'added_by' => auth()->id(),
                'notes' => $stockChange > 0 
                    ? "Stock increased by " . abs($stockChange) 
                    : "Stock adjusted/decreased by " . abs($stockChange)
            ]);

            session()->forget('seen_stock_modal');
        }

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