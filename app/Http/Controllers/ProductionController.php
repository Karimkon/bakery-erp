<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\User;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with('user')->latest()->paginate(15);
        return view('admin.productions.index', compact('productions'));
    }

    public function create()
    {
        $chefs = User::where('role', 'chef')->get();
        return view('admin.productions.create', compact('chefs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'production_date' => 'required|date',
            'flour_bags' => 'required|numeric|min:0',
        ]);

        // 🛑 Variance check: at least 150 buns per bag
        $hasVariance = false;
        $notes = null;
        if ($request->buns < ($request->flour_bags * 150)) {
            $hasVariance = true;
            $notes = "Buns below expected yield (150 per bag minimum).";
        }

        // 💰 Calculate total value
        $totalValue =
            ($request->buns * 500) +
            ($request->small_breads * 1000) +
            ($request->big_breads * 2000) +
            ($request->donuts * 800) +
            ($request->half_cakes * 8000) +
            ($request->block_cakes * 12000) +
            ($request->slab_cakes * 20000) +
            ($request->birthday_cakes * 30000);

        Production::create([
            ...$request->all(),
            'total_value' => $totalValue,
            'has_variance' => $hasVariance,
            'variance_notes' => $notes,
        ]);

        return redirect()->route('admin.productions.index')
            ->with('success', 'Production recorded successfully.');
    }

    public function show(Production $production)
    {
        return view('admin.productions.show', compact('production'));
    }
}
