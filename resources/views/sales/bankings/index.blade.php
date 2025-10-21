{{-- resources/views/sales/bankings/index.blade.php --}}
@extends('sales.layouts.app')
@section('title', 'Bankings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-bank me-2"></i>Banking Records</h4>
    <a href="{{ route('sales.bankings.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> New Banking
    </a>
</div>

{{-- Cash Balance Card --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-cash-stack me-2"></i>Cash Balance</h5>
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border-end">
                            <h3 class="text-primary">UGX {{ number_format($balance['total_sales']) }}</h3>
                            <small class="text-muted">Total Sales</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h3 class="text-danger">UGX {{ number_format($balance['total_banked']) }}</h3>
                            <small class="text-muted">Total Banked</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h3 class="text-warning">UGX {{ number_format($balance['total_expenses']) }}</h3>
                            <small class="text-muted">Total Expenses</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div>
                            <h3 class="text-success">UGX {{ number_format($balance['available_cash']) }}</h3>
                            <small class="text-muted">Available Cash</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3 small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Available Cash = Total Sales - Money Banked - Expenses
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Banking Records Table --}}
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Receipt No</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bankings as $banking)
                    <tr>
                        <td>{{ $banking->date->format('M d, Y') }}</td>
                        <td>UGX {{ number_format($banking->amount) }}</td>
                        <td>{{ $banking->receipt_number ?? 'N/A' }}</td>
                        <td>{{ Str::limit($banking->notes, 30) }}</td>
                        <td>
                            <a href="{{ route('sales.bankings.show', $banking) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-bank display-4 d-block mb-2"></i>
                            No banking records found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{ $bankings->links() }}
    </div>
</div>
@endsection