@extends('manager.layouts.app')
@section('title', 'Approve Production')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-check2-square me-2"></i>Production #{{ $production->id }}</h4>
        <a href="{{ route('manager.productions.approval.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Approvals
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Production Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Production Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Chef:</strong><br>
                    {{ $production->chef->name ?? 'Unknown' }}
                </div>
                <div class="col-md-3">
                    <strong>Production Date:</strong><br>
                    {{ $production->production_date->format('l, d M Y') }}
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong><br>
                    <span class="badge bg-warning text-dark">Pending Approval</span>
                </div>
                <div class="col-md-3">
                    <strong>Flour Used:</strong><br>
                    {{ number_format($production->flour_bags, 2) }} bags
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Form -->
    <form method="POST" action="{{ route('manager.productions.approval.approve', $production->id) }}">
        @csrf

        <!-- Ingredients Adjustment Section -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Adjust Ingredients Used
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Adjust ingredient quantities based on available stock. Current stock levels shown.
                </p>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Ingredient</th>
                                <th>Qty Entered</th>
                                <th>Available Stock</th>
                                <th>Adjusted Qty</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($production->ingredientUsages as $index => $usage)
                            <tr>
                                <td>
                                    <strong>{{ $usage->ingredient->name }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ number_format($usage->quantity, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $usage->ingredient->stock >= $usage->quantity ? 'success' : 'danger' }}">
                                        {{ number_format($usage->ingredient->stock, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="ingredient_adjustments[{{ $usage->id }}]" 
                                           class="form-control form-control-sm" 
                                           step="0.01" 
                                           min="0" 
                                           max="{{ $usage->ingredient->stock }}"
                                           value="{{ min($usage->quantity, $usage->ingredient->stock) }}"
                                           required>
                                </td>
                                <td class="text-center">
                                    {{ $usage->ingredient->unit }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Products Adjustment Section -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Adjust Production Quantities</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Adjust product quantities if needed. These will update bakery stock upon approval.
                </p>

                <div class="row g-3">
                    @php
                        $products = [
                            'buns' => 'Buns',
                            'small_breads' => 'Small Breads', 
                            'big_breads' => 'Big Breads',
                            'quarter_breads' => 'Quarter Breads',
                            'donuts' => 'Donuts',
                            'half_cakes' => 'Half Cakes',
                            'block_cakes' => 'Block Cakes', 
                            'slab_cakes' => 'Slab Cakes',
                            'birthday_cakes30k' => 'Birthday Cakes (30k)',
                            'birthday_cakes50k' => 'Birthday Cakes (50k)',
                            'mandazis' => 'Mandazis',
                            'musiba_tayi' => 'Musiba Tayi',
                            'scornes' => 'Scornes',
                            'chapatys' => 'Chapatys',
                            'toasted_bread' => 'Toasted Bread',
                            'spring_donuts' => 'Spring Donuts',
                            'cream_donuts' => 'Cream Donuts',
                            'cinnamon_rolls' => 'Cinnamon Rolls'
                        ];
                    @endphp
                    
                    @foreach($products as $field => $label)
                        @php
                            $currentQty = $production->$field ?? 0;
                        @endphp
                        <div class="col-md-4 col-sm-6">
                            <label class="form-label">{{ $label }}</label>
                            <input type="number" 
                                   name="product_adjustments[{{ $field }}]" 
                                   class="form-control" 
                                   min="0" 
                                   value="{{ old("product_adjustments.$field", $currentQty) }}">
                            <small class="text-muted">Original: {{ $currentQty }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-check-circle me-2"></i>Approve Production
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-danger btn-lg w-100" 
                                data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-2"></i>Reject Production
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Production</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('manager.productions.approval.reject', $production->id) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason</label>
                            <textarea name="rejection_reason" class="form-control" rows="4" 
                                      placeholder="Explain why this production is being rejected..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    font-weight: 600;
}
</style>
@endsection