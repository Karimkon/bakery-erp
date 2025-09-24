@extends('admin.layouts.app')
@section('title','Edit Shop Stock')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Shop Stock - Kampala Main Shop</h4>
    <a href="{{ route('admin.shop-dispatch.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.shop-dispatch.update',$shopStock) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product</label>
                    <select name="product_type" class="form-select" required>
                        @foreach($products as $p)
                            <option value="{{ $p }}" {{ $shopStock->product_type == $p ? 'selected':'' }}>
                                {{ ucfirst(str_replace('_',' ',$p)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Opening</label>
                    <input type="number" name="opening_stock" class="form-control" value="{{ $shopStock->opening_stock }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Dispatched</label>
                    <input type="number" name="dispatched" class="form-control" value="{{ $shopStock->dispatched }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Sold</label>
                    <input type="number" name="sold" class="form-control" value="{{ $shopStock->sold }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Remaining</label>
                    <input type="number" name="remaining" class="form-control" value="{{ $shopStock->remaining }}">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save2 me-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
