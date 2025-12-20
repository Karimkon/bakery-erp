<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KampalaShop;
use App\Models\KampalaSale;
use App\Models\KampalaDispatch;
use App\Models\KampalaBanking;
use App\Models\KampalaExpense;
use App\Models\KampalaStock;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KampalaAdminController extends Controller
{
    /**
     * Display comprehensive dashboard for all Kampala shops
     */
    public function dashboard()
    {
        $shops = KampalaShop::with(['manager'])->get();
        
        // Overall statistics
        $stats = [
            'total_shops' => $shops->count(),
            'active_shops' => $shops->where('status', 'active')->count(),
            'total_sales' => KampalaSale::sum('total_price'),
            'total_expenses' => KampalaExpense::sum('amount'),
            'total_banked' => KampalaBanking::sum('amount'),
            'pending_dispatches' => KampalaDispatch::where('status', 'pending')->count(),
        ];
        
        // Today's activities
        $today = Carbon::today();
        $todayStats = [
            'sales' => KampalaSale::whereDate('created_at', $today)->sum('total_price'),
            'expenses' => KampalaExpense::whereDate('expense_date', $today)->sum('amount'),
            'bankings' => KampalaBanking::whereDate('date', $today)->sum('amount'),
            'dispatches' => KampalaDispatch::whereDate('dispatch_date', $today)->count(),
        ];
        
        // Recent activities
        $recentSales = KampalaSale::with(['shop', 'user'])
            ->latest()
            ->limit(10)
            ->get();
            
        $recentDispatches = KampalaDispatch::with(['shop', 'manager'])
            ->latest()
            ->limit(10)
            ->get();
            
        $recentBankings = KampalaBanking::with(['shop', 'user'])
            ->latest()
            ->limit(10)
            ->get();
            
        $recentExpenses = KampalaExpense::with(['shop', 'user'])
            ->latest()
            ->limit(10)
            ->get();
        
        return view('admin.kampala.dashboard', compact(
            'shops', 'stats', 'todayStats', 
            'recentSales', 'recentDispatches', 
            'recentBankings', 'recentExpenses'
        ));
    }
    
    /**
     * Show detailed shop activities
     */
    public function shopActivities($shopId)
    {
        $shop = KampalaShop::with(['manager'])->findOrFail($shopId);
        
        // Date filters
        $fromDate = request('from', Carbon::today()->subDays(7)->format('Y-m-d'));
        $toDate = request('to', Carbon::today()->format('Y-m-d'));
        
        // Shop statistics
        $stats = [
            'total_sales' => KampalaSale::where('shop_id', $shopId)
                ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                ->sum('total_price'),
            'total_expenses' => KampalaExpense::where('shop_id', $shopId)
                ->whereBetween('expense_date', [$fromDate, $toDate])
                ->sum('amount'),
            'total_banked' => KampalaBanking::where('shop_id', $shopId)
                ->whereBetween('date', [$fromDate, $toDate])
                ->sum('amount'),
            'dispatches_received' => KampalaDispatch::where('shop_id', $shopId)
                ->whereBetween('received_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                ->count(),
        ];
        
        // Sales by product
        $salesByProduct = KampalaSale::where('shop_id', $shopId)
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->selectRaw('product_type, SUM(quantity) as total_quantity, SUM(total_price) as total_amount')
            ->groupBy('product_type')
            ->orderBy('total_amount', 'DESC')
            ->get();
            
        // Expenses by category
        $expensesByCategory = KampalaExpense::where('shop_id', $shopId)
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->selectRaw('category, SUM(amount) as total_amount')
            ->groupBy('category')
            ->orderBy('total_amount', 'DESC')
            ->get();
            
        // Recent activities
        $recentActivities = collect();
        
        // Add sales
        $sales = KampalaSale::where('shop_id', $shopId)
            ->with('user')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function($sale) {
                return [
                    'type' => 'sale',
                    'date' => $sale->created_at,
                    'description' => 'Sale: ' . ucwords(str_replace('_', ' ', $sale->product_type)) . ' ('.$sale->quantity.' items)',
                    'amount' => $sale->total_price,
                    'user' => $sale->user->name,
                    'color' => 'success',
                    'icon' => 'bi-cash-coin',
                ];
            });
            
        $recentActivities = $recentActivities->merge($sales);
        
        // Add bankings
        $bankings = KampalaBanking::where('shop_id', $shopId)
            ->with('user')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function($banking) {
                return [
                    'type' => 'banking',
                    'date' => $banking->created_at,
                    'description' => 'Banking: ' . $banking->receipt_number,
                    'amount' => $banking->amount,
                    'user' => $banking->user->name,
                    'color' => 'info',
                    'icon' => 'bi-bank',
                ];
            });
            
        $recentActivities = $recentActivities->merge($bankings);
        
        // Add expenses
        $expenses = KampalaExpense::where('shop_id', $shopId)
            ->with('user')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function($expense) {
                return [
                    'type' => 'expense',
                    'date' => $expense->created_at,
                    'description' => 'Expense: ' . $expense->description,
                    'amount' => -$expense->amount,
                    'user' => $expense->user->name,
                    'color' => 'danger',
                    'icon' => 'bi-receipt',
                ];
            });
            
        $recentActivities = $recentActivities->merge($expenses);
        
        // Sort by date and take top 30
        $recentActivities = $recentActivities->sortByDesc('date')->take(30);
        
        return view('admin.kampala.shop-activities', compact(
            'shop', 'stats', 'salesByProduct', 
            'expensesByCategory', 'recentActivities',
            'fromDate', 'toDate'
        ));
    }
    
    /**
     * Export shop activities
     */
    public function exportActivities($shopId)
    {
        // Similar logic to shopActivities but for export
        // You can implement Excel export here
    }
}