@extends('admin.layouts.app')
@section('title', 'Production Details')

@section('content')
<div class="container-fluid">

    {{-- Back Button --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-journal-text me-2"></i> Production Details</h4>
        <a href="{{ route('admin.productions.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Productions
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Chef Info --}}
    <div class="card mb-4 shadow-sm border-start border-primary border-3">
        <div class="card-body">
            <h5 class="card-title mb-2">
                <i class="bi bi-person-circle me-1"></i>
                {{ $production->user->name }}
                <small class="text-muted">({{ $production->user->email }})</small>
            </h5>
            <p class="mb-1"><strong>Date:</strong> {{ $production->production_date }}</p>
            <p class="mb-0"><strong>Flour Used:</strong> {{ $production->flour_bags }} bags</p>
        </div>
    </div>

    {{-- Output Summary --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-box-seam me-2"></i> Production Output
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Buns: <strong>{{ $production->buns }}</strong></li>
                        <li class="list-group-item">Small Bread: <strong>{{ $production->small_breads }}</strong></li>
                        <li class="list-group-item">Big Bread: <strong>{{ $production->big_breads }}</strong></li>
                        <li class="list-group-item">Donuts: <strong>{{ $production->donuts }}</strong></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Half Cakes: <strong>{{ $production->half_cakes }}</strong></li>
                        <li class="list-group-item">Block Cakes: <strong>{{ $production->block_cakes }}</strong></li>
                        <li class="list-group-item">Slab Cakes: <strong>{{ $production->slab_cakes }}</strong></li>
                        <li class="list-group-item">Birthday Cakes: <strong>{{ $production->birthday_cakes }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Totals & Variance --}}
    <div class="card mb-4 shadow-sm border-start border-info border-3">
        <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-cash-stack me-2"></i> Financials</h5>
            <p><strong>Total Value:</strong> UGX {{ number_format($production->total_value) }}</p>
            <p>
                <strong>Variance:</strong>
                @if($production->has_variance)
                    <span class="badge bg-danger">Variance Detected</span><br>
                    <small class="text-muted">{{ $production->variance_notes }}</small>
                @else
                    <span class="badge bg-success">OK</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Ingredient Usage --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-basket me-2"></i> Ingredients Used
        </div>
        <div class="card-body p-0">
            @if($production->ingredientUsages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ingredient</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Cost (UGX)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($production->ingredientUsages as $usage)
                            <tr>
                                <td>{{ $usage->ingredient->name }}</td>
                                <td>{{ $usage->quantity }}</td>
                                <td>{{ $usage->unit }}</td>
                                <td class="text-end">{{ number_format($usage->cost) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold text-end">
                                <td colspan="3">Total Ingredients Cost</td>
                                <td>UGX {{ number_format($production->ingredientUsages->sum('cost')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="text-muted m-3">No ingredient usage recorded.</p>
            @endif
        </div>
    </div>

</div>
@endsection
