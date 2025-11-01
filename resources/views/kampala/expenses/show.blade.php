@extends('kampala.layouts.app')
@section('title','Expense Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Expense Details</h4>
        <div>
            <a href="{{ route('kampala.expenses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Expenses
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-secondary">
                                    {{ \App\Models\KampalaExpense::expenseCategories()[$expense->category] ?? $expense->category }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Date</label>
                            <p class="form-control-plaintext">{{ $expense->expense_date->format('F d, Y') }}</p>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <p class="form-control-plaintext">{{ $expense->description }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount</label>
                            <p class="form-control-plaintext text-danger fw-bold fs-5">
                                -UGX {{ number_format($expense->amount) }}
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Receipt Number</label>
                            <p class="form-control-plaintext">{{ $expense->receipt_number ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Recorded By</label>
                            <p class="form-control-plaintext">{{ $expense->user->name }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Recorded On</label>
                            <p class="form-control-plaintext">{{ $expense->created_at->format('M d, Y g:i A') }}</p>
                        </div>

                        @if($expense->notes)
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <p class="form-control-plaintext">{{ $expense->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection