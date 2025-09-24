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
        $totalNet      = Dispatch::sum('net_handover');

        // Breakdown by clearance
        $received      = Dispatch::where('handover_received', true)->sum('net_handover');
        $outstanding   = Dispatch::where('handover_received', false)->sum('net_handover');

        // Expenses
        $totalExpenses = Expense::sum('amount');

        return view('finance.dashboard', compact(
            'totalSales',
            'totalComm',
            'totalNet',
            'received',
            'outstanding',
            'totalExpenses'
        ));
    }
}
