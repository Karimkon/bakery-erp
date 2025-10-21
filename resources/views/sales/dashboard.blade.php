@extends('sales.layouts.app')
@section('title','Dashboard')

@section('content')
    <h4 class="mb-4"><i class="bi bi-speedometer2 me-2"></i>Sales Dashboard</h4>

    {{-- Cash Balance Overview --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title mb-1">
                                <i class="bi bi-cash-stack me-2"></i>Available Cash Balance
                            </h5>
                            <p class="text-muted mb-0 small">
                                Total Sales: UGX {{ number_format($balance['total_sales']) }} | 
                                Banked: UGX {{ number_format($balance['total_banked']) }} | 
                                Expenses: UGX {{ number_format($balance['total_expenses']) }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <h2 class="text-success mb-0">UGX {{ number_format($summary['available_cash']) }}</h2>
                            <small class="text-muted">Physically available in desk</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Transactions</div>
                    <div class="stat fs-4">{{ number_format($summary['count']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Units Sold</div>
                    <div class="stat fs-4">{{ number_format($summary['qty']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total Revenue (UGX)</div>
                    <div class="stat fs-4">{{ number_format($summary['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <div class="text-muted">Available Cash</div>
                    <div class="stat fs-4 text-success">{{ number_format($summary['available_cash']) }}</div>
                    <small class="text-muted">In desk</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Cash Flow Breakdown</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total Sales:</span>
                        <strong class="text-primary">UGX {{ number_format($balance['total_sales']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Money Banked:</span>
                        <strong class="text-danger">- UGX {{ number_format($balance['total_banked']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Expenses:</span>
                        <strong class="text-warning">- UGX {{ number_format($balance['total_expenses']) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <span><strong>Available Cash:</strong></span>
                        <strong class="text-success">UGX {{ number_format($balance['available_cash']) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Top Products</h6>
                    <div style="height: 300px;"> <!-- fixed height -->
    <canvas id="productsChart"></canvas>
</div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('productsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($topProducts->keys()) !!},
        datasets: [{
            label: 'Units Sold',
            data: {!! json_encode($topProducts->values()) !!},
            backgroundColor: '#3b82f6',
            borderColor: '#2563eb',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 } }
        },
        plugins: { legend: { display: false } },
        layout: { padding: 10 }
    }
});

</script>
@endpush