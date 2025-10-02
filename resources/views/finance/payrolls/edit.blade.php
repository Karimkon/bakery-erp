@extends('finance.layouts.app')
@section('title','Edit Payroll')

@section('content')
<div class="card shadow-sm p-4">
    <h4 class="mb-4"><i class="bi bi-pencil-square me-2"></i> Edit Payroll</h4>

    <form method="POST" action="{{ route('finance.payrolls.update',$payroll) }}">
        @csrf
        @method('PUT')

        <!-- Employee (locked) -->
        <div class="mb-3">
            <label class="form-label">Employee</label>
            <input type="text" class="form-control" value="{{ $payroll->user->name }}" disabled>
        </div>

        <!-- Pay Month (locked) -->
        <div class="mb-3">
            <label class="form-label">Pay Month</label>
            <input type="month" class="form-control" value="{{ $payroll->pay_month->format('Y-m') }}" disabled>
        </div>

        <!-- Base Salary -->
        <div class="mb-3">
            <label class="form-label">Base Salary (UGX)</label>
            <input type="number" name="base_salary" class="form-control" 
                   value="{{ old('base_salary',$payroll->base_salary) }}" required>
            @error('base_salary') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Commission -->
        <div class="mb-3">
            <label class="form-label">Commission (UGX)</label>
            <input type="number" name="commission" class="form-control" 
                   value="{{ old('commission',$payroll->commission) }}">
            @error('commission') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="pending" {{ $payroll->status=='pending'?'selected':'' }}>Pending</option>
                <option value="paid" {{ $payroll->status=='paid'?'selected':'' }}>Paid</option>
            </select>
            @error('status') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-2"></i> Update Payroll
        </button>
        <a href="{{ route('finance.payrolls.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
