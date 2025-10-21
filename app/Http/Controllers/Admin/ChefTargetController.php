<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChefTarget;
use App\Models\ManagerTarget;
use App\Models\User;
use App\Models\Dispatch;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChefTargetController extends Controller
{
    public function index()
    {
        $targets = ChefTarget::with('chef')->paginate(15);
        $managers = User::where('role', 'manager')->get();
        $managerTargets = ManagerTarget::with('manager')->get();
        
        // Calculate progress for each manager target
        $managerTargetsWithProgress = $managerTargets->map(function ($managerTarget) {
            return $this->calculateManagerProgress($managerTarget);
        });
        
        return view('admin.chef_targets.index', compact(
            'targets', 
            'managers', 
            'managerTargetsWithProgress'
        ));
    }

    private function calculateManagerProgress($managerTarget)
    {
        $today = Carbon::today();
        
        // Settings from manager dashboard
        $excludedDriverIds = [20, 21]; // Nakato & Ariah
        $includedUserIds = [10, 13, 15, 17, 18]; // Sales, Mukasa, Abdu, Sales2, Umar

        // Calculate date ranges
        $dailyFrom = $today->copy()->startOfDay();
        $dailyTo = $today->copy()->endOfDay();
        
        $monthlyFrom = $today->copy()->startOfMonth();
        $monthlyTo = $today->copy()->endOfMonth();

        // Calculate daily progress
        $dailyIncludedDispatches = DB::table('dispatches as d')
            ->join('users as u', 'd.driver_id', '=', 'u.id')
            ->whereNotIn('d.driver_id', $excludedDriverIds)
            ->whereBetween('d.dispatch_date', [$dailyFrom, $dailyTo])
            ->sum('d.total_sales_value');

        $dailyBakerySales = DB::table('sales as s')
            ->whereIn('s.user_id', $includedUserIds)
            ->whereBetween('s.created_at', [$dailyFrom, $dailyTo])
            ->sum('s.total_price');

        $dailyAchieved = (float) $dailyIncludedDispatches + (float) $dailyBakerySales;
        $dailyTarget = $managerTarget->daily_target;
        $dailyProgress = $dailyTarget > 0 ? round(($dailyAchieved / $dailyTarget) * 100, 2) : 0;

        // Calculate monthly progress
        $monthlyIncludedDispatches = DB::table('dispatches as d')
            ->join('users as u', 'd.driver_id', '=', 'u.id')
            ->whereNotIn('d.driver_id', $excludedDriverIds)
            ->whereBetween('d.dispatch_date', [$monthlyFrom, $monthlyTo])
            ->sum('d.total_sales_value');

        $monthlyBakerySales = DB::table('sales as s')
            ->whereIn('s.user_id', $includedUserIds)
            ->whereBetween('s.created_at', [$monthlyFrom, $monthlyTo])
            ->sum('s.total_price');

        $monthlyAchieved = (float) $monthlyIncludedDispatches + (float) $monthlyBakerySales;
        $monthlyTarget = $managerTarget->monthly_target;
        $monthlyProgress = $monthlyTarget > 0 ? round(($monthlyAchieved / $monthlyTarget) * 100, 2) : 0;

        // Add progress data to manager target
        $managerTarget->daily_progress = $dailyProgress;
        $managerTarget->daily_achieved = $dailyAchieved;
        $managerTarget->daily_remaining = max(0, $dailyTarget - $dailyAchieved);
        
        $managerTarget->monthly_progress = $monthlyProgress;
        $managerTarget->monthly_achieved = $monthlyAchieved;
        $managerTarget->monthly_remaining = max(0, $monthlyTarget - $monthlyAchieved);

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