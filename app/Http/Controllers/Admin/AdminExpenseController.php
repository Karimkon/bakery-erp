<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\DriverExpense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminExpenseController extends Controller
{
    public function dashboard(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->toDateString());
        $selectedDriver = $request->input('driver_id');

        // Base query
        $expensesQuery = DriverExpense::with('driver', 'dispatch')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($selectedDriver) {
            $expensesQuery->where('driver_id', $selectedDriver);
        }

        $expenses = $expensesQuery->orderBy('created_at', 'desc')->get();

        // Summary Statistics
        $totalExpenses = $expenses->sum('amount');
        $totalDispatches = $expenses->pluck('dispatch_id')->unique()->count();
        $averagePerDispatch = $totalDispatches > 0 ? $totalExpenses / $totalDispatches : 0;

        // Expenses by Type
        $expensesByType = $expenses->groupBy('expense_type')->map(function ($group) {
            return [
                'total' => $group->sum('amount'),
                'count' => $group->count(),
                'average' => $group->avg('amount'),
            ];
        });

        // Daily Trend
        $dailyTrend = $expenses->groupBy(function ($expense) {
            return Carbon::parse($expense->created_at)->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('amount');
        });

        // Top Expense Types
        $topExpenseTypes = $expensesByType->sortByDesc('total')->take(5);

        // Driver Comparison
        $driverExpenses = $expenses->groupBy('driver_id')->map(function ($group) {
            return [
                'driver' => $group->first()->driver->name,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
                'average' => $group->avg('amount'),
            ];
        })->sortByDesc('total');

        // Recent Expenses
        $recentExpenses = $expenses->take(20);

        // Drivers for filter
        $drivers = User::where('role', 'driver')->orderBy('name')->get();

        // Alerts - High expenses
        $highExpenses = $expenses->where('amount', '>', 100000); // Over 100k
        $expensesWithoutReceipts = $expenses->whereNull('receipt_image')->where('amount', '>', 20000);

        return view('admin.expenses.dashboard', compact(
            'expenses',
            'totalExpenses',
            'totalDispatches',
            'averagePerDispatch',
            'expensesByType',
            'dailyTrend',
            'topExpenseTypes',
            'driverExpenses',
            'recentExpenses',
            'drivers',
            'dateFrom',
            'dateTo',
            'selectedDriver',
            'highExpenses',
            'expensesWithoutReceipts'
        ));
    }

    public function dailyReport(Request $request)
    {
        $date = $request->input('date', Carbon::now()->toDateString());

        $dispatches = Dispatch::with('driver', 'expenses', 'items')
            ->whereDate('dispatch_date', $date)
            ->orderBy('driver_id')
            ->get();

        $totalExpenses = 0;
        $totalSales = 0;
        $totalCommission = 0;
        $totalCashReceived = 0;

        foreach ($dispatches as $dispatch) {
            $totalExpenses += $dispatch->driver_expenses;
            $totalSales += $dispatch->total_sales_value;
            $totalCommission += $dispatch->commission_total;
            $totalCashReceived += $dispatch->cash_received;
        }

        $netProfit = $totalSales - $totalCommission - $totalExpenses;

        return view('admin.expenses.daily-report', compact(
            'dispatches',
            'date',
            'totalExpenses',
            'totalSales',
            'totalCommission',
            'totalCashReceived',
            'netProfit'
        ));
    }

    public function driverAnalysis(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $drivers = User::where('role', 'driver')
            ->with(['dispatches' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('dispatch_date', [$startDate, $endDate])
                    ->with('expenses');
            }])
            ->get();

        $driverAnalysis = [];

        foreach ($drivers as $driver) {
            $dispatches = $driver->dispatches;
            $totalExpenses = $dispatches->sum('driver_expenses');
            $totalSales = $dispatches->sum('total_sales_value');
            $totalCommission = $dispatches->sum('commission_total');
            $dispatchCount = $dispatches->count();

            // Expense breakdown
            $expenseBreakdown = [];
            foreach ($dispatches as $dispatch) {
                foreach ($dispatch->expenses as $expense) {
                    if (!isset($expenseBreakdown[$expense->expense_type])) {
                        $expenseBreakdown[$expense->expense_type] = 0;
                    }
                    $expenseBreakdown[$expense->expense_type] += $expense->amount;
                }
            }

            $driverAnalysis[] = [
                'driver' => $driver,
                'dispatch_count' => $dispatchCount,
                'total_sales' => $totalSales,
                'total_expenses' => $totalExpenses,
                'total_commission' => $totalCommission,
                'average_expense_per_dispatch' => $dispatchCount > 0 ? $totalExpenses / $dispatchCount : 0,
                'expense_to_sales_ratio' => $totalSales > 0 ? ($totalExpenses / $totalSales) * 100 : 0,
                'expense_breakdown' => $expenseBreakdown,
            ];
        }

        return view('admin.expenses.driver-analysis', compact('driverAnalysis', 'month'));
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->toDateString());

        $expenses = DriverExpense::with('driver', 'dispatch')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "expenses_report_{$dateFrom}_to_{$dateTo}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($expenses) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Date',
                'Driver',
                'Dispatch #',
                'Expense Type',
                'Amount (UGX)',
                'Description',
                'Has Receipt'
            ]);

            // Data rows
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->created_at->format('Y-m-d'),
                    $expense->driver->name,
                    $expense->dispatch->dispatch_no ?? 'N/A',
                    DriverExpense::expenseTypes()[$expense->expense_type] ?? $expense->expense_type,
                    number_format($expense->amount, 2, '.', ''),
                    $expense->description ?? '',
                    $expense->receipt_image ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}