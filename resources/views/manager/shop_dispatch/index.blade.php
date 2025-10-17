@extends('manager.layouts.app')
@section('title','Shop Dispatch')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-shop me-2"></i> Bakery Shop Dispatch</h4>
    <a href="{{ route('manager.shop-dispatch.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Dispatch New Stock
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-end">Opening</th>
                    <th class="text-end">Dispatched</th>
                    <th class="text-end">Sold</th>
                    <th class="text-end">Remaining</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td class="text-capitalize">{{ str_replace('_',' ', $row->product_type) }}</td>
                    <td class="text-end">{{ number_format($row->opening_stock) }}</td>
                    <td class="text-end">{{ number_format($row->dispatched) }}</td>
                    <td class="text-end">{{ number_format($row->sold) }}</td>
                    <td class="text-end fw-semibold">{{ number_format($row->remaining) }}</td>
                    <td class="text-end">
                        <form action="{{ route('manager.shop-dispatch.destroy',$row) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No stock records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $stocks->links() }}
    </div>
</div>
@endsection
