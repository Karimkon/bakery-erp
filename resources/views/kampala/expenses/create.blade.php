@extends('kampala.layouts.app')
@section('title','Record Expense')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Record New Expense</h4>
        <a href="{{ route('kampala.expenses.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Available Cash Alert -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Available Cash: <strong>UGX {{ number_format($availableCash) }}</strong>
                    </div>

                    <form method="POST" action="{{ route('kampala.expenses.store') }}">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control" 
                                       value="{{ old('expense_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control" 
                                       placeholder="e.g., Paid shop rent for March" 
                                       value="{{ old('description') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Amount (UGX) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" 
                                       min="100" step="100" placeholder="50000" 
                                       value="{{ old('amount') }}" required>
                                <div class="form-text">Minimum: UGX 100</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Receipt Number</label>
                                <input type="text" name="receipt_number" class="form-control" 
                                       placeholder="Optional receipt number" 
                                       value="{{ old('receipt_number') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" rows="3" class="form-control" 
                                          placeholder="Additional notes about this expense...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="bi bi-check-circle me-1"></i> Record Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Quick Info -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Expense Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Record all shop expenses</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Keep receipts for verification</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Expenses reduce available cash</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Use appropriate categories</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <a href="{{ route('kampala.bankings.index') }}" class="btn btn-outline-success w-100 mb-2">
                        <i class="bi bi-bank me-1"></i> Record Banking
                    </a>
                    <a href="{{ route('kampala.sales.create') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-bag-plus me-1"></i> New Sale
                    </a>
                    <a href="{{ route('kampala.dashboard') }}" class="btn btn-outline-info w-100">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection