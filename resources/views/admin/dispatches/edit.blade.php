@extends('admin.layouts.app')
@section('title', 'Edit Dispatch')

@section('content')
<h4 class="mb-3"><i class="bi bi-truck me-2"></i> Edit Driver Dispatch</h4>

<form method="POST" action="{{ route('admin.dispatches.update',$dispatch->id) }}">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Driver</label>
            <select name="driver_id" class="form-select" required>
                <option value="">-- Select Driver --</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" 
                        {{ old('driver_id',$dispatch->driver_id)==$driver->id ? 'selected':'' }}>
                        {{ $driver->name }}
                    </option>
                @endforeach
            </select>
            @error('driver_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="dispatch_date" class="form-control"
                   value="{{ old('dispatch_date',$dispatch->dispatch_date->toDateString()) }}" required>
            @error('dispatch_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes',$dispatch->notes) }}</textarea>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="mb-3"><i class="bi bi-basket2 me-1"></i> Items</h5>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Opening</th>
                    <th>Dispatched</th>
                    <th>Sold (Cash)</th>
                    <th>Sold (Credit)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product => $price)
                @php
                    $row = $dispatch->items->firstWhere('product',$product);
                @endphp
                <tr>
                    <td>
                        {{ ucfirst(str_replace('_',' ',$product)) }}
                        <div class="text-muted small">UGX {{ number_format($price) }}</div>
                    </td>
                    <td>
                        <input type="number" class="form-control"
                               name="items[{{ $product }}][opening_stock]" 
                               value="{{ old("items.$product.opening_stock",$row->opening_stock ?? 0) }}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control"
                               name="items[{{ $product }}][dispatched_qty]"
                               value="{{ old("items.$product.dispatched_qty",$row->dispatched_qty ?? 0) }}">
                    </td>
                    <td>
                        <input type="number" class="form-control"
                               name="items[{{ $product }}][sold_cash]"
                               value="{{ old("items.$product.sold_cash",$row->sold_cash ?? 0) }}">
                    </td>
                    <td>
                        <input type="number" class="form-control"
                               name="items[{{ $product }}][sold_credit]"
                               value="{{ old("items.$product.sold_credit",$row->sold_credit ?? 0) }}">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <button class="btn btn-success mt-3"><i class="bi bi-save"></i> Update Dispatch</button>
</form>
@endsection
