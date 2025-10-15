{{-- resources/views/sales/sales/create.blade.php --}}
@extends('sales.layouts.app')
@section('title','New Sale (POS)')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-bag-plus me-2"></i>New Sale (POS)</h4>
        <a href="{{ route('sales.sales.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Sale Details</h5>
                    <form method="POST" action="{{ route('sales.sales.store') }}" target="_blank" id="posForm">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Product</label>
                                <select name="product_type" id="product_type" class="form-select" required>
                                    <option value="">-- Select Product --</option>
                                    @foreach($products as $key => $label)
                                        <option value="{{ $key }}" 
                                                data-price="{{ $stocks[$key]->unit_price ?? 0 }}"
                                                data-remaining="{{ $stocks[$key]->remaining ?? 0 }}">
                                            {{ $label }} (Stock: {{ $stocks[$key]->remaining ?? 0 }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Select a product from the dropdown or use Quick Pick below</div>
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
                                <div id="stockInfo" class="form-text text-primary"></div>
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label">Unit Price (UGX)</label>
                                <input type="number" name="unit_price" id="unit_price" class="form-control" min="0" step="0.01" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Total (UGX)</label>
                                <input type="number" name="total_price" id="total_price" class="form-control" step="0.01" readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Payment Method</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pm_cash" value="cash" checked>
                                        <label class="form-check-label" for="pm_cash"><i class="bi bi-cash-coin me-1"></i>Cash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pm_momo" value="momo">
                                        <label class="form-check-label" for="pm_momo"><i class="bi bi-phone-flip me-1"></i>Mobile Money</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any details…"></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save2 me-1"></i> Save Sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Product quick-pick --}}
        <div class="col-12 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Quick Pick</h6>
                    <div class="row g-2" id="quickPick">
                        {{-- ✅ FIXED: Now loads ALL products from controller --}}
                        @foreach($products as $key => $label)
                            @php
                                $stock = $stocks[$key] ?? null;
                                $remaining = $stock ? $stock->remaining : 0;
                                $price = $stock ? $stock->unit_price : 0;
                            @endphp
                            <div class="col-6">
                                <div class="card border pos-card" 
                                     data-ptype="{{ $key }}" 
                                     data-price="{{ $price }}"
                                     data-remaining="{{ $remaining }}">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">{{ $label }}</div>
                                                <small class="text-muted">UGX {{ number_format($price) }}</small>
                                            </div>
                                            <span class="badge {{ $remaining > 0 ? 'bg-info' : 'bg-danger' }}">
                                                Stock: {{ $remaining }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 small text-muted">Tap a card to fill product & unit price automatically.</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function(){
    const form   = document.getElementById('posForm');
    const qty    = document.getElementById('quantity');
    const unit   = document.getElementById('unit_price');
    const total  = document.getElementById('total_price');
    const ptype  = document.getElementById('product_type');
    const stockInfo = document.getElementById('stockInfo');

    function recalc(){
        total.value = (Number(qty.value||0) * Number(unit.value||0)).toFixed(2);
    }

    // ✅ NEW: Update price when dropdown changes
    ptype.addEventListener('change', function(){
        const selected = this.options[this.selectedIndex];
        if(selected && selected.value){
            unit.value = selected.dataset.price || 0;
            stockInfo.textContent = "Remaining stock: " + (selected.dataset.remaining || 0);
            recalc();
        } else {
            unit.value = "";
            stockInfo.textContent = "";
            total.value = "";
        }
    });

    // ✅ Quick pick cards
    document.querySelectorAll('.pos-card').forEach(card=>{
        card.addEventListener('click', ()=>{
            ptype.value = card.dataset.ptype;
            unit.value  = card.dataset.price;
            if(!qty.value || Number(qty.value) < 1) qty.value = 1;
            recalc();
            stockInfo.textContent = "Remaining stock: " + card.dataset.remaining;
        });
    });

    // ✅ AJAX form submit
    form.addEventListener('submit', async function(e){
        e.preventDefault();

        const formData = new FormData(form);
        try {
            let res = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                },
                body: formData
            });

            let data = await res.json();
            if(res.ok){
                // Update the stock badge live
                let card = document.querySelector('.pos-card[data-ptype="'+data.product+'"]');
                if(card){
                    card.dataset.remaining = data.remaining;
                    let badge = card.querySelector('.badge');
                    badge.textContent = "Stock: " + data.remaining;
                    badge.className = data.remaining > 0 ? 'badge bg-info' : 'badge bg-danger';
                }

                // Update dropdown option
                let option = ptype.querySelector('option[value="'+data.product+'"]');
                if(option){
                    option.dataset.remaining = data.remaining;
                    option.textContent = option.textContent.replace(/Stock: \d+/, 'Stock: ' + data.remaining);
                }

                // Open receipt in a new tab
                window.open("/sales/receipt/" + data.id, '_blank');

                // Reset form
                form.reset();
                total.value = "";
                stockInfo.textContent = "";
            } else {
                alert(data.error || "Error processing sale.");
            }
        } catch(err){
            console.error(err);
            alert("Network error, please try again.");
        }
    });

    qty.addEventListener('input', recalc);
    unit.addEventListener('input', recalc);
    recalc();
})();
</script>
@endpush