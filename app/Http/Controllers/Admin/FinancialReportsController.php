<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\BankDeposit;
use App\Models\Banking;
use App\Models\Damage;
use App\Models\StaffBreakfast;
use App\Models\Production;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Ingredient;
use App\Models\KampalaSale;
use App\Models\KampalaExpense;
use App\Models\KampalaBanking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportsExport;
use Illuminate\Support\Facades\DB; 

class FinancialReportsController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->get('report_type', 'income_statement');
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->generateReportData($reportType, $period, $startDate, $endDate);

        return view('admin.reports.financial.index', compact(
            'reportType', 'period', 'startDate', 'endDate', 'data'
        ));
    }

    public function exportExcel(Request $request)
    {
        $reportType = $request->get('report_type', 'income_statement');
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->generateReportData($reportType, $period, $startDate, $endDate);

        $filename = $this->getFilename($reportType, $period, $startDate, $endDate);

        return Excel::download(new FinancialReportsExport($data, $reportType), $filename);
    }

    private function generateReportData($reportType, $period, $startDate, $endDate)
    {
        switch ($reportType) {
            case 'income_statement':
                return $this->generateIncomeStatement($startDate, $endDate);
            case 'balance_sheet':
                return $this->generateBalanceSheet($endDate);
            case 'cash_flow':
                return $this->generateCashFlowStatement($startDate, $endDate);
            default:
                return $this->generateIncomeStatement($startDate, $endDate);
        }
    }

    private function generateIncomeStatement($startDate, $endDate)
{
    // Sales Revenue
    $bakerySales = Sale::whereBetween('created_at', [$startDate, $endDate])->sum('total_price');
    
    // FIXED: Use correct column names for dispatch sales calculation
    $dispatchSales = DispatchItem::whereHas('dispatch', function($q) use ($startDate, $endDate) {
        $q->whereBetween('dispatch_date', [$startDate, $endDate]);
    })->sum(DB::raw('sold_qty * unit_price')); // Calculate sold_value from sold_qty * unit_price
    
    $kampalaSales = KampalaSale::whereBetween('created_at', [$startDate, $endDate])->sum('total_price');
    
    $damageSales = Damage::where('status', 'approved')
        ->whereNotNull('sold_quantity')
        ->whereBetween('updated_at', [$startDate, $endDate])
        ->sum(DB::raw('sold_quantity * approved_price'));

    $totalRevenue = $bakerySales + $dispatchSales + $kampalaSales + $damageSales;

    // Cost of Goods Sold (COGS)
    $ingredientCosts = $this->calculateIngredientCosts($startDate, $endDate);
    
    $packagingCosts = Expense::where('category', 'like', '%packaging%')
        ->whereBetween('expense_date', [$startDate, $endDate])
        ->sum('amount');

    $totalCOGS = $ingredientCosts + $packagingCosts;
    $grossProfit = $totalRevenue - $totalCOGS;

    // Operating Expenses
    $rentExpenses = Expense::where('category', 'like', '%rent%')
        ->whereBetween('expense_date', [$startDate, $endDate])
        ->sum('amount');
    
    $salaryExpenses = Expense::where('category', 'like', '%salary%')
        ->whereBetween('expense_date', [$startDate, $endDate])
        ->sum('amount');
    
    $utilityExpenses = Expense::where('category', 'like', '%utility%')
        ->whereBetween('expense_date', [$startDate, $endDate])
        ->sum('amount');
    
    $transportExpenses = Expense::where('category', 'like', '%transport%')
        ->whereBetween('expense_date', [$startDate, $endDate])
        ->sum('amount');
    
    $kampalaExpenses = KampalaExpense::whereBetween('expense_date', [$startDate, $endDate])
        ->sum('amount');
    
    $otherExpenses = Expense::whereNotIn('category', ['rent', 'salary', 'utility', 'transport'])
        ->whereBetween('expense_date', [$startDate, $endDate])
        ->sum('amount');

    $totalOperatingExpenses = $rentExpenses + $salaryExpenses + $utilityExpenses + 
                             $transportExpenses + $kampalaExpenses + $otherExpenses;

    $netProfit = $grossProfit - $totalOperatingExpenses;

    return [
        'report_type' => 'income_statement',
        'period' => "$startDate to $endDate",
        'revenue' => [
            'bakery_sales' => $bakerySales,
            'dispatch_sales' => $dispatchSales,
            'kampala_sales' => $kampalaSales,
            'damage_sales' => $damageSales,
            'total_revenue' => $totalRevenue,
        ],
        'cogs' => [
            'ingredient_costs' => $ingredientCosts,
            'packaging_costs' => $packagingCosts,
            'total_cogs' => $totalCOGS,
        ],
        'gross_profit' => $grossProfit,
        'operating_expenses' => [
            'rent' => $rentExpenses,
            'salaries' => $salaryExpenses,
            'utilities' => $utilityExpenses,
            'transport' => $transportExpenses,
            'kampala_expenses' => $kampalaExpenses,
            'other' => $otherExpenses,
            'total_expenses' => $totalOperatingExpenses,
        ],
        'net_profit' => $netProfit,
    ];
}

    private function generateBalanceSheet($asOfDate)
    {
        // Assets
        $cash = $this->calculateCashBalance($asOfDate);
        $inventory = $this->calculateInventoryValue();
        $equipment = 5000000; // Fixed value for equipment
        
        $totalAssets = $cash + $inventory + $equipment;

        // Liabilities
        $accountsPayable = 400000; // Fixed value
        $bankLoan = 2000000; // Fixed value
        
        $totalLiabilities = $accountsPayable + $bankLoan;

        // Equity
        $ownersEquity = $totalAssets - $totalLiabilities;

        return [
            'report_type' => 'balance_sheet',
            'as_of_date' => $asOfDate,
            'assets' => [
                'cash' => $cash,
                'inventory' => $inventory,
                'equipment' => $equipment,
                'total_assets' => $totalAssets,
            ],
            'liabilities' => [
                'accounts_payable' => $accountsPayable,
                'bank_loan' => $bankLoan,
                'total_liabilities' => $totalLiabilities,
            ],
            'equity' => [
                'owners_equity' => $ownersEquity,
            ],
        ];
    }

    private function generateCashFlowStatement($startDate, $endDate)
    {
        // Operating Activities
        $cashSales = Sale::where('payment_method', 'cash')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_price');
        
        $cashCollections = Banking::whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
        
        $cashExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');
        
        $kampalaCashExpenses = KampalaExpense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $netOperatingCash = $cashSales + $cashCollections - $cashExpenses - $kampalaCashExpenses;

        // Investing Activities (simplified)
        $equipmentPurchases = Expense::where('category', 'like', '%equipment%')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $netInvestingCash = -$equipmentPurchases; // Outflow

        // Financing Activities
        $loanReceived = 0; // You might want to track this separately
        $loanRepayments = 0; // You might want to track this separately

        $netFinancingCash = $loanReceived - $loanRepayments;

        $netCashFlow = $netOperatingCash + $netInvestingCash + $netFinancingCash;

        return [
            'report_type' => 'cash_flow',
            'period' => "$startDate to $endDate",
            'operating_activities' => [
                'cash_sales' => $cashSales,
                'cash_collections' => $cashCollections,
                'cash_expenses' => $cashExpenses + $kampalaCashExpenses,
                'net_operating_cash' => $netOperatingCash,
            ],
            'investing_activities' => [
                'equipment_purchases' => $equipmentPurchases,
                'net_investing_cash' => $netInvestingCash,
            ],
            'financing_activities' => [
                'loan_received' => $loanReceived,
                'loan_repayments' => $loanRepayments,
                'net_financing_cash' => $netFinancingCash,
            ],
            'net_cash_flow' => $netCashFlow,
        ];
    }

   private function calculateIngredientCosts($startDate, $endDate)
{
    // Simple calculation - you can enhance this based on your actual data structure
    // For now, return a fixed percentage of revenue or calculate based on production records
    
    $productions = Production::whereBetween('production_date', [$startDate, $endDate])->get();
    $totalIngredientCost = 0;

    foreach ($productions as $production) {
        // If you have ingredient cost tracking in your production model
        if (isset($production->total_ingredient_cost)) {
            $totalIngredientCost += $production->total_ingredient_cost;
        } else {
            // Estimate as 40% of production value if no specific cost tracking
            $totalIngredientCost += $production->total_value * 0.4;
        }
    }

    return $totalIngredientCost > 0 ? $totalIngredientCost : 0;
}

    private function calculateCashBalance($asOfDate)
    {
        // Calculate total cash balance
        $totalSales = Sale::where('payment_method', 'cash')
            ->whereDate('created_at', '<=', $asOfDate)
            ->sum('total_price');
        
        $totalBanked = BankDeposit::whereDate('deposit_date', '<=', $asOfDate)
            ->sum('amount') + Banking::whereDate('date', '<=', $asOfDate)
            ->sum('amount');
        
        $totalExpenses = Expense::whereDate('expense_date', '<=', $asOfDate)
            ->sum('amount') + KampalaExpense::whereDate('expense_date', '<=', $asOfDate)
            ->sum('amount');

        return $totalSales - $totalBanked - $totalExpenses;
    }

    private function calculateInventoryValue()
    {
        // Calculate current inventory value
        $ingredients = Ingredient::all();
        $totalValue = 0;

        foreach ($ingredients as $ingredient) {
            $totalValue += $ingredient->current_stock * $ingredient->unit_cost;
        }

        return $totalValue;
    }

    private function getFilename($reportType, $period, $startDate, $endDate)
    {
        $typeMap = [
            'income_statement' => 'Income_Statement',
            'balance_sheet' => 'Balance_Sheet',
            'cash_flow' => 'Cash_Flow_Statement'
        ];

        $type = $typeMap[$reportType] ?? 'Financial_Report';
        $dateRange = $period === 'custom' ? "{$startDate}_to_{$endDate}" : $period;

        return "{$type}_{$dateRange}_" . now()->format('Y_m_d_His') . '.xlsx';
    }
}