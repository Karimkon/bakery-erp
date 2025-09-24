@extends('finance.layouts.app')
@section('title','Record Bank Deposit')

@section('content')
<h4 class="mb-3"><i class="bi bi-plus-circle me-2"></i> New Deposit</h4>

<form method="POST" action="{{ route('finance.deposits.store') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">Depositor</label>
        <select name="user_id" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Amount (UGX)</label>
        <input type="number" step="0.01" name="amount" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Deposit Date</label>
        <input type="date" name="deposit_date" class="form-control" required>
    </div>

    <button class="btn btn-success">Save Deposit</button>
    <a href="{{ route('finance.deposits.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
