@extends('kampala.layouts.app')
@section('title', 'Bankings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-bank me-2"></i>Banking Records</h4>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bankingModal">
        <i class="bi bi-plus-circle"></i> New Banking
    </button>
</div>

{{-- Cash Balance Card --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-cash-stack me-2"></i>Cash Balance</h5>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border-end">
                            <h3 class="text-primary">UGX {{ number_format($totalSales) }}</h3>
                            <small class="text-muted">Total Cash Sales</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-end">
                            <h3 class="text-danger">UGX {{ number_format($totalBanked) }}</h3>
                            <small class="text-muted">Total Banked</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div>
                            <h3 class="text-success">UGX {{ number_format($availableCash) }}</h3>
                            <small class="text-muted">Available Cash</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3 small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Available Cash = Total Cash Sales - Money Banked
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
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bankings as $banking)
                    <tr>
                        <td>{{ $banking->date->format('M d, Y') }}</td>
                        <td class="fw-semibold">UGX {{ number_format($banking->amount) }}</td>
                        <td>{{ $banking->receipt_number ?? 'N/A' }}</td>
                        <td>{{ Str::limit($banking->notes, 30) }}</td>
                        <td>{{ $banking->user->name }}</td>
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

<!-- Banking Modal -->
<div class="modal fade" id="bankingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Record Banking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('kampala.bankings.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount (UGX) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" min="100" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Receipt Number (optional)</label>
                        <input type="text" name="receipt_number" class="form-control" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Bank branch, transaction reference..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Banking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection