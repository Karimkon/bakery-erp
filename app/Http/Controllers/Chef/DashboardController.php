<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\ChefTarget;
use App\Models\ChefProgressDaily;
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

        // Get chef target
        $chefTarget = ChefTarget::where('chef_id', $userId)->first();
        
        // ✅ FIXED: Calculate today's production correctly
        $todayProduction = Production::where('user_id', $userId)
            ->whereDate('production_date', $today)
            ->sum('total_value');

        // ✅ FIXED: Update ALL missing progress records, not just today
        if ($chefTarget) {
            $this->updateAllMissingProgress($chefTarget);
        }

        // Recalculate today's progress after update
        $todayProgress = ChefProgressDaily::where('chef_id', $userId)
            ->whereDate('progress_date', $today)
            ->first();
            
        $progressPercentage = $todayProgress ? $todayProgress->progress_percentage : 0;
        $dailyRemaining = $todayProgress ? max(0, $chefTarget->daily_target - $todayProgress->achieved_amount) : $chefTarget->daily_target;

        // Chart data
        $chartData = $this->getChartData($userId);

        return view('chef.dashboard', compact(
            'myTotal', 'myToday', 'myValue', 'myVariance', 
            'chartData', 'chefTarget', 'todayProduction',
            'progressPercentage', 'dailyRemaining'
        ));
    }
       private function saveChefDailyProgress($chefTarget, $achievedAmount, $date = null)
    {
        try {
            $date = $date ? Carbon::parse($date) : Carbon::today();
            
            // ✅ USE ACTUAL TARGET from chef_targets table
            $targetAmount = $chefTarget->daily_target;
            
            $progressPercentage = $targetAmount > 0 
                ? round(($achievedAmount / $targetAmount) * 100, 2) 
                : 0;

            ChefProgressDaily::updateOrCreate(
                [
                    'chef_id' => $chefTarget->chef_id,
                    'progress_date' => $date->format('Y-m-d')
                ],
                [
                    'target_amount' => $targetAmount, // ✅ Correct target
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

        // Get date filters
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $searchDate = $request->get('search_date');
        
        // Build query
        $query = ChefProgressDaily::where('chef_id', $chefId)
            ->orderBy('progress_date', 'desc');
        
        // Apply date filters
        if ($searchDate) {
            $query->whereDate('progress_date', $searchDate);
        } else {
            $query->whereBetween('progress_date', [$startDate, $endDate]);
        }
        
        $progressHistory = $query->paginate(20);
        
        // Calculate summary statistics
        $summary = [
            'total_days' => $progressHistory->total(),
            'average_progress' => $progressHistory->avg('progress_percentage') ?? 0,
            'target_achieved_days' => $progressHistory->where('progress_percentage', '>=', 100)->count(),
            'total_achieved' => $progressHistory->sum('achieved_amount'),
            'total_target' => $progressHistory->sum('target_amount'),
            'success_rate' => $progressHistory->total() > 0 
                ? round(($progressHistory->where('progress_percentage', '>=', 100)->count() / $progressHistory->total()) * 100, 1)
                : 0
        ];
        
        return view('chef.progress_history', compact(
            'chefTarget',
            'progressHistory',
            'summary',
            'startDate',
            'endDate',
            'searchDate'
        ));
    }
}