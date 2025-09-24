{{-- resources/views/sales/bankings/edit.blade.php --}}
@extends('sales.layouts.app')
@section('title','Edit Banking')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Banking</h4>
        <a href="{{ route('sales.bankings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('sales.bankings.update',$banking) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Amount (UGX)</label>
                        <input type="number" name="amount" class="form-control" min="100" step="0.01" value="{{ old('amount',$banking->amount) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', optional($banking->date)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Receipt Number (optional)</label>
                        <input type="text" name="receipt_number" class="form-control" maxlength="50" value="{{ old('receipt_number',$banking->receipt_number) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Replace Receipt (jpg, png, pdf; max 2MB)</label>
                        <input type="file" name="receipt_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        @if($banking->receipt_file)
                            <div class="mt-2">
                                <a href="{{ asset('storage/'.$banking->receipt_file) }}" target="_blank" class="small">
                                    <i class="bi bi-file-earmark-text me-1"></i>Current receipt
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes',$banking->notes) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button class="btn btn-primary"><i class="bi bi-save2 me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection