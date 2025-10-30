@extends('manager.layouts.app')
@section('title', 'Kampala Dispatches')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-truck me-2"></i>Kampala Shop Dispatches</h4>
        <a href="{{ route('manager.kampala-dispatches.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>New Dispatch
        </a>
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

    <!-- Status Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Shop</label>
                    <select class="form-select" onchange="window.location.href = this.value">
                        <option value="{{ route('manager.kampala-dispatches.index') }}">All Shops</option>
                        @foreach($shops as $shop)
                            <option value="{{ route('manager.kampala-dispatches.index', ['shop' => $shop->id]) }}" 
                                    {{ request('shop') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" onchange="window.location.href = this.value">
                        <option value="{{ route('manager.kampala-dispatches.index') }}">All Status</option>
                        <option value="{{ route('manager.kampala-dispatches.index', ['status' => 'pending']) }}" 
                                {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="{{ route('manager.kampala-dispatches.index', ['status' => 'received']) }}" 
                                {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="{{ route('manager.kampala-dispatches.index', ['status' => 'partial']) }}" 
                                {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" value="{{ request('from') }}" onchange="filterDate()" id="fromDate">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" value="{{ request('to') }}" onchange="filterDate()" id="toDate">
                </div>
            </div>
        </div>
    </div>

    <!-- Dispatches Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Dispatch No</th>
                            <th>Shop</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Received By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dispatches as $dispatch)
                        <tr>
                            <td>
                                <strong>{{ $dispatch->dispatch_no }}</strong>
                                @if($dispatch->notes)
                                    <i class="bi bi-chat-text text-muted ms-1" title="{{ $dispatch->notes }}"></i>
                                @endif
                            </td>
                            <td>{{ $dispatch->shop->name }}</td>
                            <td>{{ $dispatch->dispatch_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-info">{{ number_format($dispatch->total_items) }} items</span>
                            </td>
                            <td class="fw-semibold">UGX {{ number_format($dispatch->total_value) }}</td>
                            <td>
                                @if($dispatch->status == 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i>Pending
                                    </span>
                                @elseif($dispatch->status == 'received')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Received
                                    </span>
                                @elseif($dispatch->status == 'partial')
                                    <span class="badge bg-primary">
                                        <i class="bi bi-exclamation-circle me-1"></i>Partial
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i>Rejected
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($dispatch->receiver)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-success text-white me-2">
                                            {{ substr($dispatch->receiver->name, 0, 1) }}
                                        </div>
                                        {{ $dispatch->receiver->name }}
                                        <br>
                                        <small class="text-muted">{{ $dispatch->received_at->format('M d, H:i') }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('manager.kampala-dispatches.show', $dispatch->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-truck fs-1 d-block mb-2"></i>
                                No dispatches found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-end">
        {{ $dispatches->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
}
</style>

<script>
function filterDate() {
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;
    const url = new URL(window.location.href);
    
    if (from) url.searchParams.set('from', from);
    else url.searchParams.delete('from');
    
    if (to) url.searchParams.set('to', to);
    else url.searchParams.delete('to');
    
    window.location.href = url.toString();
}
</script>
@endsection