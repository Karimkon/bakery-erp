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
                    <td class="opening-stock">0</td>
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
let openingsUrlTemplate = "{{ url('admin/dispatches/openings/DRIVER_ID/DATE') }}";

$('#driver_id, input[name="dispatch_date"]').on('change', function () {
    let driverId = $('#driver_id').val();
    let date = $('input[name="dispatch_date"]').val();
    if (!driverId || !date) return;

    $.get("{{ route('admin.dispatches.openings', ['driver' => 'DRIVERID', 'date' => 'DATE']) }}"
        .replace('DRIVERID', driverId)
        .replace('DATE', date),
        function(data){
            if(data.success){
                $.each(data.openings, function(product, qty){
                    $('tr[data-product="'+product+'"]').find('.opening-stock').text(qty);
                });
            } else {
                alert(data.error || 'Failed to load opening stock');
            }
        }
    );
});

</script>

@endpush
