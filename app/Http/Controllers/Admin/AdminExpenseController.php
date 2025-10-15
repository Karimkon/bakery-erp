<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\DriverExpense;
use App\Models\Dispatch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminExpenseController extends Controller
{
    public function dashboard(Request $request)
    {
        // --- Filters ---
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->toDateString());
        $selectedDriver = $request->input('driver_id');

        // --- General Expenses ---
        $generalExpensesQuery = Expense::with('recorder')
            ->whereBetween('expense_date', [$dateFrom, $dateTo]);

        $generalExpenses = $generalExpensesQuery->get();
        $totalGeneral = $generalExpenses->sum('amount');

        // --- Driver Expenses ---
        $driverExpensesQuery = DriverExpense::with('driver', 'dispatch')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($selectedDriver) {
            $driverExpensesQuery->where('driver_id', $selectedDriver);
        }

        $driverExpenses = $driverExpensesQuery->orderBy('created_at', 'desc')->get();
        $totalDriver = $driverExpenses->sum('amount');

        // --- Combined Total ---
        $combinedTotal = $totalGeneral + $totalDriver;

        // --- Summary Stats ---
        $totalDispatches = $driverExpenses->pluck('dispatch_id')->unique()->count();
        $averagePerDispatch = $totalDispatches > 0 ? $totalDriver / $totalDispatches : 0;

        // --- Expenses by Type (Driver Expenses) ---
        $expensesByType = $driverExpenses->groupBy('expense_type')->map(function ($group) {
            return [
                'total' => $group->sum('amount'),
                'count' => $group->count(),
                'average' => $group->avg('amount'),
            ];
        });

        // --- Daily Trend ---
        $dailyTrend = $driverExpenses->groupBy(function ($expense) {
            return Carbon::parse($expense->created_at)->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('amount');
        });

        // --- Top Expense Types ---
        $topExpenseTypes = $expensesByType->sortByDesc('total')->take(5);

        // --- Driver Comparison ---
        $driverComparison = $driverExpenses->groupBy('driver_id')->map(function ($group) {
            return [
                'driver' => $group->first()->driver->name,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
                'average' => $group->avg('amount'),
            ];
        })->sortByDesc('total');

        // --- Recent Expenses ---
        $recentExpenses = $driverExpenses->take(20);

        // --- Alerts ---
        $highExpenses = $driverExpenses->where('amount', '>', 100000); // Over 100k
        $expensesWithoutReceipts = $driverExpenses->whereNull('receipt_image')->where('amount', '>', 20000);

        // --- Drivers for filter ---
        $drivers = User::where('role', 'driver')->orderBy('name')->get();

        return view('admin.expenses.dashboard', compact(
            'generalExpenses',
            'driverExpenses',
            'totalGeneral',
            'totalDriver',
            'combinedTotal',
            'totalDispatches',
            'averagePerDispatch',
            'expensesByType',
            'dailyTrend',
            'topExpenseTypes',
            'driverComparison',
            'recentExpenses',
            'highExpenses',
            'expensesWithoutReceipts',
            'drivers',
            'dateFrom',
            'dateTo',
            'selectedDriver'
        ));
    }

    // --- Optional: Export CSV ---
    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->toDateString());
        $driverId = $request->input('driver_id');

        $driverExpensesQuery = DriverExpense::with('driver', 'dispatch')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($driverId) {
            $driverExpensesQuery->where('driver_id', $driverId);
        }

        $driverExpenses = $driverExpensesQuery->get();

        $filename = "driver_expenses_{$dateFrom}_to_{$dateTo}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($driverExpenses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Driver', 'Dispatch #', 'Expense Type', 'Amount (UGX)', 'Description', 'Has Receipt']);

            foreach ($driverExpenses as $expense) {
                fputcsv($file, [
                    $expense->created_at->format('Y-m-d'),
                    $expense->driver->name,
                    $expense->dispatch->dispatch_no ?? 'N/A',
                    $expense->expense_type,
                    number_format($expense->amount, 2, '.', ''),
                    $expense->description ?? '',
                    $expense->receipt_image ? 'Yes' : 'No'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
