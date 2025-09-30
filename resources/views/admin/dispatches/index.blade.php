@extends('admin.layouts.app')
@section('title', 'Driver Dispatches')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-truck me-2"></i> Driver Dispatches (Latest Only)</h4>
    <a href="{{ route('admin.dispatches.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> New Dispatch
    </a>
</div>

<form method="GET" class="mb-3">
    <div class="input-group" style="max-width: 300px;">
        <input type="text" name="driver" class="form-control" placeholder="Search by driver" value="{{ $searchDriver }}">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
    </div>
</form>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive">
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>Date</th>
            <th>Dispatch #</th>
            <th>Driver</th>
            <th>Items Sold</th>
            <th>Total Sales (UGX)</th>
            <th>Cash Received (UGX)</th>
            <th>Balance Due (UGX)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($dispatches as $d)
        <tr>
            <td>{{ \Carbon\Carbon::parse($d->dispatch_date)->format('d M Y') }}</td>
            <td>{{ $d->dispatch_no }}</td>
            <td>{{ $d->driver?->name }}</td>
            <td>{{ number_format($d->total_items_sold) }}</td>
            <td>{{ number_format($d->total_sales_value, 0) }}</td>
            <td>{{ number_format($d->cash_received, 0) }}</td>
            <td>{{ number_format($d->balanceDue, 0) }}</td>
            <td class="d-flex gap-2">
                <a href="{{ route('admin.dispatches.show',$d->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> View
                </a>
                <a href="{{ route('admin.dispatches.edit',$d->id) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No dispatches found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
@endsection
