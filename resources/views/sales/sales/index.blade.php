{{-- resources/views/sales/sales/index.blade.php --}}
@extends('sales.layouts.app')
@section('title','My Sales')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-cash-coin me-2"></i>My Sales</h4>
        <a href="{{ route('sales.sales.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-bag-plus"></i> New Sale
        </a>
    </div>

    <form method="GET" class="card shadow-sm mb-3">
        <div class="card-body row g-3">
            <div class="col-12 col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Product</label>
                <input type="text" name="product" value="{{ request('product') }}" class="form-control" placeholder="buns, donuts …">
            </div>
            <div class="col-12 col-md-3 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
            </div>
        </div>
    </form>

    @php
        $query = \App\Models\Sale::where('user_id', auth()->id());
        if(request('from')) $query->whereDate('created_at','>=',request('from'));
        if(request('to'))   $query->whereDate('created_at','<=',request('to'));
        if(request('product')) $query->where('product_type','LIKE','%'.request('product').'%');
        $summary = [
            'count' => (clone $query)->count(),
            'qty'   => (clone $query)->sum('quantity'),
            'total' => (clone $query)->sum('total_price'),
        ];
        $sales = $query->latest()->paginate(20)->withQueryString();
    @endphp

    <div class="row g-3 mb-2">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Transactions</div>
                    <div class="stat fs-4">{{ number_format($summary['count']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Units Sold</div>
                    <div class="stat fs-4">{{ number_format($summary['qty']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total Amount (UGX)</div>
                    <div class="stat fs-4">{{ number_format($summary['total']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit</th>
                    <th class="text-end">Total</th>
                    <th>Payment</th>
                    <th>When</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($sales as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td class="text-capitalize">{{ str_replace('_',' ', $row->product_type) }}</td>
                        <td class="text-end">{{ number_format($row->quantity) }}</td>
                        <td class="text-end">{{ number_format($row->unit_price,2) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($row->total_price,2) }}</td>
                        <td><span class="badge bg-{{ $row->payment_method === 'cash' ? 'success':'info' }}">{{ $row->payment_method }}</span></td>
                        <td>{{ $row->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('sales.sales.destroy',$row) }}" method="POST" onsubmit="return confirm('Delete this sale?')">
                                @csrf @method('DELETE')
                                <a href="{{ route('sales.sales.edit',$row) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No sales yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">
            {{ $sales->links() }}
        </div>
    </div>
@endsection
