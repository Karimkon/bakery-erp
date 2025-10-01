@extends('manager.layouts.app')
@section('title','Reports')
@section('content')
<h3>Reports</h3>

<div class="mb-3">
    <a href="{{ route('manager.production.exportPdf', ['reportType' => 'production']) }}" class="btn btn-primary">Export PDF</a>
    <a href="{{ route('manager.production.exportExcel', ['reportType' => 'production']) }}" class="btn btn-success">Export Excel</a>
</div>

<form method="GET" class="row g-3 mb-4">
    <div class="col-md-2">
        <input type="date" name="from_date" class="form-control" value="{{ $from }}">
    </div>
    <div class="col-md-2">
        <input type="date" name="to_date" class="form-control" value="{{ $to }}">
    </div>
    <div class="col-md-2">
        <input type="text" name="driver" class="form-control" placeholder="Driver" value="{{ $driver }}">
    </div>
    <div class="col-md-2">
        <input type="text" name="product" class="form-control" placeholder="Product" value="{{ $product }}">
    </div>
    <div class="col-md-2">
        <select name="type" class="form-select">
            <option value="daily" {{ $type=='daily'?'selected':'' }}>Daily</option>
            <option value="weekly" {{ $type=='weekly'?'selected':'' }}>Weekly</option>
            <option value="monthly" {{ $type=='monthly'?'selected':'' }}>Monthly</option>
            <option value="custom" {{ $type=='custom'?'selected':'' }}>Custom</option>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary">Filter</button>
    </div>
</form>

<h4>Dispatches</h4>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th><th>Date</th><th>Driver</th><th>Product</th>
            <th>Dispatched</th><th>Sold Cash</th><th>Sold Credit</th><th>Remaining</th><th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dispatches as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->dispatch->dispatch_date ?? '' }}</td>
            <td>{{ $item->dispatch->driver->name ?? '' }}</td>
            <td>{{ $item->product }}</td>
            <td>{{ $item->dispatched_qty }}</td>
            <td>{{ $item->sold_cash }}</td>
            <td>{{ $item->sold_credit }}</td>
            <td>{{ $item->remaining_qty }}</td>
            <td>{{ number_format($item->line_total,2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h4>Productions</h4>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th><th>Date</th><th>Product</th><th>Produced</th><th>Used</th><th>Remaining</th>
        </tr>
    </thead>
    <tbody>
        @foreach($productions as $prod)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $prod->production_date }}</td>
            <td>{{ $prod->product }}</td>
            <td>{{ $prod->produced_qty }}</td>
            <td>{{ $prod->used_qty }}</td>
            <td>{{ $prod->remaining_qty }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
