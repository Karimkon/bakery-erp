@extends('admin.layouts.app')
@section('title','Admin Dashboard')

@section('content')
<div class="container-fluid py-4">

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-lg-8 col-md-7">
            <h3 class="fw-bold mb-2">
                <i class="bi bi-speedometer2 me-2 text-primary"></i>Bakery Admin Dashboard
            </h3>
            <p class="text-muted mb-0">Overview — {{ $title ?? 'Today' }}</p>
        </div>
        <div class="col-lg-4 col-md-5 mt-3 mt-md-0">
            <form method="GET" class="d-flex align-items-center gap-2">
                <label class="text-muted small mb-0 text-nowrap">Time Range:</label>
                <select name="filter" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()">
                    <option value="today" {{ ($filter ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ ($filter ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ ($filter ?? '') == 'month' ? 'selected' : '' }}>This Month</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="row g-3 mb-4">
        <!-- Total Users -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Total Users</p>
                            <h4 class="fw-bold mb-0">{{ number_format($totalUsers) }}</h4>
                        </div>
                        <div class="icon-wrapper bg-info-subtle">
                            <i class="bi bi-people text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Productions -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Total Productions</p>
                            <h4 class="fw-bold mb-0">{{ number_format($totalProductions) }}</h4>
                        </div>
                        <div class="icon-wrapper bg-primary-subtle">
                            <i class="bi bi-journal-text text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flour Used -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Flour Used ({{ $title }})</p>
                            <h4 class="fw-bold mb-0">{{ number_format($flourUsed, 2) }} <small class="text-muted fs-6">kg</small></h4>
                        </div>
                        <div class="icon-wrapper bg-warning-subtle">
                            <i class="bi bi-bag-fill text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Dispatches -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Total Dispatches</p>
                            <h4 class="fw-bold mb-0">{{ number_format($totalDispatches) }}</h4>
                        </div>
                        <div class="icon-wrapper bg-secondary-subtle">
                            <i class="bi bi-truck text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Overview -->
    <div class="row g-3 mb-4">
        <!-- Bakery Sales -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">{{ $title }} Bakery Sales</p>
                            <h4 class="fw-bold text-success mb-0">{{ number_format($bakerySales, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                            <p class="text-muted small mb-0 mt-1">Cash: {{ number_format($bakerySalesCash, 0) }} UGX</p>
                        </div>
                        <div class="icon-wrapper bg-success-subtle">
                            <i class="bi bi-shop text-success"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.sales.bakery') }}" class="btn btn-sm btn-outline-success w-100">
                        <i class="bi bi-eye me-1"></i>View Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Kampala Sales -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">{{ $title }} Kampala Sales</p>
                            <h4 class="fw-bold text-primary mb-0">{{ number_format($kampalaSales ?? 0, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                            <p class="text-muted small mb-0 mt-1">Banked: {{ number_format($kampalaBanked ?? 0, 0) }} UGX</p>
                        </div>
                        <div class="icon-wrapper bg-primary-subtle">
                            <i class="bi bi-shop-window text-primary"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.sales.kampala') }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-eye me-1"></i>View Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Damaged Sales -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">{{ $title }} Damaged Sales</p>
                            <h4 class="fw-bold text-warning mb-0">{{ number_format($damageRevenue, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                        </div>
                        <div class="icon-wrapper bg-warning-subtle">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Deposits -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">{{ $title }} Bank Deposits</p>
                            <h4 class="fw-bold text-success mb-0">{{ number_format($bankedTotal ?? 0, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                        </div>
                        <div class="icon-wrapper bg-success-subtle">
                            <i class="bi bi-bank2 text-success"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.deposits.index') }}" class="btn btn-sm btn-outline-success w-100">
                        <i class="bi bi-graph-up me-1"></i>View
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Metrics -->
    <div class="row g-3 mb-4">
        <!-- Gross Profit -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">{{ $title }} Gross Profit</p>
                            <h4 class="fw-bold text-primary mb-0">{{ number_format($grossProfit, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                        </div>
                        <div class="icon-wrapper bg-primary-subtle">
                            <i class="bi bi-cash-coin text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">{{ $title }} Net Profit</p>
                            <h4 class="fw-bold text-success mb-0">{{ number_format($netProfit, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                        </div>
                        <div class="icon-wrapper bg-success-subtle">
                            <i class="bi bi-graph-up-arrow text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Breakfast Cost -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">{{ $title }} Staff Breakfast Cost</p>
                            <h4 class="fw-bold text-danger mb-0">{{ number_format($totalBreakfastCost, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                        </div>
                        <div class="icon-wrapper bg-danger-subtle">
                            <i class="bi bi-egg-fried text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">{{ $title }} Expenses</p>
                            <h4 class="fw-bold text-danger mb-0">{{ number_format($expensesTotal, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                        </div>
                        <div class="icon-wrapper bg-danger-subtle">
                            <i class="bi bi-wallet2 text-danger"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.expenses.dashboard') }}" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-graph-up me-1"></i>View
                    </a>
                </div>
            </div>
        </div>

        <!-- Money at Bakery -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">{{ $title }} Money at Bakery</p>
                            <h4 class="fw-bold text-success mb-0">{{ number_format($bakeryCashLeft, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                        </div>
                        <div class="icon-wrapper bg-success-subtle">
                            <i class="bi bi-cash-stack text-success"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.reports.daily-cash') }}" class="btn btn-sm btn-outline-success w-100">
                        <i class="bi bi-eye me-1"></i>View Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Production & Dispatch Value -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Production Value ({{ $title }})</p>
                    <h4 class="fw-bold mb-0">{{ number_format($productionValue, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Dispatch Value ({{ $title }})</p>
                    <h4 class="fw-bold mb-0">{{ number_format($dispatchValue, 0) }} <small class="text-muted fs-6">UGX</small></h4>
                    <p class="text-muted small mb-0 mt-1">Dispatched items: <strong>{{ number_format($dispatchItemsCount) }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart & Recent Activity -->
    <div class="row g-3 mb-4">
        <!-- Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Last 7 Days — Production vs Dispatch</h6>
                </div>
                <div class="card-body">
                    <canvas id="prodDispatchChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4">
            <!-- Recent Dispatches -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Recent Dispatches</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:280px; overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">#</th>
                                    <th class="border-0">Driver</th>
                                    <th class="text-end border-0">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDispatches as $d)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($d->dispatch_date)->format('d M') }}</td>
                                    <td>{{ $d->dispatch_no }}</td>
                                    <td>{{ $d->driver?->name }}</td>
                                    <td class="text-end">{{ number_format($d->total_sales_value, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Productions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Recent Productions</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:220px; overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Chef</th>
                                    <th class="text-end border-0">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProductions as $p)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($p->production_date)->format('d M') }}</td>
                                    <td>{{ $p->user?->name ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($p->total_value, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock & Metrics -->
    <div class="row g-3">
        <!-- Stock Snapshot -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Stock Snapshot</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Product</th>
                                    <th class="text-end border-0">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bakeryStocks as $s)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $s->product)) }}</td>
                                    <td class="text-end">{{ number_format($s->quantity) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Short Metrics -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Quick Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Today Productions</small>
                        <h5 class="mb-0 fw-bold">{{ number_format($todayProductions) }}</h5>
                    </div>
                    <div>
                        <small class="text-muted d-block mb-1">Dispatch Items (Range)</small>
                        <h5 class="mb-0 fw-bold">{{ number_format($dispatchItemsCount) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Stock Alert Modal -->
<div class="modal fade" id="stockAlertModal" tabindex="-1" aria-labelledby="stockAlertLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="stockAlertLabel">
                    <i class="bi bi-box-seam me-2"></i>Recent Stock Additions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($recentStockAdditions->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No recent stock additions.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ingredient</th>
                                <th>Quantity</th>
                                <th>Type</th>
                                <th>Chef</th>
                                <th>Added By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentStockAdditions as $stock)
                            <tr>
                                <td>{{ $stock->ingredient->name ?? '-' }}</td>
                                <td>{{ number_format($stock->quantity_changed, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $stock->transaction_type == 'addition' ? 'success' : 'warning' }}">
                                        {{ ucfirst($stock->transaction_type) }}
                                    </span>
                                </td>
                                <td>{{ $stock->chef->name ?? '-' }}</td>
                                <td>{{ $stock->addedBy->name ?? '-' }}</td>
                                <td>{{ $stock->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.ingredients.stock_history') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-clock-history me-1"></i>View Full History
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    @if(!empty($showStockModal) && $showStockModal)
        var stockModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
        stockModal.show();
    @endif

    const labels = {!! json_encode($labels) !!};
    const prodData = {!! json_encode($prodSeries) !!};
    const dispData = {!! json_encode($dispSeries) !!};

    const ctx = document.getElementById('prodDispatchChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Production (UGX)',
                    data: prodData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2
                },
                {
                    label: 'Dispatch (UGX)',
                    data: dispData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },
                y: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: { size: 11 },
                        callback: function(v) {
                            if (v >= 1000000) return (v/1000000).toFixed(1) + 'M';
                            if (v >= 1000) return (v/1000).toFixed(1) + 'k';
                            return v;
                        }
                    }
                }
            }
        }
    });
});
</script>

<style>
/* Card Hover Effects */
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Icon Wrapper Styling */
.icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

/* Bootstrap subtle color utilities */
.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1);
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1);
}

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1);
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1);
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1);
}

.bg-secondary-subtle {
    background-color: rgba(108, 117, 125, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .icon-wrapper {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}

/* Table hover effect */
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Sticky table header enhancement */
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Card header styling */
.card-header {
    padding: 1rem 1.25rem;
}

/* Button hover improvements */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}
</style>
@endsection