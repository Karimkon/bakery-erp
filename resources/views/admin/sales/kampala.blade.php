@extends('admin.layouts.app')
@section('title', 'Kampala Shop Sales Report')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-shop-window me-2"></i> Kampala Shop Sales Report</h3>
            <div class="text-muted small">Monitor Kampala shop sales and banking reconciliation</div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Shop</label>
                    <select name="shop_id" class="form-select">
                        <option value="">All Shops</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="{{ request('date_from', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="banking_status" class="form-select">
                        <option value="">All Status</option>
                        <option value="banked" {{ request('banking_status') == 'banked' ? 'selected' : '' }}>Banked</option>
                        <option value="pending" {{ request('banking_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="overdue" {{ request('banking_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
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
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Banked Amount</h6>
                            <h3>{{ number_format($totalBanked, 0) }} UGX</h3>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="bi bi-bank"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Pending Banking</h6>
                            <h3>{{ number_format($pendingBanking, 0) }} UGX</h3>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card {{ $balanceDue >= 0 ? 'bg-danger' : 'bg-info' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Balance Due</h6>
                            <h3>{{ number_format(abs($balanceDue), 0) }} UGX</h3>
                            <small>{{ $balanceDue >= 0 ? 'To Be Banked' : 'Over-Banked' }}</small>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="bi bi-calculator"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop-wise Breakdown -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Shop-wise Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Shop</th>
                            <th>Total Sales</th>
                            <th>Banked Amount</th>
                            <th>Pending</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shopSummary as $summary)
                        <tr>
                            <td>
                                <strong>{{ $summary['shop_name'] }}</strong>
                                <br><small class="text-muted">{{ $summary['manager'] }}</small>
                            </td>
                            <td>{{ number_format($summary['total_sales'], 0) }} UGX</td>
                            <td>{{ number_format($summary['banked_amount'], 0) }} UGX</td>
                            <td>{{ number_format($summary['pending_amount'], 0) }} UGX</td>
                            <td class="{{ $summary['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format(abs($summary['balance']), 0) }} UGX
                            </td>
                            <td>
                                @if($summary['balance'] == 0)
                                    <span class="badge bg-success">Settled</span>
                                @elseif($summary['balance'] > 0)
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-info">Overpaid</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.sales.kampala-details', $summary['shop_id']) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sales Transactions -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Sales Transactions</h5>
            <a href="{{ route('admin.kampala-sales.export') }}" class="btn btn-sm btn-success">
                <i class="bi bi-download me-1"></i> Export
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Shop</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Banked</th>
                            <th>Banking Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $sale->shop->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $sale->product_type)) }}</td>
                            <td>{{ number_format($sale->quantity) }}</td>
                            <td>{{ number_format($sale->unit_price, 0) }} UGX</td>
                            <td>{{ number_format($sale->total_price, 0) }} UGX</td>
                            <td>
                                @if($sale->is_banked)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-warning">No</span>
                                @endif
                            </td>
                            <td>
                                @if($sale->banking_date)
                                    {{ \Carbon\Carbon::parse($sale->banking_date)->format('M d, Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                                No sales transactions found
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