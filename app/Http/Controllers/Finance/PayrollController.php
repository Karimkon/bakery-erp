<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use App\Models\ChefTarget;
use App\Models\Production;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollController extends Controller
{
    /**
     * Display payroll records with filters
     */
    public function index(Request $request)
    {
        $query = Payroll::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('pay_month')) {
            $query->whereMonth('pay_month', date('m', strtotime($request->pay_month)))
                  ->whereYear('pay_month', date('Y', strtotime($request->pay_month)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls = $query->latest()->paginate(20);
        $users = User::orderBy('name')->get();

        return view('finance.payrolls.index', compact('payrolls','users'));
    }

    /**
     * Show create payroll form with preview
     */
    public function create(Request $request)
    {
        $users = User::whereHas('chefTarget')->orderBy('name')->get();
        
        $preview = null;
        
        // If user selects chef and month, show preview
        if ($request->filled('preview_user_id') && $request->filled('preview_month')) {
            $userId = $request->preview_user_id;
            $month = $request->preview_month;
            
            $preview = $this->getPayrollPreview($userId, $month);
        }

        return view('finance.payrolls.create', compact('users', 'preview'));
    }

    /**
     * Store payroll with automatic commission
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'pay_month' => 'required|date',
        ]);

        $user = User::findOrFail($data['user_id']);
        $month = $data['pay_month'];

        // Check if payroll already exists for this user and month
        $exists = Payroll::where('user_id', $user->id)
            ->whereYear('pay_month', date('Y', strtotime($month)))
            ->whereMonth('pay_month', date('m', strtotime($month)))
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'Payroll for this user and month already exists.']);
        }

        // Get calculated values
        $preview = $this->getPayrollPreview($user->id, $month);

        // Create payroll
        Payroll::create([
            'user_id' => $user->id,
            'employee_name' => $user->name,
            'pay_month' => $month,
            'base_salary' => 0, // Base salary is 0 since commission = salary
            'commission' => $preview['commission'],
            'total_salary' => $preview['commission'],
            'status' => 'pending',
        ]);

        return redirect()->route('finance.payrolls.index')
            ->with('success', 'Payroll recorded successfully with auto-calculated commission of UGX ' . number_format($preview['commission']));
    }

    /**
     * Edit payroll
     */
    public function edit(Payroll $payroll)
    {
        $users = User::orderBy('name')->get();
        return view('finance.payrolls.edit', compact('payroll','users'));
    }

    /**
     * Update payroll status only
     */
    public function update(Request $request, Payroll $payroll)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,paid'
        ]);

        $payroll->update($data);

        return redirect()->route('finance.payrolls.index')
            ->with('success','Payroll status updated to ' . $data['status']);
    }

    /**
     * Generate payslip
     */
    public function payslip(Payroll $payroll)
    {
        $pdf = Pdf::loadView('finance.payrolls.payslip', compact('payroll'))
                  ->setPaper('A5', 'portrait');

        return $pdf->stream("Payslip_{$payroll->user->name}_{$payroll->pay_month->format('Y-m')}.pdf");
    }

    /**
     * Get payroll preview with all calculations
     */
    private function getPayrollPreview(int $userId, string $month): array
    {
        $user = User::findOrFail($userId);
        $target = ChefTarget::where('chef_id', $userId)->first();

        if (!$target) {
            return [
                'error' => 'No target set for this chef',
                'monthly_target' => 0,
                'total_produced' => 0,
                'working_days' => 0,
                'days_worked' => 0,
                'percentage' => 0,
                'commission' => 0,
            ];
        }

        $year = Carbon::parse($month)->year;
        $monthNumber = Carbon::parse($month)->month;
        $startDate = Carbon::createFromDate($year, $monthNumber, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Get all productions for the month with detailed logging
        $productions = Production::where('user_id', $userId)
            ->whereYear('production_date', $year)
            ->whereMonth('production_date', $monthNumber)
            ->get();

        \Log::info('Payroll Preview Debug', [
            'user_id' => $userId,
            'month' => $month,
            'year' => $year,
            'monthNumber' => $monthNumber,
            'productions_count' => $productions->count(),
            'productions' => $productions->pluck('production_date', 'id')->toArray()
        ]);

        // Calculate working days (excluding days off)
        $workingDays = 0;
        $daysWorked = 0;
        $totalProduced = 0;
        $productionDates = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayName = strtolower($date->format('l'));
            
            // Skip days off
            if (in_array($dayName, $target->days_off ?? [])) {
                continue;
            }

            $workingDays++;

            // Check if chef worked this day - try both date formats
            $dateString = $date->format('Y-m-d');
            $production = $productions->first(function($p) use ($dateString) {
                // Handle both Carbon and string dates
                $prodDate = $p->production_date instanceof Carbon 
                    ? $p->production_date->format('Y-m-d')
                    : Carbon::parse($p->production_date)->format('Y-m-d');
                
                return $prodDate === $dateString;
            });

            if ($production) {
                $daysWorked++;
                $totalProduced += (float)$production->total_value;
                $productionDates[] = $dateString;
            }
        }

        \Log::info('Production Calculation', [
            'working_days' => $workingDays,
            'days_worked' => $daysWorked,
            'total_produced' => $totalProduced,
            'production_dates' => $productionDates
        ]);

        // Calculate percentage achieved
        $percentage = $target->monthly_target > 0
            ? ($totalProduced / $target->monthly_target) * 100
            : 0;

        // ✅ NEW CALCULATION: Salary is proportional to target achievement
        // Commission = (Percentage / 100) × Fixed Salary × (Commission % / 100)
        $commission = ($percentage / 100) * $target->fixed_salary * ($target->commission_percentage / 100);

        return [
            'user_name' => $user->name,
            'monthly_target' => $target->monthly_target,
            'daily_target' => $target->daily_target,
            'fixed_salary' => $target->fixed_salary, // ✅ NEW
            'commission_percentage' => $target->commission_percentage,
            'total_produced' => $totalProduced,
            'working_days' => $workingDays,
            'days_worked' => $daysWorked,
            'percentage' => round($percentage, 2),
            'commission' => round($commission, 2),
            'production_dates' => $productionDates, // for debugging
        ];
    }
}