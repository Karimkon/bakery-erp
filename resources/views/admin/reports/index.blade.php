@extends('admin.layouts.app')
@section('title','Reports')

@section('content')
<h4 class="mb-3"><i class="bi bi-bar-chart"></i> Reports</h4>

<form class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="text" name="driver" value="{{ $driver }}" class="form-control" placeholder="Driver name">
    </div>
    <div class="col-md-2">
        <input type="date" name="from_date" value="{{ $from }}" class="form-control">
    </div>
    <div class="col-md-2">
        <input type="date" name="to_date" value="{{ $to }}" class="form-control">
    </div>
    <div class="col-md-2">
        <select name="product" class="form-select">
            <option value="">All products</option>
            @foreach(config('bakery_products') as $p => $price)
                <option value="{{ $p }}" @selected($product==$p)>{{ ucfirst(str_replace('_',' ',$p)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.reports.exportPdf', request()->all()) }}" class="btn btn-danger">PDF</a>
        <a href="{{ route('admin.reports.exportExcel', request()->all()) }}" class="btn btn-success">Excel</a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Driver</th>
                <th>Product</th>
                <th>Dispatched</th>
                <th>Sold (Cash)</th>
                <th>Sold (Credit)</th>
                <th>Remaining</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @php
            $sumDispatched = $sumCash = $sumCredit = $sumRemaining = $sumTotal = 0;
        @endphp
        @foreach($items as $item)
            @php
                $sumDispatched += $item->dispatched_qty;
                $sumCash       += $item->sold_cash;
                $sumCredit     += $item->sold_credit;
                $sumRemaining  += $item->remaining_qty;
                $sumTotal      += $item->line_total;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($item->dispatch->dispatch_date)->format('d M Y') }}</td>
                <td>{{ $item->dispatch->driver->name }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$item->product)) }}</td>
                <td>{{ number_format($item->dispatched_qty) }}</td>
                <td>{{ number_format($item->sold_cash) }}</td>
                <td>{{ number_format($item->sold_credit) }}</td>
                <td>{{ number_format($item->remaining_qty) }}</td>
                <td>{{ number_format($item->unit_price,2) }}</td>
                <td>{{ number_format($item->line_total,2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot class="table-light">
            <tr>
                <th colspan="3" class="text-end">Totals:</th>
                <th>{{ number_format($sumDispatched) }}</th>
                <th>{{ number_format($sumCash) }}</th>
                <th>{{ number_format($sumCredit) }}</th>
                <th>{{ number_format($sumRemaining) }}</th>
                <th></th>
                <th>{{ number_format($sumTotal,2) }}</th>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Bootstrap small pagination --}}
<div class="d-flex justify-content-center mt-3">
    {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
</div>
@endsection
