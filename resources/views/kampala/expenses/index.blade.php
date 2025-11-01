@extends('kampala.layouts.app')
@section('title','Shop Expenses')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Shop Expenses</h4>
        <div>
            <a href="{{ route('kampala.expenses.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> New Expense
            </a>
        </div>
    </div>

    <!-- Cash Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted">Available Cash</h6>
                    <h3 class="text-success">UGX {{ number_format($availableCash) }}</h3>
                    <small class="text-muted">After expenses & banking</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th>Recorded By</th>
                            <th>Receipt No.</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ \App\Models\KampalaExpense::expenseCategories()[$expense->category] ?? $expense->category }}
                                </span>
                            </td>
                            <td>{{ $expense->description }}</td>
                            <td class="text-end text-danger fw-bold">-UGX {{ number_format($expense->amount) }}</td>
                            <td>{{ $expense->user->name }}</td>
                            <td>{{ $expense->receipt_number ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('kampala.expenses.show', $expense) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-receipt display-4 d-block mb-2"></i>
                                No expenses recorded yet
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
@endsection