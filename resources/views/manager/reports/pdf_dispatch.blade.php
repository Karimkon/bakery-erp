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
    $total = $dispatches->reduce(function($carry,$item){
        $carry['dispatched'] += $item->dispatched_qty;
        $carry['cash']       += $item->sold_cash;
        $carry['credit']     += $item->sold_credit;
        $carry['remaining']  += $item->remaining_qty;
        $carry['total']      += $item->line_total;
        return $carry;
    }, ['dispatched'=>0,'cash'=>0,'credit'=>0,'remaining'=>0,'total'=>0]);
@endphp
<table>
    <tr><td>Total Dispatched</td><td>{{ $total['dispatched'] }}</td></tr>
    <tr><td>Total Sold (Cash)</td><td>{{ $total['cash'] }}</td></tr>
    <tr><td>Total Sold (Credit)</td><td>{{ $total['credit'] }}</td></tr>
    <tr><td>Total Remaining</td><td>{{ $total['remaining'] }}</td></tr>
    <tr><td>Total Value</td><td>{{ number_format($total['total'],2) }}</td></tr>
</table>
<table>
    <thead>
        <tr>
            <th>#</th><th>Date</th><th>Driver</th><th>Product</th>
            <th>Dispatched</th><th>Sold Cash</th><th>Sold Credit</th><th>Remaining</th><th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dispatches as $row)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row->dispatch->dispatch_date ?? '' }}</td>
            <td>{{ $row->dispatch->driver->name ?? '' }}</td>
            <td>{{ $row->product }}</td>
            <td>{{ $row->dispatched_qty }}</td>
            <td>{{ $row->sold_cash }}</td>
            <td>{{ $row->sold_credit }}</td>
            <td>{{ $row->remaining_qty }}</td>
            <td>{{ number_format($row->line_total,2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
