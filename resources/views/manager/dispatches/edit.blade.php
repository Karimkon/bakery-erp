@extends('manager.layouts.app')
@section('title', 'Edit Dispatch')

@section('content')
<h4 class="mb-3"><i class="bi bi-truck me-2"></i> Edit Driver Dispatch</h4>

<form method="POST" action="{{ route('manager.dispatches.update', $dispatch->id) }}">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Driver</label>
            <select id="driver_id" name="driver_id" class="form-select select2" required disabled>
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
                   value="{{ old('dispatch_date', $dispatch->dispatch_date->toDateString()) }}" required disabled>
        </div>

        <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes',$dispatch->notes) }}</textarea>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="mb-3"><i class="bi bi-basket2 me-1"></i> Items</h5>
    <p class="text-muted small">
        Opening, Remaining and Commission auto-update after typing.
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
                                value="{{ $dispatched }}"
                                readonly>
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
        <!-- Calculated Cash Received (readonly, computed by JS) -->
        <div class="col-md-6">
            <label class="form-label">Calculated Cash Received (UGX)</label>
            <input type="number" step="0.01" id="calculated_cash_received" class="form-control" readonly>
            <small class="text-muted">Auto-calculated from cash sales.</small>
        </div>

        <!-- Actual Cash Received (editable by manager) -->
        <div class="col-md-6">
            <label class="form-label">Actual Cash Received (UGX)</label>
            <input type="number" step="0.01" name="cash_received" id="actual_cash_received" class="form-control"
                   value="{{ old('cash_received', $dispatch->cash_received) }}">
            <small class="text-muted">Enter actual amount received. Leave blank to use calculated value.</small>
        </div>

        <!-- Balance Due (readonly, computed dynamically) -->
        <div class="col-md-6">
            <label class="form-label">Balance Due (UGX)</label>
            <input type="number" step="0.01" id="balance_due_display" class="form-control" readonly>
            <small class="text-muted">Credit sales + Remaining inventory value - Actual cash received.</small>
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
    function parseFloatSafe(x) { return Number.isFinite(Number(x)) ? parseFloat(x) : 0; }

    function recomputeRow($row) {
        const opening = parseIntSafe($row.find('.opening-stock').val());
        let dispatched = parseIntSafe($row.find('.dispatched-qty').val());
        let soldCash = parseIntSafe($row.find('.sold-cash').val());
        let soldCredit = parseIntSafe($row.find('.sold-credit').val());

        // Remaining
        const remaining = (opening + dispatched) - (soldCash + soldCredit);
        $row.find('.remaining-col').text(remaining);

        // Commission
        const product = $row.data('product');
        const soldQty = soldCash + soldCredit;
        const rates = @json(config('commissions.rates'));
        const threshold = Number(@json(config('commissions.threshold'))) || 0;
        
        let totalAvailableValue = 0;
        $('tbody tr').each(function(){
            const op = parseIntSafe($(this).find('.opening-stock').val());
            const dis = parseIntSafe($(this).find('.dispatched-qty').val());
            const unitPrice = parseIntSafe($(this).find('td div.text-muted').text().replace(/\D/g,''));
            totalAvailableValue += (op + dis) * unitPrice;
        });
        
        const multiplier = totalAvailableValue >= threshold ? 1 : 0.5;
        const rate = rates[product] ?? 0;
        const commission = soldQty * rate * multiplier;
        $row.find('.commission-col').text(Math.round(commission).toLocaleString());

        // Hidden commission
        let hiddenCommission = $row.find('input[name*="[commission]"]');
        if(!hiddenCommission.length) {
            $row.append(`<input type="hidden" name="items[${product}][commission]" value="${commission}">`);
        } else {
            hiddenCommission.val(commission);
        }
    }

    function recomputeTotals() {
        let calculatedCashReceived = 0;
        let totalSales = 0;
        let totalItemsSold = 0;
        let commissionTotal = 0;
        let remainingInventoryValue = 0;
        let creditSalesValue = 0;

        $('tbody tr').each(function(){
            const $row = $(this);
            const soldCash = parseIntSafe($row.find('.sold-cash').val());
            const soldCredit = parseIntSafe($row.find('.sold-credit').val());
            const opening = parseIntSafe($row.find('.opening-stock').val());
            const dispatched = parseIntSafe($row.find('.dispatched-qty').val());
            const unitPrice = parseIntSafe($row.find('td div.text-muted').text().replace(/\D/g,''));
            const commission = parseIntSafe($row.find('input[name*="[commission]"]').val());
            
            const soldTotal = soldCash + soldCredit;
            const remaining = (opening + dispatched) - soldTotal;
            
            calculatedCashReceived += soldCash * unitPrice;
            creditSalesValue += soldCredit * unitPrice;
            totalSales += soldTotal * unitPrice;
            totalItemsSold += soldTotal;
            commissionTotal += commission;
            remainingInventoryValue += remaining * unitPrice;
        });

        // Update calculated cash received (readonly)
        $('#calculated_cash_received').val(calculatedCashReceived.toFixed(2));

        // Get actual cash received (user input or default to calculated)
        let actualCashReceived = parseFloatSafe($('#actual_cash_received').val());
        if (actualCashReceived === 0 || $('#actual_cash_received').val() === '') {
            actualCashReceived = calculatedCashReceived;
        }

        // Balance due = Credit sales + Remaining inventory - (Actual cash - Total sales including credit)
        // Simplified: Balance due = Remaining inventory value + Credit sales value
        const balanceDue = remainingInventoryValue + creditSalesValue;
        
        $('#balance_due_display').val(balanceDue.toFixed(2));
        $('#commission_total').val(commissionTotal);
        $('#total_sales_value').val(totalSales);
        $('#total_items_sold').val(totalItemsSold);
    }

    // Recompute when items change
    $('table').on('input change', '.sold-cash, .sold-credit', function(){
        const $row = $(this).closest('tr');
        recomputeRow($row);
        recomputeTotals();
    });

    // Recompute balance due when actual cash received changes
    $('#actual_cash_received').on('input change', function(){
        recomputeTotals();
    });

    // Initial computation
    $('tbody tr').each(function(){ recomputeRow($(this)); });
    recomputeTotals();

    // Signature pad
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    const existingSig = $('#driver_signature').val();
    if(existingSig){
        const img = new Image();
        img.onload = function(){
            ctx.clearRect(0,0,canvas.width,canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            ctx.beginPath();
        };
        img.src = existingSig;
    }
    let drawing = false;
    canvas.addEventListener('mousedown', () => drawing = true);
    canvas.addEventListener('mouseup', () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);
    canvas.addEventListener('mousemove', draw);
    function draw(e){
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
        ctx.clearRect(0,0,canvas.width,canvas.height);
        $('#driver_signature').val('');
    });
    $('form').on('submit', function(){
        $('#driver_signature').val(canvas.toDataURL());
    });
});
</script>
@endpush