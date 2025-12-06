<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Dispatch;
use App\Models\BakeryStock;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Sale; 
use App\Models\Expense;
use App\Models\StockHistory;
use App\Models\BankDeposit;
use App\Models\Damage;
use App\Models\StaffBreakfast;
use App\Models\Banking;




class DashboardController extends Controller
{
    /**
     * Admin dashboard
     * - supports filter: today | week | month (via GET param `filter`)
     * - shows production & dispatch totals for the selected range
     * - shows total dispatched items (count)
     * - prepares last-7-days chart (production vs dispatch)
     */
    public function index(Request $request)
{
    $filter = $request->query('filter', 'today'); // 'today'|'week'|'month'
    $today = Carbon::today();
    $now = Carbon::now();

    switch ($filter) {
        case 'week':
            $from = Carbon::now()->startOfWeek();
            $title = 'This Week';
            break;
        case 'month':
            $from = Carbon::now()->startOfMonth();
            $title = 'This Month';
            break;
        default:
            $from = $today->startOfDay();
            $title = 'Today';
    }

    $to = $now->endOfDay();

    // Total spent on approved staff breakfasts for this period
    $totalBreakfastCost = StaffBreakfast::where('status', 'approved')
        ->whereDate('created_at', '>=', $from->toDateString())
        ->whereDate('created_at', '<=', $to->toDateString())
        ->sum('total_value');

    $kampalaSales = \App\Models\KampalaSale::whereDate('created_at', '>=', $from->toDateString())
    ->whereDate('created_at', '<=', $to->toDateString())
    ->sum('total_price');

$kampalaBanked = \App\Models\KampalaBanking::whereDate('date', '>=', $from->toDateString())
    ->whereDate('date', '<=', $to->toDateString())
    ->sum('amount');


    // Summary counts
    $totalUsers = User::count();
    $totalProductions = Production::count();
    $totalDispatches = Dispatch::count();
    $todayProductions = Production::whereDate('production_date', $today)->count();

    // Production & Dispatch values
    $productionValue = (float) Production::whereDate('production_date', '>=', $from->toDateString())
        ->whereDate('production_date', '<=', $to->toDateString())
        ->sum('total_value');

    $dispatchValue = (float) Dispatch::whereDate('dispatch_date', '>=', $from->toDateString())
        ->whereDate('dispatch_date', '<=', $to->toDateString())
        ->sum('total_sales_value');

    // --- Gross Profit Calculation ---
    $productPrices = config('bakery_products');
    $grossProfit = 0;

    // 1. Dispatch items revenue
    $dispatchItems = DB::table('dispatch_items')
        ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
        ->whereDate('dispatches.dispatch_date', '>=', $from->toDateString())
        ->whereDate('dispatches.dispatch_date', '<=', $to->toDateString())
        ->select('dispatch_items.product', DB::raw('SUM(dispatch_items.sold_qty) as total_sold'))
        ->groupBy('dispatch_items.product')
        ->get();

    foreach ($dispatchItems as $item) {
        $price = $productPrices[$item->product] ?? 0;
        $grossProfit += $price * $item->total_sold;
    }

    // 2. Include approved sold damages
    $damageRevenue = Damage::where('status', 'approved')
        ->whereNotNull('sold_quantity')
        ->whereBetween('updated_at', [$from->startOfDay(), $to->endOfDay()])
        ->sum(DB::raw('sold_quantity * approved_price'));

    $grossProfit += $damageRevenue;

    // 3. Include Bakery Sales
    $bakerySales = Sale::whereDate('created_at', '>=', $from->toDateString())
        ->whereDate('created_at', '<=', $to->toDateString())
        ->sum('total_price');

    $grossProfit += $bakerySales;

    // 4. Expenses
    $expensesTotal = Expense::whereDate('expense_date', '>=', $from->toDateString())
        ->whereDate('expense_date', '<=', $to->toDateString())
        ->sum('amount');

    // Net Profit
    // Ensure net profit never goes below zero
    $netProfit = max(0, $grossProfit - $expensesTotal - $totalBreakfastCost);


    // Dispatch items count
    $dispatchItemsCount = (int) Dispatch::whereDate('dispatch_date', '>=', $from->toDateString())
        ->whereDate('dispatch_date', '<=', $to->toDateString())
        ->sum('total_items_sold');

    // Recent lists
    $recentDispatches = Dispatch::with('driver')
        ->orderBy('dispatch_date', 'desc')
        ->orderBy('dispatch_no', 'desc')
        ->limit(10)
        ->get();

    $recentProductions = Production::with('user')
        ->orderBy('production_date', 'desc')
        ->limit(10)
        ->get();

    // Bakery Sales (split by payment method)
    $bakerySalesCash = Sale::whereDate('created_at', '>=', $from->toDateString())
        ->whereDate('created_at', '<=', $to->toDateString())
        ->where('payment_method', 'cash')
        ->sum('total_price');

    // Recent Stock Additions
   $recentStockAdditions = StockHistory::with(['ingredient', 'chef', 'addedBy'])
    ->whereIn('transaction_type', ['addition', 'usage'])
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();


    $showStockModal = !$recentStockAdditions->isEmpty() && !session()->get('seen_stock_modal', false);
    if ($showStockModal) {
        session()->put('seen_stock_modal', true);
    }

    // TOTAL bankings = Bakery + Driver bankings
    $bakeryBankings = BankDeposit::whereDate('deposit_date', '>=', $from->toDateString())
        ->whereDate('deposit_date', '<=', $to->toDateString())
        ->sum('amount');

    $driverBankings = Banking::whereDate('date', '>=', $from->toDateString())
        ->whereDate('date', '<=', $to->toDateString())
        ->sum('amount');

    $bankedTotal = $bakeryBankings + $driverBankings;

    // Charts: last 7 days
    $chartDays = 7;
    $chartFrom = Carbon::now()->subDays($chartDays - 1)->startOfDay();
    $chartTo = Carbon::now()->endOfDay();

    $prodPerDay = Production::selectRaw('DATE(production_date) as day, SUM(total_value) as value')
        ->whereDate('production_date', '>=', $chartFrom->toDateString())
        ->whereDate('production_date', '<=', $chartTo->toDateString())
        ->groupBy('day')
        ->orderBy('day', 'asc')
        ->pluck('value', 'day')
        ->toArray();

    $dispPerDay = Dispatch::selectRaw('DATE(dispatch_date) as day, SUM(total_sales_value) as value')
        ->whereDate('dispatch_date', '>=', $chartFrom->toDateString())
        ->whereDate('dispatch_date', '<=', $chartTo->toDateString())
        ->groupBy('day')
        ->orderBy('day', 'asc')
        ->pluck('value', 'day')
        ->toArray();

    $labels = [];
    $prodSeries = [];
    $dispSeries = [];
    for ($i = 0; $i < $chartDays; $i++) {
        $d = Carbon::now()->subDays($chartDays - 1 - $i)->format('Y-m-d');
        $labels[] = Carbon::parse($d)->format('M d');
        $prodSeries[] = $prodPerDay[$d] ?? 0;
        $dispSeries[] = $dispPerDay[$d] ?? 0;
    }

    // Flour used
    $flourUsed = (float) Production::whereDate('production_date', '>=', $from->toDateString())
        ->whereDate('production_date', '<=', $to->toDateString())
        ->sum('flour_bags') * 50;

    // Bakery stocks
    $bakeryStocks = BakeryStock::orderBy('product')->get();

    // Money left at bakery (cash physically available)
    // Use the same CashBalanceService as Sales dashboard
    $cashService = new \App\Services\CashBalanceService();
    $balance = $cashService->getAvailableCash($from);
    $bakeryCashLeft = $balance['available_cash'];


    return view('admin.dashboard', compact(
        'filter',
        'title',
        'totalUsers',
        'totalProductions',
        'totalDispatches',
        'todayProductions',
        'productionValue',
        'dispatchValue',
        'dispatchItemsCount',
        'recentDispatches',
        'recentProductions',
        'labels',
        'prodSeries',
        'dispSeries',
        'bakeryStocks',
        'flourUsed',
        'bakerySales',
        'bakerySalesCash',
        'expensesTotal',
        'recentStockAdditions',
        'showStockModal',
        'bankedTotal',
        'grossProfit',
        'netProfit',
        'damageRevenue',
        'totalBreakfastCost',
        'bakeryCashLeft'
    ));
}

}
