@extends('admin.layouts.app')
@section('title', 'Product Profit Report')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-graph-up-arrow me-2 text-success"></i> 
                Product Profit Analysis
            </h3>
            <p class="text-muted small mb-0">Item-wise sales performance and profitability metrics</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Period Filter -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted small me-2">Time Period:</span>
                <a href="?period=today" class="btn btn-sm {{ $period == 'today' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-calendar-day"></i> Today
                </a>
                <a href="?period=week" class="btn btn-sm {{ $period == 'week' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-calendar-week"></i> This Week
                </a>
                <a href="?period=month" class="btn btn-sm {{ $period == 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-calendar-month"></i> This Month
                </a>
                <a href="?period=quarter" class="btn btn-sm {{ $period == 'quarter' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-calendar3"></i> This Quarter
                </a>
                <a href="?period=year" class="btn btn-sm {{ $period == 'year' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-calendar4"></i> This Year
                </a>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block mb-1">Total Revenue</small>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($summary['total_revenue'], 0) }}</h4>
                            <small class="text-muted">UGX</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-cash-stack text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block mb-1">Total Profit</small>
                            <h4 class="mb-0 fw-bold text-primary">{{ number_format($summary['total_profit'], 0) }}</h4>
                            <small class="text-muted">UGX</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-graph-up text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block mb-1">Avg Profit Margin</small>
                            <h4 class="mb-0 fw-bold text-warning">{{ number_format($summary['overall_margin'], 1) }}%</h4>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-percent text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block mb-1">Period</small>
                            <h5 class="mb-0 fw-bold text-info">{{ ucfirst($summary['period']) }}</h5>
                            <small class="text-muted">{{ $summary['start_date'] }} - {{ $summary['end_date'] }}</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar-range text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(count($reportData) > 0)
    <!-- Top Performers Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-trophy-fill me-2"></i> Top 5 Best Sellers
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach(array_slice($reportData, 0, 5) as $index => $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success me-2">{{ $index + 1 }}</span>
                                <strong>{{ $item['product_name'] }}</strong>
                            </div>
                            <span class="badge bg-success-subtle text-success">
                                {{ number_format($item['total_sold']) }} units
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-cash-coin me-2"></i> Highest Revenue
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach(array_slice($reportData, 0, 5) as $index => $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                <strong>{{ $item['product_name'] }}</strong>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">
                                {{ number_format($item['gross_revenue'], 0) }} UGX
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-percent me-2"></i> Best Profit Margins
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @php
                            $sortedByMargin = collect($reportData)->sortByDesc('profit_margin')->take(5)->values();
                        @endphp
                        @foreach($sortedByMargin as $index => $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-warning me-2">{{ $index + 1 }}</span>
                                <strong>{{ $item['product_name'] }}</strong>
                            </div>
                            <span class="badge bg-warning-subtle text-warning">
                                {{ number_format($item['profit_margin'], 1) }}%
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Product Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i> Detailed Product Analysis</h5>
            <small class="text-muted">Analyzed {{ $summary['products_analyzed'] }} products</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Rank</th>
                            <th>Product</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Produced</th>
                            <th class="text-end">Sold</th>
                            <th class="text-end">Stock</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Profit</th>
                            <th class="text-center">Margin</th>
                            <th class="text-center">Sell-Through</th>
                            <th class="text-end">Velocity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData as $index => $item)
                        <tr>
                            <td>
                                <span class="badge {{ $index < 3 ? 'bg-warning' : 'bg-secondary' }}">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $item['product_name'] }}</strong>
                                <div class="small text-muted">
                                    <i class="bi bi-cash"></i> {{ number_format($item['cash_sales']) }} cash, 
                                    <i class="bi bi-credit-card"></i> {{ number_format($item['credit_sales']) }} credit
                                </div>
                            </td>
                            <td class="text-end">{{ number_format($item['price']) }}</td>
                            <td class="text-end">{{ number_format($item['total_produced']) }}</td>
                            <td class="text-end">
                                <span class="badge bg-success-subtle text-success">
                                    {{ number_format($item['total_sold']) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="badge {{ $item['current_stock'] < 10 ? 'bg-danger' : 'bg-info' }}">
                                    {{ number_format($item['current_stock']) }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                {{ number_format($item['gross_revenue'], 0) }}
                            </td>
                            <td class="text-end fw-bold {{ $item['profit'] > 0 ? 'text-primary' : 'text-danger' }}">
                                {{ number_format($item['profit'], 0) }}
                            </td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $item['profit_margin'] > 40 ? 'bg-success' : ($item['profit_margin'] > 30 ? 'bg-warning' : 'bg-danger') }}" 
                                         style="width: {{ min($item['profit_margin'], 100) }}%">
                                        {{ number_format($item['profit_margin'], 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" 
                                         style="width: {{ min($item['sell_through_rate'], 100) }}%">
                                        {{ number_format($item['sell_through_rate'], 0) }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <small class="text-muted">
                                    {{ number_format($item['sales_velocity'], 1) }}/day
                                </small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">TOTALS:</td>
                            <td class="text-end text-success">
                                {{ number_format($summary['total_revenue'], 0) }}
                            </td>
                            <td class="text-end text-primary">
                                {{ number_format($summary['total_profit'], 0) }}
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Performance Insights -->
    <div class="row g-3 mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle me-2"></i> Underperforming Products
                </div>
                <div class="card-body">
                    @php
                        $underperformers = collect($reportData)->filter(fn($i) => $i['sell_through_rate'] < 50 && $i['total_produced'] > 0)->take(5);
                    @endphp
                    @if($underperformers->count() > 0)
                        <ul class="list-unstyled mb-0">
                            @foreach($underperformers as $item)
                            <li class="mb-2">
                                <strong>{{ $item['product_name'] }}</strong>
                                <div class="progress" style="height: 15px;">
                                    <div class="progress-bar bg-danger" 
                                         style="width: {{ $item['sell_through_rate'] }}%">
                                        {{ number_format($item['sell_through_rate'], 0) }}% sold
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">All products performing well! 🎉</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-lightbulb me-2"></i> Stock Alerts
                </div>
                <div class="card-body">
                    @php
                        $lowStock = collect($reportData)->filter(fn($i) => $i['current_stock'] < 10 && $i['sales_velocity'] > 1);
                    @endphp
                    @if($lowStock->count() > 0)
                        <ul class="list-unstyled mb-0">
                            @foreach($lowStock as $item)
                            <li class="mb-2">
                                <i class="bi bi-exclamation-circle text-warning"></i>
                                <strong>{{ $item['product_name'] }}</strong>
                                <span class="badge bg-danger">{{ $item['current_stock'] }} left</span>
                                <small class="text-muted">
                                    (Sells {{ number_format($item['sales_velocity'], 1) }}/day)
                                </small>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">All products well-stocked! ✅</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- No Data Message -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
            <h4 class="mt-3">No Data Available</h4>
            <p class="text-muted">No product profit data found for the selected period.</p>
            <p class="text-muted small">Please check if you have production, sales, and dispatch data for this period.</p>
        </div>
    </div>
    @endif
</div>

<style>
@media print {
    .btn, .card-header button { display: none !important; }
}

.progress {
    background-color: rgba(0,0,0,0.1);
}

.table tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

.badge.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
    color: #198754 !important;
}

.badge.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1) !important;
    color: #0d6efd !important;
}

.badge.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
    color: #ffc107 !important;
}
</style>
@endsection