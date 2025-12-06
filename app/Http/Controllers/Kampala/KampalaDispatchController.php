<?php

namespace App\Http\Controllers\Kampala;

use App\Http\Controllers\Controller;
use App\Models\KampalaDispatch;
use App\Models\BakeryStock;
use App\Models\KampalaShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KampalaDispatchController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
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

    public function show($id)
    {
        $user = Auth::user();
        
        \Log::info('KAMPALA SHOW DISPATCH ATTEMPT', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_shop_id' => $user->shop_id,
            'dispatch_id_param' => $id,
            'user_role' => $user->role
        ]);

        $kampalaDispatch = KampalaDispatch::with(['manager', 'items', 'receiver', 'shop'])
            ->find($id);
        
        if (!$kampalaDispatch) {
            \Log::error('Dispatch not found', ['id' => $id]);
            abort(404, 'Dispatch not found.');
        }

        if ($user->role !== 'kampala_shop') {
            abort(403, 'Only Kampala shop staff can view dispatches.');
        }

        if ($kampalaDispatch->shop_id !== $user->shop_id) {
            abort(403, 'You are not authorized to view dispatches for this shop.');
        }

        return view('kampala.dispatches.show', compact('kampalaDispatch'));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        $kampalaDispatch = KampalaDispatch::with('items')->find($id);
        
        if (!$kampalaDispatch) {
            return back()->with('error', 'Dispatch not found.');
        }

        if ($user->role !== 'kampala_shop') {
            abort(403, 'Only Kampala shop staff can delete dispatches.');
        }

        if ($kampalaDispatch->status !== 'pending') {
            return back()->with('error', "Only pending dispatches can be deleted. Current status: {$kampalaDispatch->status}");
        }

        try {
            DB::transaction(function () use ($kampalaDispatch) {
                foreach ($kampalaDispatch->items as $item) {
                    $bakeryStock = BakeryStock::where('product', $item->product_type)->first();
                    if ($bakeryStock) {
                        $bakeryStock->increment('quantity', $item->quantity);
                    }
                }

                $kampalaDispatch->delete();
            });

            return redirect()->route('kampala.dispatches.index')
                ->with('success', 'Dispatch deleted successfully! Bakery stock restored.');

        } catch (\Exception $e) {
            \Log::error('Delete error', [
                'error' => $e->getMessage(),
                'dispatch_id' => $kampalaDispatch->id
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

   public function receive(Request $request, $id)
{
    $user = Auth::user();

    if ($user->role !== 'kampala_shop') {
        abort(403, 'Only Kampala shop staff can receive dispatches.');
    }

    $kampalaDispatch = \App\Models\KampalaDispatch::with('items')->find($id);

    if (!$kampalaDispatch) {
        return back()->with('error', 'Dispatch not found.');
    }

    if ($kampalaDispatch->status === 'received') {
        return back()->with('info', 'This dispatch has already been received.');
    }

    $receivedItems = $request->input('received_items', []);
    if (empty($receivedItems)) {
        return back()->with('error', 'No items were submitted.');
    }

    try {
        DB::transaction(function () use ($kampalaDispatch, $receivedItems, $user) {
            foreach ($kampalaDispatch->items as $item) {
                $receivedQty = isset($receivedItems[$item->id]) ? (int)$receivedItems[$item->id] : 0;

                // Update received quantity in dispatch items
                $item->received_quantity = $receivedQty;
                $item->save();

                // ✅ NEW: Update Kampala stock for received items
                if ($receivedQty > 0) {
                    $this->updateKampalaStock(
                        $kampalaDispatch->shop_id,
                        $item->product_type,
                        $receivedQty,
                        $item->unit_price
                    );
                }
            }

            $kampalaDispatch->status = 'received';
            $kampalaDispatch->received_by = Auth::id();
            $kampalaDispatch->received_at = now();
            $kampalaDispatch->save();
        });

        return redirect()->route('kampala.dispatches.index')
            ->with('success', 'Dispatch received and stock updated successfully!');

    } catch (\Exception $e) {
        \Log::error('Receive dispatch failed', [
            'error' => $e->getMessage(),
            'dispatch_id' => $id,
            'user_id' => $user->id,
        ]);

        return back()->with('error', 'Error receiving dispatch: ' . $e->getMessage());
    }
}



/**
 * Update Kampala stock when items are received
 */
private function updateKampalaStock($shopId, $productType, $receivedQty, $unitPrice)
{
    $kampalaStock = \App\Models\KampalaStock::where('shop_id', $shopId)
        ->where('product_type', $productType)
        ->first();

    if ($kampalaStock) {
        // Update existing stock
        $kampalaStock->dispatched += $receivedQty;
        $kampalaStock->remaining = $kampalaStock->opening_stock + $kampalaStock->dispatched - $kampalaStock->sold;
        $kampalaStock->save();
        
        \Log::info('Kampala stock updated', [
            'shop_id' => $shopId,
            'product' => $productType,
            'received_qty' => $receivedQty,
            'new_dispatched' => $kampalaStock->dispatched,
            'new_remaining' => $kampalaStock->remaining
        ]);
    } else {
        // Create new stock record if it doesn't exist
        $kampalaStock = \App\Models\KampalaStock::create([
            'shop_id' => $shopId,
            'product_type' => $productType,
            'opening_stock' => 0,
            'dispatched' => $receivedQty,
            'sold' => 0,
            'remaining' => $receivedQty, // opening(0) + dispatched(received) - sold(0)
            'unit_price' => $unitPrice
        ]);
        
        \Log::info('New Kampala stock created', [
            'shop_id' => $shopId,
            'product' => $productType,
            'initial_stock' => $receivedQty
        ]);
    }

    return $kampalaStock;
}
}
