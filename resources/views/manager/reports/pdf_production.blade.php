<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Production Report</title>
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
<h3>Production Report</h3>

@php
    $totalProduced = $items->sum('produced_qty');
    $totalUsed     = $items->sum('used_qty');
    $totalRemaining= $items->sum('remaining_qty');
@endphp

{{-- Summary --}}
<table>
    <tr><td>Total Produced</td><td>{{ $totalProduced }}</td></tr>
    <tr><td>Total Used</td><td>{{ $totalUsed }}</td></tr>
    <tr><td>Total Remaining</td><td>{{ $totalRemaining }}</td></tr>
</table>

{{-- Details --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Product</th>
            <th>Produced</th>
            <th>Used</th>
            <th>Remaining</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $row)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ \Carbon\Carbon::parse($row->production_date)->format('d/m/Y') }}</td>
            <td>{{ ucfirst(str_replace('_',' ',$row->product)) }}</td>
            <td>{{ $row->produced_qty }}</td>
            <td>{{ $row->used_qty }}</td>
            <td>{{ $row->remaining_qty }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
