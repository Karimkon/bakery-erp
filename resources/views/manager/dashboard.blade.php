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
                <option value="yesterday" {{ ($filter ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
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

<!-- Enhanced Manager Target Section with Detailed Breakdown -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">🎯 Target Progress Breakdown - {{ $title }}</h5>
        <small>Date Range: {{ $verificationData['date_range'] }}</small>
    </div>
    
    <div class="card-body">
        <!-- Summary Section -->
        <div class="row mb-4">
           <div class="col-md-3">
    <div class="text-center p-3 bg-light rounded position-relative">
        <!-- Progress History Link -->
        <a href="{{ route('manager.progress-history') }}" 
           class="position-absolute top-0 end-0 mt-2 me-2 text-muted" 
           title="View Progress History"
           style="text-decoration: none;">
            <i class="bi bi-clock-history"></i>
        </a>
        
        <h6 class="text-muted mb-1">TARGET</h6>
        <h4 class="mb-0 text-primary">{{ number_format($target) }}</h4>
        <small class="text-muted">UGX</small>
        <div class="mt-2">
            <small>{{ number_format($dailyTarget) }} × {{ $daysCount }} day{{ $daysCount > 1 ? 's' : '' }}</small>
        </div>
        
        <!-- Small link below -->
        <div class="mt-2">
            <a href="{{ route('manager.progress-history') }}" 
               class="small text-primary text-decoration-none">
                <i class="bi bi-bar-chart-line me-1"></i> View History
            </a>
        </div>
    </div>
</div>
            <div class="col-md-3">
                <div class="text-center p-3 bg-success text-white rounded">
                    <h6 class="mb-1">ACHIEVED</h6>
                    <h4 class="mb-0">{{ number_format($totalProduced) }}</h4>
                    <small>UGX</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3 bg-info text-white rounded">
                    <h6 class="mb-1">PROGRESS</h6>
                    <h4 class="mb-0">{{ $progress }}%</h4>
                    <small>
                        @if($progress >= 100)
                            ✅ Target Met!
                        @else
                            🚀 In Progress
                        @endif
                    </small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-3 bg-warning text-dark rounded">
                    <h6 class="mb-1">REMAINING</h6>
                    <h4 class="mb-0">{{ number_format($remaining) }}</h4>
                    <small>UGX</small>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                     style="width: {{ $progressCapped }}%"
                     role="progressbar" 
                     aria-valuenow="{{ $progressCapped }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <strong>{{ $progress }}%</strong>
                </div>
            </div>
            <div class="text-center mt-2">
                @if($progress >= 100)
                    <span class="badge badge-success">✅ Excellent! Target achieved.</span>
                @elseif($progress >= 75)
                    <span class="badge badge-info">🎯 Almost there! {{ 100 - round($progress) }}% remaining</span>
                @elseif($progress >= 50)
                    <span class="badge badge-warning">⚡ Halfway there! Keep going!</span>
                @else
                    <span class="badge badge-secondary">🚀 {{ 100 - round($progress) }}% remaining to reach {{ strtolower($title) }}'s goal</span>
                @endif
            </div>
        </div>

        <!-- Detailed Breakdown -->
        <div class="row">
            <!-- Included Dispatches -->
            <div class="col-md-6 mb-3">
                <div class="border rounded p-3 h-100">
                    <h6 class="text-success mb-3">
                        ✅ INCLUDED DISPATCHES 
                        <span class="badge badge-success">{{ number_format($dispatchTotal) }} UGX</span>
                    </h6>
                    @if($includedDispatches->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Driver</th>
                                        <th class="text-right">Sales</th>
                                        <th class="text-center">Dispatches</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($includedDispatches as $dispatch)
                                        <tr>
                                            <td>
                                                <i class="fas fa-check-circle text-success"></i>
                                                {{ $dispatch->driver_name }}
                                            </td>
                                            <td class="text-right font-weight-bold">
                                                {{ number_format($dispatch->total_sales) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">{{ $dispatch->dispatch_count }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="font-weight-bold bg-light">
                                    <tr>
                                        <td>TOTAL</td>
                                        <td class="text-right">{{ number_format($dispatchTotal) }}</td>
                                        <td class="text-center">{{ $includedDispatches->sum('dispatch_count') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle"></i> No included dispatches for this period
                        </p>
                    @endif
                </div>
            </div>

            <!-- Bakery Sales -->
            <div class="col-md-6 mb-3">
                <div class="border rounded p-3 h-100">
                    <h6 class="text-success mb-3">
                        ✅ INCLUDED BAKERY SALES 
                        <span class="badge badge-success">{{ number_format($bakeryTotal) }} UGX</span>
                    </h6>
                    @if($bakeryBreakdown->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>User</th>
                                        <th class="text-right">Sales</th>
                                        <th class="text-center">Transactions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bakeryBreakdown as $sale)
                                        <tr>
                                            <td>
                                                <i class="fas fa-check-circle text-success"></i>
                                                {{ $sale->user_name }}
                                            </td>
                                            <td class="text-right font-weight-bold">
                                                {{ number_format($sale->total_sales) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">{{ $sale->sale_count }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="font-weight-bold bg-light">
                                    <tr>
                                        <td>TOTAL</td>
                                        <td class="text-right">{{ number_format($bakeryTotal) }}</td>
                                        <td class="text-center">{{ $bakeryBreakdown->sum('sale_count') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle"></i> No bakery sales for this period
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Excluded Section -->
        @if($excludedDispatches->count() > 0)
            <div class="border border-danger rounded p-3 mt-3">
                <h6 class="text-danger mb-3">
                    ❌ EXCLUDED FROM TARGET 
                    <span class="badge badge-danger">{{ number_format($excludedDispatchTotal) }} UGX</span>
                    <small class="text-muted">(Not counted in target calculation)</small>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Driver</th>
                                <th class="text-right">Sales</th>
                                <th class="text-center">Dispatches</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($excludedDispatches as $dispatch)
                                <tr class="table-danger">
                                    <td>
                                        <i class="fas fa-times-circle text-danger"></i>
                                        {{ $dispatch->driver_name }}
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($dispatch->total_sales) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $dispatch->dispatch_count }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            @if($dispatch->driver_id == 20)
                                                Nakato - Excluded by policy
                                            @elseif($dispatch->driver_id == 21)
                                                Ariah - Excluded by policy
                                            @else
                                                Excluded driver
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="font-weight-bold bg-light">
                            <tr>
                                <td>TOTAL EXCLUDED</td>
                                <td class="text-right">{{ number_format($excludedDispatchTotal) }}</td>
                                <td class="text-center">{{ $excludedDispatches->sum('dispatch_count') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        <!-- Calculation Formula -->
        <div class="alert alert-info mt-4 mb-0">
            <h6 class="alert-heading">
                <i class="fas fa-calculator"></i> CALCULATION BREAKDOWN
            </h6>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <p class="mb-2">
                        <strong>Included Dispatches:</strong> {{ number_format($dispatchTotal) }} UGX
                        <small class="text-muted">({{ $includedDispatches->count() }} drivers)</small>
                    </p>
                    <p class="mb-2">
                        <strong>+ Bakery Sales:</strong> {{ number_format($bakeryTotal) }} UGX
                        <small class="text-muted">({{ $bakeryBreakdown->count() }} users)</small>
                    </p>
                    <hr class="my-2">
                    <p class="mb-2 font-weight-bold h5">
                        <strong>= TOTAL PRODUCED:</strong> {{ number_format($totalProduced) }} UGX
                    </p>
                    <p class="mb-0">
                        <strong>Target:</strong> {{ number_format($target) }} UGX → 
                        <strong>Progress:</strong> {{ $progress }}%
                        @if($progress >= 100)
                            <span class="badge badge-success">✅ ACHIEVED</span>
                        @else
                            <span class="badge badge-warning">{{ number_format($remaining) }} UGX remaining</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verification Data (Collapsible) -->
<div class="card shadow-sm mb-4">
    <div class="card-header" data-toggle="collapse" data-target="#verificationData" style="cursor: pointer;">
        <h6 class="mb-0">
            <i class="fas fa-database"></i> Verification Data 
            <small class="text-muted">(Click to expand)</small>
            <i class="fas fa-chevron-down float-right"></i>
        </h6>
    </div>
    <div id="verificationData" class="collapse">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <tbody>
                        <tr>
                            <th width="40%">Filter Period</th>
                            <td>{{ $verificationData['filter'] }} ({{ $verificationData['days_count'] }} day{{ $verificationData['days_count'] > 1 ? 's' : '' }})</td>
                        </tr>
                        <tr>
                            <th>Date Range</th>
                            <td>{{ $verificationData['date_range'] }}</td>
                        </tr>
                        <tr>
                            <th>Daily Target</th>
                            <td>{{ number_format($verificationData['daily_target']) }} UGX</td>
                        </tr>
                        <tr>
                            <th>Calculated Target</th>
                            <td>{{ number_format($verificationData['calculated_target']) }} UGX</td>
                        </tr>
                        <tr class="table-info">
                            <th>Total All Dispatches (before exclusion)</th>
                            <td>{{ number_format($verificationData['total_dispatches_all']) }} UGX</td>
                        </tr>
                        <tr class="table-success">
                            <th>Included Dispatches</th>
                            <td>{{ number_format($verificationData['included_dispatches']) }} UGX</td>
                        </tr>
                        <tr class="table-danger">
                            <th>Excluded Dispatches</th>
                            <td>{{ number_format($verificationData['excluded_dispatches']) }} UGX</td>
                        </tr>
                        <tr class="table-success">
                            <th>Bakery Sales</th>
                            <td>{{ number_format($verificationData['bakery_sales']) }} UGX</td>
                        </tr>
                        <tr class="table-primary font-weight-bold">
                            <th>GRAND TOTAL</th>
                            <td>{{ number_format($verificationData['grand_total']) }} UGX</td>
                        </tr>
                        <tr class="table-warning font-weight-bold">
                            <th>PROGRESS</th>
                            <td>{{ $verificationData['progress_percent'] }}%</td>
                        </tr>
                    </tbody>
                </table>
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
