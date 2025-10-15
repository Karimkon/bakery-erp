@extends('admin.layouts.app')

@section('title','Admin Expense Dashboard')

@push('styles')
<style>
    .stat-card { border-left: 4px solid; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .expense-chart { height: 300px; }
    .alert-badge { animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3><i class="bi bi-graph-up-arrow me-2 text-primary"></i> Expense Dashboard</h3>
        <p class="text-muted mb-0">Real-time expense tracking and insights</p>
    </div>
    <div class="btn-group">
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
                 <a href="{{ route('admin.expenses.dashboard', ['date_from' => \Carbon\Carbon::today()->toDateString(), 'date_to' => \Carbon\Carbon::today()->toDateString()]) }}" 
                    class="btn btn-outline-secondary w-100">
                        <i class="bi bi-calendar-day"></i> Today
                    </a>
                    <br>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
                <br>
               
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
            </p>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-primary">
            <div class="card-body">
                <p class="text-muted mb-1">General Expenses</p>
                <h3 class="mb-0">{{ number_format($totalGeneral,0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-info">
            <div class="card-body">
                <p class="text-muted mb-1">Driver Expenses</p>
                <h3 class="mb-0">{{ number_format($totalDriver,0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-success">
            <div class="card-body">
                <p class="text-muted mb-1">Total Expenses</p>
                <h3 class="mb-0">{{ number_format($combinedTotal,0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-warning">
            <div class="card-body">
                <p class="text-muted mb-1">Total Dispatches</p>
                <h3 class="mb-0">{{ $totalDispatches }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
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

<!-- Tables -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">General Expenses</div>
            <div class="card-body table-responsive" style="max-height:400px;">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th>Recorded By</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($generalExpenses as $exp)
                        <tr>
                            <td>{{ $exp->expense_date }}</td>
                            <td>{{ $exp->category }}</td>
                            <td>{{ $exp->description }}</td>
                            <td class="text-end">{{ number_format($exp->amount) }}</td>
                            <td>{{ $exp->recorder->name }}</td>
                            <td>
                                @if($exp->receipt)
                                    @if(Str::endsWith($exp->receipt, ['pdf']))
                                        <a href="{{ asset('storage/'.$exp->receipt) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i></a>
                                    @else
                                        <a href="{{ asset('storage/'.$exp->receipt) }}" target="_blank"><img src="{{ asset('storage/'.$exp->receipt) }}" width="40"></a>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">No general expenses found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Driver Expenses</div>
            <div class="card-body table-responsive" style="max-height:400px;">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Driver</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($driverExpenses as $exp)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($exp->created_at)->format('d M Y') }}</td>
                            <td>{{ $exp->driver?->name }}</td>
                            <td>{{ $exp->expense_type }}</td>
                            <td>{{ $exp->description }}</td>
                            <td class="text-end">{{ number_format($exp->amount) }}</td>
                            <td>
                                @if($exp->receipt_image)
                                    @if(Str::endsWith($exp->receipt_image, ['pdf']))
                                        <a href="{{ asset('storage/'.$exp->receipt_image) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i></a>
                                    @else
                                        <a href="{{ asset('storage/'.$exp->receipt_image) }}" target="_blank"><img src="{{ asset('storage/'.$exp->receipt_image) }}" width="40"></a>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">No driver expenses found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
            @foreach($expensesByType as $type => $data) '{{ $type }}', @endforeach
        ],
        datasets: [{
            data: [@foreach($expensesByType as $data) {{ $data['total'] }}, @endforeach],
            backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF']
        }]
    },
    options: { responsive:true, maintainAspectRatio:false }
});

// Daily Trend Line Chart
const dailyTrendCtx = document.getElementById('dailyTrendChart').getContext('2d');
new Chart(dailyTrendCtx, {
    type: 'line',
    data: {
        labels: [@foreach($dailyTrend as $day => $amt) '{{ $day }}', @endforeach],
        datasets: [{
            label: 'Total Expenses (UGX)',
            data: [@foreach($dailyTrend as $amt) {{ $amt }}, @endforeach],
            fill: true,
            borderColor: '#36A2EB',
            backgroundColor: 'rgba(54,162,235,0.2)',
            tension: 0.3
        }]
    },
    options: { responsive:true, maintainAspectRatio:false }
});
</script>
@endpush
