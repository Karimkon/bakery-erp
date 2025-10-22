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
            <strong>Total Items Sold:</strong> {{ number_format($dispatch->total_items_sold) }} &nbsp;|&nbsp;
            <strong>Total Sales:</strong> UGX {{ number_format($dispatch->total_sales_value, 0) }} &nbsp;|&nbsp;
            <strong>Total Commission:</strong> UGX {{ number_format($dispatch->commission_total, 0) }}
        </p>
    </div>
</div>

<h5 class="mb-3"><i class="bi bi-basket2 me-1"></i> Dispatched Items</h5>
<table class="table table-bordered table-striped">
    <thead class="table-light">
        <tr>
            <th>Product</th>
            <th>Opening</th>
            <th>Dispatched</th>
            <th>Sold (Cash)</th>
            <th>Sold (Credit)</th>
            <th>Total Sold</th>
            <th>Remaining</th>
            <th>Unit Price</th>
            <th>Line Total</th>
            <th>Commission</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sumOpening = $sumDispatched = $sumCash = $sumCredit = $sumSold = $sumRemaining = $sumTotal = $sumCommission = 0;
        @endphp
        @foreach($dispatch->items as $item)
            @php
                $sumOpening   += $item->opening_stock;
                $sumDispatched+= $item->dispatched_qty;
                $sumCash      += $item->sold_cash ?? 0;
                $sumCredit    += $item->sold_credit ?? 0;
                $sumSold      += $item->sold_qty;
                $sumRemaining += $item->remaining_qty;
                $sumTotal     += $item->line_total;
                $sumCommission+= $item->commission;
            @endphp
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $item->product)) }}</td>
                <td>{{ number_format($item->opening_stock) }}</td>
                <td>{{ number_format($item->dispatched_qty) }}</td>
                <td>{{ number_format($item->sold_cash ?? 0) }}</td>
                <td>{{ number_format($item->sold_credit ?? 0) }}</td>
                <td>{{ number_format($item->sold_qty) }}</td>
                <td>{{ number_format($item->remaining_qty) }}</td>
                <td>{{ number_format($item->unit_price, 0) }}</td>
                <td>{{ number_format($item->line_total, 0) }}</td>
                <td>{{ number_format($item->commission, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
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
    // Calculate financial summary safely
    $remainingInventoryValue = $dispatch->items->sum(fn($i) => $i->remaining_qty * $i->unit_price);
    $creditSalesValue = $dispatch->items->sum(fn($i) => $i->sold_credit * $i->unit_price);
    $driverBackDebt = $dispatch->driver?->back_debt ?? 0;
    $driverExpenses = $dispatch->expenses->sum('amount') ?? 0;

    $expectedAfterDeductions = $dispatch->total_sales_value - $dispatch->commission_total - $driverExpenses;
    $balanceDue = $remainingInventoryValue + $creditSalesValue + $driverBackDebt;
@endphp

<div class="mt-4">
    <h6>Financial Summary:</h6>
    <table class="table table-sm table-bordered">
        <tr>
            <td><strong>Total Sales Value:</strong></td>
            <td>UGX {{ number_format($dispatch->total_sales_value, 0) }}</td>
        </tr>
        <tr>
            <td><strong>Commission Total:</strong></td>
            <td class="text-warning">- UGX {{ number_format($dispatch->commission_total, 0) }}</td>
        </tr>
        @if($driverExpenses > 0)
        <tr>
            <td><strong>Driver Expenses:</strong></td>
            <td class="text-warning">- UGX {{ number_format($driverExpenses, 0) }}</td>
        </tr>
        @endif
        <tr>
            <td><strong>Expected Cash After Deductions:</strong></td>
            <td>UGX {{ number_format($expectedAfterDeductions, 0) }}</td>
        </tr>
        <tr>
            <td><strong>Actual Cash Received:</strong></td>
            <td class="text-success fw-bold">UGX {{ number_format($dispatch->cash_received, 0) }}</td>
        </tr>
        <tr class="table-light">
            <td colspan="2"><strong>What Driver Still Owes:</strong></td>
        </tr>
        <tr>
            <td><strong>Remaining Inventory Value:</strong></td>
            <td>
                @if($remainingInventoryValue > 0)
                    <span class="text-danger">UGX {{ number_format($remainingInventoryValue, 0) }}</span>
                @else
                    <span class="text-success">0 (All sold)</span>
                @endif
            </td>
        </tr>
        @if($creditSalesValue > 0)
        <tr>
            <td><strong>Credit Sales Value:</strong></td>
            <td class="text-info">UGX {{ number_format($creditSalesValue, 0) }}</td>
        </tr>
        @endif
        @if($driverBackDebt > 0)
        <tr>
            <td><strong>Previous Back Debt:</strong></td>
            <td class="text-danger">UGX {{ number_format($driverBackDebt, 0) }}</td>
        </tr>
        @endif
        <tr class="table-success">
            <td><strong>Total Balance Due:</strong></td>
            <td><strong class="text-danger">UGX {{ number_format($balanceDue, 0) }}</strong></td>
        </tr>
    </table>

    @if($dispatch->driver_signature)
        <div class="mt-4">
            <h6>Driver Signature:</h6>
            <img src="{{ $dispatch->driver_signature }}" alt="Driver Signature" style="max-width:400px;border:1px solid #ccc;padding:10px;background:#fff;">
        </div>
    @endif

    {{-- Grace Period Alerts --}}
    @if($balanceDue > 500000)
        <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle"></i> <strong>Grace period: 30 days</strong>
        </div>
    @elseif($balanceDue > 200000)
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> <strong>Grace period: 14 days</strong>
        </div>
    @endif
</div>
@endsection
