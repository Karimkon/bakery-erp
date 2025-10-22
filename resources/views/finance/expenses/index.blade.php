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

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('finance.expenses.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" 
                                {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" name="start_date" id="start_date" 
                           value="{{ request('start_date') }}" class="form-control">
                </div>
                
                <div class="col-md-2">
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" name="end_date" id="end_date" 
                           value="{{ request('end_date') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label for="min_amount" class="form-label">Min Amount</label>
                    <input type="number" name="min_amount" id="min_amount" 
                           value="{{ request('min_amount') }}" class="form-control" 
                           placeholder="0" min="0">
                </div>

                <div class="col-md-2">
                    <label for="max_amount" class="form-label">Max Amount</label>
                    <input type="number" name="max_amount" id="max_amount" 
                           value="{{ request('max_amount') }}" class="form-control" 
                           placeholder="Any" min="0">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2 w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </div>
            
            <div class="row mt-2">
                <div class="col-12">
                    <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i> Reset Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

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

<div class="mt-3 d-flex justify-content-end">
    {{ $expenses->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endsection
