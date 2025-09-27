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
                    <th>All Remaining items</th>
                    <th>Dispatched</th>
                    <th>Qty Sold (Cash)</th>
                    <th>Qty Sold (Credit)</th>
                    <th>Commission</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product => $price)
                @php
                    $row = $dispatch->items->firstWhere('product',$product);
                    $opening    = old("items.$product.opening_stock", $openings[$product] ?? 0);
                    $dispatched = old("items.$product.dispatched_qty", $row?->dispatched_qty ?? 0);
                    $soldCash   = old("items.$product.sold_cash", $row?->sold_cash ?? 0);
                    $soldCredit = old("items.$product.sold_credit", $row?->sold_credit ?? 0);
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

    
    <!-- Remaining -->
    <td class="remaining-col">{{ $remaining }}</td>

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



    <!-- Commission -->
    <td class="commission-col">{{ number_format($commission,0) }}</td>
</tr>

                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ADD THESE 3 LINES HERE -->
<input type="hidden" name="commission_total" id="commission_total">
<input type="hidden" name="total_sales_value" id="total_sales_value"> 
<input type="hidden" name="total_items_sold" id="total_items_sold">

    <div class="row g-3 mt-3">
    <div class="col-md-6">
        <label class="form-label">Cash Received (UGX)</label>
        <input type="number" step="0.01" name="cash_received" class="form-control"
               value="{{ old('cash_received', $dispatch->cash_received) }}">
        <small class="text-muted">System auto-calculates, but you can adjust if needed.</small>
    </div>

    <div class="col-md-6">
        <label class="form-label">Balance Due (UGX)</label>
        <input type="number" step="0.01" name="balance_due" class="form-control"
               value="{{ old('balance_due', $dispatch->balance_due) }}">
        <small class="text-muted">Calculated as Total Sales – Cash Received (editable).</small>
    </div>
