<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Damage;

class AdminDamageController extends Controller
{
    // List all damage reports
    public function index()
    {
        $damages = Damage::latest()->paginate(20);
        return view('admin.damages.index', compact('damages'));
    }

    // Show details of a damage report
    public function show(Damage $damage)
    {
        return view('admin.damages.show', compact('damage'));
    }

    // Approve a damage report
    public function approve(Request $request, Damage $damage)
    {
        $request->validate([
            'approved_price' => 'required|numeric|min:0'
        ]);

        $damage->update([
            'admin_id' => auth()->id(),
            'approved_price' => $request->approved_price,
            'status' => 'approved',
        ]);

        return redirect()->route('admin.damages.index')
            ->with('success', 'Damage approved successfully.');
    }

    // Reject a damage report
    public function reject(Damage $damage)
    {
        $damage->update([
            'admin_id' => auth()->id(),
            'status' => 'rejected',
        ]);

        return redirect()->route('admin.damages.index')
            ->with('success', 'Damage rejected successfully.');
    }

   // Mark damage as sold (partial/custom quantity & price)
public function markAsSold(Request $request, Damage $damage)
{
    $request->validate([
        'sold_quantity' => "required|integer|min:1|max:{$damage->quantity}",
        'sold_price' => 'required|numeric|min:0',
    ]);

    $soldQuantity = $request->sold_quantity;
    $soldPrice = $request->sold_price;

    // Update sold quantity
    $damage->sold_quantity = ($damage->sold_quantity ?? 0) + $soldQuantity;

    // Reduce remaining quantity
    $damage->quantity -= $soldQuantity;

    // Update status
    $damage->status = $damage->quantity == 0 ? 'sold' : 'approved';

    // Update last sold price/unit
    $damage->approved_price = $soldPrice;

    $damage->save();

    $totalReceived = $soldQuantity * $soldPrice;

    return redirect()->route('admin.damages.index')
        ->with('success', "Sold {$soldQuantity} units of {$damage->product} at {$soldPrice} each. Total received: {$totalReceived}");
}



public function destroy(Damage $damage)
{
    $damage->delete();
    return redirect()->route('admin.damages.index')->with('success', 'Damage deleted successfully.');
}


}
