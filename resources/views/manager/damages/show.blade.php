@extends('manager.layouts.app')

@section('title', 'Damage Details')

@section('content')
<div class="container">
    <h2 class="mb-4">Damage Details</h2>

    <div class="card">
        <div class="card-header">
            <strong>Product:</strong> {{ $damage->product }}
            <span class="float-end"><strong>Status:</strong> {{ ucfirst($damage->status) }}</span>
        </div>
        <div class="card-body">
            <p><strong>Quantity:</strong> {{ $damage->quantity }}</p>
            <p><strong>Reported By:</strong> {{ $damage->manager->name ?? 'N/A' }}</p>
            <p><strong>Approved Price:</strong> 
                {{ $damage->approved_price ? number_format($damage->approved_price, 2) : '-' }}
            </p>
            <p><strong>Notes:</strong> {{ $damage->notes ?? '-' }}</p>

            @if($damage->photo)
                <p><strong>Photo:</strong></p>
                <img src="{{ asset('storage/' . $damage->photo) }}" alt="Damage Photo" class="img-fluid rounded" style="max-width:300px;">
            @endif

            @if($damage->status === 'approved')
                <p class="mt-3 text-info">This damage is approved by admin and can be marked as sold.</p>
            @elseif($damage->status === 'sold')
                <p class="mt-3 text-success">This damage has been sold.</p>
            @elseif($damage->status === 'rejected')
                <p class="mt-3 text-danger">This damage was rejected by admin.</p>
            @endif
        </div>
        <div class="card-footer">
            <a href="{{ route('manager.damages.index') }}" class="btn btn-secondary">Back to Damages</a>
        </div>
    </div>
</div>
@endsection
