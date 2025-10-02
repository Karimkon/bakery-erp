<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\BakeryStock;
use App\Models\Production;
use App\Models\Ingredient;
use App\Models\Dispatch;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

         // Bakery stock
        $bakeryStocks = BakeryStock::all();
        $totalStockQuantity = $bakeryStocks->sum('quantity');
        $totalStockItems = $bakeryStocks->count();

        // Ingredients summary (optional)
        $ingredients = Ingredient::all();
        $totalIngredientQuantity = $ingredients->sum('stock');
        $totalIngredientItems = $ingredients->count();

       
        // Productions
        $totalProductions = Production::count();
        $todayProductions = Production::whereDate('production_date', $today)->count();

        // Dispatches
        $totalDispatches = Dispatch::count();

        return view('manager.dashboard', compact(
           'bakeryStocks',
            'totalStockQuantity',
            'totalStockItems',
            'ingredients',
            'totalIngredientQuantity',
            'totalIngredientItems',
            'totalProductions',
            'todayProductions',
            'totalDispatches',
        ));
    }
}
