<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\Expense;

class DashboardController extends Controller
{
    public function index()
    {
        // Totals
        $totalSales    = Dispatch::sum('total_sales_value');
        $totalComm     = Dispatch::sum('commission_total');

        // Expenses
        $totalExpenses = Expense::sum('amount');

        return view('finance.dashboard', compact(
            'totalSales',
            'totalComm',
            'totalExpenses'
        ));
    }
}
