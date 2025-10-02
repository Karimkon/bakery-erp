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

<div class="table-responsive">
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>Chef</th>
            <th>Name</th>
            <th>Unit</th>
            <th>Price/Unit (UGX)</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ingredients as $ing)
        <tr>
            <td>{{ $ing->chef ? $ing->chef->name : '-' }}</td>
            <td>{{ $ing->name }}</td>
            <td>{{ $ing->unit }}</td>
            <td>{{ number_format($ing->unit_cost) }}</td>
            <td>{{ $ing->stock }}</td>
            <td>
                <a href="{{ route('manager.ingredients.show',$ing) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('manager.ingredients.edit',$ing) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('manager.ingredients.destroy',$ing) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this ingredient?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>

{{ $ingredients->links() }}
@endsection
