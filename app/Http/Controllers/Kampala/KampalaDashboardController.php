<?php

namespace App\Http\Controllers\Kampala;

use App\Http\Controllers\Controller;
use App\Models\KampalaDispatch;
use App\Models\KampalaSale;
use App\Models\KampalaStock;
use App\Models\KampalaBanking;
use App\Models\KampalaExpense; // Add this
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KampalaDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->shop_id) {
            Auth::logout();
            return redirect()->route('kampala.login')
                ->with('error', 'No shop assigned to your account. Please contact administrator.');
        }

        $shop = $user->kampalaShop;
        
        if (!$shop) {
            Auth::logout();
            return redirect()->route('kampala.login')
                ->with('error', 'Shop not found. Please contact administrator.');
        }

        // Initialize counts
        $pendingDispatches = 0;
        $todaySales = 0;
        $stockAlerts = 0;
        $availableCash = 0;
        $totalExpenses = 0;

        try {
            // Pending dispatches
            $pendingDispatches = KampalaDispatch::where('shop_id', $shop->id)
                ->where('status', 'pending')
                ->count();

            // Today's sales
            $todaySales = KampalaSale::where('shop_id', $shop->id)
                ->whereDate('created_at', today())
                ->sum('total_price');

            // Stock alerts
            $stockAlerts = KampalaStock::where('shop_id', $shop->id)
                ->where('remaining', '<', 10)
                ->count();

            // Calculate available cash (including expenses)
            $totalSales = KampalaSale::where('shop_id', $shop->id)->sum('total_price');
            $totalBanking = KampalaBanking::where('shop_id', $shop->id)->sum('amount');
            $totalExpenses = KampalaExpense::where('shop_id', $shop->id)->sum('amount');
            
            $availableCash = $totalSales - $totalBanking - $totalExpenses;

        } catch (\Exception $e) {
            \Log::error('Dashboard calculation error: ' . $e->getMessage());
        }

        return view('kampala.dashboard', compact(
            'pendingDispatches', 
            'todaySales', 
            'stockAlerts',
            'availableCash',
            'totalExpenses',
            'shop'
        ));
    }
}