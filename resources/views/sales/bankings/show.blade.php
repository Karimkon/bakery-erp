{{-- resources/views/sales/bankings/show.blade.php --}}
@extends('sales.layouts.app')
@section('title', 'Banking Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-eye me-2"></i>Banking Details</h4>
    <div>
        <a href="{{ route('sales.bankings.edit', $banking) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('sales.bankings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title border-bottom pb-2">Banking Information</h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Amount</label>
                        <div class="fs-4 text-primary">UGX {{ number_format($banking->amount) }}</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date</label>
                        <div class="fs-6">{{ $banking->date->format('F d, Y') }}</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Receipt Number</label>
                        <div class="fs-6">{{ $banking->receipt_number ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Created</label>
                        <div class="fs-6">{{ $banking->created_at->format('M d, Y \\a\\t h:i A') }}</div>
                    </div>
                    
                    @if($banking->notes)
                    <div class="col-12">
                        <label class="form-label fw-bold">Notes</label>
                        <div class="border rounded p-3 bg-light">
                            {{ $banking->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        {{-- Receipt Preview --}}
        @if($banking->receipt_file)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-receipt me-2"></i>Receipt</h6>
                
                @if(in_array(pathinfo($banking->receipt_file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                    {{-- Image receipt --}}
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $banking->receipt_file) }}" 
                             alt="Banking Receipt" 
                             class="img-fluid rounded border" 
                             style="max-height: 300px;">
                        <div class="mt-2">
                            <a href="{{ asset('storage/' . $banking->receipt_file) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-zoom-in"></i> View Full Size
                            </a>
                        </div>
                    </div>
                @else
                    {{-- PDF receipt --}}
                    <div class="text-center">
                        <i class="bi bi-file-earmark-pdf display-4 text-danger"></i>
                        <div class="mt-2">
                            <a href="{{ asset('storage/' . $banking->receipt_file) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-download"></i> Download PDF
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif
        
        {{-- Actions Card --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title">Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('sales.bankings.edit', $banking) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i>Edit Record
                    </a>
                    
                    {{-- Delete Form --}}
                    <form action="{{ route('sales.bankings.destroy', $banking) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this banking record?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-2"></i>Delete Record
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Navigation --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <a href="{{ route('sales.bankings.create') }}" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> New Banking
                </a>
                <a href="{{ route('sales.bankings.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list-ul"></i> All Bankings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection