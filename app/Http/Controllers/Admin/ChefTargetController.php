<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChefTarget;
use App\Models\User;
use Illuminate\Http\Request;

class ChefTargetController extends Controller
{
    public function index()
    {
        $targets = ChefTarget::with('chef')->paginate(15);
        return view('admin.chef_targets.index', compact('targets'));
    }

    public function create()
    {
        $chefs = User::where('role', 'chef')->get();
        return view('admin.chef_targets.create', compact('chefs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'chef_id' => 'required|exists:users,id|unique:chef_targets,chef_id',
            'daily_target' => 'required|numeric|min:0',
            'monthly_target' => 'required|numeric|min:0',
            'fixed_salary' => 'required|numeric|min:0', // ✅ NEW
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'days_off' => 'nullable|array',
        ]);

        ChefTarget::create($request->all());

        return redirect()->route('admin.chef_targets.index')
            ->with('success', 'Chef target created successfully.');
    }

    public function edit(ChefTarget $chefTarget)
    {
        $chefs = User::where('role', 'chef')->get();
        return view('admin.chef_targets.edit', compact('chefTarget', 'chefs'));
    }

    public function update(Request $request, ChefTarget $chefTarget)
    {
        $request->validate([
            'chef_id' => 'required|exists:users,id|unique:chef_targets,chef_id,' . $chefTarget->id,
            'daily_target' => 'required|numeric|min:0',
            'monthly_target' => 'required|numeric|min:0',
            'fixed_salary' => 'required|numeric|min:0', // ✅ NEW
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'days_off' => 'nullable|array',
        ]);

        $chefTarget->update($request->all());

        return redirect()->route('admin.chef_targets.index')
            ->with('success', 'Chef target updated successfully.');
    }

    public function destroy(ChefTarget $chefTarget)
    {
        $chefTarget->delete();
        return redirect()->route('admin.chef_targets.index')
            ->with('success', 'Chef target deleted successfully.');
    }
}