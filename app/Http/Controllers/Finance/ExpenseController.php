<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('recorder')
            ->latest('expense_date')
            ->paginate(15);

        return view('finance.expenses.index', compact('expenses'));
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
        ]);

        $validated['recorded_by'] = Auth::id();

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
