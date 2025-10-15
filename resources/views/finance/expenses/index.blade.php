@extends('finance.layouts.app')

@section('title','Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-wallet2 me-2"></i> Expenses</h4>
    <a href="{{ route('finance.expenses.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Add Expense
    </a>

    <a href="{{ route('finance.overview') }}" class="btn btn-success btn-sm">
        <i class="bi bi-graph-up"></i> View Overview
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered table-hover">
    <thead class="table-light">
        <tr>
            <th>Date</th>
            <th>Category</th>
            <th>Description</th>
            <th class="text-end">Amount (UGX)</th>
            <th>Receipt</th>
            <th>Recorded By</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($expenses as $expense)
        <tr>
            <td>{{ $expense->expense_date }}</td>
            <td>{{ $expense->category }}</td>
            <td>{{ $expense->description }}</td>
            <td class="text-end">{{ number_format($expense->amount) }}</td>
            <td>
                @if($expense->receipt)
                    <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        View
                    </a>
                @else
                    -
                @endif
            </td>
            <td>{{ $expense->recorder->name }}</td>
            <td>
                <form action="{{ route('finance.expenses.destroy',$expense) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center">No expenses found.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $expenses->links() }}
@endsection
