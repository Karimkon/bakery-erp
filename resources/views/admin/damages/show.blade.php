@extends('admin.layouts.app')

@section('title', 'Damage Details')

@section('content')
<div class="container">
    <h2>Damage Details #{{ $damage->id }}</h2>

    <div class="card p-4 mb-3">
        <div class="row">
            <div class="col-md-6">
                <h5>Product:</h5> {{ ucfirst($damage->product) }}
                <h5>Quantity Left:</h5> {{ $damage->quantity }}
                <h5>Sold Quantity:</h5> {{ $damage->sold_quantity ?? 0 }}
                <h5>Manager:</h5> {{ $damage->manager->name ?? 'N/A' }}
                <h5>Status:</h5> {{ ucfirst($damage->status) }}
                @if($damage->notes)
                    <h5>Notes:</h5> {{ $damage->notes }}
                @endif
            </div>
            <div class="col-md-6">
                @if($damage->photo)
                    <h5>Photo:</h5>
                    <img src="{{ asset('storage/' . $damage->photo) }}" class="img-fluid rounded shadow-sm">
                @else
                    <p>No photo uploaded</p>
                @endif
            </div>
        </div>

        @if($damage->status === 'pending')
            <div class="mt-4">
                <form action="{{ route('admin.damages.approve', $damage) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="number" name="approved_price" placeholder="Approved Price" class="form-control mb-2" required>
                    <button type="submit" class="btn btn-success">Approve</button>
                </form>
                <form action="{{ route('admin.damages.reject', $damage) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Reject</button>
                </form>
            </div>
        @elseif($damage->status === 'approved')
            <div class="mt-4">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sellModal">Sell</button>

                <!-- Modal same as index modal -->
            </div>
        @endif
    </div>

    <a href="{{ route('admin.damages.index') }}" class="btn btn-secondary mt-3">Back to Damages</a>
</div>
@endsection
