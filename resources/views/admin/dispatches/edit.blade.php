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

    <h5 class="mt-4">Driver Signature</h5>
    <canvas id="signature-pad" width="400" height="150" style="border:1px solid #ccc;"></canvas>
    <br>
    <button type="button" id="clear-signature" class="btn btn-secondary btn-sm">Clear</button>
    <input type="hidden" name="driver_signature" id="driver_signature" value="{{ old('driver_signature', $dispatch->driver_signature) }}">

    <div class="table-responsive mt-3">
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
                        <td>
                            <input type="number" class="form-control opening-stock"
                                   name="items[{{ $product }}][opening_stock]"
                                   value="{{ $opening }}" readonly>
                        </td>
                        <td class="remaining-col">{{ $remaining }}</td>
                        <td>
                            <input type="number" class="form-control dispatched-qty"
                                   name="items[{{ $product }}][dispatched_qty]"
                                   value="{{ $dispatched }}">
                        </td>
                        <td>
                            <input type="number" class="form-control sold-cash"
                                   name="items[{{ $product }}][sold_cash]"
                                   value="{{ $soldCash }}">
                        </td>
                        <td>
                            <input type="number" class="form-control sold-credit"
                                   name="items[{{ $product }}][sold_credit]"
                                   value="{{ $soldCredit }}">
                        </td>
                        <td class="commission-col">{{ number_format($commission,0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Hidden fields for totals -->
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

    <button class="btn btn-success mt-3"><i class="bi bi-save"></i> Update Dispatch</button>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    $('.select2').select2({ placeholder: 'Search driver' });

    function parseIntSafe(x) { return Number.isFinite(Number(x)) ? parseInt(x) : 0; }

    function recomputeRow($row) {
        let opening    = parseIntSafe($row.find('.opening-stock').val());
        let dispatched = parseIntSafe($row.find('.dispatched-qty').val());
        let soldCash   = parseIntSafe($row.find('.sold-cash').val());
        let soldCredit = parseIntSafe($row.find('.sold-credit').val());
        let remaining = (opening + dispatched) - (soldCash + soldCredit);
        $row.find('.remaining-col').text(remaining);

        // Commission
        let product = $row.data('product');
        let soldQty = soldCash + soldCredit;
        let rates = @json(config('commissions.rates'));
        let threshold = Number(@json(config('commissions.threshold'))) || 0;
        let totalAvailableValue = 0;
        $('tbody tr').each(function(){
            let op = parseIntSafe($(this).find('.opening-stock').val());
            let dis = parseIntSafe($(this).find('.dispatched-qty').val());
            let unitPrice = parseIntSafe($(this).find('td div.text-muted').text().replace(/\D/g,''));
            totalAvailableValue += (op + dis) * unitPrice;
        });
        let multiplier = (totalAvailableValue >= threshold) ? 1.0 : 0.5;
        let rate = rates[product] ?? 0;
        let commission = soldQty * (rate * multiplier);
        $row.find('.commission-col').text(Math.round(commission).toLocaleString());

        // Hidden input for commission
        let hiddenCommission = $row.find('input[name*="[commission]"]');
        if (!hiddenCommission.length) {
            $row.append(`<input type="hidden" name="items[${product}][commission]" value="${commission}">`);
        } else {
            hiddenCommission.val(commission);
        }
    }

    function recomputeAll() { $('tbody tr').each(function(){ recomputeRow($(this)); }); }
    function recomputeTotals() {
        let cashReceived = 0, totalSales = 0, totalItemsSold = 0, commissionTotal = 0;
        $('tbody tr').each(function(){
            let $row = $(this);
            let soldCash = parseIntSafe($row.find('.sold-cash').val());
            let soldCredit = parseIntSafe($row.find('.sold-credit').val());
            let unitPrice = parseIntSafe($row.find('td div.text-muted').text().replace(/\D/g,''));
            let commission = parseIntSafe($row.find('input[name*="[commission]"]').val());
            let soldTotal = soldCash + soldCredit;
            cashReceived += soldCash * unitPrice;
            totalSales += soldTotal * unitPrice;
            totalItemsSold += soldTotal;
            commissionTotal += commission;
        });
        $('input[name="cash_received"]').val(cashReceived);
        $('input[name="balance_due"]').val(totalSales - cashReceived);
        $('#commission_total').val(commissionTotal);
        $('#total_sales_value').val(totalSales);
        $('#total_items_sold').val(totalItemsSold);
    }

    $('table').on('input change', '.dispatched-qty, .sold-cash, .sold-credit', function(){
        let $row = $(this).closest('tr');
        recomputeRow($row);
        recomputeTotals();
    });

    recomputeAll();
    recomputeTotals();

    // Signature pad
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');

    // --- DRAW EXISTING SIGNATURE IF PRESENT ---
    const existingSig = $('#driver_signature').val();
    if(existingSig){
        const img = new Image();
        img.onload = function(){
            // clear canvas first
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // draw the image to fill the canvas
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            // start a new path for new drawing
            ctx.beginPath();
        };
        // important: set src AFTER onload
        img.src = existingSig;
    }

    let drawing = false;

    canvas.addEventListener('mousedown', () => drawing = true);
    canvas.addEventListener('mouseup', () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);
    canvas.addEventListener('mousemove', draw);

    function draw(e) {
        if(!drawing) return;
        const rect = canvas.getBoundingClientRect();
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';
        ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
    }

    $('#clear-signature').on('click', ()=>{
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        $('#driver_signature').val('');
    });

    $('form').on('submit', function(){
        $('#driver_signature').val(canvas.toDataURL());
    });
});
</script>
@endpush
