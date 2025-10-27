<?php

namespace App\Http\Controllers\Manager;

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

        return view('manager.productions.approval-index', compact('productions', 'status'));
    }


    public function edit(Production $production)
{
    if ($production->status !== 'pending') {
        return redirect()->route('manager.productions.approval.index')
            ->with('error', 'Only pending productions can be edited.');
    }

    $production->load(['chef', 'ingredientUsages.ingredient']);
    $products = config('bakery_products');
    
    return view('manager.productions.approval-edit', compact('production', 'products'));
}

public function update(Request $request, Production $production)
{
    if ($production->status !== 'pending') {
        return back()->with('error', 'Only pending productions can be edited.');
    }

    $request->validate([
        'flour_kgs' => 'required|numeric|min:0',
        'outputs' => 'required|array',
        'outputs.*' => 'nullable|integer|min:0',
        'ingredients' => 'nullable|array',
        'ingredients.*' => 'nullable|numeric|min:0',
    ]);

    try {
        DB::transaction(function () use ($production, $request) {
            $prices = config('bakery_products');
            $flourKgs = (float) $request->flour_kgs;
            $outputs = $request->input('outputs', []);
            $ingredientInputs = $request->input('ingredients', []);

            // Calculate total production value
            $totalValue = 0;
            foreach ($prices as $product => $price) {
                $qty = (int) ($outputs[$product] ?? 0);
                $totalValue += $qty * (int) $price;
            }

            // Update production data
            $production->update([
                'flour_bags' => $flourKgs / 50,
                'total_value' => $totalValue,
                // Update all product quantities
                'buns' => (int)($outputs['buns'] ?? 0),
                'small_breads' => (int)($outputs['small_breads'] ?? 0),
                'big_breads' => (int)($outputs['big_breads'] ?? 0),
                'donuts' => (int)($outputs['donuts'] ?? 0),
                'half_cakes' => (int)($outputs['half_cakes'] ?? 0),
                'block_cakes' => (int)($outputs['block_cakes'] ?? 0),
                'slab_cakes' => (int)($outputs['slab_cakes'] ?? 0),
                'quarter_breads' => (int)($outputs['quarter_breads'] ?? 0),
                'birthday_cakes30k' => (int)($outputs['birthday_cakes30k'] ?? 0),
                'birthday_cakes50k' => (int)($outputs['birthday_cakes50k'] ?? 0),
                'mandazis' => (int)($outputs['mandazis'] ?? 0),
                'musiba_tayi' => (int)($outputs['musiba_tayi'] ?? 0),
                'scornes' => (int)($outputs['scornes'] ?? 0),
                'chapatys' => (int)($outputs['chapatys'] ?? 0),
                'toasted_bread' => (int)($outputs['toasted_bread'] ?? 0),
                'spring_donuts' => (int)($outputs['spring_donuts'] ?? 0),
                'cream_donuts' => (int)($outputs['cream_donuts'] ?? 0),
                'cinnamon_rolls' => (int)($outputs['cinnamon_rolls'] ?? 0),
            ]);

            // Update ingredient usages
            $production->ingredientUsages()->delete(); // Remove old usages

            foreach ($ingredientInputs as $id => $qtyKg) {
                $qtyKg = (float) $qtyKg;
                if ($qtyKg <= 0) continue;

                $ingredient = \App\Models\Ingredient::findOrFail($id);

                $cost = $qtyKg * (float) $ingredient->unit_cost;

                $production->ingredientUsages()->create([
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $qtyKg,
                    'unit' => $ingredient->unit,
                    'cost' => $cost,
                ]);
            }
        });

        return redirect()->route('manager.productions.approval.show', $production->id)
            ->with('success', 'Production data updated successfully!');

    } catch (\Exception $e) {
        return back()->with('error', 'Error updating production: ' . $e->getMessage());
    }
}

    public function show(Production $production)
    {
        $production->load(['chef', 'ingredientUsages.ingredient', 'approvedBy']);
        return view('manager.productions.approval-show', compact('production'));
    }

   public function approve(Request $request, Production $production)
{
    $request->validate([
        'product_adjustments' => 'nullable|array',
        'product_adjustments.*' => 'nullable|integer|min:0',
        'ingredient_adjustments' => 'required|array',
        'ingredient_adjustments.*' => 'required|numeric|min:0',
    ]);

    // Check if already processed
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
                    throw new \Exception("Insufficient stock for {$ingredient->name}. Available: {$ingredient->stock} kg, Requested: {$adjustedQty} kg");
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

        return redirect()->route('manager.productions.approval.index')
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

        return redirect()->route('manager.productions.approval.index')
            ->with('success', 'Production rejected successfully.');
    }
}