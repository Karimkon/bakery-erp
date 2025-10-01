@extends('chef.layouts.app')

@section('title','Add Production')

@section('content')
    <h4 class="mb-4"><i class="bi bi-plus-lg me-2"></i> Add My Production</h4>

    <form method="POST" action="{{ route('chef.productions.store') }}" class="card shadow-sm p-4">
        @csrf

        <!-- Production Date & Flour -->
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" name="production_date" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Flour (bags)</label>
                <input type="number" step="0.01" name="flour_bags" class="form-control" required>
            </div>
        </div>

        <!-- Outputs -->
        <hr class="my-4">
        <h5 class="mb-3">Outputs</h5>
        <div class="row g-3">
            @foreach($products as $product => $price)
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">
                        {{ ucfirst(str_replace('_',' ', $product)) }} 
                        <small class="text-muted">(UGX {{ number_format($price) }})</small>
                    </label>
                    <input type="number" name="outputs[{{ $product }}]" 
                           class="form-control" min="0">
                </div>
            @endforeach
        </div>

        <!-- Ingredients -->
        <hr class="my-4">
        <h5 class="mb-3">Ingredients Used</h5>
        <div class="row g-3">
           @foreach($ingredients as $ing)
            <div class="col-md-3 col-sm-6">
                <label class="form-label">
                    {{ $ing->name }} 
                    <small class="text-muted">({{ $ing->unit }}, Stock: {{ $ing->stock }})</small>
                </label>
                <input type="number" step="0.01" name="ingredients[{{ $ing->id }}]" 
                    class="form-control" min="0" max="{{ $ing->stock }}">
            </div>
            @endforeach

        </div>

        <button class="btn btn-success mt-4">
            <i class="bi bi-check-circle me-1"></i> Save Production
        </button>
    </form>
@endsection
