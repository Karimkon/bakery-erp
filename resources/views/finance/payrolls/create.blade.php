@extends('finance.layouts.app')
@section('title','Add Payroll')

@section('content')
<div class="card shadow-sm p-4">
    <h4 class="mb-4"><i class="bi bi-plus-circle me-2"></i> Add Payroll</h4>

    <form method="POST" action="{{ route('finance.payrolls.store') }}">
        @csrf

        <!-- Employee -->
        <div class="mb-3">
            <label class="form-label">Employee</label>
            <select name="user_id" class="form-select" required>
                <option value="">-- Select Employee --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected':'' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
            @error('user_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Pay Month -->
        <div class="mb-3">
            <label class="form-label">Pay Month</label>
            <input type="month" name="pay_month" class="form-control" value="{{ old('pay_month') }}" required>
            @error('pay_month') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Base Salary -->
        <div class="mb-3">
            <label class="form-label">Base Salary (UGX)</label>
            <input type="number" name="base_salary" class="form-control" value="{{ old('base_salary') }}" required>
            @error('base_salary') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <!-- Commission -->
        <div class="mb-3">
            <label class="form-label">Commission (UGX)</label>
            <input type="number" name="commission" class="form-control" value="{{ old('commission',0) }}">
            @error('commission') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-save me-2"></i> Save Payroll
        </button>
        <a href="{{ route('finance.payrolls.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
