<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\ChefTarget;
use App\Models\ChefProgressDaily;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // Basic production stats
        $myTotal = Production::where('user_id', $userId)->count();
        $myToday = Production::where('user_id', $userId)->whereDate('production_date', $today)->count();
        $myValue = Production::where('user_id', $userId)->sum('total_value');
        $myVariance = Production::where('user_id', $userId)->where('has_variance', true)->count();

        // Get chef's ingredients
        $ingredients = Ingredient::where('chef_id', $userId)
            ->orderBy('name')
            ->get();
        
        // Calculate ingredient statistics
        $totalIngredients = $ingredients->count();
        $availableIngredients = $ingredients->where('stock', '>', 10)->count();
        $lowStockIngredients = $ingredients->where('stock', '<=', 10)->where('stock', '>', 0)->count();
        $outOfStockIngredients = $ingredients->where('stock', '<=', 0)->count();

        // Get chef target and calculate progress
        $chefTarget = ChefTarget::where('chef_id', $userId)->first();
        $todayProduction = Production::where('user_id', $userId)
            ->whereDate('production_date', $today)
            ->where('status', 'approved')
            ->sum('total_value');

        // Calculate progress
        $progressPercentage = 0;
        $dailyRemaining = 0;
        if ($chefTarget && $chefTarget->daily_target > 0) {
            $progressPercentage = round(($todayProduction / $chefTarget->daily_target) * 100, 2);
            $dailyRemaining = max(0, $chefTarget->daily_target - $todayProduction);
        }

        // Chart data (last 7 days, only approved)
        $chartData = Production::where('user_id', $userId)
            ->where('status', 'approved')
            ->selectRaw('production_date, SUM(total_value) as value')
            ->groupBy('production_date')
            ->orderBy('production_date', 'asc')
            ->where('production_date', '>=', Carbon::now()->subDays(7))
            ->pluck('value', 'production_date');

        return view('chef.dashboard', compact(
            'myTotal', 
            'myToday', 
            'myValue', 
            'myVariance', 
            'chartData',
            'chefTarget',
            'todayProduction',
            'progressPercentage',
            'dailyRemaining',
            'ingredients', // Add this
            'totalIngredients', // Add this
            'availableIngredients', // Add this
            'lowStockIngredients', // Add this
            'outOfStockIngredients' // Add this
        ));
    }


    public function ingredients()
{
    $userId = Auth::id();
    
    $ingredients = Ingredient::where('chef_id', $userId)
        ->orderBy('name')
        ->get();
    
    $totalIngredients = $ingredients->count();
    $availableIngredients = $ingredients->where('stock', '>', 10)->count();
    $lowStockIngredients = $ingredients->where('stock', '<=', 10)->where('stock', '>', 0)->count();
    $outOfStockIngredients = $ingredients->where('stock', '<=', 0)->count();
    
    return view('chef.ingredients', compact(
        'ingredients',
        'totalIngredients',
        'availableIngredients',
        'lowStockIngredients',
        'outOfStockIngredients'
    ));
}
    private function saveChefDailyProgress($chefTarget, $achievedAmount)
    {
        try {
            $today = Carbon::today();
            $targetAmount = $chefTarget->daily_target;
            
            $progressPercentage = $targetAmount > 0 
                ? round(($achievedAmount / $targetAmount) * 100, 2) 
                : 0;

            ChefProgressDaily::updateOrCreate(
                [
                    'chef_id' => $chefTarget->chef_id,
                    'progress_date' => $today->format('Y-m-d')
                ],
                [
                    'target_amount' => $targetAmount,
                    'achieved_amount' => $achievedAmount,
                    'progress_percentage' => $progressPercentage
                ]
            );
            
        } catch (\Exception $e) {
            \Log::error('Error saving chef progress: ' . $e->getMessage());
        }
    }

    public function progressHistory(Request $request)
    {
        $chefId = Auth::id();
        $chefTarget = ChefTarget::where('chef_id', $chefId)->first();
        
        if (!$chefTarget) {
            return redirect()->route('chef.dashboard')
                ->with('error', 'No target set for you. Please contact administrator.');
        }

        // ✅ NEW: Default to current month instead of last 30 days
        $now = Carbon::now();
        $startDate = $request->get('start_date', $now->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', $now->copy()->endOfMonth()->format('Y-m-d'));
        
        // Build query
        $query = ChefProgressDaily::where('chef_id', $chefId)
            ->whereBetween('progress_date', [$startDate, $endDate])
            ->orderBy('progress_date', 'desc');
        
        $progressHistory = $query->paginate(31); // Show up to 31 days per page
        
        // ✅ Calculate summary statistics from the filtered data
        $allRecords = ChefProgressDaily::where('chef_id', $chefId)
            ->whereBetween('progress_date', [$startDate, $endDate])
            ->get();
        
        $summary = [
            'total_days' => $allRecords->count(),
            'average_progress' => $allRecords->avg('progress_percentage') ?? 0,
            'target_achieved_days' => $allRecords->where('progress_percentage', '>=', 100)->count(),
            'total_achieved' => $allRecords->sum('achieved_amount'),
            'total_target' => $allRecords->sum('target_amount'),
            'success_rate' => $allRecords->count() > 0 
                ? round(($allRecords->where('progress_percentage', '>=', 100)->count() / $allRecords->count()) * 100, 1)
                : 0
        ];

        // ✅ Add month name for display
        $currentMonth = Carbon::parse($startDate)->format('F Y');
        
        return view('chef.progress_history', compact(
            'chefTarget',
            'progressHistory',
            'summary',
            'startDate',
            'endDate',
            'currentMonth'
        ));
    }
}