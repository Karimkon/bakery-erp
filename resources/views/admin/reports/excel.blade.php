{{-- resources/views/admin/reports/excel.blade.php --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Dispatch Date</th>
            <th>Driver</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->dispatch->dispatch_date ?? '' }}</td>
                <td>{{ $item->dispatch->driver->name ?? '' }}</td>
                <td>{{ $item->product }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->amount }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
