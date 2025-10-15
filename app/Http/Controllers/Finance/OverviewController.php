<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\DriverExpense;
use Carbon\Carbon;

class OverviewController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'today');
        $today = Carbon::today();
        $now = Carbon::now();

        switch ($filter) {
            case 'week':
                $from = $today->startOfWeek();
                $title = 'This Week';
                break;
            case 'month':
                $from = $today->startOfMonth();
                $title = 'This Month';
                break;
            default:
                $from = $today;
                $title = 'Today';
        }

        $to = $now;

        // General Expenses
        $generalExpenses = Expense::with('recorder')
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->get();

        $totalGeneral = $generalExpenses->sum('amount');

        // Driver Expenses
        $driverExpenses = DriverExpense::with('driver')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->get();

        $totalDriver = $driverExpenses->sum('amount');

        $combinedTotal = $totalGeneral + $totalDriver;

        return view('finance.overview.index', compact(
            'generalExpenses',
            'driverExpenses',
            'totalGeneral',
            'totalDriver',
            'combinedTotal',
            'filter',
            'title'
        ));
    }
}
