<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Production;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        $myTotal = Production::where('user_id', $userId)->count();
        $myToday = Production::where('user_id', $userId)->whereDate('production_date', $today)->count();
        $myValue = Production::where('user_id', $userId)->sum('total_value');
        $myVariance = Production::where('user_id', $userId)->where('has_variance', true)->count();

        $chartData = Production::where('user_id', $userId)
            ->selectRaw('production_date, SUM(total_value) as value')
            ->groupBy('production_date')
            ->orderBy('production_date', 'asc')
            ->where('production_date', '>=', Carbon::now()->subDays(7))
            ->pluck('value', 'production_date');

        return view('chef.dashboard', compact(
            'myTotal', 'myToday', 'myValue', 'myVariance', 'chartData'
        ));
    }
}
