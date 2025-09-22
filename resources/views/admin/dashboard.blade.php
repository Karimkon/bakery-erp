@extends('admin.layouts.app')
@section('title','Admin Dashboard')

@section('content')
<h3 class="mb-4"><i class="bi bi-speedometer2 me-2"></i> Bakery Admin Dashboard</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <h6>Total Productions</h6>
            <h3>{{ $totalProductions }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <h6>Today’s Records</h6>
            <h3>{{ $todayProductions }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <h6>Total Value (UGX)</h6>
            <h3>{{ number_format($totalValue) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <h6>Variances</h6>
            <h3 class="text-danger">{{ $varianceCount }}</h3>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="card shadow-sm p-3">
    <h5>Last 7 Days Production Value</h5>
    <canvas id="productionChart" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('productionChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData->keys()->map(fn($d)=>\Carbon\Carbon::parse($d)->format('M d'))) !!},
        datasets: [{
            label: 'Total Value (UGX)',
            data: {!! json_encode($chartData->values()) !!},
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.2)',
            tension: 0.3,
            fill: true
        }]
    }
});
</script>
@endsection
