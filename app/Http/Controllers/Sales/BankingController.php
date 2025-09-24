<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Banking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BankingController extends Controller
{
    // Show all bankings
    public function index()
    {
        $bankings = Banking::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('sales.bankings.index', compact('bankings'));
    }

    // Form
    public function create()
    {
        return view('sales.bankings.create');
    }

    // Save banking record
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:100',
            'date'           => 'required|date',
            'receipt_number' => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:255',
            'receipt_file'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Handle file upload
        if ($request->hasFile('receipt_file')) {
            $path = $request->file('receipt_file')->store('banking_receipts', 'public');
            $validated['receipt_file'] = $path;
        }

        $validated['user_id'] = Auth::id();

        Banking::create($validated);

        return redirect()
            ->route('sales.bankings.index')
            ->with('success', 'Banking record added successfully.');
    }

    // Show
    public function show(Banking $banking)
    {
        $this->authorize('view', $banking);
        return view('sales.bankings.show', compact('banking'));
    }

    // Edit
    public function edit(Banking $banking)
    {
        $this->authorize('update', $banking);
        return view('sales.bankings.edit', compact('banking'));
    }

    // Update
    public function update(Request $request, Banking $banking)
    {
        $this->authorize('update', $banking);

        $validated = $request->validate([
            'amount'         => 'required|numeric|min:100',
            'date'           => 'required|date',
            'receipt_number' => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:255',
            'receipt_file'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Update receipt if new uploaded
        if ($request->hasFile('receipt_file')) {
            if ($banking->receipt_file) {
                Storage::disk('public')->delete($banking->receipt_file);
            }
            $path = $request->file('receipt_file')->store('banking_receipts', 'public');
            $validated['receipt_file'] = $path;
        }

        $banking->update($validated);

        return redirect()
            ->route('sales.bankings.index')
            ->with('success', 'Banking record updated successfully.');
    }

    // Delete
    public function destroy(Banking $banking)
    {
        $this->authorize('delete', $banking);

        if ($banking->receipt_file) {
            Storage::disk('public')->delete($banking->receipt_file);
        }

        $banking->delete();

        return redirect()
            ->route('sales.bankings.index')
            ->with('success', 'Banking record deleted successfully.');
    }
}
