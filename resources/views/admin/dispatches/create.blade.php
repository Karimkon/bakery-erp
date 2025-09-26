@extends('admin.layouts.app')
@section('title', 'New Dispatch')

@section('content')
<h4 class="mb-3"><i class="bi bi-truck me-2"></i> New Driver Dispatch</h4>

<form method="POST" action="{{ route('admin.dispatches.store') }}">
    @csrf

    <div class="row g-3">
        <!-- Driver -->
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

        <!-- Date -->
        <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="dispatch_date" class="form-control"
                   value="{{ old('dispatch_date', now()->toDateString()) }}" required>
            @error('dispatch_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <!-- Notes -->
        <div class="col-md-12">
            <label class="form-label">Notes (optional)</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="mb-3"><i class="bi bi-basket2 me-1"></i> Items</h5>
    <p class="text-muted small">
        Opening stock will be auto-calculated from the previous dispatch for this driver (yesterday’s remaining).
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
                </tr>
            </thead>
            <tbody>
               @foreach($products as $product => $price)
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
                        <input type="number" class="form-control" name="items[{{ $product }}][dispatched_qty]" min="0">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="items[{{ $product }}][sold_cash]" min="0">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="items[{{ $product }}][sold_credit]" min="0">
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
    // Check if jQuery is loaded
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded!');
} else {
    console.log('jQuery version:', jQuery.fn.jquery);
}

$(document).ready(function() {
    console.log('Document ready - dispatch script loaded');
    
    // Setup CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Initialize Select2
    $('#driver_id').select2({
        placeholder: '-- Select Driver --',
        allowClear: true
    });
    
    $('#driver_id, input[name="dispatch_date"]').on('change', function () {
        console.log('Driver or date changed');
        
        let driverId = $('#driver_id').val();
        let date = $('input[name="dispatch_date"]').val();
        
        console.log('Driver ID:', driverId, 'Date:', date);
        
        if (!driverId || !date) {
            console.log('Missing driver ID or date');
            return;
        }

        // Correct URL construction
        let url = "{{ url('admin/dispatches/openings') }}/" + driverId + "/" + date;
        console.log('Request URL:', url);

        // Show loading
        $('.opening-stock').text('Loading...');

        $.get(url, function (data) {
            console.log('Response received:', data);
            
            if (data.success) {
                console.log('Openings data:', data.openings);
                // Update opening stock for each product
                for (const [product, qty] of Object.entries(data.openings)) {
                    console.log('Setting opening for', product, 'to', qty);
                    // Update both the display text and the hidden input
                    const $row = $('tr[data-product="' + product + '"]');
                    $row.find('.opening-stock').text(qty);
                    $row.find('.opening-stock-input').val(qty);
                }
            } else {
                console.error('API error:', data.error);
                alert(data.error || 'Failed to load opening stock');
            }
        }).fail(function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            console.log('Response text:', xhr.responseText);
            
            // Reset opening stock to 0 on error
            $('.opening-stock').text('0');
            $('.opening-stock-input').val('0');
            
            // Show user-friendly error message
            if (xhr.status === 404) {
                console.warn('Opening stock data not found - using 0 as default');
            } else {
                alert('Could not load opening stock data. Using 0 as default.');
            }
        });
    });

    $('#driver_id').on('change', function() {
        if (!$(this).val()) {
            $('.opening-stock').text('0');
            $('.opening-stock-input').val('0');
        }
    });
});
</script>
@endpush