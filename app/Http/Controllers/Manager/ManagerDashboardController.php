<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\BakeryStock;
use App\Models\Production;
use App\Models\Ingredient;
use App\Models\User;
use App\Models\Dispatch;
use App\Models\Sale;
use App\Models\ManagerTarget;
use Illuminate\Support\Facades\DB;

class ManagerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'today'); // today | week | month

        // --- Calculate date range once ---
        $today = Carbon::today();
        $now = Carbon::now();

        switch ($filter) {
            case 'yesterday':  // ADD THIS
                $from = $today->copy()->subDay()->startOfDay();
                $to = $today->copy()->subDay()->endOfDay();
                $title = 'Yesterday';
                break;
            case 'week':
                $from = $now->copy()->startOfWeek();
                $to = $now->copy()->endOfWeek();
                $title = 'This Week';
                break;
            case 'month':
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                $title = 'This Month';
                break;
            default: // today
                $from = $today->copy()->startOfDay();
                $to = $today->copy()->endOfDay();
                $title = 'Today';
        }

        // --- Bakery Stock ---
        $bakeryStocks = BakeryStock::orderBy('product')->get();
        $totalStockQuantity = $bakeryStocks->sum('quantity');
        $totalStockItems = $bakeryStocks->count();

        // --- Ingredients ---
        $ingredients = Ingredient::orderBy('name')->get();
        $totalIngredientQuantity = $ingredients->sum('stock');
        $totalIngredientItems = $ingredients->count();

        // --- Productions & Dispatches ---
        $totalProductions = Production::count();
        $totalDispatches = Dispatch::count();
        $todayProductions = Production::whereDate('production_date', $today)->count();

        $productionValue = (float) Production::whereBetween('production_date', [$from, $to])->sum('total_value');
        $dispatchValue = (float) Dispatch::whereBetween('dispatch_date', [$from, $to])->sum('total_sales_value');
        $combinedValue = $productionValue + $dispatchValue;

        // Flour used (bags → kg)
        $flourBagsUsed = (float) Production::whereBetween('production_date', [$from, $to])->sum('flour_bags');
        $flourKgUsed = $flourBagsUsed * 50;

        // Total dispatch items sold
        $dispatchItemsCount = (int) Dispatch::whereBetween('dispatch_date', [$from, $to])->sum('total_items_sold');

        // --- Last 7 Days Chart ---
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

        $labels = $prodSeries = $dispSeries = [];
        for ($i = 0; $i < $chartDays; $i++) {
            $d = Carbon::now()->subDays($chartDays - 1 - $i)->format('Y-m-d');
            $labels[] = Carbon::parse($d)->format('M d');
            $prodSeries[] = $prodPerDay[$d] ?? 0;
            $dispSeries[] = $dispPerDay[$d] ?? 0;
        }

        // --- Recent activities ---
        $recentProductions = Production::orderBy('production_date', 'desc')->limit(10)->get();
        $recentDispatches = Dispatch::with('driver')->orderBy('dispatch_date', 'desc')->limit(10)->get();

        // --- ENHANCED MANAGER TARGET CALCULATION WITH BREAKDOWN ---
        $excludedDriverIds = [20, 21]; // Nakato & Ariah
        $includedUserIds = [10, 13, 15, 17, 18]; // Sales, Mukasa, Abdu, Sales2, Umar

        // Get detailed dispatch breakdown by driver
        $dispatchBreakdown = DB::table('dispatches as d')
            ->join('users as u', 'd.driver_id', '=', 'u.id')
            ->select(
                'u.id as driver_id',
                'u.name as driver_name',
                DB::raw('SUM(d.total_sales_value) as total_sales'),
                DB::raw('COUNT(d.id) as dispatch_count')
            )
            ->whereDate('d.dispatch_date', '>=', $from)->whereDate('d.dispatch_date', '<=', $to)
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('total_sales')
            ->get()
            ->map(function ($item) use ($excludedDriverIds) {
                $item->is_excluded = in_array($item->driver_id, $excludedDriverIds);
                $item->status = $item->is_excluded ? 'EXCLUDED' : 'INCLUDED';
                return $item;
            });

        // Get bakery sales breakdown by user
        $bakeryBreakdown = DB::table('sales as s')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->select(
                'u.id as user_id',
                'u.name as user_name',
                DB::raw('SUM(s.total_price) as total_sales'),
                DB::raw('COUNT(s.id) as sale_count')
            )
            ->whereIn('s.user_id', $includedUserIds)
            ->whereDate('s.created_at', '>=', $from)->whereDate('s.created_at', '<=', $to)
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('total_sales')
            ->get();

        // Calculate included/excluded totals
        $includedDispatches = $dispatchBreakdown->where('is_excluded', false);
        $excludedDispatches = $dispatchBreakdown->where('is_excluded', true);

        $dispatchTotal = (float) $includedDispatches->sum('total_sales');
        $excludedDispatchTotal = (float) $excludedDispatches->sum('total_sales');
        $bakeryTotal = (float) $bakeryBreakdown->sum('total_sales');

        // Combined total for target
        $totalProduced = $dispatchTotal + $bakeryTotal;

        // Target calculation
        $managerTarget = ManagerTarget::where('manager_id', auth()->id())->first();
        $dailyTarget = $managerTarget->daily_target ?? 3000000;

        switch ($filter) {

            case 'yesterday': 
                $daysCount = 1;
                $target = $dailyTarget;
                break;
            case 'week':
                $daysCount = 7;
                $target = $dailyTarget * $daysCount;
                break;
            case 'month':
                $daysCount = $from->daysInMonth;
                $target = $dailyTarget * $daysCount;
                break;
            default:
                $daysCount = 1;
                $target = $dailyTarget;
        }

        $progress = $target > 0 ? round(($totalProduced / $target) * 100, 2) : 0;
        $remaining = max(0, $target - $totalProduced);
        $progressCapped = min($progress, 100);

        // Verification data
        $verificationData = [
            'date_range' => $from->format('Y-m-d') . ' to ' . $to->format('Y-m-d'),
            'filter' => $filter,
            'days_count' => $daysCount,
            'daily_target' => $dailyTarget,
            'calculated_target' => $target,
            'total_dispatches_all' => $dispatchBreakdown->sum('total_sales'),
            'included_dispatches' => $dispatchTotal,
            'excluded_dispatches' => $excludedDispatchTotal,
            'bakery_sales' => $bakeryTotal,
            'grand_total' => $totalProduced,
            'progress_percent' => $progress,
        ];

        return view('manager.dashboard', compact(
            'filter', 'title',
            'bakeryStocks', 'ingredients',
            'totalStockQuantity', 'totalStockItems',
            'totalIngredientQuantity', 'totalIngredientItems',
            'totalProductions', 'totalDispatches', 'todayProductions',
            'productionValue', 'dispatchValue', 'combinedValue',
            'flourBagsUsed', 'flourKgUsed', 'dispatchItemsCount',
            'labels', 'prodSeries', 'dispSeries',
            'recentProductions', 'recentDispatches',
            'target', 'dispatchTotal', 'bakeryTotal',
            'totalProduced', 'progress', 'remaining', 'progressCapped',
            'dispatchBreakdown', 'includedDispatches', 'excludedDispatches',
            'bakeryBreakdown', 'excludedDispatchTotal',
            'verificationData', 'dailyTarget', 'daysCount'
        ));
    }
}