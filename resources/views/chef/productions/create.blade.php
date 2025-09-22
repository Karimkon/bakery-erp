@extends('chef.layouts.app')
@section('title','Add Production')

@section('content')
<h4><i class="bi bi-plus-lg me-2"></i> Add My Production</h4>

<form method="POST" action="{{ route('chef.productions.store') }}">
    @csrf

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
    <hr>
    <h5>Outputs</h5>
    <div class="row g-3">
        @foreach($products as $product => $price)
            <div class="col-md-3">
                <label>{{ ucfirst(str_replace('_',' ', $product)) }} (UGX {{ number_format($price) }})</label>
                <input type="number" name="outputs[{{ $product }}]" class="form-control" min="0" value="0">
            </div>
        @endforeach
    </div>

    <!-- Ingredients -->
    <hr>
    <h5>Ingredients Used</h5>
    <div class="row g-3">
        @foreach($ingredients as $ing)
            <div class="col-md-3">
                <label>{{ $ing->name }} ({{ $ing->unit }}, Stock: {{ $ing->stock }})</label>
                <input type="number" step="0.01" name="ingredients[{{ $ing->id }}]" class="form-control" min="0" value="0">
            </div>
        @endforeach
    </div>

    <button class="btn btn-success mt-4">Save</button>
</form>
@endsection
