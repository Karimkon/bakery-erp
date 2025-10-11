@extends('manager.layouts.app')
@section('title', 'Dispatch History - ' . $driver->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>
        <i class="bi bi-clock-history me-2"></i> 
        Dispatch History: <strong class="text-primary">{{ $driver->name }}</strong>
    </h4>
    <a href="{{ route('manager.dispatches.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to All Drivers
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Dispatch #</th>
                        <th>Items Sold</th>
                        <th>Total Sales (UGX)</th>
                        <th>Commission (UGX)</th>
                        <th>Expenses (UGX)</th>
                        <th>Cash Received (UGX)</th>
                        <th>Balance Due (UGX)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispatches as $dispatch)
                        <tr>
                            <td>
                                <strong>{{ $dispatch->dispatch_date->format('d M Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ $dispatch->dispatch_date->diffForHumans() }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">#{{ $dispatch->dispatch_no }}</span>
                            </td>
                            <td>{{ number_format($dispatch->total_items_sold) }}</td>
                            <td>{{ number_format($dispatch->total_sales_value, 0) }}</td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    {{ number_format($dispatch->commission_total, 0) }}
                                </span>
                            </td>
                            <td>
                                @if($dispatch->total_expenses > 0)
                                    <span class="badge bg-danger">
                                        {{ number_format($dispatch->total_expenses, 0) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        ({{ $dispatch->expenses->count() }} item{{ $dispatch->expenses->count() > 1 ? 's' : '' }})
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ number_format($dispatch->cash_received, 0) }}</td>
                            <td>
                                <strong class="text-{{ $dispatch->balance_due > 0 ? 'danger' : 'success' }}">
                                    {{ number_format($dispatch->balance_due, 0) }}
                                </strong>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('manager.dispatches.show', $dispatch->id) }}" 
                                       class="btn btn-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('manager.dispatches.edit', $dispatch->id) }}" 
                                       class="btn btn-warning" title="Edit Dispatch">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Expense Details Row (Collapsible) -->
                        @if($dispatch->expenses->count() > 0)
                            <tr>
                                <td colspan="9" class="bg-light p-0">
                                    <div class="accordion accordion-flush" id="expenseAccordion{{ $dispatch->id }}">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed py-2 px-3 bg-light" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#expenseDetails{{ $dispatch->id }}">
                                                    <i class="bi bi-receipt me-2"></i>
                                                    <small>View Expense Breakdown ({{ $dispatch->expenses->count() }} items)</small>
                                                </button>
                                            </h2>
                                            <div id="expenseDetails{{ $dispatch->id }}" 
                                                 class="accordion-collapse collapse" 
                                                 data-bs-parent="#expenseAccordion{{ $dispatch->id }}">
                                                <div class="accordion-body p-3">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th>Type</th>
                                                                <th>Amount (UGX)</th>
                                                                <th>Description</th>
                                                                <th>Receipt</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($dispatch->expenses as $expense)
                                                                <tr>
                                                                    <td>
                                                                        <span class="badge bg-secondary">
                                                                            {{ \App\Models\DriverExpense::expenseTypes()[$expense->expense_type] ?? $expense->expense_type }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ number_format($expense->amount, 0) }}</td>
                                                                    <td>{{ $expense->description ?? '-' }}</td>
                                                                    <td>
                                                                        @if($expense->receipt_image)
                                                                            <a href="{{ asset('storage/' . $expense->receipt_image) }}" 
                                                                               target="_blank" 
                                                                               class="btn btn-sm btn-outline-primary">
                                                                                <i class="bi bi-image"></i> View
                                                                            </a>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot class="table-light">
                                                            <tr>
                                                                <th>Total:</th>
                                                                <th colspan="3">
                                                                    {{ number_format($dispatch->total_expenses, 0) }} UGX
                                                                </th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No dispatch history found for this driver.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $dispatches->links() }}
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total Dispatches</h6>
                <h3>{{ $dispatches->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Total Sales</h6>
                <h3>{{ number_format($dispatches->sum('total_sales_value'), 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-title">Total Expenses</h6>
                <h3>{{ number_format($dispatches->sum('driver_expenses'), 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6 class="card-title">Total Commission</h6>
                <h3>{{ number_format($dispatches->sum('commission_total'), 0) }}</h3>
            </div>
        </div>
    </div>
</div>
@endsection