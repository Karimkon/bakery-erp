<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopStock;
use App\Models\Sale;
use Illuminate\Http\Request;

class ShopReportController extends Controller
{
    public function index(Request $request)
{
    $query = ShopStock::where('shop_name', 'Kampala Main Shop');

    if ($request->filled('product')) {
        $query->where('product_type', $request->product);
    }

    $stocks = $query->orderBy('product_type')->get();

    $summary = [
        'dispatched' => $stocks->sum('dispatched'),
        'sold'       => $stocks->sum('sold'),
        'remaining'  => $stocks->sum('remaining'),
    ];

    // 🟦 Sales filter by date
    $salesQuery = Sale::whereHas('user', fn($q)=>$q->where('role','sales'))
        ->where('product_type', 'LIKE', '%'.$request->get('product','').'%');

    if ($request->filled('from')) {
        $salesQuery->whereDate('created_at','>=',$request->from);
    }
    if ($request->filled('to')) {
        $salesQuery->whereDate('created_at','<=',$request->to);
    }

    $sales = $salesQuery->latest()->paginate(20)->withQueryString();

    return view('admin.shop_dispatch.report', compact('stocks','summary','sales'));
}

}
