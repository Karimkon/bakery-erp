@extends('admin.layouts.app')
@section('title', 'Expense Analytics Dashboard')

@push('styles')
<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .expense-chart {
        height: 300px;
    }
    .alert-badge {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3><i class="bi bi-graph-up-arrow me-2 text-primary"></i> Expense Analytics Dashboard</h3>
        <p class="text-muted mb-0">Real-time expense tracking and insights</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.expenses.daily') }}" class="btn btn-outline-primary">
            <i class="bi bi-calendar-day"></i> Daily Report
        </a>
        <a href="{{ route('admin.expenses.driver-analysis') }}" class="btn btn-outline-info">
            <i class="bi bi-people"></i> Driver Analysis
        </a>
        <a href="{{ route('admin.expenses.export', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'driver_id' => $selectedDriver]) }}" 
           class="btn btn-outline-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Driver (Optional)</label>
                <select name="driver_id" class="form-select">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ $selectedDriver == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Alerts -->
@if($highExpenses->count() > 0 || $expensesWithoutReceipts->count() > 0)
<div class="row mb-4">
    @if($highExpenses->count() > 0)
    <div class="col-md-6">
        <div class="alert alert-warning">
            <h6 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                High Value Expenses Detected
            </h6>
            <p class="mb-0">
                <strong>{{ $highExpenses->count() }}</strong> expense(s) over 100,000 UGX require review.
                <a href="#high-expenses" class="alert-link">View details</a>
            </p>
        </div>
    </div>
    @endif

    @if($expensesWithoutReceipts->count() > 0)
    <div class="col-md-6">
        <div class="alert alert-danger">
            <h6 class="alert-heading">
                <i class="bi bi-receipt me-2"></i>
                Missing Receipts
            </h6>
            <p class="mb-0">
                <strong>{{ $expensesWithoutReceipts->count() }}</strong> expense(s) over 20,000 UGX without receipts.
                <a href="#missing-receipts" class="alert-link">View details</a>
            </p>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Expenses</p>
                        <h3 class="mb-0">{{ number_format($totalExpenses, 0) }}</h3>
                        <small class="text-primary">UGX</small>
                    </div>
                    <div class="fs-1 text-primary opacity-25">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Dispatches</p>
                        <h3 class="mb-0">{{ $totalDispatches }}</h3>
                        <small class="text-info">with expenses</small>
                    </div>
                    <div class="fs-1 text-info opacity-25">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Avg Per Dispatch</p>
                        <h3 class="mb-0">{{ number_format($averagePerDispatch, 0) }}</h3>
                        <small class="text-success">UGX</small>
                    </div>
                    <div class="fs-1 text-success opacity-25">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Expense Items</p>
                        <h3 class="mb-0">{{ $expenses->count() }}</h3>
                        <small class="text-warning">total records</small>
                    </div>
                    <div class="fs-1 text-warning opacity-25">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Expense by Type -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i> Expenses by Type</h6>
            </div>
            <div class="card-body">
                <canvas id="expenseTypeChart" class="expense-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Daily Trend -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i> Daily Expense Trend</h6>
            </div>
            <div class="card-body">
                <canvas id="dailyTrendChart" class="expense-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Expense Types Table -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="bi bi-trophy me-2"></i> Top 5 Expense Categories</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Total (UGX)</th>
                            <th>Count</th>
                            <th>Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topExpenseTypes as $type => $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ \App\Models\DriverExpense::expenseTypes()[$type] ?? $type }}
                                    </span>
                                </td>
                                <td><strong>{{ number_format($data['total'], 0) }}</strong></td>
                                <td>{{ $data['count'] }}</td>
                                <td>{{ number_format($data['average'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Driver Comparison -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-people me-2"></i> Driver Expense Comparison</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Driver</th>
                            <th>Total (UGX)</th>
                            <th>Transactions</th>
                            <th>Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($driverExpenses->take(5) as $driverData)
                            <tr>
                                <td>{{ $driverData['driver'] }}</td>
                                <td><strong>{{ number_format($driverData['total'], 0) }}</strong></td>
                                <td>{{ $driverData['count'] }}</td>
                                <td>{{ number_format($driverData['average'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Expenses -->
<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i> Recent Expenses</h6>
        <span class="badge bg-light text-dark">Last 20</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Date & Time</th>
                        <th>Driver</th>
                        <th>Dispatch #</th>
                        <th>Type</th>
                        <th>Amount (UGX)</th>
                        <th>Description</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentExpenses as $expense)
                        <tr>
                            <td>
                                <strong>{{ $expense->created_at->format('M d, Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ $expense->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <i class="bi bi-person-circle me-1"></i>
                                {{ $expense->driver->name }}
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    #{{ $expense->dispatch->dispatch_no }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ \App\Models\DriverExpense::expenseTypes()[$expense->expense_type] ?? $expense->expense_type }}
                                </span>
                            </td>
                            <td>
                                <strong class="{{ $expense->amount > 50000 ? 'text-danger' : '' }}">
                                    {{ number_format($expense->amount, 0) }}
                                </strong>
                                @if($expense->amount > 100000)
                                    <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="High value"></i>
                                @endif
                            </td>
                            <td>
                                <small>{{ $expense->description ?? '-' }}</small>
                            </td>
                            <td>
                                @if($expense->receipt_image)
                                    <a href="{{ asset('storage/' . $expense->receipt_image) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-image"></i>
                                    </a>
                                @else
                                    <span class="text-muted">
                                        <i class="bi bi-x-circle"></i>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Expense Type Pie Chart
const expenseTypeCtx = document.getElementById('expenseTypeChart').getContext('2d');
new Chart(expenseTypeCtx, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($expensesByType as $type => $data)
                '{{ \App\Models\DriverExpense::expenseTypes()[$type] ?? $type }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($expensesByType as $data)
                    {{ $data['total'] }},
                @endforeach
            ],
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': UGX ' + context.parsed.toLocaleString();
                    }
                }
            }
        }
    }
});

// Daily Trend Line Chart
const dailyTrendCtx = document.getElementById('dailyTrendChart').getContext('2d');
new Chart(dailyTrendCtx, {
    type: 'line',
    data: {
        labels: [
            @foreach($dailyTrend as $date => $total)
                '{{ Carbon\Carbon::parse($date)->format('M d') }}',
            @endforeach
        ],
        datasets: [{
            label: 'Daily Expenses (UGX)',
            data: [
                @foreach($dailyTrend as $total)
                    {{ $total }},
                @endforeach
            ],
            borderColor: '#36A2EB',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'UGX ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'UGX ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endpush