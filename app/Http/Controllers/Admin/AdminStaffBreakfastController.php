<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffBreakfast;
use App\Models\BakeryStock;

class AdminStaffBreakfastController extends Controller
{
    public function index()
    {
        $breakfasts = StaffBreakfast::latest()->paginate(20);
        $totalSpent = $breakfasts->sum('total_value');

        return view('admin.staff_breakfast.index', compact('breakfasts', 'totalSpent'));
    }

    public function approve(StaffBreakfast $breakfast)
    {
        $updated = BakeryStock::where('product', $breakfast->product)
    ->where('quantity', '>=', $breakfast->quantity)
    ->decrement('quantity', $breakfast->quantity);

        if (!$updated) {
            return back()->with('error', 'Not enough stock to approve this request.');
        }

        // Use original price from stock/config
        $pricePerUnit = config('bakery_products')[$breakfast->product] ?? 0;
        $totalValue = $pricePerUnit * $breakfast->quantity;

        $breakfast->update([
            'admin_id' => auth()->id(),
            'status' => 'approved',
            'total_value' => $totalValue,
        ]);

        return back()->with('success', 'Staff breakfast approved and stock updated.');
    }

    public function reject(StaffBreakfast $breakfast)
    {
        $breakfast->update([
            'admin_id' => auth()->id(),
            'status' => 'rejected'
        ]);

        return back()->with('success', 'Staff breakfast request rejected.');
    }
}
