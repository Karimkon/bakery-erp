<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Damage;
use Illuminate\Support\Facades\Auth;

class ManagerDamageController extends Controller
{
    // List all damage reports by this manager
    public function index()
    {
        $damages = Damage::where('manager_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('manager.damages.index', compact('damages'));
    }

    // Show form to report new damage
    public function create()
    {
        $products = [
            'buns','small_breads','big_breads','donuts','half_cakes','block_cakes','slab_cakes','birthday_cakes'
        ];

        return view('manager.damages.create', compact('products'));
    }

    // Store new damage report
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('photo')) {
    $damagePhotosPath = storage_path('app/public/damage_photos');

    if (!file_exists($damagePhotosPath)) {
        mkdir($damagePhotosPath, 0755, true);
    }

    $validated['photo'] = $request->photo->store('damage_photos','public');
}


        $validated['manager_id'] = Auth::id();
        $damage = Damage::create($validated);

        return redirect()->route('manager.damages.index')
            ->with('success', 'Damage reported successfully. Waiting admin approval.');
    }

    public function show(Damage $damage)
{
    // Ensure manager can only view their own damages
    if ($damage->manager_id !== Auth::id()) {
        abort(403, 'Unauthorized access.');
    }

    return view('manager.damages.show', compact('damage'));
}

public function markAsSold(Request $request, Damage $damage)
{
    // Only allow if damage is approved
    if ($damage->status !== 'approved') {
        return redirect()->back()->with('error', 'Cannot sell. Damage not approved.');
    }

    $request->validate([
        'sold_quantity' => "required|integer|min:1|max:{$damage->quantity}",
        'sold_price' => 'required|numeric|min:0',
    ]);

    $soldQuantity = $request->sold_quantity;
    $soldPrice = $request->sold_price;

    $damage->sold_quantity = ($damage->sold_quantity ?? 0) + $soldQuantity;
    $damage->quantity -= $soldQuantity;
    $damage->status = $damage->quantity == 0 ? 'sold' : 'approved';
    $damage->approved_price = $soldPrice;
    $damage->save();

    $totalReceived = $soldQuantity * $soldPrice;

    return redirect()->route('manager.damages.index')
        ->with('success', "Sold {$soldQuantity} units of {$damage->product} at {$soldPrice} each. Total received: {$totalReceived}");
}

}
