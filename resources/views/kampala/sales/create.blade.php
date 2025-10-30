@extends('kampala.layouts.app')
@section('title','New Sale')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-bag-plus me-2"></i>New Sale (POS)</h4>
        <a href="{{ route('kampala.sales.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('kampala.sales.store') }}">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Product <span class="text-danger">*</span></label>
                                <select name="product_type" class="form-select" required onchange="updateProductInfo(this)">
                                    <option value="">Select Product</option>
                                    @foreach($stocks as $stock)
                                        <option value="{{ $stock->product_type }}" 
                                                data-price="{{ $stock->unit_price }}"
                                                data-stock="{{ $stock->remaining }}"
                                                data-product="{{ $stock->product_type }}">
                                            {{ ucwords(str_replace('_', ' ', $stock->product_type)) }} 
                                            (Stock: {{ $stock->remaining }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text" id="stock-info">Select a product to see details</div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" min="1" value="1" 
                                       oninput="calculateTotal()" required id="quantity">
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">Unit Price (UGX)</label>
                                <input type="text" class="form-control" readonly id="unit-price-display">
                                <input type="hidden" id="unit-price" value="0">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Total Amount (UGX)</label>
                                <input type="text" class="form-control fw-bold fs-5 text-success" 
                                       readonly id="total-price-display" value="UGX 0">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" rows="2" class="form-control" 
                                          placeholder="Customer notes, special requests..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-1"></i> Complete Sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Available Stock -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Available Stock</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Available</th>
                                    <th class="text-end">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                    <tr class="{{ $stock->remaining == 0 ? 'table-danger' : ($stock->remaining < 10 ? 'table-warning' : '') }}">
                                        <td>{{ ucwords(str_replace('_', ' ', $stock->product_type)) }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-{{ $stock->remaining == 0 ? 'danger' : ($stock->remaining < 10 ? 'warning' : 'success') }}">
                                                {{ $stock->remaining }}
                                            </span>
                                        </td>
                                        <td class="text-end">UGX {{ number_format($stock->unit_price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <a href="{{ route('kampala.dispatches.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-truck me-1"></i> Check Dispatches
                    </a>
                    <a href="{{ route('kampala.stock.index') }}" class="btn btn-outline-info w-100 mb-2">
                        <i class="bi bi-box-seam me-1"></i> View Stock
                    </a>
                    <a href="{{ route('kampala.bankings.index') }}" class="btn btn-outline-success w-100">
                        <i class="bi bi-bank me-1"></i> Record Banking
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateProductInfo(select) {
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.dataset.price;
        const stock = selectedOption.dataset.stock;
        const product = selectedOption.dataset.product;
        
        document.getElementById('unit-price').value = price;
        document.getElementById('unit-price-display').value = `UGX ${parseFloat(price).toLocaleString()}`;
        
        const stockInfo = document.getElementById('stock-info');
        if (stock == 0) {
            stockInfo.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Out of stock!</span>`;
        } else if (stock < 10) {
            stockInfo.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> Low stock: ${stock} remaining</span>`;
        } else {
            stockInfo.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> In stock: ${stock} available</span>`;
        }
        
        calculateTotal();
    }

    function calculateTotal() {
        const quantity = parseInt(document.getElementById('quantity').value) || 0;
        const unitPrice = parseFloat(document.getElementById('unit-price').value) || 0;
        const total = quantity * unitPrice;
        
        document.getElementById('total-price-display').value = `UGX ${total.toLocaleString()}`;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateTotal();
    });
    </script>
@endsection