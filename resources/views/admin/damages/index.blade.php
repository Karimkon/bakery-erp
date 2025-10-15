@extends('admin.layouts.app')

@section('title', 'Damage Reports')

@section('content')
<div class="container">
    <h2 class="mb-4">Damage Reports</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Total Revenue -->
    @php
        $totalRevenue = $damages->sum(fn($d) => ($d->sold_quantity ?? 0) * $d->approved_price);
    @endphp
    <div class="alert alert-info">
        Total revenue from sold damages: <strong>{{ number_format($totalRevenue, 2) }}</strong>
    </div>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Photo</th>
                <th>Product</th>
                <th>Quantity Left</th>
                <th>Sold Quantity</th>
                <th>Manager</th>
                <th>Status</th>
                <th>Approved Price/unit</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($damages as $damage)
            <tr>
                <td>{{ $damage->id }}</td>

                <!-- Photo Column -->
                <td>
                    @if($damage->photo)
                        <a href="{{ asset('storage/damage_photos/'.basename($damage->photo)) }}" target="_blank">
                            <img src="{{ asset('storage/damage_photos/'.basename($damage->photo)) }}" width="50" class="img-thumbnail">
                        </a>
                    @else
                        —
                    @endif
                </td>

                <td>{{ ucfirst($damage->product) }}</td>
                <td>{{ $damage->quantity }}</td>
                <td>{{ $damage->sold_quantity ?? 0 }}</td>
                <td>{{ $damage->manager->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($damage->status) }}</td>
                <td>{{ $damage->approved_price ? number_format($damage->approved_price, 2) : '-' }}</td>
                <td>
                    <a href="{{ route('admin.damages.show', $damage) }}" class="btn btn-sm btn-info">View</a>

                    @if($damage->status === 'pending')
                        <form action="{{ route('admin.damages.approve', $damage) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="number" name="approved_price" placeholder="Price/unit" required class="form-control form-control-sm d-inline w-auto">
                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form action="{{ route('admin.damages.reject', $damage) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                        </form>
                    @elseif($damage->status === 'approved')
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sellModal{{ $damage->id }}">Sell</button>

                        <form action="{{ route('admin.damages.destroy', $damage) }}" method="POST" class="d-inline" 
                            onsubmit="return confirm('Are you sure you want to delete this damage? This will also remove the photo.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Damage</button>
                        </form>

                        <!-- Sell Modal -->
                        <div class="modal fade" id="sellModal{{ $damage->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.damages.sold', $damage) }}" method="POST" class="modal-content">
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
                    @elseif($damage->status === 'sold')
                        <span class="badge bg-success">Sold</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $damages->links() }}
</div>
@endsection
