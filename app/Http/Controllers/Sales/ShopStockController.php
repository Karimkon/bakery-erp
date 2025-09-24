<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\ShopStock;

class ShopStockController extends Controller
{
    public function index()
    {
        $stocks = ShopStock::where('shop_name','Kampala Main Shop')->get();
        return view('sales.stock.index', compact('stocks'));
    }
}
