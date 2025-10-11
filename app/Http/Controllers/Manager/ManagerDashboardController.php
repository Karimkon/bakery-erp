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
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'today'); // today | week | month

        $today = Carbon::today();
        $now = Carbon::now();

        switch ($filter) {
            case 'week':
                $from = Carbon::now()->startOfWeek();
                $title = 'This Week';
                break;
            case 'month':
                $from = Carbon::now()->startOfMonth();
                $title = 'This Month';
                break;
            default:
                $from = $today->startOfDay();
                $title = 'Today';
        }

        $to = $now->endOfDay();

        // Bakery Stock Summary
        $bakeryStocks = BakeryStock::orderBy('product')->get();
        $totalStockQuantity = $bakeryStocks->sum('quantity');
        $totalStockItems = $bakeryStocks->count();

        // Ingredients Summary
        $ingredients = Ingredient::orderBy('name')->get();
        $totalIngredientQuantity = $ingredients->sum('stock');
        $totalIngredientItems = $ingredients->count();

        // Productions & Dispatch Summary
        $totalProductions = Production::count();
        $totalDispatches = Dispatch::count();
        $todayProductions = Production::whereDate('production_date', $today)->count();

        // Range-based summaries
        $productionValue = (float) Production::whereBetween('production_date', [$from, $to])->sum('total_value');
        $dispatchValue = (float) Dispatch::whereBetween('dispatch_date', [$from, $to])->sum('total_sales_value');
        $combinedValue = $productionValue + $dispatchValue;

        // Flour used (bags → kg)
        $flourBagsUsed = (float) Production::whereBetween('production_date', [$from, $to])->sum('flour_bags');
        $flourKgUsed = $flourBagsUsed * 50; // 1 bag = 50kg

        // Dispatch items (total sold in this period)
        $dispatchItemsCount = (int) Dispatch::whereBetween('dispatch_date', [$from, $to])->sum('total_items_sold');

        // Chart (last 7 days)
        $chartDays = 7;
        $chartFrom = Carbon::now()->subDays($chartDays - 1)->startOfDay();
        $chartTo = Carbon::now()->endOfDay();

        $prodPerDay = Production::selectRaw('DATE(production_date) as day, SUM(total_value) as value')
            ->whereBetween('production_date', [$chartFrom, $chartTo])
            ->groupBy('day')
            ->pluck('value', 'day')
            ->toArray();

        $dispPerDay = Dispatch::selectRaw('DATE(dispatch_date) as day, SUM(total_sales_value) as value')
            ->whereBetween('dispatch_date', [$chartFrom, $chartTo])
            ->groupBy('day')
            ->pluck('value', 'day')
            ->toArray();

        $labels = [];
        $prodSeries = [];
        $dispSeries = [];
        for ($i = 0; $i < $chartDays; $i++) {
            $d = Carbon::now()->subDays($chartDays - 1 - $i)->format('Y-m-d');
            $labels[] = Carbon::parse($d)->format('M d');
            $prodSeries[] = isset($prodPerDay[$d]) ? (float)$prodPerDay[$d] : 0;
            $dispSeries[] = isset($dispPerDay[$d]) ? (float)$dispPerDay[$d] : 0;
        }

        // Recent activities
        $recentProductions = Production::orderBy('production_date', 'desc')->limit(10)->get();
        $recentDispatches = Dispatch::with('driver')->orderBy('dispatch_date', 'desc')->limit(10)->get();

        return view('manager.dashboard', compact(
            'filter',
            'title',
            'bakeryStocks',
            'ingredients',
            'totalStockQuantity',
            'totalStockItems',
            'totalIngredientQuantity',
            'totalIngredientItems',
            'totalProductions',
            'totalDispatches',
            'todayProductions',
            'productionValue',
            'dispatchValue',
            'combinedValue',
            'flourBagsUsed',
            'flourKgUsed',
            'dispatchItemsCount',
            'labels',
            'prodSeries',
            'dispSeries',
            'recentProductions',
            'recentDispatches'
        ));
    }
}
