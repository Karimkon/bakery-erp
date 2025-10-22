<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Banking;
use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\CashBalanceService;
use Carbon\Carbon;

class BankingController extends Controller
{
    public function index()
    {
        $bankings = Banking::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        // Calculate balance manually - remove user_id from expenses query
        $totalSales = Sale::where('user_id', Auth::id())
            ->where('payment_method', 'cash')
            ->whereDate('created_at', '<=', Carbon::today())
            ->sum('total_price');
            
        $totalBanked = Banking::where('user_id', Auth::id())
            ->whereDate('date', '<=', Carbon::today())
            ->sum('amount');
            
        // Expenses are global (no user_id filter)
        $totalExpenses = Expense::whereDate('expense_date', '<=', Carbon::today())
            ->sum('amount');
            
        $balance = [
            'total_sales' => $totalSales ?? 0,
            'total_banked' => $totalBanked ?? 0,
            'total_expenses' => $totalExpenses ?? 0,
            'available_cash' => ($totalSales ?? 0) - ($totalBanked ?? 0) - ($totalExpenses ?? 0)
        ];

        return view('sales.bankings.index', compact('bankings', 'balance'));
    }

    public function create()
    {
        // Calculate balance manually - remove user_id from expenses query
        $totalSales = Sale::where('user_id', Auth::id())
            ->where('payment_method', 'cash')
            ->whereDate('created_at', '<=', Carbon::today())
            ->sum('total_price');
            
        $totalBanked = Banking::where('user_id', Auth::id())
            ->whereDate('date', '<=', Carbon::today())
            ->sum('amount');
            
        // Expenses are global (no user_id filter)
        $totalExpenses = Expense::whereDate('expense_date', '<=', Carbon::today())
            ->sum('amount');
            
        $balance = [
            'total_sales' => $totalSales ?? 0,
            'total_banked' => $totalBanked ?? 0,
            'total_expenses' => $totalExpenses ?? 0,
            'available_cash' => ($totalSales ?? 0) - ($totalBanked ?? 0) - ($totalExpenses ?? 0)
        ];
        
        return view('sales.bankings.create', compact('balance'));
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
        // Manual authorization check
        if ($banking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        return view('sales.bankings.show', compact('banking'));
    }

    // Edit
    public function edit(Banking $banking)
    {
        // Manual authorization check
        if ($banking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        return view('sales.bankings.edit', compact('banking'));
    }

    // Update
    public function update(Request $request, Banking $banking)
    {
        // Manual authorization check
        if ($banking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

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
        // Manual authorization check
        if ($banking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($banking->receipt_file) {
            Storage::disk('public')->delete($banking->receipt_file);
        }

        $banking->delete();

        return redirect()
            ->route('sales.bankings.index')
            ->with('success', 'Banking record deleted successfully.');
    }
}