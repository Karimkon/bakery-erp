<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\ShopStock;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $sales = Sale::where('user_id', Auth::id());

        $summary = [
            'count' => $sales->count(),
            'qty'   => $sales->sum('quantity'),
            'total' => $sales->sum('total_price'),
        ];

        // top 5 products for chart
        $topProducts = Sale::selectRaw('product_type, SUM(quantity) as qty')
            ->where('user_id', Auth::id())
            ->groupBy('product_type')
            ->orderByDesc('qty')
            ->limit(5)
            ->pluck('qty','product_type');

        return view('sales.dashboard', compact('summary','topProducts'));
    }
}
