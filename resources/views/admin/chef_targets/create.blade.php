@extends('admin.layouts.app')
@section('title','Add Chef Target')

@section('content')
<h4 class="mb-3"><i class="bi bi-plus-lg me-2"></i> Add Chef Target</h4>

<form action="{{ route('admin.chef_targets.store') }}" method="POST" class="card shadow-sm p-4">
    @csrf

    <div class="mb-3">
        <label>Chef</label>
        <select name="chef_id" class="form-select" required>
            <option value="">-- Select Chef --</option>
            @foreach($chefs as $chef)
            <option value="{{ $chef->id }}" {{ old('chef_id')==$chef->id?'selected':'' }}>
                {{ $chef->name }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Daily Target (UGX)</label>
        <input type="number" step="0.01" name="daily_target" class="form-control" value="{{ old('daily_target') }}" required>
        <small class="text-muted">Expected daily production value</small>
    </div>

    <div class="mb-3">
        <label>Monthly Target (UGX)</label>
        <input type="number" step="0.01" name="monthly_target" class="form-control" value="{{ old('monthly_target') }}" required>
        <small class="text-muted">Total target for the month</small>
    </div>

    <div class="mb-3">
        <label>💰 Fixed Salary (UGX)</label>
        <input type="number" step="0.01" name="fixed_salary" class="form-control" value="{{ old('fixed_salary') }}" required>
        <small class="text-muted">
            <strong>Salary chef receives when they meet 100% of monthly target</strong><br>
            Example: If target is 60M and fixed salary is 1.3M, chef gets 1.3M when they produce 60M
        </small>
    </div>

    <div class="mb-3">
        <label>Days Off</label>
        <select name="days_off[]" class="form-select" multiple>
            @foreach(['sunday','monday','tuesday','wednesday','thursday','friday','saturday'] as $day)
            <option value="{{ $day }}" {{ collect(old('days_off'))->contains($day)?'selected':'' }}>{{ ucfirst($day) }}</option>
            @endforeach
        </select>
        <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple</small>
    </div>

    <div class="mb-3">
        <label>Commission %</label>
        <input type="number" step="0.01" name="commission_percentage" class="form-control" value="{{ old('commission_percentage',100) }}" required>
        <small class="text-muted">
            Percentage used to calculate salary proportionally<br>
            <strong>100% = Full proportional payment</strong> (if chef produces 50% of target, they get 50% of fixed salary)
        </small>
    </div>

    <div class="alert alert-info">
        <strong>📊 How Salary is Calculated:</strong><br>
        <code>Salary = (Produced / Target) × Fixed Salary × (Commission % / 100)</code><br><br>
        <strong>Example:</strong><br>
        Target: 60M, Fixed Salary: 1.3M, Commission: 100%<br>
        If chef produces 45M (75% of target): <code>Salary = (45M / 60M) × 1.3M × 1 = 975,000 UGX</code><br>
        If chef produces 60M (100% of target): <code>Salary = (60M / 60M) × 1.3M × 1 = 1,300,000 UGX</code>
    </div>

    <button class="btn btn-success">Save Target</button>
    <a href="{{ route('admin.chef_targets.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection