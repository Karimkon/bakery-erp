<?php

namespace App\Http\Controllers\Kampala;

use App\Http\Controllers\Controller;
use App\Models\KampalaExpense;
use App\Models\KampalaSale;
use App\Models\KampalaBanking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KampalaExpenseController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->kampalaShop;
        $expenses = KampalaExpense::with('user')
            ->where('shop_id', $shop->id)
            ->latest()
            ->paginate(20);

        // Calculate available cash (including expenses)
        $availableCash = $this->calculateAvailableCash($shop);

        return view('kampala.expenses.index', compact('expenses', 'availableCash'));
    }

    public function create()
    {
        $shop = Auth::user()->kampalaShop;
        $availableCash = $this->calculateAvailableCash($shop);
        $categories = KampalaExpense::expenseCategories();

        return view('kampala.expenses.create', compact('availableCash', 'categories'));
    }

    public function store(Request $request)
    {
        $shop = Auth::user()->kampalaShop;

        $request->validate([
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:100',
            'expense_date' => 'required|date',
            'receipt_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if expense amount exceeds available cash
        $availableCash = $this->calculateAvailableCash($shop);
        if ($request->amount > $availableCash) {
            return back()->with('error', 'Expense amount exceeds available cash. Available: UGX ' . number_format($availableCash));
        }

        KampalaExpense::create([
            'shop_id' => $shop->id,
            'user_id' => Auth::id(),
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'receipt_number' => $request->receipt_number,
            'notes' => $request->notes,
        ]);

        return redirect()->route('kampala.expenses.index')
            ->with('success', 'Expense recorded successfully!');
    }

    public function show(KampalaExpense $expense)
    {
        // Authorization - ensure user owns this expense
        if ($expense->shop_id !== Auth::user()->kampalaShop->id) {
            abort(403, 'Unauthorized');
        }

        return view('kampala.expenses.show', compact('expense'));
    }

    /**
     * Calculate available cash including expenses
     */
    private function calculateAvailableCash($shop)
    {
        $totalSales = KampalaSale::where('shop_id', $shop->id)
            ->where('payment_method', 'cash')
            ->sum('total_price');

        $totalBanked = KampalaBanking::where('shop_id', $shop->id)
            ->sum('amount');

        $totalExpenses = KampalaExpense::where('shop_id', $shop->id)
            ->sum('amount');

        return ($totalSales - $totalBanked - $totalExpenses);
    }
}