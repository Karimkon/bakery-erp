@extends('finance.layouts.app')

@section('title','Add Expense')

@section('content')
<h4 class="mb-3"><i class="bi bi-plus-circle me-2"></i> New Expense</h4>

<form method="POST" action="{{ route('finance.expenses.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <input type="text" name="description" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Amount (UGX)</label>
        <input type="number" step="0.01" name="amount" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Expense Date</label>
        <input type="date" name="expense_date" class="form-control" required>
    </div>

    <button class="btn btn-success">Save Expense</button>
    <a href="{{ route('finance.expenses.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
