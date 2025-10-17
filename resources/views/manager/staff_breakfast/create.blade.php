@extends('manager.layouts.app')
@section('title','Request Staff Breakfast')

@section('content')
<h4>Request Staff Breakfast</h4>

<form action="{{ route('manager.staff_breakfast.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label class="form-label">Product</label>
        <select name="product" class="form-control" required>
            @foreach($products as $product)
                <option value="{{ $product }}">{{ ucfirst(str_replace('_',' ',$product)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" class="form-control" min="1" value="1" required>
    </div>


    <button type="submit" class="btn btn-success">Submit Request</button>
</form>
@endsection
