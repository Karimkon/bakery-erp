<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Dispatch;
use App\Models\BakeryStock;
use App\Models\Sale;
use App\Models\Damage;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductProfitReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        $products = config('bakery_products', []);
        
        if (empty($products)) {
            // Fallback products if config is missing
            $products = [
                'buns' => 2500,
                'small_breads' => 2000,
                'big_breads' => 3000,
                'donuts' => 1500,
                'half_cakes' => 8000,
                'block_cakes' => 12000,
                'slab_cakes' => 15000,
                'birthday_cakes' => 25000,
                'quarter_breads' => 1500,
                'mandazis' => 500,
                'chapatys' => 1000,
                'toasted_bread' => 2000,
                'spring_donuts' => 2000,
                'cream_donuts' => 2500,
                'cinnamon_rolls' => 3000,
                'marble_cakes' => 2500,
                'musiba_tayi' => 800,
                'scornes' => 1200,
            ];
        }

        $reportData = [];
        $totalRevenue = 0;
        $totalProfit = 0;

        foreach ($products as $product => $price) {
            try {
                if (!$this->isValidProductColumn($product)) {
                    continue;
                }

                // --- Production ---
                $totalProduced = Production::whereBetween('production_date', [$startDate, $endDate])
                    ->sum(DB::raw("COALESCE(`$product`, 0)")) ?? 0;

                // --- Dispatch Revenue (Like Dashboard) ---
                $dispatchRevenue = DB::table('dispatch_items')
                    ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                    ->where('dispatch_items.product', $product)
                    ->whereBetween('dispatches.dispatch_date', [$startDate, $endDate])
                    ->sum('dispatch_items.line_total') ?? 0;

                $totalDispatchSold = DB::table('dispatch_items')
                    ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                    ->where('dispatch_items.product', $product)
                    ->whereBetween('dispatches.dispatch_date', [$startDate, $endDate])
                    ->sum('dispatch_items.sold_qty') ?? 0;

                // --- Bakery Shop Sales ---
                $totalSalesSold = Sale::where('product_type', $product)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('quantity');

                $salesRevenue = Sale::where('product_type', $product)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total_price');

                // --- Damage Revenue (Like Dashboard) ---
                $damageRevenue = Damage::where('status', 'approved')
                    ->where('product', $product)
                    ->whereNotNull('sold_quantity')
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->sum(DB::raw('COALESCE(sold_quantity * approved_price, 0)')) ?? 0;

                // --- Calculate Revenue & Profit (Like Dashboard) ---
                $totalSold = $totalDispatchSold + $totalSalesSold;
                $grossRevenue = $dispatchRevenue + $salesRevenue + $damageRevenue;
                
                // NO ESTIMATED COST - We'll calculate profit per product based on revenue only
                // Since we don't have per-product cost tracking, we'll show gross profit
                $profit = $grossRevenue; // This is actually gross revenue since we don't have product-level costs
                $profitMargin = 0; // We can't calculate true margin without costs

                // --- Stock ---
                $currentStock = BakeryStock::where('product', $product)->value('quantity') ?? 0;

                // --- Performance Metrics ---
                $daysInPeriod = max(1, $startDate->diffInDays($endDate));
                $sellThrough = $totalProduced > 0 ? ($totalSold / $totalProduced) * 100 : 0;
                $salesVelocity = $daysInPeriod > 0 ? $totalSold / $daysInPeriod : 0;

                // --- Sales Breakdown ---
                $cashSales = Sale::where('product_type', $product)
                    ->where('payment_method', 'cash')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('quantity') ?? 0;

                $creditSales = Sale::where('product_type', $product)
                    ->where('payment_method', 'credit')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('quantity') ?? 0;

                $reportData[] = [
                    'product' => $product,
                    'product_name' => $this->formatProductName($product),
                    'price' => $price,
                    'total_produced' => (int)$totalProduced,
                    'total_sold' => (int)$totalSold,
                    'dispatch_revenue' => (float)$dispatchRevenue,
                    'shop_revenue' => (float)$salesRevenue,
                    'damage_revenue' => (float)$damageRevenue,
                    'gross_revenue' => (float)$grossRevenue,
                    'profit' => (float)$profit, // This is actually gross revenue
                    'profit_margin' => (float)$profitMargin, // Can't calculate without costs
                    'current_stock' => (int)$currentStock,
                    'sell_through_rate' => (float)$sellThrough,
                    'sales_velocity' => (float)$salesVelocity,
                    'cash_sales' => (int)$cashSales,
                    'credit_sales' => (int)$creditSales,
                ];

                $totalRevenue += $grossRevenue;
                $totalProfit += $profit; // This accumulates gross revenue

            } catch (\Exception $e) {
                \Log::error("Product profit calculation error for {$product}: " . $e->getMessage());
                continue;
            }
        }

        // Calculate overall expenses and net profit (Like Dashboard)
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $netProfit = $totalProfit - $totalExpenses; // This gives actual net profit

        // Calculate overall margin based on net profit
        $overallMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Sort by gross revenue descending
        usort($reportData, fn($a, $b) => $b['gross_revenue'] <=> $a['gross_revenue']);

        $summary = [
            'total_revenue' => $totalRevenue,
            'total_profit' => $netProfit, // This is the actual net profit
            'overall_margin' => $overallMargin,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'products_analyzed' => count($reportData),
            'period' => $period,
            'start_date' => $startDate->format('d M Y'),
            'end_date' => $endDate->format('d M Y'),
        ];

        return view('admin.reports.productprofits', compact('reportData', 'summary', 'period'));
    }

    /**
     * Check if a product column exists in the productions table
     */
    private function isValidProductColumn($product)
    {
        $validColumns = [
            'buns', 'small_breads', 'big_breads', 'donuts', 'half_cakes', 
            'block_cakes', 'slab_cakes', 'birthday_cakes', 'quarter_breads',
            'mandazis', 'chapatys', 'toasted_bread', 'spring_donuts', 
            'cream_donuts', 'cinnamon_rolls', 'musiba_tayi', 'scornes'
        ];
        
        return in_array($product, $validColumns);
    }

    /**
     * Format product name for display
     */
    private function formatProductName($product)
    {
        return ucfirst(str_replace('_', ' ', $product));
    }

    private function getStartDate($period)
    {
        return match($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
    }

    public function export(Request $request)
    {
        // PDF/Excel export logic here
    }
}