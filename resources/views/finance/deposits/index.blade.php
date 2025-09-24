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

<table class="table table-bordered table-hover">
    <thead class="table-light">
        <tr>
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
            <td>{{ $deposit->deposit_date }}</td>
            <td>{{ $deposit->depositor->name }}</td>
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

{{ $deposits->links() }}
@endsection
