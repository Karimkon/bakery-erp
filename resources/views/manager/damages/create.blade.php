@extends('manager.layouts.app')
@section('title','Report Damage')

@section('content')
<h4>Report Damaged Product</h4>

<form action="{{ route('manager.damages.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label class="form-label">Product</label>
        <select name="product" class="form-control" required>
            @foreach($products as $p)
                <option value="{{ $p }}">{{ ucfirst(str_replace('_',' ',$p)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" class="form-control" min="1" value="1" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Notes (optional)</label>
        <textarea name="notes" class="form-control" rows="2"></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Photo (optional)</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
    </div>

    <button type="submit" class="btn btn-success">Report Damage</button>
</form>
@endsection
