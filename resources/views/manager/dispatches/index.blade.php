@extends('manager.layouts.app')
@section('title', 'Driver Dispatches')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-truck me-2"></i> Driver Dispatches (Latest Only)</h4>
    <a href="{{ route('manager.dispatches.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> New Dispatch
    </a>
</div>

<form method="GET" class="mb-3">
    <div class="input-group" style="max-width: 300px;">
        <input type="text" name="driver" class="form-control" placeholder="Search by driver" value="{{ $searchDriver }}">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
    </div>
</form>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive">
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>Date</th>
            <th>Dispatch #</th>
            <th>Driver</th>
            <th>Items Sold</th>
            <th>Total Sales (UGX)</th>
            <th>Cash Received (UGX)</th>
            <th>Back Debt</th>
            <th>Balance Due (UGX)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($dispatches as $d)
        <tr>
            <td>{{ \Carbon\Carbon::parse($d->dispatch_date)->format('d M Y') }}</td>
            <td>{{ $d->dispatch_no }}</td>
            <td>{{ $d->driver?->name }}</td>
            <td>{{ number_format($d->total_items_sold) }}</td>
            <td>{{ number_format($d->total_sales_value, 0) }}</td>
            <td>{{ number_format($d->cash_received, 0) }}</td>
            <td>
    <div class="d-flex gap-1">
        <span class="badge @if($d->driver->back_debt > 0) bg-danger @elseif($d->driver->back_debt < 0) bg-success @else bg-secondary @endif">
            {{ number_format($d->driver->back_debt, 0) }} UGX
        </span>
        <a href="{{ route('manager.dispatches.back-debt-history', $d->driver->id) }}" 
           class="btn btn-sm btn-outline-info" 
           title="View Back Debt History">
            <i class="bi bi-clock-history"></i>
        </a>
    </div>
</td>
            
           @php
                $remainingInventoryValue = $d->items->sum(fn($i) => $i->remaining_qty * $i->unit_price);
                $driverBackDebt = $d->driver?->back_debt ?? 0;
                $creditSalesValue = $d->items->sum(fn($i) => $i->sold_credit * $i->unit_price);
                $totalBalanceDue = $remainingInventoryValue + $creditSalesValue + $driverBackDebt;
            @endphp

            <td>{{ number_format($totalBalanceDue, 0) }}</td>




            <td class="d-flex gap-2">
                <a href="{{ route('manager.dispatches.show',$d->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> View
                </a>
                 <a href="{{ route('manager.dispatches.edit',$d->id) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil-square"></i> Update
                </a>
                <a href="{{ route('manager.dispatches.history', $d->driver_id) }}" 
                class="btn btn-sm btn-secondary">
                    <i class="bi bi-clock-history"></i> History
                </a>
                <button class="btn btn-success btn-sm sendWhatsApp"
                    data-driver="{{ $d->driver->name }}"
                    data-phone="{{ $d->driver->phone }}"
                    data-items='@json($d->items)'
                    data-date="{{ $d->dispatch_date->format('d M Y') }}">
                    <i class="bi bi-whatsapp"></i> Send to Driver
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No dispatches found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.sendWhatsApp').forEach(btn => {
    btn.addEventListener('click', () => {
        const driver = btn.dataset.driver;
        const phone = btn.dataset.phone;
        const date = btn.dataset.date;
        const items = JSON.parse(btn.dataset.items);

        let message = `*Bakery Dispatch - ${date}*\n`;
        message += `Driver: ${driver}\n\n`;
        message += `Items Dispatched:\n`;

        items.forEach(item => {
            message += `• ${item.product.replace(/_/g,' ')} - ${item.dispatched_qty} pcs @ ${item.unit_price} = ${item.line_total}\n`;
        });

        message += `\n✅ Total Sales: UGX ${Number(items.reduce((t, i) => t + i.line_total, 0)).toLocaleString()}\n`;
        message += `\nThank you & safe delivery! 🚚`;

        // Remove + or 0 if user stores number as 07...
        let cleanedPhone = phone.replace(/^0/, '256'); // replace starting 0 with country code
        if (!cleanedPhone.startsWith('256')) cleanedPhone = '256' + cleanedPhone;

        // Encode and open WhatsApp
        const url = `https://wa.me/${cleanedPhone}?text=${encodeURIComponent(message)}`;
        window.open(url, '_blank');
    });
});
</script>
@endpush