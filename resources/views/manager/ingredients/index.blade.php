@extends('manager.layouts.app')
@section('title','Ingredients')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4><i class="bi bi-box-seam me-2"></i> Ingredients</h4>
    <a href="{{ route('manager.ingredients.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Add Ingredient
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Filter Form -->
<form method="GET" action="{{ route('manager.ingredients.index') }}" class="mb-3 row g-2">
    <div class="col-md-4">
        <select name="chef_id" class="form-control">
            <option value="">-- Filter by Chef --</option>
            @foreach($chefs as $chef)
                <option value="{{ $chef->id }}" {{ request('chef_id') == $chef->id ? 'selected' : '' }}>
                    {{ $chef->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <input type="text" name="name" class="form-control" placeholder="Search by Ingredient Name" value="{{ request('name') }}">
    </div>
    <div class="col-md-4">
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('manager.ingredients.index') }}" class="btn btn-secondary">Reset</a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Chef</th>
                <th>Name</th>
                <th>Unit</th>
                <th>Price/Unit (UGX)</th>
                <th>Stock</th>
                <th>Quick Add</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ingredients as $ing)
            <tr>
                <td>{{ $ing->chef ? $ing->chef->name : '-' }}</td>
                <td>{{ $ing->name }}</td>
                <td>{{ $ing->unit }}</td>
                <td>{{ number_format($ing->unit_cost, 2) }}</td>
                <td>{{ number_format($ing->stock, 2) }}</td>
                <td>
                    <!-- Quick Add Stock Form -->
                    <form action="{{ route('manager.ingredients.quick-add-stock', $ing) }}" method="POST" class="d-inline">
                        @csrf
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <input type="number" name="stock_to_add" class="form-control form-control-sm" 
                                   placeholder="Add stock" step="0.01" min="0.01" required>
                            <button type="submit" class="btn btn-success btn-sm" title="Add Stock">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                    </form>
                </td>
                <td>
                    <a href="{{ route('manager.ingredients.show',$ing) }}" class="btn btn-sm btn-info" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('manager.ingredients.edit',$ing) }}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('manager.ingredients.destroy',$ing) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this ingredient?')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No ingredients found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex justify-content-end">
    {{ $ingredients->links('pagination::bootstrap-5') }}
</div>
@endsection