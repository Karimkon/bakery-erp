@extends('manager.layouts.app')
@section('title','Staff Breakfast Requests')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manager Staff Breakfast Requests</h2>
        <a href="{{ route('manager.staff_breakfast.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Request Breakfast
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

     {{-- Total Spent --}}
    <div class="alert alert-info">
        Total spent on staff breakfast: <strong>{{ number_format($totalSpent, 2) }} UGX</strong>
    </div>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Total Value (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($breakfasts as $b)
            <tr>
                <td>{{ $b->id }}</td>
                <td>{{ ucfirst($b->product) }}</td>
                <td>{{ $b->quantity }}</td>
                <td>{{ ucfirst($b->status) }}</td>
                <td>{{ $b->total_value ? number_format($b->total_value,2) : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No requests found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $breakfasts->links() }}
</div>
@endsection
