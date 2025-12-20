@extends('admin.layouts.app')
@section('title', $shop->name . ' Activities')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-shop me-2"></i>{{ $shop->name }} Activities
        <small class="text-muted fs-6">• {{ $shop->location }}</small>
    </h4>
    <a href="{{ route('admin.kampala.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to All Shops
    </a>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from" value="{{ $fromDate }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to" value="{{ $toDate }}" class="form-control">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a href="?from={{ $fromDate }}&to={{ $toDate }}&export=excel" class="btn btn-success w-100">
                    <i class="bi bi-download me-1"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Shop Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Total Sales</div>
                <div class="stat fs-2 text-success">UGX {{ number_format($stats['total_sales']) }}</div>
                <small class="text-muted">Selected period</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Total Expenses</div>
                <div class="stat fs-2 text-danger">UGX {{ number_format($stats['total_expenses']) }}</div>
                <small class="text-muted">Shop expenses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Total Banked</div>
                <div class="stat fs-2 text-info">UGX {{ number_format($stats['total_banked']) }}</div>
                <small class="text-muted">Bank deposits</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Dispatches Received</div>
                <div class="stat fs-2 text-warning">{{ $stats['dispatches_received'] }}</div>
                <small class="text-muted">From bakery</small>
            </div>
        </div>
    </div>
</div>

<!-- Sales by Product -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Sales by Product</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-end">Quantity Sold</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Average Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesByProduct as $product)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $product->product_type)) }}</td>
                        <td class="text-end">{{ number_format($product->total_quantity) }}</td>
                        <td class="text-end text-success">UGX {{ number_format($product->total_amount) }}</td>
                        <td class="text-end">
                            @php
                                $avg = $product->total_quantity > 0 ? $product->total_amount / $product->total_quantity : 0;
                            @endphp
                            UGX {{ number_format($avg, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Expenses by Category -->
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">Expenses by Category</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-end">Amount</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalExpenses = $expensesByCategory->sum('total_amount');
                    @endphp
                    @foreach($expensesByCategory as $expense)
                    <tr>
                        <td>{{ \App\Models\KampalaExpense::expenseCategories()[$expense->category] ?? $expense->category }}</td>
                        <td class="text-end text-danger">UGX {{ number_format($expense->total_amount) }}</td>
                        <td>
                            @php
                                $percentage = $totalExpenses > 0 ? ($expense->total_amount / $totalExpenses) * 100 : 0;
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: {{ $percentage }}%;" 
                                     aria-valuenow="{{ $percentage }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    {{ number_format($percentage, 1) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Activities Timeline -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Recent Activities Timeline</h5>
    </div>
    <div class="card-body">
        <div class="timeline">
            @foreach($recentActivities as $activity)
            <div class="timeline-item border-start ps-4 pb-3">
                <div class="timeline-marker bg-{{ $activity['color'] }}"></div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between">
                        <div>
                            <i class="bi {{ $activity['icon'] }} me-2 text-{{ $activity['color'] }}"></i>
                            {{ $activity['description'] }}
                        </div>
                        <div class="text-{{ $activity['color'] }} fw-bold">
                            @if($activity['amount'] >= 0)
                                UGX {{ number_format($activity['amount']) }}
                            @else
                                -UGX {{ number_format(abs($activity['amount'])) }}
                            @endif
                        </div>
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-person me-1"></i>{{ $activity['user'] }}
                        • {{ $activity['date']->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}
.timeline-item {
    position: relative;
}
.timeline-marker {
    position: absolute;
    left: -8px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
}
</style>
@endsection