<?php

namespace App\Http\Controllers\Kampala;

use App\Http\Controllers\Controller;
use App\Models\KampalaDispatch;
use App\Models\KampalaStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KampalaDispatchController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user has a shop assigned
        if (!$user->kampalaShop) {
            return redirect()->route('kampala.dashboard')
                ->with('error', 'Your account is not assigned to any shop. Please contact administrator.');
        }

        $dispatches = KampalaDispatch::with(['manager', 'items'])
            ->where('shop_id', $user->kampalaShop->id)
            ->latest()
            ->paginate(20);

        return view('kampala.dispatches.index', compact('dispatches'));
    }

    public function show(KampalaDispatch $kampalaDispatch)
{
    $user = Auth::user();
    
    \Log::info('Dispatch access attempt', [
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_shop_id' => $user->shop_id,
        'dispatch_id' => $kampalaDispatch->id,
        'dispatch_shop_id' => $kampalaDispatch->shop_id,
        'dispatch_no' => $kampalaDispatch->dispatch_no
    ]);

    // Check if user has a shop assigned
    if (!$user->kampalaShop) {
        \Log::error('User has no shop assigned', ['user_id' => $user->id]);
        abort(403, 'Your account is not assigned to any shop.');
    }

    // Debug the shop relationship
    \Log::info('Shop relationship check', [
        'user_kampala_shop_id' => $user->kampalaShop->id,
        'user_kampala_shop_name' => $user->kampalaShop->name,
        'dispatch_shop_id' => $kampalaDispatch->shop_id
    ]);

    // Authorization - ensure user owns this shop
    if ($kampalaDispatch->shop_id !== $user->kampalaShop->id) {
        \Log::error('Authorization failed', [
            'user_kampala_shop_id' => $user->kampalaShop->id,
            'dispatch_shop_id' => $kampalaDispatch->shop_id,
            'comparison_result' => $kampalaDispatch->shop_id !== $user->kampalaShop->id
        ]);
        abort(403, 'You are not authorized to view dispatches for this shop.');
    }

    $kampalaDispatch->load(['manager', 'items']);
    return view('kampala.dispatches.show', compact('kampalaDispatch'));
}

    public function receive(Request $request, KampalaDispatch $kampalaDispatch)
    {
        $user = Auth::user();
        
        // Check if user has a shop assigned
        if (!$user->kampalaShop) {
            abort(403, 'Your account is not assigned to any shop.');
        }

        if ($kampalaDispatch->shop_id !== $user->kampalaShop->id) {
            abort(403, 'You are not authorized to receive dispatches for this shop.');
        }

        // Check if dispatch is already fully received
        if ($kampalaDispatch->status === 'received') {
            return back()->with('error', 'This dispatch has already been fully received.');
        }

        $request->validate([
            'received_items' => 'required|array',
            'received_items.*' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($kampalaDispatch, $request, $user) {
                $allFullyReceived = true;
                $anyReceived = false;

                foreach ($kampalaDispatch->items as $item) {
                    $receivedQty = $request->received_items[$item->id] ?? 0;
                    
                    // Validate received quantity doesn't exceed dispatched quantity
                    if ($receivedQty > $item->quantity) {
                        throw new \Exception("Received quantity for {$item->product_type} cannot exceed dispatched quantity ({$item->quantity}).");
                    }
                    
                    if ($receivedQty > 0) {
                        $anyReceived = true;
                        $item->received_quantity = $receivedQty;
                        $item->save();

                        // Update shop stock
                        $stock = KampalaStock::firstOrCreate(
                            [
                                'shop_id' => $kampalaDispatch->shop_id,
                                'product_type' => $item->product_type
                            ],
                            [
                                'opening_stock' => 0,
                                'dispatched' => 0,
                                'sold' => 0,
                                'remaining' => 0,
                                'unit_price' => $item->unit_price
                            ]
                        );

                        $stock->updateOnDispatch($receivedQty);
                    }

                    if ($receivedQty < $item->quantity) {
                        $allFullyReceived = false;
                    }
                }

                // Update dispatch status
                $kampalaDispatch->update([
                    'status' => $allFullyReceived ? 'received' : ($anyReceived ? 'partial' : 'pending'),
                    'received_by' => $user->id,
                    'received_at' => now(),
                ]);
            });

            return redirect()->route('kampala.dispatches.index')
                ->with('success', 'Dispatch received successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error receiving dispatch: ' . $e->getMessage());
        }
    }
}