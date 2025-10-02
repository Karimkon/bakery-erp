<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // make sure barryvdh/laravel-dompdf is installed


class PayrollController extends Controller
{
    public function index(Request $request)
{
    $query = Payroll::with('user');

    // Filter by user
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    // Filter by month (year-month)
    if ($request->filled('pay_month')) {
        $query->whereMonth('pay_month', date('m', strtotime($request->pay_month)))
              ->whereYear('pay_month', date('Y', strtotime($request->pay_month)));
    }

    // Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $payrolls = $query->latest()->paginate(20);

    $users = User::orderBy('name')->get(); // For filter dropdowns

    return view('finance.payrolls.index', compact('payrolls','users'));
}


    public function create()
    {
        $users = User::all();
        return view('finance.payrolls.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'pay_month' => 'required|date',
            'base_salary' => 'required|numeric',
            'commission' => 'nullable|numeric',
        ]);

        $data['total_salary'] = $data['base_salary'] + ($data['commission'] ?? 0);

        Payroll::create($data);

        return redirect()->route('finance.payrolls.index')->with('success','Payroll recorded successfully.');
    }

    public function edit(Payroll $payroll)
    {
        $users = User::all();
        return view('finance.payrolls.edit', compact('payroll','users'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $data = $request->validate([
            'base_salary' => 'required|numeric',
            'commission' => 'nullable|numeric',
            'status' => 'required|string'
        ]);

        $data['total_salary'] = $data['base_salary'] + ($data['commission'] ?? 0);

        $payroll->update($data);

        return redirect()->route('finance.payrolls.index')->with('success','Payroll updated.');
    }

    public function payslip(Payroll $payroll)
{
    $pdf = Pdf::loadView('finance.payrolls.payslip', compact('payroll'))
              ->setPaper('A5', 'portrait'); // nice payslip size

    return $pdf->stream("Payslip_{$payroll->user->name}_{$payroll->pay_month->format('Y-m')}.pdf");
}
}