</div>

    <button class="btn btn-success mt-3">
        <i class="bi bi-save"></i> Update Dispatch
    </button>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ placeholder: 'Search driver' });

    function parseIntSafe(x) {
        return Number.isFinite(Number(x)) ? parseInt(x) : 0;
    }

    function recomputeRow($row) {
    let opening    = parseIntSafe($row.find('.opening-stock').val());
    let dispatched = parseIntSafe($row.find('input[name*="[dispatched_qty]"]').val());
    let soldCash   = parseIntSafe($row.find('input[name*="[sold_cash]"]').val());
    let soldCredit = parseIntSafe($row.find('input[name*="[sold_credit]"]').val());

    let remaining = (opening + dispatched) - (soldCash + soldCredit);
    $row.find('.remaining-col').text(remaining);

    // Commission calculation
    let product = $row.data('product');
    let soldQty = soldCash + soldCredit;
    let rates = @json(config('commissions.rates'));
    let threshold = Number(@json(config('commissions.threshold'))) || 0;

    let totalAvailableValue = 0;
    $('tbody tr').each(function(){
        let op = parseIntSafe($(this).find('.opening-stock').val());
        let dis = parseIntSafe($(this).find('input[name*="[dispatched_qty]"]').val());
        let unitPrice = parseIntSafe($(this).find('td div.text-muted').text().replace(/\D/g,''));
        totalAvailableValue += (op + dis) * unitPrice;
    });

    let multiplier = (totalAvailableValue >= threshold) ? 1.0 : 0.5;
    let rate = rates[product] ?? 0;
    let commission = soldQty * (rate * multiplier);

    $row.find('.commission-col').text(Math.round(commission).toLocaleString());
    
    // Store commission
    let hiddenCommission = $row.find('input[name*="[commission]"]');
    if (hiddenCommission.length === 0) {
        $row.append(`<input type="hidden" name="items[${product}][commission]" value="${commission}">`);
    } else {
        hiddenCommission.val(commission);
    }
}

    // recompute all rows (useful after ajax load)
    function recomputeAll() {
        $('tbody tr').each(function(){ recomputeRow($(this)); });
    }

    // Live recompute on typing/change for relevant inputs
    $('table').on('input change', 'input[name*="[dispatched_qty]"], input[name*="[sold_cash]"], input[name*="[sold_credit]"]', function () {
        recomputeRow($(this).closest('tr'));
    });

    // When driver or date changes -> fetch openings then update inputs
    $('#driver_id, input[name="dispatch_date"]').on('change', function () {
        let driverId = $('#driver_id').val();
        let date = $('input[name="dispatch_date"]').val();
        if (!driverId || !date) return;

        let url = "{{ url('admin/dispatches/openings') }}/" + driverId + "/" + date;
        $.get(url, function (data) {
            if (data && data.success) {
                // Update opening inputs only and clear user-editable fields (dispatched, sold)
                for (const [product, qty] of Object.entries(data.openings)) {
                    let $row = $('tr[data-product="'+product+'"]');
                    if (!$row.length) continue;
                    // set opening (input)
                    $row.find('.opening-stock').val(qty);
                    // clear only dispatched / sold fields (keep opening)
                    $row.find('input[name*="[dispatched_qty]"]').val('');
                    $row.find('input[name*="[sold_cash]"]').val('');
                    $row.find('input[name*="[sold_credit]"]').val('');
                }
                // recompute all rows after updating openings
                recomputeAll();
            } else {
                console.warn('Openings API returned error', data);
            }
        }).fail(function(xhr){
            console.error('Failed to fetch openings', xhr.responseText);
        });
    });

    // initial recompute once on page load (so remaining & commission visible)
    recomputeAll();

    function validateRows() {
        let isValid = true;

        $('tbody tr').each(function () {
            let $row = $(this);
            let opening    = parseInt($row.find('.opening-stock').val() || $row.find('.opening-stock').text()) || 0;
            let dispatched = parseInt($row.find('input[name*="[dispatched_qty]"]').val()) || 0;
            let soldCash   = parseInt($row.find('input[name*="[sold_cash]"]').val()) || 0;
            let soldCredit = parseInt($row.find('input[name*="[sold_credit]"]').val()) || 0;

            let allowed = opening + dispatched;
            let sold    = soldCash + soldCredit;

            // remove old error highlight
            $row.removeClass('table-danger');

            if (sold > allowed) {
                isValid = false;
                // highlight the row so the user sees the problem
                $row.addClass('table-danger');
            }
        });

        // enable/disable the submit button
        $('button[type="submit"]').prop('disabled', !isValid);

        return isValid;
    }

    // re-validate whenever any input changes
    $('table').on('input change', 'input', validateRows);

    // also validate just before submit
    $('form').on('submit', function (e) {
        if (!validateRows()) {
            e.preventDefault();
            alert('Some rows have Sold greater than Opening + Dispatched. Please correct before saving.');
        }
    });

    // initial validation on page load
    validateRows();

    function recomputeTotals() {
    let cashReceived = 0;
    let totalSales = 0;
    let totalItemsSold = 0;
    let commissionTotal = 0;

    $('tbody tr').each(function() {
        let $row = $(this);
        let soldCash   = parseIntSafe($row.find('input[name*="[sold_cash]"]').val());
        let soldCredit = parseIntSafe($row.find('input[name*="[sold_credit]"]').val());
        let unitPrice  = parseIntSafe($row.find('td div.text-muted').text().replace(/\D/g,''));
        let commission = parseIntSafe($row.find('input[name*="[commission]"]').val());

        let soldTotal = soldCash + soldCredit;
        
        cashReceived += soldCash * unitPrice;
        totalSales   += soldTotal * unitPrice;
        totalItemsSold += soldTotal;
        commissionTotal += commission;
    });

    $('input[name="cash_received"]').val(cashReceived);
    $('input[name="balance_due"]').val(totalSales - cashReceived);
    
    $('#commission_total').val(commissionTotal);
    $('#total_sales_value').val(totalSales);
    $('#total_items_sold').val(totalItemsSold);
}

// Call it after every row recompute
$('table').on('input change', 'input[name*="[dispatched_qty]"], input[name*="[sold_cash]"], input[name*="[sold_credit]"]', function () {
    let $row = $(this).closest('tr');
    recomputeRow($row);
    recomputeTotals(); // <-- update totals
});

// Also call on page load after initial recomputeAll
recomputeTotals();
});

</script>
@endpush