@extends('manager.layouts.app')
@section('title','Ingredient Details')

@section('content')
<h4><i class="bi bi-box me-2"></i> Ingredient Details</h4>

<ul class="list-group mb-3">
    <li class="list-group-item"><strong>Name:</strong> {{ $ingredient->name }}</li>
    <li class="list-group-item"><strong>Unit:</strong> {{ $ingredient->unit }}</li>
    <li class="list-group-item"><strong>Price/Unit:</strong> UGX {{ number_format($ingredient->unit_cost) }}</li>
    <li class="list-group-item"><strong>Stock:</strong> {{ $ingredient->stock }}</li>
    <li class="list-group-item"><strong>Chef:</strong> {{ $ingredient->chef ? $ingredient->chef->name : '-' }}</li>
</ul>

<a href="{{ route('manager.ingredients.edit',$ingredient) }}" class="btn btn-warning">Edit</a>
<a href="{{ route('manager.ingredients.index') }}" class="btn btn-secondary">Back</a>
@endsection
