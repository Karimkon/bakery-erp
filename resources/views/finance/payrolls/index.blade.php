@extends('finance.layouts.app')
@section('title','Payroll Records')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4><i class="bi bi-cash-stack me-2"></i> Payroll Records</h4>
    <a href="{{ route('finance.payrolls.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Payroll
    </a>
</div>

<!-- Filter Form -->
<form method="GET" action="{{ route('finance.payrolls.index') }}" class="card p-3 mb-3 shadow-sm">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label>User</label>
            <select name="user_id" class="form-select">
                <option value="">-- All Users --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id')==$user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Month</label>
            <input type="month" name="pay_month" class="form-control" value="{{ request('pay_month') }}">
        </div>

        <div class="col-md-3">
            <label>Status</label>
            <select name="status" class="form-select">
                <option value="">-- All --</option>
                <option value="paid" {{ request('status')=='paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route('finance.payrolls.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<!-- Payroll Table -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>User</th>
            <th>Month</th>
            <th>Base Salary</th>
            <th>Commission</th>
            <th>Total Salary</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payrolls as $pay)
        <tr>
            <td>{{ $pay->user->name }}</td>
            <td>{{ $pay->pay_month->format('F Y') }}</td>
            <td>{{ number_format($pay->base_salary,0) }}</td>
            <td>{{ number_format($pay->commission,0) }}</td>
            <td>{{ number_format($pay->total_salary,0) }}</td>
            <td>
                <span class="badge bg-{{ $pay->status=='paid'?'success':'warning' }}">
                    {{ ucfirst($pay->status) }}
                </span>
            </td>
            <td class="d-flex gap-2">
                <a href="{{ route('finance.payrolls.edit',$pay) }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <a href="{{ route('finance.payrolls.payslip',$pay) }}" target="_blank" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-printer"></i> Payslip
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-muted">No payroll records found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $payrolls->appends(request()->query())->links() }}
@endsection
