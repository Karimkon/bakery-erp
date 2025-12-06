<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\KampalaSale;
use App\Models\KampalaShop;
use App\Models\KampalaBanking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesReportController extends Controller
{
    public function bakerySales(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $paymentMethod = $request->input('payment_method');

        $query = Sale::with('user')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(50);

        // Calculate totals
        $totalSales = $sales->sum('total_price');
        $cashSales = Sale::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('payment_method', 'cash')
            ->sum('total_price');

        // Get bank deposits for the period
        $bankDeposits = \App\Models\BankDeposit::whereDate('deposit_date', '>=', $dateFrom)
            ->whereDate('deposit_date', '<=', $dateTo)
            ->sum('amount');

        $balanceDue = $cashSales - $bankDeposits;

        return view('admin.sales.bakery', compact(
            'sales', 'totalSales', 'cashSales', 'bankDeposits', 'balanceDue',
            'dateFrom', 'dateTo', 'paymentMethod'
        ));
    }

    public function kampalaSales(Request $request)
    {
        $shopId = $request->input('shop_id');
        $dateFrom = $request->input('date_from', now()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $bankingStatus = $request->input('banking_status');

        $shops = KampalaShop::with('manager')->get();

        // Shop-wise summary
        $shopSummary = [];
        foreach ($shops as $shop) {
            $salesQuery = KampalaSale::where('shop_id', $shop->id)
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);

            $totalSales = $salesQuery->sum('total_price');
            
            // FIXED: Use 'date' column instead of 'banking_date'
            $bankedAmount = KampalaBanking::where('shop_id', $shop->id)
                ->whereDate('date', '>=', $dateFrom)
                ->whereDate('date', '<=', $dateTo)
                ->sum('amount');

            $pendingAmount = $totalSales - $bankedAmount;
            $balance = $pendingAmount;

            $shopSummary[] = [
                'shop_id' => $shop->id,
                'shop_name' => $shop->name,
                'manager' => $shop->manager->name ?? 'N/A',
                'total_sales' => $totalSales,
                'banked_amount' => $bankedAmount,
                'pending_amount' => $pendingAmount,
                'balance' => $balance,
            ];
        }

        // Recent sales
        $salesQuery = KampalaSale::with('shop')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if ($shopId) {
            $salesQuery->where('shop_id', $shopId);
        }

        $recentSales = $salesQuery->orderBy('created_at', 'desc')->limit(50)->get();

        // Totals
        $totalSales = collect($shopSummary)->sum('total_sales');
        $totalBanked = collect($shopSummary)->sum('banked_amount');
        $pendingBanking = collect($shopSummary)->sum('pending_amount');
        $balanceDue = $pendingBanking;

        return view('admin.sales.kampala', compact(
            'shops', 'shopSummary', 'recentSales', 'totalSales', 'totalBanked',
            'pendingBanking', 'balanceDue', 'shopId', 'dateFrom', 'dateTo', 'bankingStatus'
        ));
    }

    public function kampalaShopDetails($shopId, Request $request)
    {
        $shop = KampalaShop::with('manager')->findOrFail($shopId);
        $dateFrom = $request->input('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $sales = KampalaSale::where('shop_id', $shopId)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // FIXED: Use 'date' column instead of 'banking_date'
        $bankings = KampalaBanking::where('shop_id', $shopId)
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->orderBy('date', 'desc')
            ->get();

        $totalSales = $sales->sum('total_price');
        $totalBanked = $bankings->sum('amount');
        $balance = $totalSales - $totalBanked;

        return view('admin.sales.kampala-shop-details', compact(
            'shop', 'sales', 'bankings', 'totalSales', 'totalBanked', 'balance',
            'dateFrom', 'dateTo'
        ));
    }

    public function exportKampalaSales()
    {
        // Implementation for export functionality
        return response()->download('path/to/export/file.csv');
    }
}