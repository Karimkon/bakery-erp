@extends('admin.layouts.app')
@section('title','Dispatch Stock to Shop')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Dispatch Stock - Kampala Main Shop</h4>
    <a href="{{ route('admin.shop-dispatch.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.shop-dispatch.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product</label>
                    <select name="product_type" class="form-select" required>
                        <option value="">-- Select --</option>
                        @foreach($products as $key => $label)
                            <option value="{{ is_numeric($key) ? $label : $key }}">{{ is_numeric($key) ? ucfirst($label) : $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save2 me-1"></i> Save Dispatch
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
