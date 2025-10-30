@extends('manager.layouts.app')
@section('title', 'Create Kampala Dispatch')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-truck me-2"></i>Create Kampala Dispatch</h4>
        <a href="{{ route('manager.kampala-dispatches.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Dispatches
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Dispatch Information</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('manager.kampala-dispatches.store') }}" method="POST" id="dispatchForm">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="shop_id" class="form-label">Shop *</label>
                        <select name="shop_id" id="shop_id" class="form-select" required>
                            <option value="">Select Shop</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ old('shop_id') == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->name }} - {{ $shop->location }}
                                </option>
                            @endforeach
                        </select>
                        @error('shop_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="dispatch_date" class="form-label">Dispatch Date *</label>
                        <input type="date" name="dispatch_date" id="dispatch_date" 
                               class="form-control" value="{{ old('dispatch_date', date('Y-m-d')) }}" required>
                        @error('dispatch_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Dispatch Items -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Dispatch Items</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">
                            <i class="bi bi-plus-circle me-1"></i>Add Item
                        </button>
                    </div>

                    <div id="items-container">
                        <!-- Items will be added here dynamically -->
                        <div class="item-row mb-3 p-3 border rounded">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label">Product *</label>
                                    <select name="items[0][product_type]" class="form-select product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $productKey => $productPrice)
                                            <option value="{{ $productKey }}">
                                                {{ ucwords(str_replace('_', ' ', $productKey)) }} - UGX {{ number_format($productPrice) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantity *</label>
                                    <input type="number" name="items[0][quantity]" class="form-control quantity-input" 
                                           min="1" required placeholder="Qty">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Available Stock</label>
                                    <input type="text" class="form-control stock-display" readonly placeholder="Check stock">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item d-none">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <input type="text" name="items[0][notes]" class="form-control" 
                                           placeholder="Item notes (optional)">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Total Items:</strong> <span id="total-items">0</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Total Value:</strong> UGX <span id="total-value">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label for="notes" class="form-label">Dispatch Notes (Optional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                              placeholder="Any additional notes for this dispatch">{{ old('notes') }}</textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">Create Dispatch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.item-row {
    background: #f8f9fa;
    transition: all 0.3s ease;
}
.item-row:hover {
    background: #e9ecef;
}
.stock-display.insufficient {
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 1;
    const products = @json($products);
    const bakeryStocks = @json($bakeryStocks->keyBy('product')->toArray());
    
    // Add new item row
    document.getElementById('addItem').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const newRow = document.createElement('div');
        newRow.className = 'item-row mb-3 p-3 border rounded';
        newRow.innerHTML = `
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="form-label">Product *</label>
                    <select name="items[${itemCount}][product_type]" class="form-select product-select" required>
                        <option value="">Select Product</option>
                        ${Object.keys(products).map(key => 
                            `<option value="${key}">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())} - UGX ${products[key].toLocaleString()}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity *</label>
                    <input type="number" name="items[${itemCount}][quantity]" class="form-control quantity-input" 
                           min="1" required placeholder="Qty">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Available Stock</label>
                    <input type="text" class="form-control stock-display" readonly placeholder="Check stock">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <input type="text" name="items[${itemCount}][notes]" class="form-control" 
                           placeholder="Item notes (optional)">
                </div>
            </div>
        `;
        container.appendChild(newRow);
        
        // Add event listeners to new row
        addEventListenersToRow(newRow);
        itemCount++;
        
        // Show remove buttons on all rows except first
        document.querySelectorAll('.remove-item').forEach(btn => btn.classList.remove('d-none'));
    });
    
    // Add event listeners to initial row
    document.querySelectorAll('.item-row').forEach(row => addEventListenersToRow(row));
    
    function addEventListenersToRow(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const stockDisplay = row.querySelector('.stock-display');
        const removeBtn = row.querySelector('.remove-item');
        
        // Update stock display when product changes
        productSelect.addEventListener('change', function() {
            updateStockDisplay(this, stockDisplay);
            calculateTotals();
        });
        
        // Update totals when quantity changes
        quantityInput.addEventListener('input', function() {
            checkStockAvailability(productSelect, this, stockDisplay);
            calculateTotals();
        });
        
        // Remove item
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                row.remove();
                calculateTotals();
                // Hide remove button if only one row left
                if (document.querySelectorAll('.item-row').length === 1) {
                    document.querySelector('.remove-item').classList.add('d-none');
                }
            });
        }
    }
    
    function updateStockDisplay(select, display) {
        const product = select.value;
        if (product && bakeryStocks[product]) {
            const stock = bakeryStocks[product].quantity;
            display.value = stock.toLocaleString();
            display.classList.toggle('insufficient', stock === 0);
        } else {
            display.value = '';
            display.classList.remove('insufficient');
        }
    }
    
    function checkStockAvailability(select, quantityInput, display) {
        const product = select.value;
        const quantity = parseInt(quantityInput.value) || 0;
        
        if (product && bakeryStocks[product]) {
            const stock = bakeryStocks[product].quantity;
            if (quantity > stock) {
                display.classList.add('insufficient');
                quantityInput.setCustomValidity(`Insufficient stock. Available: ${stock}`);
            } else {
                display.classList.remove('insufficient');
                quantityInput.setCustomValidity('');
            }
        }
    }
    
    function calculateTotals() {
        let totalItems = 0;
        let totalValue = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            
            if (productSelect.value && quantityInput.value) {
                const quantity = parseInt(quantityInput.value);
                const product = productSelect.value;
                
                if (quantity > 0 && products[product]) {
                    totalItems += quantity;
                    totalValue += quantity * products[product];
                }
            }
        });
        
        document.getElementById('total-items').textContent = totalItems.toLocaleString();
        document.getElementById('total-value').textContent = totalValue.toLocaleString();
    }
    
    // Initial calculation
    calculateTotals();
});
</script>
@endsection                         