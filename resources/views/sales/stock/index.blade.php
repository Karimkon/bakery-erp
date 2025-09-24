@extends('sales.layouts.app')
@section('title','Shop Stock')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Shop Stock</h4>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-end">Dispatched</th>
                    <th class="text-end">Sold</th>
                    <th class="text-end">Remaining</th>
                    <th>Last Updated</th>
                </tr>
                </thead>
                <tbody>
                @forelse($stocks as $i => $row)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td class="text-capitalize">{{ str_replace('_',' ', $row->product_type) }}</td>
                        <td class="text-end">{{ number_format($row->dispatched) }}</td>
                        <td class="text-end text-success">{{ number_format($row->sold) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($row->remaining) }}</td>
                        <td>{{ $row->updated_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No stock records found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
