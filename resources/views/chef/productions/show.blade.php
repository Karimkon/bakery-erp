@extends('chef.layouts.app')

@section('title', 'Production Details')

@section('content')
<div class="container">
    {{-- 🔙 Back link --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-journal-text me-2"></i>
            Production Details
        </h4>
        <a href="{{ route('chef.productions.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to My Productions
        </a>
    </div>

    {{-- 📌 Production Info --}}
    <div class="card mb-4 shadow-sm border-start border-primary border-3">
        <div class="card-body">
            <p><strong>Date:</strong> {{ $production->production_date->format('d M Y') }}</p>
            <p><strong>Flour Used:</strong> {{ number_format($production->flour_bags,2) }} bags</p>
            <p>
                <strong>Total Value:</strong> UGX {{ number_format($production->total_value,0) }}
            </p>
            <p>
                <strong>Variance:</strong>
                @if($production->has_variance)
                    <span class="badge bg-danger">Variance</span>
                    <br>
                    <small class="text-muted">{{ $production->variance_notes }}</small>
                @else
                    <span class="badge bg-success">OK</span>
                @endif
            </p>
        </div>
    </div>

    {{-- 📦 Outputs --}}
    <h5 class="mb-3"><i class="bi bi-basket me-1"></i> Outputs</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price (UGX)</th>
                <th>Total (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @foreach(config('bakery_products') as $product => $price)
                @php
                    $qty = $production->$product ?? 0;
                @endphp
                @if($qty > 0)
                <tr>
                    <td>{{ ucfirst(str_replace('_',' ', $product)) }}</td>
                    <td>{{ number_format($qty) }}</td>
                    <td>{{ number_format($price) }}</td>
                    <td>{{ number_format($qty * $price) }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- 🧂 Ingredients --}}
    <h5 class="mt-4 mb-3"><i class="bi bi-droplet-half me-1"></i> Ingredients Used</h5>
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Ingredient</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Cost (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCost = 0;
            @endphp
            @forelse($production->ingredientUsages as $usage)
            @php
                $totalCost += $usage->cost;
            @endphp
            <tr>
                <td>{{ $usage->ingredient->name }}</td>
                <td>{{ number_format($usage->quantity,2) }}</td>
                <td>{{ $usage->unit }}</td>
                <td>{{ number_format($usage->cost,0) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">No ingredients recorded</td>
            </tr>
            @endforelse
            <tr class="table-light">
                <td colspan="3" class="text-end"><strong>Total Ingredients Cost</strong></td>
                <td><strong>UGX {{ number_format($totalCost,0) }}</strong></td>
            </tr>
            <tr class="table-info">
                <td colspan="3" class="text-end"><strong>Profit (Value – Cost)</strong></td>
                <td><strong>UGX {{ number_format($production->total_value - $totalCost,0) }}</strong></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
