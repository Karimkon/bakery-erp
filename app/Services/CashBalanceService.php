<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Banking; // Only this banking model affects bakery cash
use App\Models\Expense;
use Carbon\Carbon;

class CashBalanceService
{
    public function getAvailableCash($date = null)
    {
        if (!$date) {
            $date = Carbon::today();
        }

        // 1. CASH SALES - Money that came INTO the bakery desk as physical cash
        $cashSales = Sale::whereDate('created_at', '<=', $date)
            ->where('payment_method', 'cash')
            ->sum('total_price');

        // 2. MONEY BANKED - Only Banking model (bakery bankings) taken OUT of bakery desk
        $totalBanked = Banking::whereDate('date', '<=', $date)
            ->sum('amount');

        // 3. EXPENSES - Money taken OUT of bakery desk for expenses (paid in cash)
        $totalExpenses = Expense::whereDate('expense_date', '<=', $date)
            ->sum('amount');

        // PHYSICAL CASH CALCULATION:
        // Starting cash (0) + Cash Sales - Bakery Bankings - Expenses
        $availableCash = $cashSales - $totalBanked - $totalExpenses;

        return [
            'cash_sales' => $cashSales,           // Money that came IN as cash
            'total_banked' => $totalBanked,       // Money taken OUT to bank (Bakery only)
            'total_expenses' => $totalExpenses,   // Money taken OUT for expenses
            'available_cash' => max(0, $availableCash), // Physical cash remaining in bakery desk
            'calculation_date' => $date
        ];
    }

    // Get today's cash movements only
    public function getTodayCashFlow()
    {
        $today = Carbon::today();
        
        // Today's cash sales (money IN)
        $todayCashSales = Sale::whereDate('created_at', $today)
            ->where('payment_method', 'cash')
            ->sum('total_price');

        // Today's expenses (money OUT)
        $todayExpenses = Expense::whereDate('expense_date', $today)
            ->sum('amount');

        // Today's bankings (money OUT) - Only bakery bankings
        $todayBanked = Banking::whereDate('date', $today)
            ->sum('amount');

        return [
            'today_cash_sales' => $todayCashSales,     // IN today
            'today_expenses' => $todayExpenses,        // OUT today
            'today_banked' => $todayBanked,            // OUT today (Bakery only)
            'today_net_cash' => $todayCashSales - $todayExpenses - $todayBanked // Net change today
        ];
    }
}