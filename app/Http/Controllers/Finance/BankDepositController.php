<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BankDeposit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankDepositController extends Controller
{
    public function index()
    {
        $deposits = BankDeposit::with(['depositor','recorder'])
            ->latest('deposit_date')
            ->paginate(15);

        return view('finance.deposits.index', compact('deposits'));
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
        ]);

        $validated['recorded_by'] = Auth::id();

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
