@extends('admin.layouts.app')
@section('title', 'Edit Dispatch')

@section('content')
<h4 class="mb-3"><i class="bi bi-truck me-2"></i> Edit Driver Dispatch</h4>

<form method="POST" action="{{ route('admin.dispatches.update',$dispatch->id) }}">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Driver</label>
            <select id="driver_id" name="driver_id" class="form-select select2" required>
                <option value="">-- Select Driver --</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}"
                        {{ old('driver_id',$dispatch->driver_id)==$driver->id ? 'selected':'' }}>
                        {{ $driver->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="dispatch_date" class="form-control"
                   value="{{ old('dispatch_date',$dispatch->dispatch_date->toDateString()) }}" required>
        </div>

        <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes',$dispatch->notes) }}</textarea>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="mb-3"><i class="bi bi-basket2 me-1"></i> Items</h5>
    <p class="text-muted small">
        Opening, Remaining and Commission auto-update after typing or changing driver/date.
    </p>

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Opening</th>
                    <th>Dispatched</th>
                    <th>Sold (Cash)</th>
                    <th>Sold (Credit)</th>
                    <th>Remaining</th>
                    <th>Commission</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product => $price)
                @php
                    $row = $dispatch->items->firstWhere('product',$product);
                    $opening    = $row?->opening_stock ?? 0;
                    $dispatched = $row?->dispatched_qty ?? 0;
                    $soldCash   = $row?->sold_cash ?? 0;
                    $soldCredit = $row?->sold_credit ?? 0;
                    $remaining  = ($opening + $dispatched) - ($soldCash + $soldCredit);
                    $commission = $row?->commission ?? 0;
                @endphp
                <tr data-product="{{ $product }}">
    <td>
        {{ ucfirst(str_replace('_',' ',$product)) }}
        <div class="text-muted small">UGX {{ number_format($price) }}</div>
    </td>

    <!-- Opening Stock (readonly input so it's visible + stored) -->
    <td>
        <input type="number" class="form-control opening-stock"
               name="items[{{ $product }}][opening_stock]"
               value="{{ old("items.$product.opening_stock", $opening) }}" readonly>
    </td>

    <!-- Dispatched -->
    <td>
        <input type="number" class="form-control"
               name="items[{{ $product }}][dispatched_qty]"
               value="{{ old("items.$product.dispatched_qty", $dispatched) }}">
    </td>

    <!-- Sold (Cash) -->
    <td>
        <input type="number" class="form-control"
               name="items[{{ $product }}][sold_cash]"
               value="{{ old("items.$product.sold_cash", $soldCash) }}">
    </td>

    <!-- Sold (Credit) -->
    <td>
        <input type="number" class="form-control"
               name="items[{{ $product }}][sold_credit]"
               value="{{ old("items.$product.sold_credit", $soldCredit) }}">
    </td>

    <!-- Remaining -->
    <td class="remaining-col">{{ $remaining }}</td>

    <!-- Commission -->
    <td class="commission-col">{{ number_format($commission,0) }}</td>
</tr>

                @endforeach
            </tbody>
        </table>
    </div>

    <button class="btn btn-success mt-3">
        <i class="bi bi-save"></i> Update Dispatch
    </button>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('.select2').select2({ placeholder: 'Search driver' });

    function recomputeRow(row) {
        let opening    = parseInt(row.find('.opening-stock').text()) || 0;
        let dispatched = parseInt(row.find('input[name*="[dispatched_qty]"]').val()) || 0;
        let soldCash   = parseInt(row.find('input[name*="[sold_cash]"]').val()) || 0;
        let soldCredit = parseInt(row.find('input[name*="[sold_credit]"]').val()) || 0;

        let remaining = (opening + dispatched) - (soldCash + soldCredit);
        row.find('.remaining-col').text(remaining);

        // Commission quick calc (just estimate on frontend)
        let product = row.data('product');
        let soldQty = soldCash + soldCredit;
        let rates = @json(config('commissions.rates'));
        let threshold = @json(config('commissions.threshold'));

        let totalValue = 0;
        $('tbody tr').each(function(){
            let op = parseInt($(this).find('.opening-stock').text())||0;
            let dis = parseInt($(this).find('input[name*="[dispatched_qty]"]').val())||0;
            let unitPrice = parseInt($(this).find('td div.text-muted').text().replace(/\D/g,''))||0;
            totalValue += (op+dis)*unitPrice;
        });

        let multiplier = (totalValue >= threshold) ? 1.0 : 0.5;
        let rate = rates[product] ?? 0;
        let commission = soldQty * (rate * multiplier);

        row.find('.commission-col').text(commission.toLocaleString());
    }

    // Live recompute on typing
    $('table').on('input', 'input', function () {
        recomputeRow($(this).closest('tr'));
    });

    // Change driver/date → reload entire data from backend
    $('#driver_id, input[name="dispatch_date"]').on('change', function () {
        let driverId = $('#driver_id').val();
        let date = $('input[name="dispatch_date"]').val();
        if (driverId && date) {
            $.get("{{ url('admin/dispatches/openings') }}/" + driverId + "/" + date, function (data) {
                if (data.success) {
                    for (const [product, qty] of Object.entries(data.openings)) {
                        let row = $('tr[data-product="'+product+'"]');
                        row.find('.opening-stock').text(qty);
                        // clear user-entered fields when switching driver
                        row.find('input').val('');
                        row.find('.remaining-col').text('');
                        row.find('.commission-col').text('');
                    }
                }
            });
        }
    });
});
</script>
@endpush
