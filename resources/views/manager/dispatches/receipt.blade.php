<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispatch Receipt #{{ $dispatch->dispatch_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; font-size: 12px; }
        th { background: #eee; }
        h3 { text-align: center; margin-bottom: 0; }
        .summary td { border: none; }
    </style>
</head>
<body>
    <h3>Bakery Dispatch Receipt</h3>
    <p><strong>Date:</strong> {{ $dispatch->dispatch_date->format('d M Y') }}</p>
    <p><strong>Driver:</strong> {{ $dispatch->driver->name }}</p>
    <p><strong>Dispatch #:</strong> {{ $dispatch->dispatch_no }}</p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Opening</th>
                <th>Dispatched</th>
                <th>Sold (Cash)</th>
                <th>Sold (Credit)</th>
                <th>Remaining</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($dispatch->items as $item)
            <tr>
                <td>{{ ucfirst(str_replace('_',' ', $item->product)) }}</td>
                <td>{{ $item->opening_stock }}</td>
                <td>{{ $item->dispatched_qty }}</td>
                <td>{{ $item->sold_cash }}</td>
                <td>{{ $item->sold_credit }}</td>
                <td>{{ $item->remaining_qty }}</td>
                <td>{{ number_format($item->unit_price) }}</td>
                <td>{{ number_format($item->line_total) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="summary" style="margin-top: 20px;">
        <tr><td><strong>Total Items Sold:</strong></td><td>{{ $dispatch->total_items_sold }}</td></tr>
        <tr><td><strong>Total Sales Value:</strong></td><td>UGX {{ number_format($dispatch->total_sales_value) }}</td></tr>
        <tr><td><strong>Cash Received:</strong></td><td>UGX {{ number_format($dispatch->cash_received) }}</td></tr>
        <tr><td><strong>Balance Due:</strong></td><td>UGX {{ number_format($dispatch->balance_due) }}</td></tr>
        <tr><td><strong>Commission:</strong></td><td>UGX {{ number_format($dispatch->commission_total) }}</td></tr>
    </table>

    @if($dispatch->driver_signature)
    <p><strong>Driver Signature:</strong></p>
    <img src="{{ $dispatch->driver_signature }}" width="200">
    @endif
</body>
</html>
