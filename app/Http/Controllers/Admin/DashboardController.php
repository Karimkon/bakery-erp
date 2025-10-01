<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Production;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\BakeryStock;


class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $totalusers = \App\Models\User::count();

        // Quick stats
        $totalProductions = Production::count();
        $todayProductions = Production::whereDate('production_date', $today)->count();
        $totalValue = Production::sum('total_value');
        $varianceCount = Production::where('has_variance', true)->count();

        // Chart: last 7 days production values
        $chartData = Production::selectRaw('production_date, SUM(total_value) as value')
            ->groupBy('production_date')
            ->orderBy('production_date', 'asc')
            ->where('production_date', '>=', Carbon::now()->subDays(7))
            ->pluck('value', 'production_date');

            // 👉 fetch all bakery stocks
            $bakeryStocks = BakeryStock::all();

        return view('admin.dashboard', compact(
            'totalProductions',
            'todayProductions',
            'totalValue',
            'varianceCount',
            'chartData',
            'totalusers',
            'bakeryStocks'
        ));
    }
}
