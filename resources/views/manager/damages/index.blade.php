@extends('manager.layouts.app')

@section('title', 'Damage Reports')

@section('content')
<div class="container">
    <h2 class="mb-4">Damage Reports</h2>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Button to Report New Damage -->
    <a href="{{ route('manager.damages.create') }}" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> Report New Damage
    </a>

    <!-- Total Revenue from Sold Damages -->
    @php
        $totalRevenue = $damages->where('status', 'sold')->sum(function($d){ 
            return $d->quantity * $d->approved_price; 
        });
    @endphp
    <div class="alert alert-info">
        Total revenue from sold damages: <strong>{{ number_format($totalRevenue, 2) }}</strong>
    </div>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Approved Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($damages as $damage)
            <tr>
                <td>{{ $damage->id }}</td>
                <td>{{ $damage->product }}</td>
                <td>{{ $damage->quantity }}</td>
                <td>
                    @if($damage->status === 'pending')
                        <span class="badge bg-warning text-dark">Pending Admin Approval</span>
                    @elseif($damage->status === 'approved')
                        <span class="badge bg-info text-dark">Approved by Admin</span>
                    @elseif($damage->status === 'rejected')
                        <span class="badge bg-danger">Rejected by Admin</span>
                    @elseif($damage->status === 'sold')
                        <span class="badge bg-success">Sold</span>
                    @endif
                </td>
                <td>{{ $damage->approved_price ? number_format($damage->approved_price, 2) : '-' }}</td>
                <td>
                    <!-- Only View -->
                    <a href="{{ route('manager.damages.show', $damage) }}" class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i> View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No damage reports yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    {{ $damages->links() }}
</div>
@endsection
