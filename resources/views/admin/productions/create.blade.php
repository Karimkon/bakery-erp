@extends('admin.layouts.app')
@section('title','Record Production')

@section('content')
<h4><i class="bi bi-journal-plus me-2"></i> New Production Record</h4>

<form method="POST" action="{{ route('admin.productions.store') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">Chef</label>
        <select name="chef_id" class="form-select" required>
            @foreach($chefs as $chef)
                <option value="{{ $chef->id }}">{{ $chef->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Production Date</label>
        <input type="date" name="production_date" class="form-control" required>
    </div>

    <!-- Products -->
    <h5 class="mt-4">Products</h5>
    <div class="row g-2">
        @foreach(config('bakery_products') as $product => $price)
        <div class="col-md-3">
            <label>{{ ucfirst($product) }} (UGX {{ number_format($price) }})</label>
            <input type="number" name="outputs[{{ $product }}]" class="form-control" min="0">
        </div>
        @endforeach
    </div>

    <!-- Ingredients -->
    <h5 class="mt-4">Ingredients Used</h5>
    <div class="row g-2">
        @foreach($ingredients as $ing)
        <div class="col-md-3">
            <label>{{ $ing->name }} (Stock: {{ $ing->stock }} {{ $ing->unit }})</label>
            <input type="number" step="0.01" name="ingredients[{{ $ing->id }}]" class="form-control" min="0">
        </div>
        @endforeach
    </div>

    <button class="btn btn-success mt-4">Save Production</button>
</form>
@endsection
