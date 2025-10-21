<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Banking;
use App\Models\Expense;
use Carbon\Carbon;

class CashBalanceService
{
    public function getAvailableCash($date = null)
    {
        if (!$date) {
            $date = Carbon::today(); // Change from all-time to today only
        }

        // Total sales for the period
        $totalSales = Sale::whereDate('created_at', '<=', $date)
        ->where('payment_method', 'cash')
            ->sum('total_price');

        // Total money banked for the period
        $totalBanked = Banking::whereDate('date', '<=', $date)
            ->sum('amount');

        // Total expenses for the period
        $totalExpenses = Expense::whereDate('expense_date', '<=', $date)
            ->sum('amount');

        // Available cash = Total Sales - Banked - Expenses
        $availableCash = $totalSales - $totalBanked - $totalExpenses;

        return [
            'total_sales' => $totalSales,
            'total_banked' => $totalBanked,
            'total_expenses' => $totalExpenses,
            'available_cash' => $availableCash,
            'calculation_date' => $date
        ];
    }

    // Add method for all-time balance if needed
    public function getAllTimeBalance()
    {
        $totalSales = Sale::sum('total_price');
        $totalBanked = Banking::sum('amount');
        $totalExpenses = Expense::sum('amount');
        $availableCash = $totalSales - $totalBanked - $totalExpenses;

        return [
            'total_sales' => $totalSales,
            'total_banked' => $totalBanked,
            'total_expenses' => $totalExpenses,
            'available_cash' => $availableCash,
            'period' => 'all-time'
        ];
    }
}