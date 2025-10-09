<!DOCTYPE html>
<html>
<head>
    <title>Sale Receipt</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .receipt { width: 300px; border: 1px dashed #555; padding: 15px; }
        .header { text-align: center; font-weight: bold; margin-bottom: 10px; }
        .info, .footer { font-size: 13px; }
        .footer { text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
<div class="receipt">
    <div class="header">🧁 Bakery POS Receipt</div>
    <div class="info">
        <p><strong>Cashier:</strong> {{ $sale->user->name }}</p>
        <p><strong>Item:</strong> {{ ucfirst($sale->product_type) }}</p>
        <p><strong>Quantity:</strong> {{ $sale->quantity }}</p>
        <p><strong>Unit Price:</strong> {{ number_format($sale->unit_price) }}</p>
        <p><strong>Total:</strong> <b>{{ number_format($sale->total_price) }}</b></p>
        <p><strong>Payment:</strong> {{ strtoupper($sale->payment_method) }}</p>
        <p><strong>Date:</strong> {{ $sale->created_at->format('d M Y H:i') }}</p>
    </div>
    <div class="footer">
        Thank you for your purchase! <br>
        {{ config('app.name') }}
    </div>
</div>
</body>
</html>
