@extends('admin.layouts.app')
@section('title', 'Kampala Shop Details - ' . $shop->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-shop-window me-2"></i> 
                {{ $shop->name }} - Sales Details
            </h3>
            <div class="text-muted small">
                Manager: {{ $shop->manager->name ?? 'N/A' }} | 
                Location: {{ $shop->location ?? 'N/A' }}
            </div>
        </div>
        <div>
            <a href="{{ route('admin.sales.kampala') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Kampala Sales
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Sales</h6>
                            <h3>{{ number_format($totalSales, 0) }} UGX</h3>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="bi bi-cart-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Banked</h6>
                            <h3>{{ number_format($totalBanked, 0) }} UGX</h3>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="bi bi-bank"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card {{ $balance >= 0 ? 'bg-warning' : 'bg-info' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Balance</h6>
                            <h3>{{ number_format(abs($balance), 0) }} UGX</h3>
                            <small>{{ $balance >= 0 ? 'To Be Banked' : 'Over-Banked' }}</small>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="bi bi-calculator"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="{{ $dateFrom }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="{{ $dateTo }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Transactions -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Sales Transactions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Sold By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $sale->product_type)) }}</td>
                            <td>{{ number_format($sale->quantity) }}</td>
                            <td>{{ number_format($sale->unit_price, 0) }} UGX</td>
                            <td>{{ number_format($sale->total_price, 0) }} UGX</td>
                            <td>{{ $sale->user->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                                No sales transactions found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($sales->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} entries
                </div>
                {{ $sales->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Banking Records -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Banking Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Receipt Number</th>
                            <th>Notes</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bankings as $banking)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($banking->date)->format('M d, Y') }}</td>
                            <td>{{ number_format($banking->amount, 0) }} UGX</td>
                            <td>{{ $banking->receipt_number ?? 'N/A' }}</td>
                            <td>{{ $banking->notes ?? '-' }}</td>
                            <td>{{ $banking->user->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-bank display-4 d-block mb-2"></i>
                                No banking records found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
</style>
@endsection