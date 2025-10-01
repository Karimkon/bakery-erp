@extends('manager.layouts.app')

@section('title', 'Manager Dashboard')

@section('content')

@if($lowStockCount > 0)
<script>
    window.onload = function(){
        alert("⚠️ Warning: {{ $lowStockCount }} items are running low in stock!");
    }
</script>
@endif

<div class="container py-4">
    <h2 class="mb-4">📊 Manager Dashboard</h2>

    <div class="row g-4">

        <!-- Total Stock Quantity -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Stock Quantity</h6>
                    <h3>{{ number_format($totalStockQuantity) }}</h3>
                </div>
            </div>
        </div>

        <!-- Stock Items -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Stock Items</h6>
                    <h3>{{ $totalStockItems }}</h3>
                </div>
            </div>
        </div>

        <!-- Productions -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Productions</h6>
                    <h3>{{ $totalProductions }}</h3>
                </div>
            </div>
        </div>

        <!-- Today’s Productions -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Today’s Productions</h6>
                    <h3>{{ $todayProductions }}</h3>
                </div>
            </div>
        </div>

        <!-- Dispatches -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Dispatches</h6>
                    <h3>{{ $totalDispatches }}</h3>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body text-center">
                    <h6>⚠️ Low Stock</h6>
                    <h3>{{ $lowStockCount }}</h3>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
