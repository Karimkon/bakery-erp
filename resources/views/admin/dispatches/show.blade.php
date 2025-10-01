@extends('admin.layouts.app')
@section('title', 'Dispatch Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-truck me-2"></i> Dispatch Details</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.dispatches.edit',$dispatch->id) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil-square"></i> Edit
        </a>
        <a href="{{ route('admin.dispatches.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card mb-4 shadow-sm border-start border-primary border-3">
    <div class="card-body">
        <p><strong>Date:</strong> {{ $dispatch->dispatch_date->format('d M Y') }}</p>
        <p><strong>Driver:</strong> {{ $dispatch->driver?->name }}</p>
        @if($dispatch->notes)
            <p><strong>Notes:</strong> {{ $dispatch->notes }}</p>
        @endif
        <p class="mb-0">
            <strong>Items Sold:</strong> {{ number_format($dispatch->total_items_sold) }} &nbsp;|&nbsp;
            <strong>Total Sales:</strong> UGX {{ number_format($dispatch->total_sales_value, 0) }} &nbsp;|&nbsp;
            <strong>Total Commission:</strong> UGX {{ number_format($dispatch->commission_total, 0) }}
        </p>
    </div>
</div>

<h5 class="mb-3"><i class="bi bi-basket2 me-1"></i> Items</h5>
<table class="table table-bordered table-striped">
    <thead class="table-light">
        <tr>
            <th>Product</th>
            <th>Opening</th>
            <th>Dispatched</th>
            <th>Sold Cash</th>
            <th>Sold Credit</th>
            <th>Total Sold</th>
            <th>Remaining</th>
            <th>Unit Price</th>
            <th>Line Total</th>
            <th>Commission</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sumOpening = $sumDispatched = $sumSold = $sumRemaining = $sumTotal = $sumCash = $sumCredit = 0;
        @endphp
        @foreach($dispatch->items as $it)
            @php
                $sumOpening   += $it->opening_stock;
                $sumDispatched+= $it->dispatched_qty;
                $sumCash      += $it->sold_cash ?? 0;
                $sumCredit    += $it->sold_credit ?? 0;
                $sumSold      += $it->sold_qty;
                $sumRemaining += $it->remaining_qty;
                $sumTotal     += $it->line_total;
            @endphp
            <tr>
                <td>{{ ucfirst(str_replace('_',' ', $it->product)) }}</td>
                <td>{{ number_format($it->opening_stock) }}</td>
                <td>{{ number_format($it->dispatched_qty) }}</td>
                <td>{{ number_format($it->sold_cash ?? 0) }}</td>
                <td>{{ number_format($it->sold_credit ?? 0) }}</td>
                <td>{{ number_format($it->sold_qty) }}</td>
                <td>{{ number_format($it->remaining_qty) }}</td>
                <td>{{ number_format($it->unit_price, 0) }}</td>
                <td>{{ number_format($it->line_total, 0) }}</td>
                <td>{{ number_format($it->commission, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        @php
            $sumCommission = $dispatch->items->sum('commission');
        @endphp
        <tr class="table-light">
            <th>Totals</th>
            <th>{{ number_format($sumOpening) }}</th>
            <th>{{ number_format($sumDispatched) }}</th>
            <th>{{ number_format($sumCash) }}</th>
            <th>{{ number_format($sumCredit) }}</th>
            <th>{{ number_format($sumSold) }}</th>
            <th>{{ number_format($sumRemaining) }}</th>
            <th></th>
            <th>{{ number_format($sumTotal, 0) }}</th>
            <th>{{ number_format($sumCommission, 0) }}</th>
        </tr>
    </tfoot>
</table>

@php
    $remainingInventoryValue = $dispatch->items->sum(fn($i) => $i->remaining_qty * $i->unit_price);
    $driverBackDebt = $dispatch->driver?->back_debt ?? 0;

@endphp


<div class="mt-4">
    <h6>Balance Summary:</h6>
    <table class="table table-sm">
        <tr>
            <td><strong>Total Sales Value:</strong></td>
            <td>UGX {{ number_format($dispatch->total_sales_value, 0) }}</td>
        </tr>
        <tr>
            <td><strong>Cash Received:</strong></td>
            <td>UGX {{ number_format($dispatch->cash_received, 0) }}</td>
        </tr>
        <tr>
            <td><strong>Remaining Inventory Value:</strong></td>
            <td>
                @if($remainingInventoryValue > 0)
                    <span class="text-danger">UGX {{ number_format($remainingInventoryValue, 0) }}</span>
                @else
                    <span class="text-success">All items sold or returned</span>
                @endif
            </td>
        </tr>

        @if($driverBackDebt > 0)
        <tr>
            <td><strong>Driver Back Debt:</strong></td>
            <td class="text-danger">UGX {{ number_format($driverBackDebt, 0) }}</td>
        </tr>
        <tr>
            <td><strong>Total Balance Due (including debt):</strong></td>
            <td><strong>UGX {{ number_format($dispatch->balance_due + $remainingInventoryValue + $driverBackDebt, 0) }}</strong></td>
        </tr>

        @endif
    </table>

    @if($dispatch->driver_signature)
        <img src="{{ $dispatch->driver_signature }}" alt="Driver Signature" style="max-width:400px; border:1px solid #ccc;">
    @endif

    <p>Balance Due: UGX {{ number_format($dispatch->balance_due) }}</p>
    @if($dispatch->balance_due > 500000)
        <p class="text-warning">Grace period: 30 days</p>
    @elseif($dispatch->balance_due > 200000)
        <p class="text-info">Grace period: 14 days</p>
    @endif
</div>

@endsection