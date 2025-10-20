@extends('finance.layouts.app')
@section('title','Add Payroll')

@section('content')
<div class="card shadow-sm p-4">
    <h4 class="mb-4"><i class="bi bi-plus-circle me-2"></i> Generate Monthly Payroll</h4>

    {{-- Preview Form --}}
    <form method="GET" action="{{ route('finance.payrolls.create') }}" class="border p-3 rounded bg-light mb-4">
        <h6 class="mb-3">📊 Preview Commission Calculation</h6>
        
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Select Chef</label>
                <select name="preview_user_id" class="form-select" required>
                    <option value="">-- Select Chef --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" 
                            {{ request('preview_user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} (ID: {{ $user->id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Month</label>
                <input type="month" name="preview_month" class="form-control" 
                    value="{{ request('preview_month') ?? now()->format('Y-m') }}" required>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-calculator"></i> Calculate
                </button>
            </div>
        </div>
    </form>

    {{-- Preview Results --}}
    @if($preview)
        @if(isset($preview['error']))
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> {{ $preview['error'] }}
            </div>
        @else
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">💰 Commission Preview - {{ $preview['user_name'] }} (User ID: {{ request('preview_user_id') }})</h5>
                </div>
                <div class="card-body">
                    @if(isset($preview['production_dates']))
                    <div class="alert alert-info mb-3">
                        <strong>🔍 Debug Info:</strong><br>
                        <small>
                            Productions found on dates: 
                            @if(count($preview['production_dates']) > 0)
                                {{ implode(', ', $preview['production_dates']) }}
                            @else
                                <span class="text-danger">No productions found for this month</span>
                            @endif
                        </small>
                    </div>
                    @endif
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Monthly Target:</strong>
                            <p class="mb-1">UGX {{ number_format($preview['monthly_target']) }}</p>
                        </div>

                        <div class="col-md-6">
                            <strong>Fixed Salary (at 100%):</strong>
                            <p class="mb-1 text-success"><strong>UGX {{ number_format($preview['fixed_salary']) }}</strong></p>
                        </div>

                        <div class="col-md-6">
                            <strong>Total Produced:</strong>
                            <p class="mb-1">UGX {{ number_format($preview['total_produced']) }}</p>
                        </div>

                        <div class="col-md-6">
                            <strong>Working Days This Month:</strong>
                            <p class="mb-1">{{ $preview['working_days'] }} days</p>
                        </div>

                        <div class="col-md-6">
                            <strong>Days Actually Worked:</strong>
                            <p class="mb-1">{{ $preview['days_worked'] }} days</p>
                        </div>

                        <div class="col-12">
                            <strong>Target Achievement:</strong>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar {{ $preview['percentage'] >= 100 ? 'bg-success' : 'bg-warning' }}" 
                                     style="width: {{ min($preview['percentage'], 100) }}%;">
                                    {{ number_format($preview['percentage'], 1) }}%
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="alert alert-info">
                                <strong>💵 Commission Rate:</strong> {{ $preview['commission_percentage'] }}%<br>
                                <strong>📈 Formula:</strong> (Produced / Target) × Target × Commission Rate<br>
                                <strong>💰 Calculated Commission:</strong> 
                                <h4 class="text-success mb-0 mt-2">
                                    UGX {{ number_format($preview['commission']) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Save Payroll Form --}}
            <form method="POST" action="{{ route('finance.payrolls.store') }}" class="border-top pt-4">
                @csrf
                <input type="hidden" name="user_id" value="{{ request('preview_user_id') }}">
                <input type="hidden" name="pay_month" value="{{ request('preview_month') }}">

                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Note:</strong> This will create a payroll record with the calculated commission above.
                    The commission will be saved as the total salary (base salary = 0, commission = salary).
                </div>

                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-save me-2"></i> Save Payroll Record
                </button>
                <a href="{{ route('finance.payrolls.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
            </form>
        @endif
    @else
        <div class="text-center text-muted py-4">
            <i class="bi bi-calculator" style="font-size: 3rem;"></i>
            <p class="mt-3">Select a chef and month above to preview commission calculation</p>
        </div>
    @endif
</div>

@if(session('error'))
    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif
@endsection