@extends('admin.layouts.app')
@section('title','Driver's Bank Deposits Section')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-bank2 me-2"></i> Bank Deposits — {{ $title }}</h4>
    <form method="GET" class="d-flex align-items-center gap-2">
        <label class="me-1 small text-muted mb-0">Range</label>
        <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="today" {{ $filter == 'today' ? 'selected' : '' }}>Today</option>
            <option value="week" {{ $filter == 'week' ? 'selected' : '' }}>This week</option>
            <option value="month" {{ $filter == 'month' ? 'selected' : '' }}>This month</option>
        </select>
    </form>
</div>

<div class="mb-3">
    <div class="card p-3 shadow-sm">
        <small class="text-muted">Total Banked ({{ $title }})</small>
        <h4 class="mb-0 text-success">{{ number_format($bankedTotal,0) }} UGX</h4>
    </div>
</div>

<div class="card p-3 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Depositor</th>
                    <th class="text-end">Amount</th>
                    <th>Recorded By</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $d)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($d->deposit_date)->format('d M Y') }}</td>
                        <td>{{ $d->depositor->name ?? '-' }}</td>
                        <td class="text-end">{{ number_format($d->amount,0) }}</td>
                        <td>{{ $d->recorder->name ?? '-' }}</td>
                        <td>
                            @if($d->receipt)
                                <a href="{{ asset('storage/'.$d->receipt) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No deposits found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-2">
            {{ $deposits->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
