@extends('admin.layouts.app')
@section('title','Add Ingredient')

@section('content')
<h4><i class="bi bi-plus-lg me-2"></i> Add Ingredient</h4>

<form action="{{ route('admin.ingredients.store') }}" method="POST">
    @csrf

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
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Unit (e.g., kg, bag, L)</label>
        <input type="text" name="unit" class="form-control" required>
    </div>
    <div class="mb-3">
        <label> Unit Cost (UGX)</label>
        <input type="number" name="unit_cost" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Stock (optional)</label>
        <input type="number" name="stock" class="form-control">
    </div>
    <button class="btn btn-success">Save</button>
    <a href="{{ route('admin.ingredients.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
