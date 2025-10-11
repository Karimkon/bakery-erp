@extends('manager.layouts.app')
@section('title', 'Manager Dashboard')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-3 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 me-2"></i> Manager Dashboard</h3>
            <div class="text-muted small">Overview — {{ $title ?? 'Today' }}</div>
        </div>

        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="me-1 small text-muted mb-0">Range</label>
            <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="today" {{ ($filter ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ ($filter ?? '') == 'week' ? 'selected' : '' }}>This week</option>
                <option value="month" {{ ($filter ?? '') == 'month' ? 'selected' : '' }}>This month</option>
            </select>
        </form>
    </div>

    <!-- Top Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Total Productions</small>
                        <h4 class="mb-0">{{ number_format($totalProductions) }}</h4>
                    </div>
                    <div class="icon-circle bg-primary text-white"><i class="bi bi-journal-text"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Total Dispatches</small>
                        <h4 class="mb-0">{{ number_format($totalDispatches) }}</h4>
                    </div>
                    <div class="icon-circle bg-secondary text-white"><i class="bi bi-truck"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Flour Used ({{ $title }})</small>
                        <h4 class="mb-0">{{ number_format($flourKgUsed, 2) }} kg</h4>
                    </div>
                    <div class="icon-circle bg-warning text-white"><i class="bi bi-bag-fill"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Combined Value</small>
                        <h4 class="mb-0 text-success">{{ number_format($combinedValue, 0) }}</h4>
                    </div>
                    <div class="icon-circle bg-success text-white"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="card p-3 shadow-sm mb-4">
        <h6 class="mb-3">Last 7 Days — Production vs Dispatch</h6>
        <canvas id="prodDispatchChart" height="120"></canvas>
    </div>

    <!-- Recent Activities -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card p-3 shadow-sm">
                <h6 class="mb-2">Recent Productions</h6>
                <div class="table-responsive" style="max-height:220px; overflow:auto;">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th class="text-end">Value</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentProductions as $p)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($p->production_date)->format('d M') }}</td>
                                    <td class="text-end">{{ number_format($p->total_value, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-3 shadow-sm">
                <h6 class="mb-2">Recent Dispatches</h6>
                <div class="table-responsive" style="max-height:220px; overflow:auto;">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>#</th><th>Driver</th><th class="text-end">Value</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentDispatches as $d)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($d->dispatch_date)->format('d M') }}</td>
                                    <td>{{ $d->dispatch_no }}</td>
                                    <td>{{ $d->driver?->name ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($d->total_sales_value, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bakery Stock -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light fw-bold"><i class="bi bi-basket"></i> Bakery Stock Snapshot</div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Product</th><th class="text-end">Qty</th></tr></thead>
                <tbody>
                    @foreach($bakeryStocks as $s)
                        <tr>
                            <td>{{ ucfirst(str_replace('_',' ',$s->product)) }}</td>
                            <td class="text-end">{{ number_format($s->quantity) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
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
                    label: 'Production',
                    data: prodData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.12)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Dispatch',
                    data: dispData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255,193,7,0.12)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                x: { grid: { display: false } },
                y: {
                    ticks: {
                        callback: function(v) {
                            if (v >= 1000000) return (v/1000000) + 'M';
                            if (v >= 1000) return (v/1000) + 'k';
                            return v;
                        }
                    }
                }
            }
        }
    });
</script>

<style>
.icon-circle { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
@media (max-width: 768px) { .icon-circle { width:40px; height:40px; } }
</style>
@endsection
