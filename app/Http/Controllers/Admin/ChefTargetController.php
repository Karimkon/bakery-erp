<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChefTarget;
use App\Models\ManagerTarget;
use App\Models\User;
use App\Models\Dispatch;
use App\Models\Sale;
use App\Models\ManagerProgressDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChefTargetController extends Controller
{
    public function index(Request $request)
{
    $targets = ChefTarget::with('chef')->paginate(15);
    $managers = User::where('role', 'manager')->get();
    $managerTargets = ManagerTarget::with('manager')->get();
    
    // Get date filter from request
    $progressDate = $request->get('progress_date', Carbon::today()->format('Y-m-d'));
    $selectedDate = Carbon::parse($progressDate);
    
    // Calculate progress for each manager target for the selected date
    $managerTargetsWithProgress = $managerTargets->map(function ($managerTarget) use ($selectedDate) {
        return $this->calculateManagerProgress($managerTarget, $selectedDate);
    });
    
    // Get progress history for dropdown
    $availableDates = ManagerProgressDaily::select('progress_date')
        ->distinct()
        ->orderBy('progress_date', 'desc')
        ->pluck('progress_date');
    
    return view('admin.chef_targets.index', compact(
        'targets', 
        'managers', 
        'managerTargetsWithProgress',
        'progressDate',
        'availableDates'
    ));
}

public function managerProgressHistory($managerId, Request $request)
    {
        $manager = User::findOrFail($managerId);
        $managerTarget = ManagerTarget::where('manager_id', $managerId)->first();
        
        if (!$managerTarget) {
            return redirect()->route('admin.chef_targets.index')
                ->with('error', 'No target set for this manager.');
        }
        
        // Get date filters
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $searchDate = $request->get('search_date');
        
        // Build query
        $query = ManagerProgressDaily::where('manager_id', $managerId)
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
        
        return view('admin.manager_progress_history', compact(
            'manager',
            'managerTarget',
            'progressHistory',
            'summary',
            'startDate',
            'endDate',
            'searchDate'
        ));
    }

private function calculateManagerProgress($managerTarget, $date)
{
    // Try to get historical record first
    $historicalRecord = ManagerProgressDaily::where([
        'manager_id' => $managerTarget->manager_id,
        'progress_date' => $date->format('Y-m-d')
    ])->first();

    if ($historicalRecord) {
        // Use historical data
        $managerTarget->daily_progress = $historicalRecord->progress_percentage;
        $managerTarget->daily_achieved = $historicalRecord->achieved_amount;
        $managerTarget->daily_remaining = max(0, $managerTarget->daily_target - $historicalRecord->achieved_amount);
        
        // For monthly progress, you might want to calculate based on the month of the selected date
        $monthlyFrom = $date->copy()->startOfMonth();
        $monthlyTo = $date->copy()->endOfMonth();
        
        $monthlyAchieved = ManagerProgressDaily::where('manager_id', $managerTarget->manager_id)
            ->whereBetween('progress_date', [$monthlyFrom, $monthlyTo])
            ->sum('achieved_amount');
            
        $managerTarget->monthly_achieved = $monthlyAchieved;
        $managerTarget->monthly_progress = $managerTarget->monthly_target > 0 
            ? round(($monthlyAchieved / $managerTarget->monthly_target) * 100, 2) 
            : 0;
        $managerTarget->monthly_remaining = max(0, $managerTarget->monthly_target - $monthlyAchieved);
        
        $managerTarget->is_historical = true;
        $managerTarget->progress_date = $date->format('Y-m-d');
    } else {
        // Calculate real-time for the selected date
        $managerTarget = $this->calculateRealTimeProgress($managerTarget, $date);
        $managerTarget->is_historical = false;
        $managerTarget->progress_date = $date->format('Y-m-d');
    }

    return $managerTarget;
}

private function calculateRealTimeProgress($managerTarget, $date)
{
    $excludedDriverIds = [20, 21];
    $includedUserIds = [10, 13, 15, 17, 18];

    $from = $date->copy()->startOfDay();
    $to = $date->copy()->endOfDay();
    
    $monthlyFrom = $date->copy()->startOfMonth();
    $monthlyTo = $date->copy()->endOfMonth();

    // Calculate daily achievements
    $dailyIncludedDispatches = DB::table('dispatches as d')
        ->whereNotIn('d.driver_id', $excludedDriverIds)
        ->whereBetween('d.dispatch_date', [$from, $to])
        ->sum('d.total_sales_value');

    $dailyBakerySales = DB::table('sales as s')
        ->whereIn('s.user_id', $includedUserIds)
        ->whereBetween('s.created_at', [$from, $to])
        ->sum('s.total_price');

    $dailyAchieved = (float) $dailyIncludedDispatches + (float) $dailyBakerySales;
    $dailyProgress = $managerTarget->daily_target > 0 
        ? round(($dailyAchieved / $managerTarget->daily_target) * 100, 2) 
        : 0;

    // Calculate monthly achievements
    $monthlyIncludedDispatches = DB::table('dispatches as d')
        ->whereNotIn('d.driver_id', $excludedDriverIds)
        ->whereBetween('d.dispatch_date', [$monthlyFrom, $monthlyTo])
        ->sum('d.total_sales_value');

    $monthlyBakerySales = DB::table('sales as s')
        ->whereIn('s.user_id', $includedUserIds)
        ->whereBetween('s.created_at', [$monthlyFrom, $monthlyTo])
        ->sum('s.total_price');

    $monthlyAchieved = (float) $monthlyIncludedDispatches + (float) $monthlyBakerySales;
    $monthlyProgress = $managerTarget->monthly_target > 0 
        ? round(($monthlyAchieved / $managerTarget->monthly_target) * 100, 2) 
        : 0;

    $managerTarget->daily_progress = $dailyProgress;
    $managerTarget->daily_achieved = $dailyAchieved;
    $managerTarget->daily_remaining = max(0, $managerTarget->daily_target - $dailyAchieved);
    
    $managerTarget->monthly_progress = $monthlyProgress;
    $managerTarget->monthly_achieved = $monthlyAchieved;
    $managerTarget->monthly_remaining = max(0, $managerTarget->monthly_target - $monthlyAchieved);

    return $managerTarget;
}

    public function create()
    {
        $chefs = User::where('role', 'chef')->get();
        return view('admin.chef_targets.create', compact('chefs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'chef_id' => 'required|exists:users,id|unique:chef_targets,chef_id',
            'daily_target' => 'required|numeric|min:0',
            'monthly_target' => 'required|numeric|min:0',
            'fixed_salary' => 'required|numeric|min:0', // ✅ NEW
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'days_off' => 'nullable|array',
        ]);

        ChefTarget::create($request->all());

        return redirect()->route('admin.chef_targets.index')
            ->with('success', 'Chef target created successfully.');
    }

    public function edit(ChefTarget $chefTarget)
    {
        $chefs = User::where('role', 'chef')->get();
        return view('admin.chef_targets.edit', compact('chefTarget', 'chefs'));
    }

    public function update(Request $request, ChefTarget $chefTarget)
    {
        $request->validate([
            'chef_id' => 'required|exists:users,id|unique:chef_targets,chef_id,' . $chefTarget->id,
            'daily_target' => 'required|numeric|min:0',
            'monthly_target' => 'required|numeric|min:0',
            'fixed_salary' => 'required|numeric|min:0', // ✅ NEW
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'days_off' => 'nullable|array',
        ]);

        $chefTarget->update($request->all());

        return redirect()->route('admin.chef_targets.index')
            ->with('success', 'Chef target updated successfully.');
    }

    public function destroy(ChefTarget $chefTarget)
    {
        $chefTarget->delete();
        return redirect()->route('admin.chef_targets.index')
            ->with('success', 'Chef target deleted successfully.');
    }
}