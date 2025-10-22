<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Sales\DashboardController;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\ShopStock;
use Illuminate\Support\Facades\Auth;
use App\Services\CashBalanceService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $sales = Sale::where('user_id', Auth::id());
        $cashService = new CashBalanceService();
        
        // Get current available cash
        $balance = $cashService->getAvailableCash(Carbon::today());
        
        // Get today's cash flow for the real-time breakdown
        $todayFlow = $cashService->getTodayCashFlow();

        $summary = [
            'count' => $sales->whereDate('created_at', Carbon::today())->count(),
            'qty'   => $sales->whereDate('created_at', Carbon::today())->sum('quantity'),
            'total' => $sales->whereDate('created_at', Carbon::today())
                        ->where('payment_method', 'cash')
                        ->sum('total_price'),
            'available_cash' => $balance['available_cash']
        ];

        // Top 5 products for chart
        $topProducts = Sale::selectRaw('product_type, SUM(quantity) as qty')
            ->where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->groupBy('product_type')
            ->orderByDesc('qty')
            ->limit(5)
            ->pluck('qty','product_type');

        return view('sales.dashboard', compact('summary', 'topProducts', 'balance', 'todayFlow'));
    }

    // API endpoint for real-time updates
    public function getCashBalance()
    {
        $cashService = new CashBalanceService();
        $balance = $cashService->getAvailableCash(Carbon::today());
        $todayFlow = $cashService->getTodayCashFlow();

        return response()->json([
            'available_cash' => $balance['available_cash'],
            'cash_sales' => $balance['cash_sales'],
            'total_banked' => $balance['total_banked'],
            'total_expenses' => $balance['total_expenses'],
            'today_cash_sales' => $todayFlow['today_cash_sales'],
            'today_expenses' => $todayFlow['today_expenses'],
            'today_banked' => $todayFlow['today_banked'],
            'updated_at' => now()->format('H:i:s')
        ]);
    }
}