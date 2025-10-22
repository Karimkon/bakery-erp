@extends('admin.layouts.app')
@section('title', 'Financial Details - ' . $driver->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-file-earmark-text me-2"></i> 
            Financial Details: <span class="text-primary">{{ $driver->name }}</span>
        </h4>
        <p class="text-muted mb-0">
            {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.dispatches.financial-report') }}?driver_id={{ $driver->id }}&date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
           class="btn btn-outline-primary btn-sm">
            <i class="bi bi-graph-up"></i> Summary View
        </a>
        <a href="{{ route('admin.dispatches.history', $driver->id) }}" 
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history"></i> Dispatch History
        </a>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="driver_id" value="{{ $driver->id }}">
            <div class="col-md-4">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Update Date Range</button>
            </div>
        </form>
    </div>
</div>

<!-- Executive Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-calculator me-2"></i> Executive Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border-end pe-3">
                            <small class="text-muted d-block">Total Sales Value</small>
                            <h4 class="text-success mb-0">{{ number_format($totals['total_sales_value'], 0) }}</h4>
                            <small class="text-muted">UGX</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end pe-3">
                            <small class="text-muted d-block">Cash Collected</small>
                            <h4 class="text-info mb-0">{{ number_format($totals['total_cash_received'], 0) }}</h4>
                            <small class="text-muted">UGX</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="border-end pe-3">
                            <small class="text-muted d-block">Commission</small>
                            <h4 class="text-warning mb-0">{{ number_format($totals['total_commission'], 0) }}</h4>
                            <small class="text-muted">UGX</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="border-end pe-3">
                            <small class="text-muted d-block">Expenses</small>
                            <h4 class="text-danger mb-0">{{ number_format($totals['total_expenses'], 0) }}</h4>
                            <small class="text-muted">UGX</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div>
                            <small class="text-muted d-block">Expected to Bank</small>
                            <h4 class="text-primary mb-0">{{ number_format($totals['total_expected_after_deductions'], 0) }}</h4>
                            <small class="text-muted">UGX</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row text-center mt-3">
                    <div class="col-md-4">
                        <div class="border-end pe-3">
                            <small class="text-muted d-block">Actually Banked</small>
                            <h4 class="text-success mb-0">{{ number_format($totals['total_deposits'], 0) }}</h4>
                            <small class="text-muted">UGX</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-end pe-3">
                            <small class="text-muted d-block">Shortage/Excess</small>
                            <h4 class="text-{{ $totals['calculated_shortage'] >= 0 ? 'danger' : 'success' }} mb-0">
                                {{ number_format(abs($totals['calculated_shortage']), 0) }}
                            </h4>
                            <small class="text-muted">
                                {{ $totals['calculated_shortage'] >= 0 ? 'Driver Owes' : 'Overpayment' }}
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div>
                            <small class="text-muted d-block">Current Back Debt</small>
                            <h4 class="text-secondary mb-0">{{ number_format($totals['driver_back_debt'], 0) }}</h4>
                            <small class="text-muted">UGX</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Dispatches Section -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i> Dispatch Details ({{ $dispatches->count() }})</h5>
                <span class="badge bg-light text-dark">Total: {{ number_format($totals['total_sales_value'], 0) }} UGX</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th>#</th>
                                <th class="text-end">Sales Value</th>
                                <th class="text-end">Cash Received</th>
                                <th class="text-end">Commission</th>
                                <th class="text-end">Expenses</th>
                                <th class="text-end">Expected to Bank</th>
                                <th class="text-end">Balance Due</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dispatches as $dispatch)
                            <tr>
                                <td>
                                    <strong>{{ $dispatch->dispatch_date->format('d M') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $dispatch->dispatch_date->format('Y') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">#{{ $dispatch->dispatch_no }}</span>
                                </td>
                                <td class="text-end">{{ number_format($dispatch->total_sales_value, 0) }}</td>
                                <td class="text-end">{{ number_format($dispatch->cash_received, 0) }}</td>
                                <td class="text-end">
                                    <span class="badge bg-warning text-dark">{{ number_format($dispatch->commission_total, 0) }}</span>
                                </td>
                                <td class="text-end">
                                    @php
                                        $dispatchExpenses = $dispatch->expenses->sum('amount');
                                    @endphp
                                    @if($dispatchExpenses > 0)
                                        <span class="badge bg-danger">{{ number_format($dispatchExpenses, 0) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary">{{ number_format($dispatch->expected_cash_after_deductions, 0) }}</strong>
                                </td>
                                <td class="text-end">
                                    <strong class="text-{{ $dispatch->balance_due > 0 ? 'danger' : 'success' }}">
                                        {{ number_format($dispatch->balance_due, 0) }}
                                    </strong>
                                </td>
                                <td>
                                    <a href="{{ route('admin.dispatches.edit', $dispatch->id) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit Dispatch">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Expenses Breakdown (Collapsible) -->
                            @if($dispatch->expenses->count() > 0)
                            <tr class="bg-light">
                                <td colspan="9" class="p-0">
                                    <div class="accordion accordion-flush" id="expensesAccordion{{ $dispatch->id }}">
                                        <div class="accordion-item border-0">
                                            <div class="accordion-header">
                                                <button class="accordion-button collapsed py-2 px-3 bg-light" 
                                                        type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#expensesCollapse{{ $dispatch->id }}">
                                                    <small>
                                                        <i class="bi bi-receipt me-2"></i>
                                                        View {{ $dispatch->expenses->count() }} Expense(s) - Total: {{ number_format($dispatchExpenses, 0) }} UGX
                                                    </small>
                                                </button>
                                            </div>
                                            <div id="expensesCollapse{{ $dispatch->id }}" 
                                                 class="accordion-collapse collapse" 
                                                 data-bs-parent="#expensesAccordion{{ $dispatch->id }}">
                                                <div class="accordion-body p-3">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th>Type</th>
                                                                <th>Description</th>
                                                                <th class="text-end">Amount</th>
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
                                                                <td>{{ $expense->description ?? '-' }}</td>
                                                                <td class="text-end">{{ number_format($expense->amount, 0) }}</td>
                                                                <td>
                                                                    @if($expense->receipt_image)
                                                                        <a href="{{ asset('storage/' . $expense->receipt_image) }}" 
                                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                                            <i class="bi bi-image"></i> View
                                                                        </a>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Deposits Section -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-bank me-2"></i> Bank Deposits ({{ $deposits->count() }})</h5>
                <span class="badge bg-light text-dark">Total: {{ number_format($totals['total_deposits'], 0) }} UGX</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                                <th>Receipt</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deposits as $deposit)
                            <tr>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($deposit->deposit_date)->format('d M') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($deposit->deposit_date)->format('Y') }}</small>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">{{ number_format($deposit->amount, 0) }}</strong>
                                </td>
                                <td>
                                    @if($deposit->receipt)
                                        <a href="{{ asset('storage/' . $deposit->receipt) }}" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-image"></i> View
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $deposit->recorder->name ?? 'System' }}</small>
                                </td>
                            </tr>
                            @endforeach
                            @if($deposits->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                    No bank deposits found
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Financial Calculation Card -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-calculator me-2"></i> Financial Reconciliation</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Total Cash Collected:</strong></td>
                        <td class="text-end">{{ number_format($totals['total_cash_received'], 0) }} UGX</td>
                    </tr>
                    <tr>
                        <td><strong>Less: Total Commission:</strong></td>
                        <td class="text-end">- {{ number_format($totals['total_commission'], 0) }} UGX</td>
                    </tr>
                    <tr>
                        <td><strong>Less: Total Expenses:</strong></td>
                        <td class="text-end">- {{ number_format($totals['total_expenses'], 0) }} UGX</td>
                    </tr>
                    <tr class="table-primary fw-bold">
                        <td><strong>Expected to Bank:</strong></td>
                        <td class="text-end">{{ number_format($totals['total_expected_after_deductions'], 0) }} UGX</td>
                    </tr>
                    <tr>
                        <td><strong>Actually Banked:</strong></td>
                        <td class="text-end">- {{ number_format($totals['total_deposits'], 0) }} UGX</td>
                    </tr>
                    <tr class="table-{{ $totals['calculated_shortage'] >= 0 ? 'danger' : 'success' }} fw-bold">
                        <td><strong>Shortage/Excess:</strong></td>
                        <td class="text-end">{{ number_format($totals['calculated_shortage'], 0) }} UGX</td>
                    </tr>
                </table>

                @if($totals['calculated_shortage'] > 0)
                    <div class="alert alert-danger mt-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Action Required:</strong> Driver needs to pay {{ number_format($totals['calculated_shortage'], 0) }} UGX
                    </div>
                @elseif($totals['calculated_shortage'] < 0)
                    <div class="alert alert-success mt-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Good Standing:</strong> Driver has overpaid by {{ number_format(abs($totals['calculated_shortage']), 0) }} UGX
                    </div>
                @else
                    <div class="alert alert-success mt-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Perfect Settlement:</strong> All accounts are settled
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Print/Export Section -->
<div class="card mt-4">
    <div class="card-body text-center">
        <button onclick="window.print()" class="btn btn-outline-primary me-2">
            <i class="bi bi-printer me-2"></i> Print Report
        </button>
        <a href="javascript:void(0)" class="btn btn-outline-success" onclick="exportToExcel()">
            <i class="bi bi-file-earmark-excel me-2"></i> Export to Excel
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportToExcel() {
    // Simple Excel export - you can enhance this with a proper library
    const htmlContent = document.documentElement.outerHTML;
    const blob = new Blob([htmlContent], { type: 'application/vnd.ms-excel' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'Financial_Details_{{ $driver->name }}_{{ $dateFrom }}_to_{{ $dateTo }}.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>

<style>
@media print {
    .btn, .accordion-button, .card-header .badge {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
    }
    .card-header {
        background: #f8f9fa !important;
        color: #000 !important;
    }
}
</style>
@endpush