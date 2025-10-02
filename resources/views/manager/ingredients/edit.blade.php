@extends('manager.layouts.app')
@section('title','Edit Ingredient')

@section('content')
<h4><i class="bi bi-pencil me-2"></i> Edit Ingredient</h4>

<form action="{{ route('manager.ingredients.update',$ingredient) }}" method="POST">
    @csrf @method('PUT')

    <div class="mb-3">
        <label>Assign to Chef</label>
        <select name="chef_id" class="form-control">
            <option value="">-- None --</option>
            @foreach($chefs as $chef)
                <option value="{{ $chef->id }}" {{ ($ingredient->chef_id ?? old('chef_id')) == $chef->id ? 'selected' : '' }}>
                    {{ $chef->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $ingredient->name) }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Unit</label>
        <input type="text" name="unit" value="{{ old('unit', $ingredient->unit) }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Unit Cost (UGX)</label>
        <input type="number" name="unit_cost" value="{{ old('unit_cost', $ingredient->unit_cost) }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Stock</label>
        <input type="number" name="stock" value="{{ old('stock', $ingredient->stock) }}" class="form-control">
    </div>

    <button class="btn btn-primary">Update</button>
    <a href="{{ route('manager.ingredients.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
