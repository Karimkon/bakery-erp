@extends('layouts.app')

@section('content')
<div class="container">
    <h3>🍞 Daily Summary for {{ $today }}</h3>
    <p><b>Total Sales:</b> {{ number_format($totalAll) }}</p>
    <p><b>Cash:</b> {{ number_format($totalCash) }} | <b>Mobile Money:</b> {{ number_format($totalMomo) }}</p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($sales as $s)
            <tr>
                <td>{{ ucfirst($s->product_type) }}</td>
                <td>{{ $s->quantity }}</td>
                <td>{{ number_format($s->total_price) }}</td>
                <td>{{ strtoupper($s->payment_method) }}</td>
                <td>{{ $s->created_at->format('H:i') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
