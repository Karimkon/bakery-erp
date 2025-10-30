<?php

namespace App\Http\Controllers\Kampala;

use App\Http\Controllers\Controller;
use App\Models\KampalaBanking;
use App\Models\KampalaSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KampalaBankingController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->kampalaShop;
        $bankings = KampalaBanking::with('user')
            ->where('shop_id', $shop->id)
            ->latest()
            ->paginate(20);

        // Calculate cash balance
        $totalSales = KampalaSale::where('shop_id', $shop->id)
            ->where('payment_method', 'cash')
            ->sum('total_price');

        $totalBanked = KampalaBanking::where('shop_id', $shop->id)
            ->sum('amount');

        $availableCash = $totalSales - $totalBanked;

        return view('kampala.bankings.index', compact('bankings', 'availableCash', 'totalSales', 'totalBanked'));
    }

    public function store(Request $request)
    {
        $shop = Auth::user()->kampalaShop;

        $request->validate([
            'amount' => 'required|numeric|min:100',
            'date' => 'required|date',
            'receipt_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        KampalaBanking::create([
            'shop_id' => $shop->id,
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'date' => $request->date,
            'receipt_number' => $request->receipt_number,
            'notes' => $request->notes,
        ]);

        return redirect()->route('kampala.bankings.index')
            ->with('success', 'Banking recorded successfully!');
    }
}