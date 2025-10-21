<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\ShopStock;
use Illuminate\Support\Facades\Auth;
use App\Services\CashBalanceService;
use Carbon\Carbon; // Add this import

class DashboardController extends Controller
{
    public function index()
    {
        $sales = Sale::where('user_id', Auth::id());
        $cashService = new CashBalanceService();
        
        // Use today's data instead of all-time
        $balance = $cashService->getAvailableCash(Carbon::today());

        $summary = [
            'count' => $sales->whereDate('created_at', Carbon::today())->count(),
            'qty'   => $sales->whereDate('created_at', Carbon::today())->sum('quantity'),
            'total' => $sales->whereDate('created_at', Carbon::today())
                        ->where('payment_method', 'cash')
                        ->sum('total_price'),
            'available_cash' => $balance['available_cash']
        ];

        // top 5 products for chart (also filter for today)
        $topProducts = Sale::selectRaw('product_type, SUM(quantity) as qty')
            ->where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today()) // Add this filter
            ->groupBy('product_type')
            ->orderByDesc('qty')
            ->limit(5)
            ->pluck('qty','product_type');

        return view('sales.dashboard', compact('summary', 'topProducts', 'balance'));
    }
}