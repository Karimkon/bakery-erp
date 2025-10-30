@extends('kampala.layouts.app')
@section('title', 'Dispatch Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-truck me-2"></i>Dispatch #{{ $kampalaDispatch->dispatch_no }}</h4>
        <div>
            <a href="{{ route('kampala.dispatches.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Dispatches
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Dispatch Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Dispatch Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>From Manager:</strong><br>
                    {{ $kampalaDispatch->manager->name }}
                </div>
                <div class="col-md-3">
                    <strong>Dispatch Date:</strong><br>
                    {{ $kampalaDispatch->dispatch_date->format('l, d M Y') }}
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong><br>
                    @if($kampalaDispatch->status == 'pending')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($kampalaDispatch->status == 'received')
                        <span class="badge bg-success">Received</span>
                    @elseif($kampalaDispatch->status == 'partial')
                        <span class="badge bg-primary">Partial</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Total Value:</strong><br>
                    <span class="fw-bold text-success">UGX {{ number_format($kampalaDispatch->total_value) }}</span>
                </div>
            </div>
            @if($kampalaDispatch->notes)
            <div class="row mt-3">
                <div class="col-12">
                    <strong>Notes:</strong><br>
                    {{ $kampalaDispatch->notes }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Dispatch Items -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Dispatch Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Dispatched Qty</th>
                            <th class="text-center">Received Qty</th>
                            <th class="text-center">Pending</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kampalaDispatch->items as $item)
                        <tr>
                            <td>
                                <strong>{{ ucwords(str_replace('_', ' ', $item->product_type)) }}</strong>
                                @if($item->notes)
                                    <br><small class="text-muted">{{ $item->notes }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($item->quantity) }}</td>
                            <td class="text-center">
                                <span class="fw-semibold">{{ number_format($item->received_quantity) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $item->pending_quantity > 0 ? 'warning' : 'success' }}">
                                    {{ number_format($item->pending_quantity) }}
                                </span>
                            </td>
                            <td class="text-end">UGX {{ number_format($item->unit_price) }}</td>
                            <td class="text-end fw-bold">UGX {{ number_format($item->total_price) }}</td>
                            <td>
                                @if($item->is_fully_received)
                                    <span class="badge bg-success">Fully Received</span>
                                @elseif($item->received_quantity > 0)
                                    <span class="badge bg-primary">Partial</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="1"><strong>Totals</strong></td>
                            <td class="text-center"><strong>{{ number_format($kampalaDispatch->total_items) }}</strong></td>
                            <td class="text-center">
                                <strong>{{ number_format($kampalaDispatch->items->sum('received_quantity')) }}</strong>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($kampalaDispatch->items->sum('pending_quantity')) }}</strong>
                            </td>
                            <td></td>
                            <td class="text-end"><strong>UGX {{ number_format($kampalaDispatch->total_value) }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Receive Dispatch Form -->
    @if($kampalaDispatch->status != 'received')
    <div class="card" id="receive">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Receive Dispatch</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('kampala.dispatches.receive', $kampalaDispatch->id) }}" method="POST">
                @csrf
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Dispatched Qty</th>
                                <th class="text-center">Already Received</th>
                                <th class="text-center">Receive Qty *</th>
                                <th class="text-center">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kampalaDispatch->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ ucwords(str_replace('_', ' ', $item->product_type)) }}</strong>
                                </td>
                                <td class="text-center">{{ number_format($item->quantity) }}</td>
                                <td class="text-center">{{ number_format($item->received_quantity) }}</td>
                                <td class="text-center">
                                    <input type="number" 
                                           name="received_items[{{ $item->id }}]" 
                                           class="form-control text-center" 
                                           min="0" 
                                           max="{{ $item->quantity - $item->received_quantity }}"
                                           value="{{ $item->quantity - $item->received_quantity }}"
                                           required
                                           style="max-width: 100px; margin: 0 auto;">
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ ($item->quantity - $item->received_quantity) > 0 ? 'warning' : 'success' }}">
                                        {{ number_format($item->quantity - $item->received_quantity) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Confirm Receipt
                    </button>
                    <small class="text-muted ms-2">* Enter the quantities you are receiving now</small>
                </div>
            </form>
        </div>
    </div>
    @else
    <!-- Receipt Information -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Receipt Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Received By:</strong><br>
                    {{ $kampalaDispatch->receiver->name ?? 'N/A' }}
                </div>
                <div class="col-md-4">
                    <strong>Received At:</strong><br>
                    {{ $kampalaDispatch->received_at ? $kampalaDispatch->received_at->format('l, d M Y H:i') : 'N/A' }}
                </div>
                <div class="col-md-4">
                    <strong>Completion:</strong><br>
                    @php
                        $received = $kampalaDispatch->items->sum('received_quantity');
                        $total = $kampalaDispatch->items->sum('quantity');
                        $percentage = $total > 0 ? ($received / $total) * 100 : 0;
                    @endphp
                    <div class="progress mt-1" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $percentage }}%;" 
                             aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                            {{ number_format($percentage, 1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll to receive section if URL has #receive
    if (window.location.hash === '#receive') {
        const receiveSection = document.getElementById('receive');
        if (receiveSection) {
            receiveSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Validate receive quantities
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let allZero = true;
            const inputs = this.querySelectorAll('input[type="number"]');
            
            inputs.forEach(input => {
                if (parseInt(input.value) > 0) {
                    allZero = false;
                }
            });
            
            if (allZero) {
                e.preventDefault();
                alert('Please enter quantities for at least one item.');
            }
        });
    }
});
</script>
@endsection