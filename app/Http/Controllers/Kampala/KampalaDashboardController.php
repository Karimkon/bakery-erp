<?php

namespace App\Http\Controllers\Kampala;

use App\Http\Controllers\Controller;
use App\Models\KampalaDispatch;
use App\Models\KampalaSale;
use App\Models\KampalaStock;
use App\Models\KampalaBanking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KampalaDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user has a shop assigned
        if (!$user->shop_id) {
            Auth::logout();
            return redirect()->route('kampala.login')
                ->with('error', 'No shop assigned to your account. Please contact administrator.');
        }

        $shop = $user->kampalaShop;
        
        // Check if shop exists
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

            // Calculate available cash (total sales - total banking)
            $totalSales = KampalaSale::where('shop_id', $shop->id)->sum('total_price');
            $totalBanking = KampalaBanking::where('shop_id', $shop->id)->sum('amount');
            
            $availableCash = $totalSales - $totalBanking;

        } catch (\Exception $e) {
            // If tables don't exist yet, just continue with zeros
            \Log::error('Dashboard calculation error: ' . $e->getMessage());
        }

        return view('kampala.dashboard', compact(
            'pendingDispatches', 
            'todaySales', 
            'stockAlerts',
            'availableCash',
            'shop'
        ));
    }
}