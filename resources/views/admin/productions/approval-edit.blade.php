@extends('manager.layouts.app')
@section('title', 'Edit Production')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-pencil-square me-2"></i>Edit Production #{{ $production->id }}</h4>
        <a href="{{ route('manager.productions.approval.show', $production->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Details
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Production Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('manager.productions.approval.update', $production->id) }}">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Chef</label>
                        <p class="form-control-plaintext">{{ $production->chef->name ?? 'Unknown' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Production Date</label>
                        <p class="form-control-plaintext">{{ $production->production_date->format('d M Y') }}</p>
                    </div>
                </div>

                <!-- Flour Used -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Flour Used (Kgs)</label>
                        <input type="number" step="0.01" name="flour_kgs" class="form-control" 
                               value="{{ old('flour_kgs', $production->flour_bags * 50) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Current Flour Bags</label>
                        <p class="form-control-plaintext">{{ number_format($production->flour_bags, 2) }} bags</p>
                    </div>
                </div>

                <!-- Products Output -->
                <h5 class="mb-3 border-bottom pb-2">Products Output</h5>
                <div class="row g-3 mb-4">
                    @foreach($products as $product => $price)
                        @php
                            $currentQty = $production->$product ?? 0;
                        @endphp
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">
                                {{ ucfirst(str_replace('_', ' ', $product)) }}
                                <small class="text-muted">(UGX {{ number_format($price) }})</small>
                            </label>
                            <input type="number" name="outputs[{{ $product }}]" 
                                   class="form-control" min="0" value="{{ old("outputs.$product", $currentQty) }}">
                            <small class="text-muted">Current: {{ $currentQty }}</small>
                        </div>
                    @endforeach
                </div>

                <!-- Ingredients Used -->
                <h5 class="mb-3 border-bottom pb-2">Ingredients Used</h5>
                <div class="row g-3 mb-4">
                    @foreach($production->ingredientUsages as $usage)
                        <div class="col-md-4">
                            <label class="form-label">
                                {{ $usage->ingredient->name }}
                                <small class="text-muted">({{ $usage->ingredient->unit }})</small>
                            </label>
                            <input type="number" step="0.01" name="ingredients[{{ $usage->ingredient->id }}]" 
                                   class="form-control" min="0" max="{{ $usage->ingredient->stock }}"
                                   value="{{ old("ingredients.{$usage->ingredient->id}", $usage->quantity) }}">
                            <small class="text-muted">
                                Current: {{ $usage->quantity }} {{ $usage->ingredient->unit }} | 
                                Stock: {{ $usage->ingredient->stock }}
                            </small>
                        </div>
                    @endforeach
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="bi bi-check-circle me-1"></i>Update Production
                        </button>
                        <a href="{{ route('manager.productions.approval.show', $production->id) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection