@extends('finance.layouts.app')
@section('title','Bank Deposits')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-bank me-2"></i> Bank Deposits</h4>
    <a href="{{ route('finance.deposits.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Record Deposit
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('finance.deposits.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="depositor_id" class="form-label">Depositor</label>
                    <select name="depositor_id" id="depositor_id" class="form-select">
                        <option value="">All Depositors</option>
                        @foreach($depositors as $depositor)
                            <option value="{{ $depositor->id }}" 
                                {{ request('depositor_id') == $depositor->id ? 'selected' : '' }}>
                                {{ $depositor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" 
                           value="{{ request('start_date') }}" class="form-control">
                </div>
                
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" 
                           value="{{ request('end_date') }}" class="form-control">
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('finance.deposits.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<table class="table table-bordered table-hover">
    <thead class="table-light">
        <tr>
            <th>Reciept</th>
            <th>Date</th>
            <th>Depositor</th>
            <th class="text-end">Amount (UGX)</th>
            <th>Recorded By</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($deposits as $deposit)
        <tr>
            <td>
                @if($deposit->receipt)
                    <a href="{{ asset('storage/' . $deposit->receipt) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        View
                    </a>
                @else
                    -
                @endif
            </td>

            <td>{{ $deposit->deposit_date }}</td>
            <td>{{ $deposit->depositor?->name ?? '-' }}</td>

            <td class="text-end">{{ number_format($deposit->amount) }}</td>
            <td>{{ $deposit->recorder->name }}</td>
            <td>
                <form method="POST" action="{{ route('finance.deposits.destroy',$deposit) }}">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this deposit?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center">No deposits found.</td></tr>
        @endforelse
    </tbody>
</table>


<div class="mt-3 d-flex justify-content-end">
    {{ $deposits->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endsection
