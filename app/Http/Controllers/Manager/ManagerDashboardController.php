<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\BakeryStock;
use App\Models\Production;
use App\Models\Dispatch;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Stock totals
        $totalStockQuantity = BakeryStock::sum('quantity');
        $lowStockCount = BakeryStock::where('quantity', '<', 50)->count();
        $totalStockItems = BakeryStock::count();

        // Productions
        $totalProductions = Production::count();
        $todayProductions = Production::whereDate('production_date', $today)->count();

        // Dispatches
        $totalDispatches = Dispatch::count();

        return view('manager.dashboard', compact(
            'totalStockQuantity',
            'lowStockCount',
            'totalStockItems',
            'totalProductions',
            'todayProductions',
            'totalDispatches'
        ));
    }
}
