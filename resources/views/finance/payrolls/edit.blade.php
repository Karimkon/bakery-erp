@extends('finance.layouts.app')
@section('title','Edit Payroll')

@section('content')
<div class="card shadow-sm p-4">
    <h4 class="mb-4"><i class="bi bi-pencil-square me-2"></i> Edit Payroll Status</h4>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        <strong>Note:</strong> Commission is automatically calculated based on production.
        You can only change the payment status here.
    </div>

    {{-- Display Payroll Details --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Payroll Details</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Employee:</strong> {{ $payroll->employee_name }}</p>
                    <p><strong>Month:</strong> {{ $payroll->pay_month->format('F Y') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Commission/Salary:</strong> UGX {{ number_format($payroll->commission) }}</p>
                    <p><strong>Current Status:</strong> 
                        <span class="badge bg-{{ $payroll->status=='paid'?'success':'warning' }}">
                            {{ ucfirst($payroll->status) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Form --}}
    <form method="POST" action="{{ route('finance.payrolls.update', $payroll) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Payment Status</label>
            <select name="status" class="form-select" required>
                <option value="pending" {{ $payroll->status=='pending' ? 'selected' : '' }}>
                    Pending
                </option>
                <option value="paid" {{ $payroll->status=='paid' ? 'selected' : '' }}>
                    Paid
                </option>
            </select>
            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-save me-2"></i> Update Status
        </button>
        <a href="{{ route('finance.payrolls.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection