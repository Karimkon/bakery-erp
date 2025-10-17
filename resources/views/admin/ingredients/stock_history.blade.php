@extends('admin.layouts.app')
@section('title', 'Stock History')

@section('content')
<div class="container-fluid py-3">
    <!-- Header with Stats -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-clock-history me-2 text-primary"></i> 
                Ingredient Stock History
            </h3>
            <p class="text-muted small mb-0">Track all ingredient additions and movements</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block mb-1">Total Records</small>
                            <h4 class="mb-0 fw-bold">{{ number_format($history->total()) }}</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-journal-text text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block mb-1">Today's Additions</small>
                            <h4 class="mb-0 fw-bold text-success">{{ $todayCount ?? 0 }}</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar-check text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block mb-1">This Week</small>
                            <h4 class="mb-0 fw-bold text-info">{{ $weekCount ?? 0 }}</h4>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar-week text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block mb-1">This Month</small>
                            <h4 class="mb-0 fw-bold text-warning">{{ $monthCount ?? 0 }}</h4>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar-month text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filter Buttons -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                <span class="text-muted small me-2">Quick Filters:</span>
                <a href="{{ route('admin.ingredients.stock_history', ['period' => 'today']) }}" 
                   class="btn btn-sm {{ request('period') == 'today' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-calendar-day"></i> Today
                </a>
                <a href="{{ route('admin.ingredients.stock_history', ['period' => 'yesterday']) }}" 
                   class="btn btn-sm {{ request('period') == 'yesterday' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-calendar-minus"></i> Yesterday
                </a>
                <a href="{{ route('admin.ingredients.stock_history', ['period' => 'week']) }}" 
                   class="btn btn-sm {{ request('period') == 'week' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-calendar-week"></i> This Week
                </a>
                <a href="{{ route('admin.ingredients.stock_history', ['period' => 'month']) }}" 
                   class="btn btn-sm {{ request('period') == 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-calendar-month"></i> This Month
                </a>
                <a href="{{ route('admin.ingredients.stock_history') }}" 
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Clear All
                </a>
            </div>

            <!-- Transaction Type Filter -->
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted small me-2">Transaction Type:</span>
                <a href="{{ route('admin.ingredients.stock_history', array_merge(request()->except('transaction_type'), ['transaction_type' => 'addition'])) }}" 
                   class="btn btn-sm {{ request('transaction_type') == 'addition' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="bi bi-plus-circle"></i> Additions
                </a>
                <a href="{{ route('admin.ingredients.stock_history', array_merge(request()->except('transaction_type'), ['transaction_type' => 'usage'])) }}" 
                   class="btn btn-sm {{ request('transaction_type') == 'usage' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bi bi-dash-circle"></i> Usage
                </a>
                <a href="{{ route('admin.ingredients.stock_history', array_merge(request()->except('transaction_type'), ['transaction_type' => 'adjustment'])) }}" 
                   class="btn btn-sm {{ request('transaction_type') == 'adjustment' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="bi bi-arrow-left-right"></i> Adjustments
                </a>
            </div>
        </div>
    </div>

    <!-- Advanced Filters (Collapsible) -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                <i class="bi bi-funnel me-2"></i>Advanced Filters
            </span>
            <button class="btn btn-sm btn-link text-decoration-none" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="collapse" id="advancedFilters">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.ingredients.stock_history') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-person-badge"></i> Chef
                        </label>
                        <select name="chef_id" class="form-select form-select-sm">
                            <option value="">All Chefs</option>
                            @foreach($chefs as $chef)
                                <option value="{{ $chef->id }}" {{ request('chef_id') == $chef->id ? 'selected' : '' }}>
                                    {{ $chef->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-box-seam"></i> Ingredient
                        </label>
                        <select name="ingredient_id" class="form-select form-select-sm">
                            <option value="">All Ingredients</option>
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}" {{ request('ingredient_id') == $ing->id ? 'selected' : '' }}>
                                    {{ $ing->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-calendar-range"></i> From Date
                        </label>
                        <input type="date" name="from_date" class="form-control form-control-sm" 
                               value="{{ request('from_date') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-calendar-range"></i> To Date
                        </label>
                        <input type="date" name="to_date" class="form-control form-control-sm" 
                               value="{{ request('to_date') }}">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-search"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Active Filters Display -->
    @if(request()->hasAny(['chef_id', 'ingredient_id', 'from_date', 'to_date', 'period']))
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <div class="flex-grow-1">
            <strong>Active Filters:</strong>
            @if(request('period'))
                <span class="badge bg-primary ms-2">{{ ucfirst(request('period')) }}</span>
            @endif
            @if(request('chef_id'))
                <span class="badge bg-primary ms-2">
                    Chef: {{ $chefs->find(request('chef_id'))?->name }}
                </span>
            @endif
            @if(request('ingredient_id'))
                <span class="badge bg-primary ms-2">
                    Ingredient: {{ $ingredients->find(request('ingredient_id'))?->name }}
                </span>
            @endif
            @if(request('from_date'))
                <span class="badge bg-primary ms-2">From: {{ request('from_date') }}</span>
            @endif
            @if(request('to_date'))
                <span class="badge bg-primary ms-2">To: {{ request('to_date') }}</span>
            @endif
        </div>
        <a href="{{ route('admin.ingredients.stock_history') }}" class="btn btn-sm btn-light">
            Clear All
        </a>
    </div>
    @endif

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                <i class="bi bi-table me-2"></i>Stock Additions Log 
                <span class="badge bg-secondary">{{ $history->total() }} records</span>
            </span>
            <span class="text-muted small">
                Showing {{ $history->firstItem() ?? 0 }} - {{ $history->lastItem() ?? 0 }} of {{ $history->total() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($history->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">#</th>
                            <th><i class="bi bi-box-seam me-1"></i> Ingredient</th>
                            <th><i class="bi bi-person-badge me-1"></i> Chef</th>
                            <th class="text-end"><i class="bi bi-plus-circle me-1"></i> Quantity Added</th>
                            <th><i class="bi bi-person-check me-1"></i> Added By</th>
                            <th><i class="bi bi-clock me-1"></i> Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $index => $item)
                        <tr class="transaction-row" data-url="{{ route('admin.ingredients.stock_history.show', $item->id) }}" style="cursor: pointer;" role="button" tabindex="0">
                            <td class="text-center text-muted small">{{ $history->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-semibold">{{ $item->ingredient->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-person"></i> {{ $item->chef->name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($item->transaction_type == 'usage')
                                    <span class="badge bg-danger-subtle text-danger fw-semibold px-3 py-2">
                                        -{{ number_format($item->quantity_changed, 2) }} {{ $item->ingredient->unit ?? '' }}
                                    </span>
                                @elseif($item->transaction_type == 'adjustment')
                                    <span class="badge bg-warning-subtle text-warning fw-semibold px-3 py-2">
                                        ±{{ number_format($item->quantity_changed, 2) }} {{ $item->ingredient->unit ?? '' }}
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2">
                                        +{{ number_format($item->quantity_changed, 2) }} {{ $item->ingredient->unit ?? '' }}
                                    </span>
                                @endif
                                </td>

                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-person-circle"></i> {{ $item->addedBy->name ?? 'System' }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold small">
                                        @if($item->created_at->isToday())
                                            <span class="badge bg-success-subtle text-success">Today</span>
                                        @elseif($item->created_at->isYesterday())
                                            <span class="badge bg-info-subtle text-info">Yesterday</span>
                                        @elseif($item->created_at->diffInDays() < 7)
                                            <span class="badge bg-primary-subtle text-primary">
                                                {{ $item->created_at->diffForHumans() }}
                                            </span>
                                        @else
                                            {{ $item->created_at->format('d M Y') }}
                                        @endif
                                    </span>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> {{ $item->created_at->format('H:i') }}
                                    </small>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($history->hasPages())
            <div class="card-footer bg-white border-top">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-muted small">
                        Showing {{ $history->firstItem() }} to {{ $history->lastItem() }} of {{ $history->total() }} entries
                    </div>
                    <div>
                        {{ $history->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
            @endif


            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                    </div>
                    <h5 class="text-muted mb-2">No Stock History Found</h5>
                    <p class="text-muted small">Try adjusting your filters or check back later</p>
                    @if(request()->hasAny(['chef_id', 'ingredient_id', 'from_date', 'to_date', 'period']))
                        <a href="{{ route('admin.ingredients.stock_history') }}" class="btn btn-primary btn-sm mt-3">
                            <i class="bi bi-arrow-clockwise"></i> Reset Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-semibold"><i class="bi bi-info-circle"></i> Stock Transaction Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="transactionDetails" class="p-2">
          <p class="text-muted text-center">Loading...</p>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<style>
@media print {
    .btn, .card-header button, .alert, .pagination { display: none !important; }
}

.badge {
    font-weight: 500;
}

.table tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

.form-select-sm, .form-control-sm {
    border-radius: 0.375rem;
}

.transaction-row:hover {
    background-color: rgba(0, 123, 255, 0.1) !important;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Event delegation: capture clicks on any transaction row
    document.addEventListener('click', function (e) {
        const row = e.target.closest('.transaction-row');
        if (!row) return;

        const url = row.dataset.url;
        const modalEl = document.getElementById('transactionModal');
        const modal = new bootstrap.Modal(modalEl);
        const container = document.getElementById('transactionDetails');

        container.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted">Loading transaction details...</p>
            </div>
        `;

        modal.show();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || ''
            }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.error) throw new Error(data.error);

            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <tr><th class="w-25">Ingredient</th><td>${data.ingredient?.name || 'N/A'}</td></tr>
                        <tr><th>Chef</th><td>${data.chef?.name || '—'}</td></tr>
                        <tr><th>Transaction Type</th><td>
                            <span class="badge ${getTransactionBadgeClass(data.transaction_type)}">
                                ${data.transaction_type}
                            </span>
                        </td></tr>
                        <tr><th>Quantity Before</th><td>${formatQuantity(data.quantity_before)}</td></tr>
                        <tr><th>Quantity Changed</th><td>${formatQuantity(data.quantity_changed)} ${data.ingredient?.unit || ''}</td></tr>
                        <tr><th>Quantity After</th><td>${formatQuantity(data.quantity_after)}</td></tr>
                        <tr><th>Added By</th><td>${data.added_by?.name || 'System'}</td></tr>
                        <tr><th>Date & Time</th><td>${new Date(data.created_at).toLocaleString()}</td></tr>
                        <tr><th>Notes</th><td>${data.notes || '—'}</td></tr>
                    </table>
                </div>
            `;
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="bi bi-exclamation-triangle"></i> Error Loading Details</h6>
                    <p class="mb-1">${error.message}</p>
                    <small class="text-muted">Check the browser console for more details.</small>
                </div>
            `;
        });
    });

    function getTransactionBadgeClass(type) {
        const classes = { 'addition': 'bg-success', 'usage': 'bg-danger', 'adjustment': 'bg-warning' };
        return classes[type] || 'bg-secondary';
    }

    function formatQuantity(quantity) {
        if (quantity === null || quantity === undefined) return '—';
        return Number(quantity).toFixed(2);
    }
});
</script>
@endpush
@endsection