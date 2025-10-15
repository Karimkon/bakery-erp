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

        // Summary counts
        $totalUsers = User::count();
        $totalProductions = Production::count();
        $totalDispatches = Dispatch::count();

        // Quick today stat (kept for legacy)
        $todayProductions = Production::whereDate('production_date', $today)->count();

        // Values for selected filter range
        // Note: production.production_date and dispatch.dispatch_date assumed to be date or datetime
        $productionValue = (float) Production::whereDate('production_date', '>=', $from->toDateString())
            ->whereDate('production_date', '<=', $to->toDateString())
            ->sum('total_value');

        $dispatchValue = (float) Dispatch::whereDate('dispatch_date', '>=', $from->toDateString())
            ->whereDate('dispatch_date', '<=', $to->toDateString())
            ->sum('total_sales_value');

        $combinedValue = $productionValue + $dispatchValue;

        // Extra useful metrics
        $dispatchItemsCount = (int) Dispatch::whereDate('dispatch_date', '>=', $from->toDateString())
            ->whereDate('dispatch_date', '<=', $to->toDateString())
            ->sum('total_items_sold');

        // Recent lists to display
        $recentDispatches = Dispatch::with('driver')
            ->orderBy('dispatch_date', 'desc')
            ->orderBy('dispatch_no', 'desc')
            ->limit(10)
            ->get();

        $recentProductions = Production::with('user')
            ->orderBy('production_date', 'desc')
            ->limit(10)
            ->get();

            // Total Bakery Shop Sales (filtered by date range)
        $bakerySales = Sale::whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->sum('total_price');

        // Optional: split by payment method
        $bakerySalesCash = Sale::whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->where('payment_method', 'cash')
            ->sum('total_price');

        $expensesTotal = Expense::whereDate('expense_date', '>=', $from->toDateString())
            ->whereDate('expense_date', '<=', $to->toDateString())
            ->sum('amount');

        $recentStockAdditions = StockHistory::with(['ingredient', 'chef', 'addedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5) // last 5 additions
            ->get();

        // Determine if modal should be shown (only if there are recent additions)
        $showStockModal = !$recentStockAdditions->isEmpty() && !session()->get('seen_stock_modal', false);

        // Mark it as seen in session if we are showing
        if ($showStockModal) {
            session()->put('seen_stock_modal', true);
        }


        // Chart: last 7 days production & dispatch values (makes a continuous date series)
        $chartDays = 7;
        $chartFrom = Carbon::now()->subDays($chartDays - 1)->startOfDay();
        $chartTo = Carbon::now()->endOfDay();

        // Production per day
        $prodPerDay = Production::selectRaw('DATE(production_date) as day, SUM(total_value) as value')
            ->whereDate('production_date', '>=', $chartFrom->toDateString())
            ->whereDate('production_date', '<=', $chartTo->toDateString())
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->pluck('value', 'day')
            ->toArray();

        // Dispatch per day
        $dispPerDay = Dispatch::selectRaw('DATE(dispatch_date) as day, SUM(total_sales_value) as value')
            ->whereDate('dispatch_date', '>=', $chartFrom->toDateString())
            ->whereDate('dispatch_date', '<=', $chartTo->toDateString())
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->pluck('value', 'day')
            ->toArray();

        // Build labels and series (ensure zeros for missing days)
        $labels = [];
        $prodSeries = [];
        $dispSeries = [];
        for ($i = 0; $i < $chartDays; $i++) {
            $d = Carbon::now()->subDays($chartDays - 1 - $i)->format('Y-m-d');
            $labels[] = Carbon::parse($d)->format('M d');
            $prodSeries[] = isset($prodPerDay[$d]) ? (float)$prodPerDay[$d] : 0;
            $dispSeries[] = isset($dispPerDay[$d]) ? (float)$dispPerDay[$d] : 0;
        }

        // Bakery stocks for modal/alert
        $bakeryStocks = BakeryStock::orderBy('product')->get();

        // Assuming each flour_bag is 50kg — change as needed
        $flourUsed = (float) Production::whereDate('production_date', '>=', $from->toDateString())
            ->whereDate('production_date', '<=', $to->toDateString())
            ->sum('flour_bags') * 50;



        return view('admin.dashboard', compact(
            'filter',
            'title',
            'totalUsers',
            'totalProductions',
            'totalDispatches',
            'todayProductions',
            'productionValue',
            'dispatchValue',
            'combinedValue',
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
            'showStockModal'
        ));
    }
}
