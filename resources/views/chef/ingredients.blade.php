@extends('chef.layouts.app')

@section('title', 'My Ingredients')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-person-circle me-2"></i> Chef: <strong>{{ Auth::user()->name }}</strong></h4>
            <h4><i class="bi bi-box-seam me-2"></i> My Ingredients Stock</h4>
        </div>
        <div>
            <a href="{{ route('chef.dashboard') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Stock Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Ingredients</h6>
                            <h3 class="mb-0">{{ $totalIngredients }}</h3>
                        </div>
                        <div class="icon-circle bg-primary text-white">
                            <i class="bi bi-boxes"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-success border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Available</h6>
                            <h3 class="mb-0">{{ $availableIngredients }}</h3>
                        </div>
                        <div class="icon-circle bg-success text-white">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-warning border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Low Stock</h6>
                            <h3 class="mb-0">{{ $lowStockIngredients }}</h3>
                        </div>
                        <div class="icon-circle bg-warning text-white">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-danger border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Out of Stock</h6>
                            <h3 class="mb-0">{{ $outOfStockIngredients }}</h3>
                        </div>
                        <div class="icon-circle bg-danger text-white">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ingredients Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ingredient</th>
                            <th>Current Stock</th>
                            <th>Unit</th>
                            <th>Unit Cost</th>
                            <th>Status</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ingredients as $index => $ingredient)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $ingredient->name }}</td>
                            <td class="fw-bold {{ $ingredient->stock <= 0 ? 'text-danger' : 
                                ($ingredient->stock < 10 ? 'text-warning' : 'text-success') }}">
                                {{ number_format($ingredient->stock, 2) }}
                            </td>
                            <td><span class="badge bg-secondary">{{ $ingredient->unit }}</span></td>
                            <td>UGX {{ number_format($ingredient->unit_cost) }}</td>
                            <td>
                                @if($ingredient->stock <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($ingredient->stock < 10)
                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                @else
                                    <span class="badge bg-success">Available</span>
                                @endif
                            </td>
                            <td class="fw-bold">
                                UGX {{ number_format($ingredient->stock * $ingredient->unit_cost) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-box display-4"></i>
                                <p class="mt-2 mb-0">No ingredients assigned to you</p>
                                <small>Contact your manager to assign ingredients</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($ingredients->isNotEmpty())
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total Stock Value:</td>
                            <td class="fw-bold text-primary">
                                UGX {{ number_format($ingredients->sum(function($ing) {
                                    return $ing->stock * $ing->unit_cost;
                                })) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle { 
    width: 48px; height: 48px; border-radius: 50%; 
    display: flex; align-items: center; justify-content: center; 
    font-size: 1.15rem; 
}
</style>
@endsection