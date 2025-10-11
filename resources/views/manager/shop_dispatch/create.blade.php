@extends('manager.layouts.app')
@section('title','Dispatch to Shop')

@section('content')
<h4><i class="bi bi-plus-circle me-2"></i> Dispatch Stock to Bakery Shop</h4>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('manager.shop-dispatch.store') }}" method="POST" class="card p-3 shadow-sm">
    @csrf

    <div class="mb-3">
        <label for="product_type" class="form-label">Product Type</label>
        <select name="product_type" id="product_type" class="form-select" required>
            <option value="">-- Select Product --</option>
            @foreach($products as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="quantity" class="form-label">Quantity to Dispatch</label>
        <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
    </div>

    <button type="submit" class="btn btn-success">Dispatch</button>
    <a href="{{ route('manager.shop-dispatch.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
