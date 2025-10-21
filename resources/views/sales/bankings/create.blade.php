{{-- resources/views/sales/bankings/create.blade.php --}}
@extends('sales.layouts.app')
@section('title','Record Banking')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-bank me-2"></i>Record Banking</h4>
        <a href="{{ route('sales.bankings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Add this to the top of your create.blade.php --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>Current Available Cash:</strong> 
                    UGX {{ number_format($balance['available_cash']) }}
                </div>
                <small>
                    Total Sales: UGX {{ number_format($balance['total_sales']) }} | 
                    Banked: UGX {{ number_format($balance['total_banked']) }} | 
                    Expenses: UGX {{ number_format($balance['total_expenses']) }}
                </small>
            </div>
        </div>
    </div>
</div>
            <form method="POST" action="{{ route('sales.bankings.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Amount (UGX)</label>
                        <input type="number" name="amount" class="form-control" min="100" step="0.01" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Receipt Number (optional)</label>
                        <input type="text" name="receipt_number" class="form-control" maxlength="50">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Upload Receipt (jpg, png, pdf; max 2MB)</label>
                        <input type="file" name="receipt_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Branch, bank slip ref, comment…"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button class="btn btn-primary"><i class="bi bi-save2 me-1"></i> Save Banking</button>
                </div>
            </form>
        </div>
    </div>
@endsection
