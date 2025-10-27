<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Ingredient;
use App\Models\StockHistory; // ✅ Add this import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;


class ProductionController extends Controller
{
    public function index()
    {
        // Only THIS chef's productions
        $productions = Production::with('chef') // assuming you have a 'chef' relation in Production model
        ->where('user_id', Auth::id())
        ->latest()
        ->paginate(15);
        
        return view('chef.productions.index', compact('productions'));
    }

    public function create()
    {
        $ingredients = Ingredient::where('chef_id', Auth::id())
                            ->orderBy('name')
                            ->get();
        $products = config('bakery_products');
        return view('chef.productions.create', compact('ingredients', 'products'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'production_date' => 'required|date',
                'flour_kgs'       => 'required|numeric|min:0',
                'outputs'         => 'required|array',
                'outputs.*'       => 'nullable|integer|min:0',
                'ingredients'     => 'nullable|array',
                'ingredients.*'   => 'nullable|numeric|min:0',
            ]);

            $prices = config('bakery_products');
            $yieldMin = config('bakery_yield.yield_min_per_bag', []);
            $flourEq = config('bakery_yield.flour_equiv_bags_per_unit', []);

            $flourKgs = (float) $request->flour_kgs;
            $flourBags = $flourKgs / 50;
            $outputs = $request->input('outputs', []);

            // Calculate total production value
            $totalValue = 0;
            foreach ($prices as $product => $price) {
                $qty = (int) ($outputs[$product] ?? 0);
                $totalValue += $qty * (int) $price;
            }

            // Variance logic
            $hasVariance = false;
            $notes = '';

            if (isset($outputs['buns'], $yieldMin['buns'])) {
                $minBuns = $flourBags * $yieldMin['buns'];
                if ((int)$outputs['buns'] < $minBuns) {
                    $hasVariance = true;
                    $notes .= "Buns below minimum yield ({$outputs['buns']} < {$minBuns}). ";
                }
            }

            $impliedBags = 0.0;
            foreach ($outputs as $product => $qty) {
                $eq = (float) ($flourEq[$product] ?? 0);
                if ($eq > 0) $impliedBags += ((int)$qty) * $eq;
            }

            if ($impliedBags > $flourBags + 0.01) {
                $hasVariance = true;
                $notes .= "Over flour: outputs imply ~" . number_format($impliedBags, 2) . " bags > recorded {$flourBags}. ";
            }

            // ✅ MODIFIED: Save as PENDING (no stock updates yet)
            DB::transaction(function () use ($request, $outputs, $totalValue, $hasVariance, $notes, $flourKgs) {
                $production = Production::create([
                    'user_id'           => Auth::id(),
                    'production_date'   => $request->production_date,
                    'flour_bags'        => $flourKgs / 50,
                    'total_value'       => $totalValue,
                    'has_variance'      => $hasVariance ? 1 : 0,
                    'variance_notes'    => trim($notes) ?: null,
                    'status'            => 'pending', // ✅ NEW: Default to pending
                    'stock_updated'     => false,     // ✅ NEW: Stock not updated yet
                    
                    // All product fields...
                    'buns'              => (int)($outputs['buns'] ?? 0),
                    'small_breads'      => (int)($outputs['small_breads'] ?? 0),
                    'big_breads'        => (int)($outputs['big_breads'] ?? 0),
                    'donuts'            => (int)($outputs['donuts'] ?? 0),
                    'half_cakes'        => (int)($outputs['half_cakes'] ?? 0),
                    'block_cakes'       => (int)($outputs['block_cakes'] ?? 0),
                    'slab_cakes'        => (int)($outputs['slab_cakes'] ?? 0),
                    'quarter_breads'    => (int)($outputs['quarter_breads'] ?? 0),
                    'birthday_cakes30k' => (int)($outputs['birthday_cakes30k'] ?? 0),
                    'birthday_cakes50k' => (int)($outputs['birthday_cakes50k'] ?? 0),
                    'mandazis'          => (int)($outputs['mandazis'] ?? 0),
                    'musiba_tayi'       => (int)($outputs['musiba_tayi'] ?? 0),
                    'scornes'           => (int)($outputs['scornes'] ?? 0),
                    'chapatys'          => (int)($outputs['chapatys'] ?? 0),
                    'toasted_bread'     => (int)($outputs['toasted_bread'] ?? 0),
                    'spring_donuts'     => (int)($outputs['spring_donuts'] ?? 0),
                    'cream_donuts'      => (int)($outputs['cream_donuts'] ?? 0),
                    'cinnamon_rolls'    => (int)($outputs['cinnamon_rolls'] ?? 0),
                ]);

                // ✅ MODIFIED: Track ingredient usage but DON'T deduct from stock yet
                $ingredientInputs = $request->input('ingredients', []);
                foreach ($ingredientInputs as $id => $qtyKg) {
                    $qtyKg = (float) $qtyKg;
                    if ($qtyKg <= 0) continue;

                    $ingredient = Ingredient::findOrFail($id);

                    // ✅ Check stock availability but don't deduct
                    if ($qtyKg > (float) $ingredient->stock) {
                        throw new \Exception("Not enough stock for {$ingredient->name}. Available: {$ingredient->stock} {$ingredient->unit}");
                    }

                    $cost = $qtyKg * (float) $ingredient->unit_cost;

                    $production->ingredientUsages()->create([
                        'ingredient_id' => $ingredient->id,
                        'quantity'      => $qtyKg,
                        'unit'          => $ingredient->unit,
                        'cost'          => $cost,
                    ]);

                    // ❌ REMOVED: Stock deduction and stock history creation
                }

                // ❌ REMOVED: Bakery stock updates
            });

            return redirect()->route('chef.productions.index')
                ->with('success', 'Production submitted for approval! Manager will review and update stock.');
                
        } catch (\Exception $e) {
            \Log::error('Production Error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Production $production)
    {
        if ($production->user_id !== Auth::id()) {
            abort(403);
        }

        $production->load('ingredientUsages.ingredient');
        return view('chef.productions.show', compact('production'));
    }
}