<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Banking; // Only this banking model affects bakery cash
use App\Models\Expense;
use App\Models\Damage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        // ✅ ADD: DAMAGE SALES REVENUE - Money from selling damaged products (also physical cash)
        $damageRevenue = Damage::where('status', 'approved')
            ->whereNotNull('sold_quantity')
            ->where('sold_quantity', '>', 0)
            ->whereDate('updated_at', '<=', $date)
            ->sum(DB::raw('sold_quantity * approved_price'));

        // 2. MONEY BANKED - Only Banking model (bakery bankings) taken OUT of bakery desk
        $totalBanked = Banking::whereDate('date', '<=', $date)
            ->sum('amount');

        // 3. EXPENSES - Money taken OUT of bakery desk for expenses (paid in cash)
        $totalExpenses = Expense::whereDate('expense_date', '<=', $date)
            ->sum('amount');

        // PHYSICAL CASH CALCULATION:
        // Starting cash (0) + Cash Sales + Damage Sales Revenue - Bakery Bankings - Expenses
        $availableCash = $cashSales + $damageRevenue - $totalBanked - $totalExpenses;

        return [
            'cash_sales' => $cashSales,           // Money that came IN as cash from regular sales
            'damage_revenue' => $damageRevenue,   // ✅ NEW: Money that came IN from damage sales
            'total_cash_inflow' => $cashSales + $damageRevenue, // ✅ NEW: Total money IN
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

        // ✅ ADD: Today's damage sales revenue (money IN)
        $todayDamageRevenue = Damage::where('status', 'approved')
            ->whereNotNull('sold_quantity')
            ->where('sold_quantity', '>', 0)
            ->whereDate('updated_at', $today)
            ->sum(DB::raw('sold_quantity * approved_price'));

        // Today's expenses (money OUT)
        $todayExpenses = Expense::whereDate('expense_date', $today)
            ->sum('amount');

        // Today's bankings (money OUT) - Only bakery bankings
        $todayBanked = Banking::whereDate('date', $today)
            ->sum('amount');

        return [
            'today_cash_sales' => $todayCashSales,     // IN today from regular sales
            'today_damage_revenue' => $todayDamageRevenue, // ✅ NEW: IN today from damage sales
            'today_total_cash_inflow' => $todayCashSales + $todayDamageRevenue, // ✅ NEW: Total IN today
            'today_expenses' => $todayExpenses,        // OUT today
            'today_banked' => $todayBanked,            // OUT today (Bakery only)
            'today_net_cash' => ($todayCashSales + $todayDamageRevenue) - $todayExpenses - $todayBanked // Net change today
        ];
    }
}