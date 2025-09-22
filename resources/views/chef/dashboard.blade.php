@extends('chef.layouts.app')
@section('title','Chef Dashboard')

@section('content')
<h3 class="mb-4"><i class="bi bi-speedometer2 me-2"></i> My Dashboard</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <h6>My Total Records</h6>
            <h3>{{ $myTotal }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <h6>Today’s Entries</h6>
            <h3>{{ $myToday }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <h6>Total Value (UGX)</h6>
            <h3>{{ number_format($myValue) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <h6>My Variances</h6>
            <h3 class="text-danger">{{ $myVariance }}</h3>
        </div>
    </div>
</div>

<div class="card shadow-sm p-3">
    <h5>My Production Value (Last 7 Days)</h5>
    <canvas id="chefChart" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctxChef = document.getElementById('chefChart').getContext('2d');
new Chart(ctxChef, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartData->keys()->map(fn($d)=>\Carbon\Carbon::parse($d)->format('M d'))) !!},
        datasets: [{
            label: 'My Value (UGX)',
            data: {!! json_encode($chartData->values()) !!},
            backgroundColor: 'rgba(40,167,69,0.6)',
            borderColor: '#28a745',
            borderWidth: 1
        }]
    }
});
</script>
@endsection
