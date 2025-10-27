<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\BakeryStock;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $productions = Production::with(['chef', 'ingredientUsages.ingredient'])
            ->when($status !== 'all', function($query) use ($status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        return view('admin.productions.approval-index', compact('productions', 'status'));
    }

    public function show(Production $production)
    {
        $production->load(['chef', 'ingredientUsages.ingredient']);
        return view('admin.productions.approval-show', compact('production'));
    }

    public function approve(Request $request, Production $production)
    {
        $request->validate([
            'product_adjustments' => 'nullable|array',
            'product_adjustments.*' => 'nullable|integer|min:0',
            'ingredient_adjustments' => 'required|array',
            'ingredient_adjustments.*' => 'required|numeric|min:0',
        ]);

        // ✅ Check if already processed
        if ($production->status !== 'pending') {
            return back()->with('error', 'This production has already been processed.');
        }

        try {
            DB::transaction(function () use ($production, $request) {
                $productAdjustments = $request->input('product_adjustments', []);
                $ingredientAdjustments = $request->input('ingredient_adjustments', []);

                // ✅ 1. Process ingredient stock deduction with adjustments
                foreach ($production->ingredientUsages as $usage) {
                    $ingredient = $usage->ingredient;
                    $adjustedQty = (float) ($ingredientAdjustments[$usage->id] ?? $usage->quantity);
                    
                    // Validate adjusted quantity doesn't exceed available stock
                    if ($adjustedQty > (float) $ingredient->stock) {
                        throw new \Exception("Insufficient stock for {$ingredient->name}. Available: {$ingredient->stock} {$ingredient->unit}, Requested: {$adjustedQty} {$ingredient->unit}");
                    }

                    // Update the ingredient usage record with adjusted quantity
                    $usage->update(['quantity' => $adjustedQty]);

                    // Deduct from stock
                    $stockBefore = (float) $ingredient->stock;
                    $ingredient->decrement('stock', $adjustedQty);
                    $ingredient->refresh();
                    $stockAfter = (float) $ingredient->stock;

                    StockHistory::create([
                        'ingredient_id'   => $ingredient->id,
                        'chef_id'         => $production->user_id,
                        'production_id'   => $production->id,
                        'quantity_changed'=> -$adjustedQty,
                        'quantity_before' => $stockBefore,
                        'quantity_after'  => $stockAfter,
                        'transaction_type'=> 'usage',
                        'added_by'        => auth()->id(),
                        'notes'           => "Approved production by {$production->chef->name} with adjusted quantity"
                    ]);
                }

                // ✅ 2. Update production quantities with adjustments
                $validProducts = [
                    'buns', 'small_breads', 'big_breads', 'donuts',
                    'half_cakes', 'block_cakes', 'slab_cakes',
                    'quarter_breads', 'birthday_cakes30k', 'birthday_cakes50k',
                    'mandazis', 'musiba_tayi', 'scornes', 'chapatys',
                    'toasted_bread', 'spring_donuts', 'cream_donuts', 'cinnamon_rolls'
                ];

                $updateData = ['status' => 'approved', 'approved_at' => now(), 'approved_by' => auth()->id()];
                
                // Calculate new total value based on adjustments
                $prices = config('bakery_products');
                $newTotalValue = 0;
                
                foreach ($validProducts as $product) {
                    $adjustedQty = (int) ($productAdjustments[$product] ?? $production->$product ?? 0);
                    $updateData[$product] = $adjustedQty;
                    $newTotalValue += $adjustedQty * ($prices[$product] ?? 0);
                }
                
                $updateData['total_value'] = $newTotalValue;

                // Update production record
                $production->update($updateData);

                // ✅ 3. Update bakery stock with adjusted quantities
                foreach ($validProducts as $product) {
                    $adjustedQty = (int) ($productAdjustments[$product] ?? $production->$product ?? 0);
                    
                    if ($adjustedQty > 0) {
                        $stock = BakeryStock::firstOrCreate(
                            ['product' => $product],
                            ['quantity' => 0]
                        );
                        $stock->increment('quantity', $adjustedQty);
                    }
                }

                // Mark stock as updated
                $production->update(['stock_updated' => true]);
            });

            return redirect()->route('admin.productions.approval-index')
                ->with('success', 'Production approved and stock updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error approving production: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, Production $production)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($production->status !== 'pending') {
            return back()->with('error', 'This production has already been processed.');
        }

        $production->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('admin.productions.approval-index')
            ->with('success', 'Production rejected successfully.');
    }
}