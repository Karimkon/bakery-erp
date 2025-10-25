@extends('manager.layouts.app')
@section('title', 'Driver Back Debt History')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-clock-history me-2"></i>Back Debt History - {{ $driver->name }}
    </h4>
    <div>
        <a href="{{ route('manager.dispatches.financial-details', $driver->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-graph-up"></i> Financial Details
        </a>
        <a href="{{ route('manager.dispatches.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Dispatches
        </a>
    </div>
</div>

<!-- Current Back Debt Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-2">Current Back Debt</h6>
                <h3 class="@if($driver->back_debt > 0) text-danger @elseif($driver->back_debt < 0) text-success @else text-secondary @endif">
                    {{ number_format(abs($driver->back_debt), 0) }} UGX
                </h3>
                <span class="badge @if($driver->back_debt > 0) bg-danger @elseif($driver->back_debt < 0) bg-success @else bg-secondary @endif">
                    @if($driver->back_debt > 0) Driver Owes
                    @elseif($driver->back_debt < 0) Bakery Owes
                    @else Settled @endif
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-muted mb-3">Recent Activity</h6>
                <div class="row text-center">
                    <div class="col-4">
                        <small class="text-muted d-block">Total Transactions</small>
                        <strong class="text-primary">{{ $history->total() }}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">This Month</small>
                        <strong class="text-info">
                            {{ \App\Models\DriverBackDebtTransaction::where('driver_id', $driver->id)
                                ->whereMonth('created_at', now()->month)
                                ->count() }}
                        </strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Last Update</small>
                        <strong class="text-warning">
                            {{ $history->first() ? $history->first()->created_at->diffForHumans() : 'Never' }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Transaction History</h5>
    </div>
    <div class="card-body p-0">
        @if($history->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Transaction Type</th>
                            <th>Dispatch #</th>
                            <th>Previous Balance</th>
                            <th>Adjustment</th>
                            <th>New Balance</th>
                            <th>Recorded By</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $transaction)
                        <tr>
                            <td>
                                <small class="text-muted">{{ $transaction->created_at->format('M j, Y') }}</small><br>
                                <small class="text-muted">{{ $transaction->created_at->format('g:i A') }}</small>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($transaction->transaction_type == 'dispatch_update') bg-primary
                                    @elseif($transaction->transaction_type == 'manual_adjustment') bg-warning
                                    @elseif($transaction->transaction_type == 'payment') bg-success
                                    @else bg-secondary @endif">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->dispatch)
                                    <a href="{{ route('manager.dispatches.show', $transaction->dispatch_id) }}" 
                                       class="text-decoration-none">
                                        #{{ $transaction->dispatch->dispatch_no }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="{{ $transaction->previous_balance > 0 ? 'text-danger' : ($transaction->previous_balance < 0 ? 'text-success' : '') }}">
                                {{ number_format($transaction->previous_balance, 0) }} UGX
                            </td>
                            <td class="{{ $transaction->amount_changed > 0 ? 'text-danger' : ($transaction->amount_changed < 0 ? 'text-success' : '') }}">
                                @if($transaction->amount_changed > 0)
                                    <i class="bi bi-arrow-up text-danger"></i>
                                @elseif($transaction->amount_changed < 0)
                                    <i class="bi bi-arrow-down text-success"></i>
                                @else
                                    <i class="bi bi-dash text-muted"></i>
                                @endif
                                {{ number_format(abs($transaction->amount_changed), 0) }} UGX
                            </td>
                            <td class="{{ $transaction->new_balance > 0 ? 'text-danger' : ($transaction->new_balance < 0 ? 'text-success' : '') }}">
                                <strong>{{ number_format($transaction->new_balance, 0) }} UGX</strong>
                            </td>
                            <td>
                                <small>{{ $transaction->recordedBy->name ?? 'System' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $transaction->description }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white">
                {{ $history->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-clock-history display-1 text-muted"></i>
                <h5 class="text-muted mt-3">No Back Debt History</h5>
                <p class="text-muted">This driver has no back debt transactions yet.</p>
            </div>
        @endif
    </div>
</div>

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-info-circle me-2"></i>About Back Debt</h6>
                <p class="small text-muted mb-0">
                    Back debt is automatically adjusted when drivers pay more or less than the expected amount. 
                    Positive amounts mean the driver owes money, negative amounts mean the bakery owes the driver.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-lightning me-2"></i>Automated Tracking</h6>
                <p class="small text-muted mb-0">
                    The system automatically tracks all back debt changes when dispatches are updated. 
                    Each transaction is recorded with a full audit trail.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection