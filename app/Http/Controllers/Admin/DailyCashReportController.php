<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\BankDeposit;
use App\Models\Banking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\CashBalanceService; // Add this import
use Illuminate\Support\Str; // Add this import for Str::limit

class DailyCashReportController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);
        
        // Get all data for the selected date
        $sales = Sale::with('user')
            ->whereDate('created_at', $selectedDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $expenses = Expense::with('recorder')
            ->whereDate('expense_date', $selectedDate)
            ->orderBy('expense_date', 'desc')
            ->get();

        $bankDeposits = BankDeposit::with(['depositor', 'recorder'])
            ->whereDate('deposit_date', $selectedDate)
            ->orderBy('deposit_date', 'desc')
            ->get();

        $driverBankings = Banking::with('user')
            ->whereDate('date', $selectedDate)
            ->orderBy('date', 'desc')
            ->get();

        // Calculate totals
        $totalSales = $sales->sum('total_price');
        $cashSales = $sales->where('payment_method', 'cash')->sum('total_price');
        $mobileSales = $sales->where('payment_method', 'mobile')->sum('total_price');
        $totalExpenses = $expenses->sum('amount');
        $totalBankDeposits = $bankDeposits->sum('amount');
        $totalDriverBankings = $driverBankings->sum('amount');
        $totalBanked = $totalBankDeposits + $totalDriverBankings;

        // Calculate expected cash
        $expectedCash = $cashSales - $totalExpenses - $totalBanked;

        // Get real-time cash balance using your CashBalanceService
        $cashService = new CashBalanceService();
        $cashBalance = $cashService->getAvailableCash();
        $todayFlow = $cashService->getTodayCashFlow();

        // Use array syntax for compact to avoid syntax errors
        return view('admin.reports.daily-cash', [
            'date' => $date,
            'selectedDate' => $selectedDate,
            'sales' => $sales,
            'expenses' => $expenses,
            'bankDeposits' => $bankDeposits,
            'driverBankings' => $driverBankings,
            'totalSales' => $totalSales,
            'cashSales' => $cashSales,
            'mobileSales' => $mobileSales,
            'totalExpenses' => $totalExpenses,
            'totalBankDeposits' => $totalBankDeposits,
            'totalDriverBankings' => $totalDriverBankings,
            'totalBanked' => $totalBanked,
            'expectedCash' => $expectedCash,
            'availableCash' => $cashBalance['available_cash'],
            'todayCashSales' => $todayFlow['today_cash_sales'],
            'todayExpenses' => $todayFlow['today_expenses'],
            'todayBankings' => $todayFlow['today_banked'],
            'todayNetCash' => $todayFlow['today_net_cash'],
            'totalCashSales' => $cashBalance['cash_sales'],
            'totalExpensesBreakdown' => $cashBalance['total_expenses'],
            'totalBankings' => $cashBalance['total_banked'],
            'availableCashBreakdown' => $cashBalance['available_cash']
        ]);
    }

    public function dateRange(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::today()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Get daily summaries
        $dailyReports = DB::table('sales')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as total_sales'),
                DB::raw('SUM(CASE WHEN payment_method = "cash" THEN total_price ELSE 0 END) as cash_sales'),
                DB::raw('SUM(CASE WHEN payment_method = "mobile" THEN total_price ELSE 0 END) as mobile_sales')
            )
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy('date')
            ->get();

        // Get expenses by date
        $dailyExpenses = DB::table('expenses')
            ->select(
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as total_expenses')
            )
            ->whereDate('expense_date', '>=', $start)
            ->whereDate('expense_date', '<=', $end)
            ->groupBy('date')
            ->get();

        // Get bank deposits by date
        $dailyBankDeposits = DB::table('bank_deposits')
            ->select(
                DB::raw('DATE(deposit_date) as date'),
                DB::raw('SUM(amount) as total_deposits')
            )
            ->whereDate('deposit_date', '>=', $start)
            ->whereDate('deposit_date', '<=', $end)
            ->groupBy('date')
            ->get();

        // Get driver bankings by date
        $dailyDriverBankings = DB::table('bankings')
            ->select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(amount) as total_bankings')
            )
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->groupBy('date')
            ->get();

        // Combine all data
        $dates = [];
        $current = $start->copy();
        
        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $salesData = $dailyReports->firstWhere('date', $dateStr);
            $expenseData = $dailyExpenses->firstWhere('date', $dateStr);
            $depositData = $dailyBankDeposits->firstWhere('date', $dateStr);
            $bankingData = $dailyDriverBankings->firstWhere('date', $dateStr);

            $dates[$dateStr] = [
                'total_sales' => $salesData->total_sales ?? 0,
                'cash_sales' => $salesData->cash_sales ?? 0,
                'mobile_sales' => $salesData->mobile_sales ?? 0,
                'total_expenses' => $expenseData->total_expenses ?? 0,
                'total_deposits' => $depositData->total_deposits ?? 0,
                'total_bankings' => $bankingData->total_bankings ?? 0,
                'expected_cash' => ($salesData->cash_sales ?? 0) - ($expenseData->total_expenses ?? 0) - (($depositData->total_deposits ?? 0) + ($bankingData->total_bankings ?? 0))
            ];

            $current->addDay();
        }

        return view('admin.reports.daily-cash-range', compact(
            'startDate',
            'endDate',
            'dates'
        ));
    }

    public function getDailySummary(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);
        
        // Get all data for the selected date
        $sales = Sale::with('user')
            ->whereDate('created_at', $selectedDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $expenses = Expense::with('recorder')
            ->whereDate('expense_date', $selectedDate)
            ->orderBy('expense_date', 'desc')
            ->get();

        $bankings = Banking::with('user')
            ->whereDate('date', $selectedDate)
            ->orderBy('date', 'desc')
            ->get();

        $cashSales = $sales->where('payment_method', 'cash')->sum('total_price');
        $totalExpenses = $expenses->sum('amount');
        $totalBanked = $bankings->sum('amount');
        $expectedCash = $cashSales - $totalExpenses - $totalBanked;

        return response()->json([
            'date' => $selectedDate->format('Y-m-d'),
            'formatted_date' => $selectedDate->format('M d, Y'),
            'summary' => [
                'cash_sales' => $cashSales,
                'total_expenses' => $totalExpenses,
                'total_banked' => $totalBanked,
                'expected_cash' => $expectedCash,
            ],
            'records' => [
                'sales_count' => $sales->count(),
                'expenses_count' => $expenses->count(),
                'bankings_count' => $bankings->count(),
                'sales' => $sales->take(10)->map(function($sale) {
                    return [
                        'time' => $sale->created_at->format('H:i'),
                        'product' => ucfirst(str_replace('_', ' ', $sale->product_type)),
                        'quantity' => $sale->quantity,
                        'amount' => number_format($sale->total_price),
                        'method' => $sale->payment_method,
                        'user' => $sale->user->name ?? 'System'
                    ];
                }),
                'expenses' => $expenses->take(10)->map(function($expense) {
                    return [
                        'category' => $expense->category,
                        'description' => Str::limit($expense->description, 30),
                        'amount' => number_format($expense->amount),
                        'has_receipt' => !empty($expense->receipt),
                        'recorded_by' => $expense->recorder->name
                    ];
                }),
                'bankings' => $bankings->take(10)->map(function($banking) {
                    return [
                        'user' => $banking->user->name,
                        'amount' => number_format($banking->amount),
                        'receipt_number' => $banking->receipt_number ?? '-',
                        'has_receipt' => !empty($banking->receipt_file),
                        'notes' => $banking->notes
                    ];
                })
            ]
        ]);
    }

    public function getCashBalance()
{
    $cashService = new CashBalanceService();
    $cashBalance = $cashService->getAvailableCash();
    $todayFlow = $cashService->getTodayCashFlow();

    return response()->json([
        'available_cash' => $cashBalance['available_cash'],
        'cash_sales' => $cashBalance['cash_sales'],
        'total_expenses' => $cashBalance['total_expenses'],
        'total_banked' => $cashBalance['total_banked'],
        'today_cash_sales' => $todayFlow['today_cash_sales'],
        'today_expenses' => $todayFlow['today_expenses'],
        'today_banked' => $todayFlow['today_banked'],
        'today_net_cash' => $todayFlow['today_net_cash'],
    ]);
}
}