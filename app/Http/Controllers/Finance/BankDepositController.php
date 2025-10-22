<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BankDeposit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankDepositController extends Controller
{
    public function index(Request $request)
{
    $query = BankDeposit::with(['depositor','recorder']);

    // Apply filters
    if ($request->has('depositor_id') && $request->depositor_id != '') {
        $query->where('user_id', $request->depositor_id);
    }

    if ($request->has('start_date') && $request->start_date != '') {
        $query->where('deposit_date', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date != '') {
        $query->where('deposit_date', '<=', $request->end_date);
    }

    $deposits = $query->latest('deposit_date')->paginate(15);
    
    // Get depositors for filter dropdown
    $depositors = User::whereIn('role',['driver','shop'])->get();

    return view('finance.deposits.index', compact('deposits', 'depositors'));
}

    public function create()
    {
        // drivers/shop staff selectable
        $users = User::whereIn('role',['driver','shop'])->get();
        return view('finance.deposits.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'amount'       => 'required|numeric|min:0',
            'deposit_date' => 'required|date',
            'receipt'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', 
        ]);

        $validated['recorded_by'] = Auth::id();

         // Handle file upload
        if ($request->hasFile('receipt')) {
    $validated['receipt'] = $request->file('receipt')->storeAs(
        '', 
        time().'_'.$request->file('receipt')->getClientOriginalName(),
        'bank_receipts'
    );
}


        BankDeposit::create($validated);

        return redirect()->route('finance.deposits.index')
            ->with('success','Deposit recorded successfully.');
    }

    public function destroy(BankDeposit $deposit)
    {
        $deposit->delete();
        return redirect()->route('finance.deposits.index')
            ->with('success','Deposit deleted.');
    }
}
