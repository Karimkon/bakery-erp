@extends('admin.layouts.app')
@section('title','Shop Report - Kampala Main Shop')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-graph-up me-2"></i>Shop Report - Kampala Main Shop</h4>
</div>

<form method="GET" class="card shadow-sm mb-4">
    <div class="card-body row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Product</label>
            <input type="text" name="product" value="{{ request('product') }}" class="form-control" placeholder="buns, donuts …">
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-primary w-100">
                <i class="bi bi-funnel me-1"></i> Apply Filters
            </button>
        </div>
    </div>
</form>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <canvas id="shopChart" height="120"></canvas>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Total Dispatched</div>
                <div class="fs-4 fw-bold">{{ number_format($summary['dispatched']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Total Sold</div>
                <div class="fs-4 fw-bold text-success">{{ number_format($summary['sold']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Remaining Stock</div>
                <div class="fs-4 fw-bold text-primary">{{ number_format($summary['remaining']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th class="text-end">Dispatched</th>
                    <th class="text-end">Sold</th>
                    <th class="text-end">Remaining</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stocks as $row)
                    <tr>
                        <td class="text-capitalize">{{ str_replace('_',' ',$row->product_type) }}</td>
                        <td class="text-end">{{ number_format($row->dispatched) }}</td>
                        <td class="text-end text-success">{{ number_format($row->sold) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($row->remaining) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">Recent Sales</div>
    <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Total</th>
                    <th>Payment</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>{{ $s->product_type }}</td>
                        <td class="text-end">{{ $s->quantity }}</td>
                        <td class="text-end">{{ number_format($s->total_price) }}</td>
                        <td><span class="badge bg-{{ $s->payment_method === 'cash' ? 'success':'info' }}">{{ $s->payment_method }}</span></td>
                        <td>{{ $s->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No sales found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $sales->links() }}
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('shopChart').getContext('2d');

    const data = {
        labels: @json($stocks->pluck('product_type')->map(fn($p)=>ucwords(str_replace('_',' ',$p)))),
        datasets: [
            {
                label: 'Dispatched',
                data: @json($stocks->pluck('dispatched')),
                backgroundColor: 'rgba(59, 130, 246, 0.7)'
            },
            {
                label: 'Sold',
                data: @json($stocks->pluck('sold')),
                backgroundColor: 'rgba(34, 197, 94, 0.7)'
            },
            {
                label: 'Remaining',
                data: @json($stocks->pluck('remaining')),
                backgroundColor: 'rgba(251, 191, 36, 0.7)'
            }
        ]

    };

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Shop Stock Overview (Kampala Main Shop)' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
@endpush
