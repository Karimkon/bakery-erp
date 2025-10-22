<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
{
    $query = Expense::with('recorder');

    // Apply filters
    if ($request->has('category') && $request->category != '') {
        $query->where('category', 'like', '%' . $request->category . '%');
    }

    if ($request->has('start_date') && $request->start_date != '') {
        $query->where('expense_date', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date != '') {
        $query->where('expense_date', '<=', $request->end_date);
    }

    if ($request->has('min_amount') && $request->min_amount != '') {
        $query->where('amount', '>=', $request->min_amount);
    }

    if ($request->has('max_amount') && $request->max_amount != '') {
        $query->where('amount', '<=', $request->max_amount);
    }

    $expenses = $query->latest('expense_date')->paginate(15);

    // Get unique categories for filter dropdown
    $categories = Expense::distinct()->pluck('category');

    return view('finance.expenses.index', compact('expenses', 'categories'));
}

    public function create()
    {
        return view('finance.expenses.create');
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'category' => 'required|string|max:100',
        'description' => 'nullable|string|max:255',
        'amount' => 'required|numeric|min:0',
        'expense_date' => 'required|date',
        'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
    ]);

    $validated['recorded_by'] = Auth::id();

    // Handle receipt upload
    if ($request->hasFile('receipt')) {
        $file = $request->file('receipt');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('receipts', $filename, 'public'); // stored in storage/app/public/receipts
        $validated['receipt'] = $path;
    }

    Expense::create($validated);

    return redirect()->route('finance.expenses.index')
        ->with('success', 'Expense recorded successfully.');
}


    public function show(Expense $expense)
    {
        return view('finance.expenses.show', compact('expense'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('finance.expenses.index')
            ->with('success', 'Expense deleted.');
    }
}
