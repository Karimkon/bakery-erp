@extends('manager.layouts.app')
@section('title','Add Ingredient')

@section('content')
<h4><i class="bi bi-plus-lg me-2"></i> Add Ingredient</h4>

<form action="{{ route('manager.ingredients.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label>Assign to Chef</label>
        <select name="chef_id" class="form-control">
            <option value="">-- None --</option>
            @foreach($chefs as $chef)
                <option value="{{ $chef->id }}" {{ old('chef_id') == $chef->id ? 'selected' : '' }}>
                    {{ $chef->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
    </div>
    <div class="mb-3">
        <label>Unit (e.g., kg, bag, L)</label>
        <input type="text" name="unit" class="form-control" value="{{ old('unit') }}" required>
    </div>
    <div class="mb-3">
        <label>Unit Cost (UGX)</label>
        <input type="number" name="unit_cost" class="form-control" value="{{ old('unit_cost') }}" required>
    </div>
    <div class="mb-3">
        <label>Stock (optional)</label>
        <input type="number" name="stock" class="form-control" value="{{ old('stock') }}">
    </div>

    <button class="btn btn-success">Save</button>
    <a href="{{ route('manager.ingredients.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
