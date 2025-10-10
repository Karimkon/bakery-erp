@extends('manager.layouts.app')

@section('title', 'Damage Reports')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Damage Reports</h2>
        <a href="{{ route('manager.damages.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Report Damage
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Total Revenue -->
    @php
        $totalRevenue = $damages->sum(fn($d) => ($d->sold_quantity ?? 0) * ($d->approved_price ?? 0));
    @endphp
    <div class="alert alert-info">
        Total revenue from sold damages:
        <strong>{{ number_format($totalRevenue, 2) }} UGX</strong>
    </div>

    <table class="table table-striped table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Quantity Left</th>
                <th>Sold Quantity</th>
                <th>Approved By</th>
                <th>Status</th>
                <th>Approved Price/unit (UGX)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($damages as $damage)
            <tr>
                <td>{{ $damage->id }}</td>
                <td>{{ ucfirst($damage->product) }}</td>
                <td>{{ $damage->quantity }}</td>
                <td>{{ $damage->sold_quantity ?? 0 }}</td>
                <td>{{ $damage->admin->name ?? '-' }}</td>
                <td>
                    <span class="badge 
                        @if($damage->status == 'approved') bg-success 
                        @elseif($damage->status == 'pending') bg-warning 
                        @else bg-secondary 
                        @endif">
                        {{ ucfirst($damage->status) }}
                    </span>
                </td>
                <td>{{ $damage->approved_price ? number_format($damage->approved_price, 2) : '-' }}</td>

                <td>
                    <a href="{{ route('manager.damages.show', $damage) }}" class="btn btn-sm btn-info">View</a>

                    @if($damage->status === 'approved' && $damage->quantity > 0)
                        <!-- Sell Modal Trigger -->
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sellModal{{ $damage->id }}">
                            Sell
                        </button>
                    @endif

                    <!-- Sell Modal -->
                    @if($damage->status === 'approved' && $damage->quantity > 0)
                    <div class="modal fade" id="sellModal{{ $damage->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('manager.damages.sold', $damage) }}" method="POST" class="modal-content">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Sell {{ $damage->product }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label>Quantity to sell (max {{ $damage->quantity }})</label>
                                        <input type="number" name="sold_quantity" class="form-control" max="{{ $damage->quantity }}" min="1" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Price per unit</label>
                                        <input type="number" name="sold_price" class="form-control" min="0" step="0.01" value="{{ $damage->approved_price ?? 0 }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Sell</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">No damage reports found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $damages->links() }}
</div>
@endsection
