@extends('kampala.layouts.app')
@section('title', 'Stock')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Shop Stock</h4>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-end">Opening Stock</th>
                        <th class="text-end">Dispatched</th>
                        <th class="text-end">Sold</th>
                        <th class="text-end">Remaining</th>
                        <th class="text-end">Unit Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $stocks = \App\Models\KampalaStock::where('shop_id', auth()->user()->kampalaShop->id)
                            ->orderBy('product_type')
                            ->get();
                    @endphp
                    
                    @foreach($stocks as $stock)
                    <tr class="{{ $stock->remaining == 0 ? 'table-danger' : ($stock->remaining < 10 ? 'table-warning' : '') }}">
                        <td>
                            <strong>{{ ucwords(str_replace('_', ' ', $stock->product_type)) }}</strong>
                        </td>
                        <td class="text-end">{{ number_format($stock->opening_stock) }}</td>
                        <td class="text-end">{{ number_format($stock->dispatched) }}</td>
                        <td class="text-end">{{ number_format($stock->sold) }}</td>
                        <td class="text-end fw-bold">{{ number_format($stock->remaining) }}</td>
                        <td class="text-end">UGX {{ number_format($stock->unit_price) }}</td>
                        <td>
                            @if($stock->remaining == 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($stock->remaining < 10)
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td><strong>Totals</strong></td>
                        <td class="text-end"><strong>{{ number_format($stocks->sum('opening_stock')) }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($stocks->sum('dispatched')) }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($stocks->sum('sold')) }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($stocks->sum('remaining')) }}</strong></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Stock Alert -->
@php
    $lowStockItems = $stocks->where('remaining', '>', 0)->where('remaining', '<', 10)->count();
    $outOfStockItems = $stocks->where('remaining', 0)->count();
@endphp

@if($lowStockItems > 0 || $outOfStockItems > 0)
<div class="card mt-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Stock Alerts</h5>
    </div>
    <div class="card-body">
        @if($outOfStockItems > 0)
        <div class="alert alert-danger">
            <i class="bi bi-x-circle me-2"></i>
            <strong>{{ $outOfStockItems }} product(s) are out of stock:</strong>
            @foreach($stocks->where('remaining', 0) as $stock)
                <span class="badge bg-danger ms-2">{{ ucwords(str_replace('_', ' ', $stock->product_type)) }}</span>
            @endforeach
        </div>
        @endif
        
        @if($lowStockItems > 0)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>{{ $lowStockItems }} product(s) are running low:</strong>
            @foreach($stocks->where('remaining', '>', 0)->where('remaining', '<', 10) as $stock)
                <span class="badge bg-warning text-dark ms-2">
                    {{ ucwords(str_replace('_', ' ', $stock->product_type)) }} ({{ $stock->remaining }})
                </span>
            @endforeach
        </div>
        @endif
        
        <div class="text-center">
            <a href="{{ route('kampala.dispatches.index') }}" class="btn btn-warning">
                <i class="bi bi-truck me-1"></i> Check Pending Dispatches
            </a>
        </div>
    </div>
</div>
@endif
@endsection