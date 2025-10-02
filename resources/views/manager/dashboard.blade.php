@extends('manager.layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">📊 Manager Dashboard</h2>

    <div class="row g-4">

        <!-- Bakery Stock Summary -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Bakery Stock</h6>
                    <h3>{{ number_format($totalStockQuantity) }}</h3>
                    <small>{{ $totalStockItems }} items</small>
                </div>
            </div>
        </div>

        <!-- Ingredient Summary -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Ingredients</h6>
                    <h3>{{ number_format($totalIngredientQuantity) }}</h3>
                    <small>{{ $totalIngredientItems }} items</small>
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

    </div>

    <!-- Bakery Stock Table -->
    <div class="mt-4 card shadow-sm">
        <div class="card-header bg-warning text-dark fw-bold">
            <i class="bi bi-basket-fill me-2"></i> Bakery Stock
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bakeryStocks as $stock)
                        <tr @if($stock->quantity < 10) class="table-danger" @endif>
                            <td>{{ ucfirst(str_replace('_', ' ', $stock->product)) }}</td>
                            <td>{{ number_format($stock->quantity) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Optional Ingredients Table -->
    <div class="mt-4 card shadow-sm">
        <div class="card-header bg-info text-white fw-bold">
            <i class="bi bi-box-seam me-2"></i> Ingredients Summary
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingredients as $ingredient)
                        <tr @if($ingredient->stock < 5) class="table-danger" @endif>
                            <td>{{ $ingredient->name }}</td>
                            <td>{{ number_format($ingredient->stock) }} {{ $ingredient->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
