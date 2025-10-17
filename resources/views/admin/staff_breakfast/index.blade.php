@extends('admin.layouts.app')
@section('title','Staff Breakfast Approvals')

@section('content')
<div class="container">
    <h2>Staff Breakfast Requests</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $totalSpent = $breakfasts->sum('total_value');
    @endphp
    <div class="alert alert-info">
        Total value spent on staff breakfast: <strong>{{ number_format($totalSpent,2) }} UGX</strong>
    </div>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Manager</th>
                <th>Status</th>
                <th>Total Value (UGX)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($breakfasts as $b)
            <tr>
                <td>{{ $b->id }}</td>
                <td>{{ ucfirst($b->product) }}</td>
                <td>{{ $b->quantity }}</td>
                <td>{{ $b->manager->name ?? '-' }}</td>
                <td>{{ ucfirst($b->status) }}</td>
                <td>{{ $b->total_value ? number_format($b->total_value,2) : '-' }}</td>
                <td>
                    @if($b->status === 'pending')
                    <form action="{{ route('admin.staff_breakfast.approve',$b) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                    </form>
                    <form action="{{ route('admin.staff_breakfast.reject',$b) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                    </form>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($b->status) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">No requests found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $breakfasts->links() }}
</div>
@endsection
