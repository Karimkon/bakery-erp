<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banking;
use App\Models\User;
use Illuminate\Http\Request;

class BankingController extends Controller
{
   public function index(Request $request)
{
    $query = \App\Models\Banking::with('user')->latest();

    if ($request->filled('from')) {
        $query->whereDate('date', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $query->whereDate('date', '<=', $request->to);
    }
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    $bankings = $query->paginate(20);
    $salesUsers = \App\Models\User::where('role', 'sales')->get();

    $summary = [
        'count' => (clone $query)->count(),
        'total' => (clone $query)->sum('amount'),
    ];

    return view('admin.bankings.index', compact('bankings', 'salesUsers', 'summary'));
}
}
