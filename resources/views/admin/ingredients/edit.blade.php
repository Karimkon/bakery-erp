@extends('admin.layouts.app')
@section('title','Edit Ingredient')

@section('content')
<h4><i class="bi bi-pencil me-2"></i> Edit Ingredient</h4>

<form action="{{ route('admin.ingredients.update',$ingredient) }}" method="POST">
    @csrf @method('PUT')
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" value="{{ $ingredient->name }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Unit</label>
        <input type="text" name="unit" value="{{ $ingredient->unit }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label> Unit Cost (UGX)</label>
        <input type="number" name="unit_cost" value="{{ $ingredient->unit_cost }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Stock</label>
        <input type="number" name="stock" value="{{ $ingredient->stock }}" class="form-control">
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="{{ route('admin.ingredients.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
