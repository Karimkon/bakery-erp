@extends('finance.layouts.app')
@section('title','Payroll Records')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4><i class="bi bi-cash-stack me-2"></i> Payroll Records</h4>
    <a href="{{ route('finance.payrolls.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Generate Payroll
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
            <th>Commission/Salary</th>
            <th>Target Achievement</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payrolls as $pay)
        <tr>
            <td>{{ $pay->employee_name ?? ($pay->user->name ?? '-') }}</td>
            <td>{{ $pay->pay_month->format('F Y') }}</td>
            <td><strong>UGX {{ number_format($pay->commission) }}</strong></td>
            
            {{-- Target Achievement --}}
            <td>
                @if($pay->user && $pay->user->chefTarget)
                    @php
                        $target = $pay->user->chefTarget->monthly_target;
                        $produced = $pay->user->productions()
                                    ->whereYear('production_date', $pay->pay_month->year)
                                    ->whereMonth('production_date', $pay->pay_month->month)
                                    ->sum('total_value');
                        $progress = $target > 0 ? ($produced / $target) * 100 : 0;
                    @endphp
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : ($progress >= 75 ? 'bg-info' : 'bg-warning') }}" 
                             role="progressbar" style="width: {{ min($progress,100) }}%;">
                            {{ number_format($progress,1) }}%
                        </div>
                    </div>
                    <small class="text-muted">
                        Produced: UGX {{ number_format($produced) }} / Target: UGX {{ number_format($target) }}
                    </small>
                @else
                    <span class="text-muted">No target set</span>
                @endif
            </td>

            <td>
                <span class="badge bg-{{ $pay->status=='paid'?'success':'warning' }}">
                    {{ ucfirst($pay->status) }}
                </span>
            </td>
            
            <td>
                <div class="btn-group" role="group">
                    <a href="{{ route('finance.payrolls.edit',$pay) }}" 
                       class="btn btn-sm btn-secondary" title="Change Status">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="{{ route('finance.payrolls.payslip',$pay) }}" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-primary" title="View Payslip">
                        <i class="bi bi-printer"></i>
                    </a>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-muted">No payroll records found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $payrolls->appends(request()->query())->links() }}
@endsection