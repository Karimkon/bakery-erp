@extends('chef.layouts.app')

@section('title','Chef Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-person-circle me-2"></i> Dashboard Chef: <strong>{{ Auth::user()->name }}</strong></h4>
            <p class="text-muted mb-0">Welcome to your production dashboard</p>
        </div>
        
        @if($chefTarget)
        <div class="text-end">
            <small class="text-muted">Daily Target</small>
            <h5 class="mb-0 text-primary">UGX {{ number_format($chefTarget->daily_target) }}</h5>
        </div>
        @endif
    </div>

    <!-- Ingredients Remaining Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i> My Ingredients Stock</h5>
                </div>
                <div class="card-body">
                    @if($ingredients->isEmpty())
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No ingredients assigned to you. Contact manager.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($ingredients as $ingredient)
                                <div class="col-md-3 col-sm-4 col-6">
                                    <div class="card border-start border-3 
                                        {{ $ingredient->stock <= 0 ? 'border-danger' : 
                                           ($ingredient->stock < 10 ? 'border-warning' : 'border-success') }} 
                                        h-100">
                                        <div class="card-body">
                                            <h6 class="card-title text-truncate">{{ $ingredient->name }}</h6>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-muted">Current Stock:</span>
                                                <span class="fw-bold {{ $ingredient->stock <= 0 ? 'text-danger' : 
                                                    ($ingredient->stock < 10 ? 'text-warning' : 'text-success') }}">
                                                    {{ number_format($ingredient->stock, 2) }}
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-muted">Unit:</span>
                                                <span class="badge bg-secondary">{{ $ingredient->unit }}</span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="text-muted">Unit Cost:</span>
                                                <span class="badge bg-primary">UGX {{ number_format($ingredient->unit_cost) }}</span>
                                            </div>
                                            <div class="mt-2">
                                                @if($ingredient->stock <= 0)
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                @elseif($ingredient->stock < 10)
                                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                                @else
                                                    <span class="badge bg-success">Available</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Stock Summary -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-primary mb-1">{{ $totalIngredients }}</h3>
                                        <small class="text-muted">Total Ingredients</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-success mb-1">{{ $availableIngredients }}</h3>
                                        <small class="text-muted">Available</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-warning mb-1">{{ $lowStockIngredients }}</h3>
                                        <small class="text-muted">Low Stock</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-danger mb-1">{{ $outOfStockIngredients }}</h3>
                                        <small class="text-muted">Out of Stock</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Records</h6>
                        <h3 class="mb-0">{{ $myTotal }}</h3>
                    </div>
                    <div class="icon-circle bg-primary text-white">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="card p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Today's Entries</h6>
                        <h3 class="mb-0">{{ $myToday }}</h3>
                    </div>
                    <div class="icon-circle bg-success text-white">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="card p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Value</h6>
                        <h3 class="mb-0">{{ number_format($myValue) }}</h3>
                        <small class="text-muted">UGX</small>
                    </div>
                    <div class="icon-circle bg-info text-white">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="card p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Variances</h6>
                        <h3 class="mb-0 text-danger">{{ $myVariance }}</h3>
                    </div>
                    <div class="icon-circle bg-warning text-white">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Target Progress Section -->
    @if($chefTarget)
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bullseye me-2"></i> Today's Target Progress</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted">TARGET</small>
                                <h3 class="text-primary mb-1">UGX {{ number_format($chefTarget->daily_target) }}</h3>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">ACHIEVED TODAY</small>
                                <h4 class="text-success mb-1">UGX {{ number_format($todayProduction) }}</h4>
                            </div>
                            @if($dailyRemaining > 0)
                            <div>
                                <small class="text-muted">REMAINING</small>
                                <h5 class="text-warning mb-0">UGX {{ number_format($dailyRemaining) }}</h5>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="mb-3">
                                    <small class="text-muted">PROGRESS</small>
                                    <h2 class="{{ $progressPercentage >= 100 ? 'text-success' : 'text-info' }}">
                                        {{ $progressPercentage }}%
                                    </h2>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar 
                                        @if($progressPercentage >= 100) bg-success
                                        @elseif($progressPercentage >= 75) bg-info
                                        @elseif($progressPercentage >= 50) bg-warning
                                        @else bg-danger @endif
                                        progress-bar-striped progress-bar-animated" 
                                        style="width: {{ min($progressPercentage, 100) }}%"
                                        role="progressbar">
                                        <strong>{{ $progressPercentage }}%</strong>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    @if($progressPercentage >= 100)
                                        <span class="badge bg-success">🎯 Target Achieved!</span>
                                    @elseif($progressPercentage >= 75)
                                        <span class="badge bg-info">⚡ Almost There!</span>
                                    @elseif($progressPercentage >= 50)
                                        <span class="badge bg-warning text-dark">📈 Halfway There!</span>
                                    @else
                                        <span class="badge bg-danger">🚀 Keep Going!</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <div class="mb-3">
                        <i class="bi bi-bar-chart-line display-4 text-primary"></i>
                    </div>
                    <h5 class="card-title">Progress Tracking</h5>
                    <p class="card-text text-muted">View your detailed production progress history</p>
                    <a href="{{ route('chef.progress-history') }}" class="btn btn-primary mt-auto">
                        <i class="bi bi-clock-history me-1"></i> View Progress History
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>No target set!</strong> Please contact administrator to set your production targets.
    </div>
    @endif

    <!-- Chart -->
    <div class="card shadow-sm p-3 mb-4">
        <h5 class="mb-3"><i class="bi bi-graph-up me-2"></i> My Production Value (Last 7 Days)</h5>
        <canvas id="chefChart" height="100"></canvas>
    </div>

    <style>
    .icon-circle { 
        width: 48px; height: 48px; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 1.15rem; 
    }
    .progress { border-radius: 10px; overflow: hidden; }
    </style>

    <!-- Chart.js -->
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
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value/1000000) + 'M';
                                if (value >= 1000) return (value/1000) + 'k';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection