@extends('chef.layouts.app')

@section('title','My Productions')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-1"><i class="bi bi-person-circle me-2"></i> Chef: <strong>{{ Auth::user()->name }}</strong></h4>
        <h4><i class="bi bi-journal-text me-2"></i> My Productions</h4>
        <a href="{{ route('chef.productions.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Production
        </a>
    </div>

    <!-- Add Date Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter by Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date ?? now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('chef.productions.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Show date info -->
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Showing productions for: <strong>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong>
        | Total: <strong>{{ $productions->total() }} records</strong>
    </div>

    <div class="table-responsive shadow-sm">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Chef</th>
                    <th>Flour</th>
                    <th>Outputs</th>
                    <th>Total (UGX)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productions as $p)
                <tr>
                    <td>{{ $p->production_date->format('d M Y') }}</td>
                    <td>{{ $p->chef->name ?? 'N/A' }}</td>
                    <td>{{ $p->flour_bags }} bags</td>
                    <td>
                        @php
                            $outputs = [];
                            foreach (config('bakery_products') as $product => $price) {
                                if ($p->$product > 0) {
                                    $outputs[] = ucfirst(str_replace('_', ' ', $product)) . ': ' . $p->$product;
                                }
                            }
                        @endphp
                        {{ implode(', ', $outputs) }}
                    </td>
                    <td>{{ number_format($p->total_value) }}</td>
                    <td>
                        <span class="badge bg-{{ $p->status === 'approved' ? 'success' : ($p->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('chef.productions.show', $p->id) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-2 mb-0">No productions found for this date</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($productions->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $productions->appends(['date' => $date])->links('pagination::bootstrap-5') }}
    </div>
    @endif
@endsection