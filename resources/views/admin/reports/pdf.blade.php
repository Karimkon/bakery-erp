<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispatch Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f0f0f0; }
        .summary td { font-weight: bold; background-color: #e9ecef; }
        h3 { margin-bottom: 10px; }
    </style>
</head>
<body>
<h3>Dispatch Report</h3>

@php
    $sumDispatched = $sumCash = $sumCredit = $sumRemaining = $sumTotal = 0;
    foreach($items as $row) {
        $sumDispatched += $row->dispatched_qty;
        $sumCash       += $row->sold_cash;
        $sumCredit     += $row->sold_credit;
        $sumRemaining  += $row->remaining_qty;
        $sumTotal      += $row->line_total;
    }
@endphp

{{-- Summary Section --}}
<table>
    <tr>
        <td>Total Dispatched</td>
        <td>{{ number_format($sumDispatched) }}</td>
    </tr>
    <tr>
        <td>Total Sold (Cash)</td>
        <td>{{ number_format($sumCash) }}</td>
    </tr>
    <tr>
        <td>Total Sold (Credit)</td>
        <td>{{ number_format($sumCredit) }}</td>
    </tr>
    <tr>
        <td>Total Remaining</td>
        <td>{{ number_format($sumRemaining) }}</td>
    </tr>
    <tr>
        <td>Total Value (UGX)</td>
        <td>{{ number_format($sumTotal, 2) }}</td>
    </tr>
</table>

{{-- Details Table --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Driver</th>
            <th>Product</th>
            <th>Dispatched</th>
            <th>Sold Cash</th>
            <th>Sold Credit</th>
            <th>Remaining</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->dispatch->dispatch_date ?? '' }}</td>
                <td>{{ $row->dispatch->driver->name ?? '' }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$row->product)) }}</td>
                <td>{{ $row->dispatched_qty }}</td>
                <td>{{ $row->sold_cash }}</td>
                <td>{{ $row->sold_credit }}</td>
                <td>{{ $row->remaining_qty }}</td>
                <td>{{ number_format($row->unit_price, 2) }}</td>
                <td>{{ number_format($row->line_total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
