@extends('kampala.layouts.app')
@section('title', 'Dispatches')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-truck me-2"></i>Dispatches</h4>
</div>

<!-- Status Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" onchange="window.location.href = this.value">
                    <option value="{{ route('kampala.dispatches.index') }}">All Status</option>
                    <option value="{{ route('kampala.dispatches.index', ['status' => 'pending']) }}" 
                            {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="{{ route('kampala.dispatches.index', ['status' => 'received']) }}" 
                            {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="{{ route('kampala.dispatches.index', ['status' => 'partial']) }}" 
                            {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" value="{{ request('from') }}" onchange="filterDate()" id="fromDate">
            </div>
            <div class="col-md-4">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" value="{{ request('to') }}" onchange="filterDate()" id="toDate">
            </div>
        </div>
    </div>
</div>

<!-- Dispatches Table -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Dispatch No</th>
                        <th>Date</th>
                        <th>From Manager</th>
                        <th class="text-center">Items</th>
                        <th class="text-end">Total Value</th>
                        <th>Status</th>
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
                        <td>{{ $dispatch->dispatch_date->format('M d, Y') }}</td>
                        <td>{{ $dispatch->manager->name }}</td>
                        <td class="text-center">{{ number_format($dispatch->total_items) }}</td>
                        <td class="text-end fw-semibold">UGX {{ number_format($dispatch->total_value) }}</td>
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
                            @endif
                        </td>
                        <td>
    @if($dispatch->status == 'pending')
        <span class="badge bg-warning text-dark">
            <i class="bi bi-clock me-1"></i>Pending
        </span>
        <br>
        <small class="text-muted">Can be deleted</small>
    @elseif($dispatch->status == 'received')
        <span class="badge bg-success">
            <i class="bi bi-check-circle me-1"></i>Received
        </span>
        <br>
        <small class="text-muted">Cannot delete</small>
    @elseif($dispatch->status == 'partial')
        <span class="badge bg-primary">
            <i class="bi bi-exclamation-circle me-1"></i>Partial
        </span>
    @else
        <span class="badge bg-secondary">{{ $dispatch->status }}</span>
    @endif
</td>
<td>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('kampala.dispatches.show', $dispatch->id) }}" 
           class="btn btn-outline-primary">
            <i class="bi bi-eye"></i>
        </a>
        
        @if($dispatch->status == 'pending')
            <a href="{{ route('kampala.dispatches.show', $dispatch->id) }}#receive" 
               class="btn btn-success">
                <i class="bi bi-check-circle"></i>
            </a>
            
            <form action="{{ route('kampala.dispatches.destroy', $dispatch->id) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Delete this pending dispatch and restore bakery stock?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        @endif
    </div>
</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-truck display-4 d-block mb-2"></i>
                            No dispatches found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{ $dispatches->links() }}
    </div>
</div>

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