@extends('admin.layouts.app')
@section('title','Bankings Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-bank me-2"></i> Bankings Report</h4>
</div>

{{-- 🔎 Filters --}}
<form method="GET" class="card shadow-sm mb-4">
    <div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Sales Person</label>
            <select name="user_id" class="form-select">
                <option value="">-- All --</option>
                @foreach($salesUsers as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
        </div>
    </div>
</form>

{{-- 📊 Summary --}}
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-start border-success border-3">
            <div class="card-body">
                <div class="text-muted">Total Banked</div>
                <div class="fs-4 fw-bold text-success">
                    UGX {{ number_format($summary['total'] ?? 0) }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-start border-primary border-3">
            <div class="card-body">
                <div class="text-muted">Transactions</div>
                <div class="fs-4 fw-bold">
                    {{ number_format($summary['count'] ?? 0) }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 📑 Table --}}
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Sales Person</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Receipt No</th>
                    <th>Notes</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bankings as $b)
                    <tr>
                        <td>{{ $b->id }}</td>
                        <td>{{ optional($b->user)->name ?? 'N/A' }}</td>
                        <td class="fw-semibold text-success">
                            UGX {{ number_format($b->amount, 0) }}
                        </td>
                        <td>{{ $b->date?->format('Y-m-d') ?? '-' }}</td>
                        <td>{{ $b->receipt_number ?? '-' }}</td>
                        <td>{{ $b->notes ?? '-' }}</td>
                        <td>
                            @if(!empty($b->receipt_file))
                                <a href="{{ asset('storage/'.$b->receipt_file) }}" 
                                   target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-paperclip"></i> View
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No banking records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $bankings->links() }}
    </div>
</div>
@endsection
