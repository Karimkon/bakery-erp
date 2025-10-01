@extends('admin.layouts.app')
@section('title', 'New Dispatch')

@section('content')
<h4 class="mb-3"><i class="bi bi-truck me-2"></i> New Driver Dispatch</h4>

<form method="POST" action="{{ route('admin.dispatches.store') }}">
    @csrf

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Driver</label>
            <select id="driver_id" name="driver_id" class="form-select select2" required>
                <option value="">-- Select Driver --</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
            </select>
            @error('driver_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="dispatch_date" class="form-control" value="{{ old('dispatch_date', now()->toDateString()) }}" required>
            @error('dispatch_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="col-md-12">
            <label class="form-label">Notes (optional)</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="mb-3"><i class="bi bi-basket2 me-1"></i> Items</h5>
    <p class="text-muted small">
        Opening stock shows current available bakery stock. Admin cannot dispatch more than available.
    </p>

    <h5 class="mt-4">Driver Signature</h5>
    <canvas id="signature-pad" width="400" height="150" style="border:1px solid #ccc;"></canvas>
    <br>
    <button type="button" id="clear-signature" class="btn btn-secondary btn-sm">Clear</button>
    <input type="hidden" name="driver_signature" id="driver_signature">

    <div class="table-responsive mt-3">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
    <tr>
        <th>Product</th>
        <th>Driver Opening</th>
        <th>Available in Bakery</th>
        <th>Dispatched</th>
        <th>Sold (Cash)</th>
        <th>Sold (Credit)</th>
    </tr>
</thead>
<tbody>
   @foreach($products as $product => $price)
    @php
        $stock = \App\Models\BakeryStock::where('product', $product)->first();
        $available = $stock?->quantity ?? 0;
    @endphp
    <tr data-product="{{ $product }}">
        <td>
            {{ ucfirst(str_replace('_',' ', $product)) }}
            <div class="text-muted small">UGX {{ number_format($price) }}</div>
        </td>
        <td>
            <span class="opening-stock">0</span>
            <input type="hidden" name="items[{{ $product }}][opening_stock]" class="opening-stock-input" value="0">
        </td>
        <td>
            <span class="bakery-stock">{{ $available }}</span>
            <input type="hidden" name="items[{{ $product }}][bakery_stock]" value="{{ $available }}">
        </td>
        <td>
            <input type="number" class="form-control dispatched-qty" name="items[{{ $product }}][dispatched_qty]" min="0" max="{{ $available }}">
        </td>
        <td>
            <input type="number" class="form-control sold-cash" name="items[{{ $product }}][sold_cash]" min="0">
        </td>
        <td>
            <input type="number" class="form-control sold-credit" name="items[{{ $product }}][sold_credit]" min="0">
        </td>
    </tr>
    @endforeach
</tbody>

        </table>
    </div>

    <button class="btn btn-success mt-3">
        <i class="bi bi-save"></i> Save Dispatch
    </button>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({ placeholder: '-- Select Driver --', allowClear: true });

    // Signature pad
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
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
    $('#clear-signature').click(()=>{ ctx.clearRect(0,0,canvas.width,canvas.height); $('#driver_signature').val(''); });
    $('form').submit(function(){ $('#driver_signature').val(canvas.toDataURL()); });

    // Fetch opening stock from bakery_stocks table for the selected date
    $('#driver_id, input[name="dispatch_date"]').on('change', function(){
        const driverId = $('#driver_id').val();
        const date = $('input[name="dispatch_date"]').val();
        if(!driverId || !date) return;

        let url = "{{ url('admin/dispatches/openings') }}/" + driverId + "/" + date;

        $('.opening-stock').text('Loading...');
        $.get(url, function(data){
            if(data.success){
                for(const [product, qty] of Object.entries(data.openings)){
                    const $row = $('tr[data-product="'+product+'"]');
                    $row.find('.opening-stock').text(qty);
                    $row.find('.opening-stock-input').val(qty);
                }
            } else {
                $('.opening-stock').text('0');
                $('.opening-stock-input').val('0');
            }
        }).fail(function(){ $('.opening-stock').text('0'); $('.opening-stock-input').val('0'); });
    });
});
</script>
@endpush
