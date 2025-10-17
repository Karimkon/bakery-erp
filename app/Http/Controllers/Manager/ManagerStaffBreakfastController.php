<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffBreakfast;
use App\Models\BakeryStock;
use Illuminate\Support\Facades\Auth;

class ManagerStaffBreakfastController extends Controller
{
    public function index()
    {
        $breakfasts = StaffBreakfast::where('manager_id', Auth::id())
            ->latest()
            ->paginate(20);

        // Calculate total spent on approved breakfasts
        $totalSpent = $breakfasts->where('status', 'approved')->sum('total_value');


        return view('manager.staff_breakfast.index', compact('breakfasts', 'totalSpent'));
    }

    public function create()
    {
        $products = BakeryStock::pluck('product'); // get available products
        return view('manager.staff_breakfast.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['manager_id'] = Auth::id();
        $validated['status'] = 'pending';
        StaffBreakfast::create($validated);

        return redirect()->route('manager.staff_breakfast.index')
            ->with('success', 'Breakfast request submitted. Waiting admin approval.');
    }
}
