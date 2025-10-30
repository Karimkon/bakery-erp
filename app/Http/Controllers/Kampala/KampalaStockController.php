<?php

namespace App\Http\Controllers\Kampala;

use App\Http\Controllers\Controller;
use App\Models\KampalaStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KampalaStockController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->kampalaShop;
        $stocks = KampalaStock::where('shop_id', $shop->id)
            ->orderBy('product_type')
            ->get();

        return view('kampala.stock.index', compact('stocks'));
    }
}