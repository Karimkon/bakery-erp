<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockHistory;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StockHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = StockHistory::with(['ingredient', 'chef', 'addedBy']);

        // Handle quick period filters
        if ($request->period) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', Carbon::yesterday());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    ]);
                    break;
            }
        }

        // Advanced Filters
        if ($request->chef_id) {
            $query->where('chef_id', $request->chef_id);
        }
        
        if ($request->ingredient_id) {
            $query->where('ingredient_id', $request->ingredient_id);
        }
        
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Additional filter for transaction type (apply before pagination)
        if ($request->transaction_type) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $history = $query->latest()->paginate(20);

        // Get filter options
        $chefs = User::where('role', 'chef')->get();
        $ingredients = Ingredient::all();

        // Get counts for stats
        $todayCount = StockHistory::whereDate('created_at', Carbon::today())->count();
        $weekCount = StockHistory::whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();
        $monthCount = StockHistory::whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->count();

        // Get latest 5 stock additions for modal
        $recentStockAdditions = StockHistory::with(['ingredient', 'chef', 'addedBy'])
            ->latest()
            ->take(5)
            ->get();

        // Show modal once per session
        $showStockModal = false;
        if (!$recentStockAdditions->isEmpty() && !session()->has('seen_stock_modal')) {
            $showStockModal = true;
            session(['seen_stock_modal' => true]);
        }

        return view('admin.ingredients.stock_history', compact(
            'history',
            'chefs',
            'ingredients',
            'recentStockAdditions',
            'showStockModal',
            'todayCount',
            'weekCount',
            'monthCount'
        ));
    }

  public function show($id)
{
    try {
        \Log::info("Fetching stock history for ID: " . $id);
        
        $history = StockHistory::with(['ingredient', 'chef', 'addedBy'])->find($id);
        
        if (!$history) {
            \Log::warning("Stock history not found for ID: " . $id);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        \Log::info("Found stock history: " . $history->id);
        
        return response()->json([
            'id' => $history->id,
            'ingredient' => [
                'name' => $history->ingredient->name ?? 'N/A'
            ],
            'chef' => $history->chef ? [
                'name' => $history->chef->name
            ] : null,
            'transaction_type' => $history->transaction_type,
            'quantity_before' => $history->quantity_before,
            'quantity_changed' => $history->quantity_changed,
            'quantity_after' => $history->quantity_after,
            'added_by' => $history->addedBy ? [
                'name' => $history->addedBy->name
            ] : null,
            'created_at' => $history->created_at->toDateTimeString(),
            'notes' => $history->notes,
        ]);
        
    } catch (\Exception $e) {
        \Log::error("Error fetching stock history: " . $e->getMessage());
        return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
}

    public function getNewStockNotifications()
    {
        return StockHistory::with('ingredient', 'chef')
            ->latest()
            ->take(5)
            ->get();
    }
}