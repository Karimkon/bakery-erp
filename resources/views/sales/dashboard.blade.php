@extends('sales.layouts.app')
@section('title','Dashboard')

@section('content')
    <h4 class="mb-4"><i class="bi bi-speedometer2 me-2"></i>Sales Dashboard</h4>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Transactions</div>
                    <div class="stat fs-4">{{ number_format($summary['count']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Units Sold</div>
                    <div class="stat fs-4">{{ number_format($summary['qty']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total Revenue (UGX)</div>
                    <div class="stat fs-4">{{ number_format($summary['total']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">Top Products</h6>
            <canvas id="productsChart" height="120"></canvas>
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
                backgroundColor: '#3b82f6'
            }]
        }
    });
</script>
@endpush
