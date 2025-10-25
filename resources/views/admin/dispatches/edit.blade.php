@extends('admin.layouts.app')
@section('title', 'Edit Dispatch')

@section('content')
<h4 class="mb-3"><i class="bi bi-truck me-2"></i> Edit Driver Dispatch</h4>

<form method="POST" action="{{ route('admin.dispatches.update', $dispatch->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Driver</label>
            <select id="driver_id" name="driver_id" class="form-select select2" required>
                <option value="">-- Select Driver --</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}"
                        {{ old('driver_id', $dispatch->driver_id) == $driver->id ? 'selected' : '' }}>
                        {{ $driver->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="dispatch_date" class="form-control"
                   value="{{ old('dispatch_date', $dispatch->dispatch_date->toDateString()) }}" required>
        </div>

        <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $dispatch->notes) }}</textarea>
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
    <button type="button" id="clear-signature" class="btn btn-secondary btn-sm mt-2">Clear</button>
    <input type="hidden" name="driver_signature" id="driver_signature" value="{{ old('driver_signature', $dispatch->driver_signature) }}">
    <input type="hidden" id="original_back_debt" value="{{ old('back_debt', $dispatch->driver->back_debt) }}">

    <div class="table-responsive mt-3">
        <table class="table table-sm table-bordered align-middle" id="items-table">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Opening</th>
                    <th>Dispatched</th>
                    <th>Driver's Remaining</th>
                    <th>Qty Sold (Cash)</th>
                    <th>Qty Sold (Credit)</th>
                    <th>Commission (UGX)</th>
                </tr>
            </thead>
            <tbody>
    @foreach($products as $product => $price)
        @php
            $row = $dispatch->items->firstWhere('product', $product);
            $opening    = (int) ($openings[$product] ?? 0);
            $dispatched = (int) ($row?->dispatched_qty ?? 0);
            $soldCash   = (int) old("items.$product.sold_cash", $row?->sold_cash ?? 0);
            $soldCredit = (int) old("items.$product.sold_credit", $row?->sold_credit ?? 0);
            $commission = (float) ($row?->commission ?? 0);
            $maxSold = $opening + $dispatched;
            $remaining = $maxSold - ($soldCash + $soldCredit);
        @endphp
        <tr data-product="{{ $product }}" data-price="{{ $price }}" data-sold-credit="{{ $soldCredit }}">
            <td>
                <strong>{{ ucfirst(str_replace('_', ' ', $product)) }}</strong>
                <div class="text-muted small">UGX {{ number_format($price) }}</div>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm opening-stock" 
                       data-opening="{{ $opening }}"
                       value="{{ $opening }}" readonly tabindex="-1">
            </td>
            <td>
                <input type="number" 
                       class="form-control form-control-sm dispatched-qty" 
                       name="items[{{ $product }}][dispatched_qty]"
                       data-original-dispatched="{{ $dispatched }}"
                       value="{{ $dispatched }}" 
                       min="0">
            </td>
            <td class="text-center">
                <span class="remaining-col badge bg-info">{{ $remaining }}</span>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm sold-cash"
                       name="items[{{ $product }}][sold_cash]"
                       value="{{ $soldCash }}"
                       min="0" max="{{ $maxSold }}" 
                       data-max="{{ $maxSold }}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm sold-credit"
                       name="items[{{ $product }}][sold_credit]"
                       value="{{ $soldCredit }}"
                       min="0" max="{{ $maxSold }}" 
                       data-max="{{ $maxSold }}">
            </td>
            <td class="text-center">
                <span class="commission-col badge bg-success">{{ number_format($commission, 0) }}</span>
            </td>
            <input type="hidden" class="commission-value" value="{{ $commission }}">
        </tr>
    @endforeach
</tbody>
        </table>
    </div>

    <input type="hidden" name="commission_total" id="commission_total">
    <input type="hidden" name="total_sales_value" id="total_sales_value">
    <input type="hidden" name="total_items_sold" id="total_items_sold">
    <input type="hidden" name="driver_expenses" id="driver_expenses_total">
    <input type="hidden" name="back_debt_hidden" id="back_debt_hidden" value="{{ old('back_debt', $dispatch->driver->back_debt) }}">

    <hr class="my-4">

    <!-- DRIVER EXPENSES SECTION -->
    <h5 class="mb-3"><i class="bi bi-cash-coin me-2"></i> Driver Expenses</h5>
    <p class="text-muted small">Add itemized expenses with descriptions. Total will be calculated automatically.</p>

    <div id="expenses-container">
        @php
            $existingExpenses = old('expenses', $dispatch->expenses->toArray());
        @endphp
        
        @if(count($existingExpenses) > 0)
            @foreach($existingExpenses as $index => $expense)
                <div class="expense-row row g-2 mb-2 align-items-end" data-index="{{ $index }}">
                    <div class="col-md-3">
                        <label class="form-label small">Expense Type</label>
                        <select name="expenses[{{ $index }}][expense_type]" class="form-select form-select-sm expense-type" required>
                            <option value="">-- Select Type --</option>
                            @foreach(\App\Models\DriverExpense::expenseTypes() as $key => $label)
                                <option value="{{ $key }}" {{ ($expense['expense_type'] ?? '') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Amount (UGX)</label>
                        <input type="number" step="0.01" name="expenses[{{ $index }}][amount]" 
                               class="form-control form-control-sm expense-amount" 
                               value="{{ $expense['amount'] ?? 0 }}" 
                               min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Description</label>
                        <input type="text" name="expenses[{{ $index }}][description]" 
                               class="form-control form-control-sm" 
                               value="{{ $expense['description'] ?? '' }}" 
                               placeholder="Details about this expense">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Receipt (optional)</label>
                        <input type="file" name="expenses[{{ $index }}][receipt]" 
                               class="form-control form-control-sm" 
                               accept="image/*">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger remove-expense">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <input type="hidden" name="expenses[{{ $index }}][id]" value="{{ $expense['id'] ?? '' }}">
                </div>
            @endforeach
        @else
            <div class="expense-row row g-2 mb-2 align-items-end" data-index="0">
                <div class="col-md-3">
                    <label class="form-label small">Expense Type</label>
                    <select name="expenses[0][expense_type]" class="form-select form-select-sm expense-type">
                        <option value="">-- Select Type --</option>
                        @foreach(\App\Models\DriverExpense::expenseTypes() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Amount (UGX)</label>
                    <input type="number" step="0.01" name="expenses[0][amount]" 
                           class="form-control form-control-sm expense-amount" 
                           value="0" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Description</label>
                    <input type="text" name="expenses[0][description]" 
                           class="form-control form-control-sm" 
                           placeholder="Details about this expense">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Receipt (optional)</label>
                    <input type="file" name="expenses[0][receipt]" 
                           class="form-control form-control-sm" 
                           accept="image/*">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger remove-expense">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <button type="button" id="add-expense" class="btn btn-sm btn-outline-primary mt-2">
        <i class="bi bi-plus-circle"></i> Add Another Expense
    </button>

    <div class="row g-3 mt-3">
        <div class="col-md-3">
            <label class="form-label fw-bold">Total Driver Expenses (UGX)</label>
            <input type="text" id="total_expenses_display" class="form-control bg-warning bg-opacity-25 fw-bold" readonly>
        </div>
    </div>

    <hr class="my-4">

    <div class="row g-3 mt-4">
        <div class="col-md-6">
            <label class="form-label fw-bold">Calculated Cash Received (UGX)</label>
            <input type="text" id="calculated_cash_received" class="form-control form-control-lg bg-light text-primary fw-bold" readonly>
            <small class="text-muted">Auto-calculated from cash sales.</small>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">Actual Cash Received (UGX)</label>
            <input type="number" step="0.01" name="cash_received" id="actual_cash_received" 
                class="form-control form-control-lg"
                value="{{ old('cash_received', $dispatch->cash_received) }}"
                placeholder="Leave empty to auto-fill">
            <small class="text-muted">
                💡 <strong>Leave blank</strong> if driver paid the expected amount ({{ number_format($dispatch->expected_cash_after_deductions ?? 0, 0) }} UGX).
                Only enter a value if the actual amount differs.
            </small>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-3">
            <label class="form-label">Commission Total (UGX)</label>
            <input type="text" id="commission_total_display" class="form-control bg-warning bg-opacity-25 fw-bold" readonly>
            <small class="text-muted">Total driver commission</small>
        </div>

        <div class="col-md-3">
            <label class="form-label">Expected After Deductions (UGX)</label>
            <input type="text" id="expected_after_deductions_display" class="form-control bg-light fw-bold" readonly>
            <small class="text-muted">Cash - Commission - Expenses</small>
        </div>

        <div class="col-md-3">
            <label class="form-label">Amount Driver Must Pay (UGX)</label>
            <input type="text" id="amount_driver_should_pay" class="form-control bg-success bg-opacity-25 text-success fw-bold" readonly>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label fw-bold">Back Debt (UGX)</label>
            <div class="input-group">
                <input type="number" step="0.01" name="back_debt" id="back_debt_input"
                       class="form-control form-control-lg"
                       value="{{ old('back_debt', $dispatch->driver->back_debt) }}" readonly>
                <button type="button" id="unlock_back_debt" class="btn btn-outline-secondary">Unlock</button>
            </div>
            <small class="text-muted">Adjust only if you are manually reconciling debt.</small>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-12">
            <label class="form-label">Balance Due (UGX)</label>
            <input type="text" id="balance_due_display" class="form-control bg-info bg-opacity-25 fw-bold fs-5" readonly>
            <small class="text-muted">Remaining stock + Credit sales + Back debt</small>
        </div>
    </div>

    <button type="submit" class="btn btn-success btn-lg mt-4">
        <i class="bi bi-save"></i> Update Dispatch
    </button>
    <a href="{{ route('admin.dispatches.index') }}" class="btn btn-secondary btn-lg mt-4">
        <i class="bi bi-arrow-left"></i> Cancel
    </a>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    'use strict';

    // Define drivers who should not receive commission
    const excludedDrivers = ['Nakato Kampala', 'Aria Nabadda'];

    // Detect currently selected driver name from the dropdown
    let currentDriverName = $('#driver_id option:selected').text().trim();

    // Back debt handling
    $('#unlock_back_debt').on('click', function() {
        const $input = $('#back_debt_input');
        const isReadonly = $input.prop('readonly');
        
        if (isReadonly) {
            $input.prop('readonly', false).focus();
            $(this).text('Lock').removeClass('btn-outline-secondary').addClass('btn-outline-warning');
        } else {
            $input.prop('readonly', true);
            $(this).text('Unlock').removeClass('btn-outline-warning').addClass('btn-outline-secondary');
            recomputeTotals();
        }
    });

    // Update totals when back debt is manually changed
    $('#back_debt_input').on('input', function() {
        if (!$(this).prop('readonly')) {
            $('#back_debt_hidden').val($(this).val());
            recomputeTotals();
        }
    });

    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ placeholder: 'Search driver' });
    }

    const threshold = Number(@json(config('commissions.threshold', 1000000)));
    const rates = @json(config('commissions.rates'));
    const thresholdBasis = @json(config('commissions.threshold_basis', 'available'));
    let originalBackDebt = parseFloat($('#original_back_debt').val() || 0);

    let expenseIndex = {{ isset($existingExpenses) ? count($existingExpenses) : 0 }};

    // Utility functions
    function parseIntSafe(value) {
        const num = parseInt(value);
        return Number.isFinite(num) && num >= 0 ? num : 0;
    }

    function parseFloatSafe(value) {
        const num = parseFloat(value);
        return Number.isFinite(num) && num >= 0 ? num : 0;
    }

    function formatCurrency(value) {
        return parseFloat(value).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Expense management
    function calculateTotalExpenses() {
        let total = 0;
        $('.expense-amount').each(function() {
            total += parseFloatSafe($(this).val());
        });
        $('#total_expenses_display').val(formatCurrency(total));
        $('#driver_expenses_total').val(total.toFixed(2));
        return total;
    }

    $('#add-expense').on('click', function() {
        expenseIndex++;
        const newRow = `
            <div class="expense-row row g-2 mb-2 align-items-end" data-index="${expenseIndex}">
                <div class="col-md-3">
                    <label class="form-label small">Expense Type</label>
                    <select name="expenses[${expenseIndex}][expense_type]" class="form-select form-select-sm expense-type">
                        <option value="">-- Select Type --</option>
                        @foreach(\App\Models\DriverExpense::expenseTypes() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Amount (UGX)</label>
                    <input type="number" step="0.01" name="expenses[${expenseIndex}][amount]" 
                           class="form-control form-control-sm expense-amount" 
                           value="0" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Description</label>
                    <input type="text" name="expenses[${expenseIndex}][description]" 
                           class="form-control form-control-sm" 
                           placeholder="Details about this expense">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Receipt (optional)</label>
                    <input type="file" name="expenses[${expenseIndex}][receipt]" 
                           class="form-control form-control-sm" 
                           accept="image/*">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger remove-expense">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#expenses-container').append(newRow);
    });

    $(document).on('click', '.remove-expense', function() {
        $(this).closest('.expense-row').remove();
        calculateTotalExpenses();
        recomputeTotals();
    });

    $(document).on('input change', '.expense-amount', function() {
        calculateTotalExpenses();
        recomputeTotals();
    });

    // Recompute row remaining quantity
    function recomputeRow($row) {
        const opening = parseIntSafe($row.find('.opening-stock').data('opening'));
        const dispatched = parseIntSafe($row.find('.dispatched-qty').data('dispatched'));
        let soldCash = parseIntSafe($row.find('.sold-cash').val());
        let soldCredit = parseIntSafe($row.find('.sold-credit').val());
        
        const maxAvailable = opening + dispatched;
        const totalSold = soldCash + soldCredit;
        
        if (totalSold > maxAvailable) {
            // If total exceeds max, reduce both cash and credit proportionally
            const ratio = maxAvailable / totalSold;
            soldCash = Math.max(0, Math.floor(soldCash * ratio));
            soldCredit = Math.max(0, Math.floor(soldCredit * ratio));
            $row.find('.sold-cash').val(soldCash);
            $row.find('.sold-credit').val(soldCredit);
        }

        const remaining = maxAvailable - (soldCash + soldCredit);
        $row.find('.remaining-col').text(remaining);

        // Update max attribute for sold fields
        $row.find('.sold-cash').attr('max', maxAvailable);
        $row.find('.sold-credit').attr('max', maxAvailable);
    }

    // Recompute all commissions
    function recomputeCommissions() {
        let basisValue = 0;

        $('#items-table tbody tr').each(function() {
            const $r = $(this);
            const opening = parseIntSafe($r.find('.opening-stock').data('opening'));
            const dispatched = parseIntSafe($r.find('.dispatched-qty').data('dispatched'));
            const soldCash = parseIntSafe($r.find('.sold-cash').val());
            const soldCredit = parseIntSafe($r.find('.sold-credit').val());
            const totalSold = soldCash + soldCredit;
            const unitPrice = parseFloatSafe($r.data('price'));

            let qtyForBasis = 0;
            if (thresholdBasis === 'sold') {
                qtyForBasis = totalSold;
            } else if (thresholdBasis === 'dispatched') {
                qtyForBasis = dispatched;
            } else {
                qtyForBasis = opening + dispatched;
            }

            basisValue += qtyForBasis * unitPrice;
        });

        const multiplier = (basisValue >= threshold) ? 1.0 : 0.5;

        $('#items-table tbody tr').each(function() {
            const $r = $(this);
            const product = $r.data('product');
            const soldCash = parseIntSafe($r.find('.sold-cash').val());
            const soldCredit = parseIntSafe($r.find('.sold-credit').val());
            const totalSold = soldCash + soldCredit;
            const rate = parseFloatSafe(rates[product] || 0);

            let commission = 0;

            // Only calculate commission if driver is not excluded
            if (!excludedDrivers.includes(currentDriverName)) {
                commission = Math.round(totalSold * rate * multiplier);
            }

            $r.find('.commission-col').text(formatCurrency(commission));
            $r.find('.commission-value').val(commission);
        });
    }

    // Recompute all totals
    function recomputeTotals() {
        let calculatedCashReceived = 0;
        let totalItemsSold = 0;
        let commissionTotal = 0;
        let remainingInventoryValue = 0;
        let creditSalesValue = 0;

        // Loop through each product row
        $('#items-table tbody tr').each(function() {
            const $row = $(this);
            const opening = parseIntSafe($row.find('.opening-stock').data('opening'));
            const dispatched = parseIntSafe($row.find('.dispatched-qty').data('dispatched'));
            const soldCash = parseIntSafe($row.find('.sold-cash').val());
            const soldCredit = parseIntSafe($row.find('.sold-credit').val());
            const unitPrice = parseFloatSafe($row.data('price'));
            const commission = parseFloatSafe($row.find('.commission-value').val());

            const totalSold = soldCash + soldCredit;
            const remaining = (opening + dispatched) - totalSold;

            calculatedCashReceived += soldCash * unitPrice;
            creditSalesValue += soldCredit * unitPrice;
            totalItemsSold += totalSold;
            commissionTotal += commission;
            remainingInventoryValue += remaining * unitPrice;

            // Update remaining display
            $row.find('.remaining-col').text(remaining);
        });

        // Get actual cash received
        let actualCashReceived = parseFloatSafe($('#actual_cash_received').val().replace(/,/g, ''));
        
        // Calculate total driver expenses
        const driverExpenses = calculateTotalExpenses();

        // Expected after deductions
        const expectedAfterDeductions = calculatedCashReceived - commissionTotal - driverExpenses;

        // If no actual cash entered, use expected
        if (!actualCashReceived || isNaN(actualCashReceived)) {
            actualCashReceived = expectedAfterDeductions;
        }

        // Get current back debt value from input (manual adjustment only)
        const currentBackDebt = parseFloatSafe($('#back_debt_input').val());

        // Balance due = remaining inventory + credit sales + current back debt
        const balanceDue = remainingInventoryValue + creditSalesValue + currentBackDebt;

        // Update display fields
        $('#calculated_cash_received').val(formatCurrency(calculatedCashReceived));
        $('#commission_total_display').val(formatCurrency(commissionTotal));
        $('#expected_after_deductions_display').val(formatCurrency(expectedAfterDeductions));
        $('#amount_driver_should_pay').val(formatCurrency(expectedAfterDeductions));
        $('#balance_due_display').val(formatCurrency(balanceDue));

        // Update hidden form fields
        $('#commission_total').val(commissionTotal.toFixed(2));
        $('#total_sales_value').val((calculatedCashReceived + creditSalesValue).toFixed(2));
        $('#total_items_sold').val(totalItemsSold);
        $('#driver_expenses_total').val(driverExpenses.toFixed(2));
    }

    // NEW: Add event handler for dispatched quantity changes
    $('#items-table').on('input change', '.dispatched-qty', function() {
        const $row = $(this).closest('tr');
        recomputeRow($row);
        recomputeCommissions();
        recomputeTotals();
    });

    // Event handlers
    $('#items-table').on('input change', '.sold-cash, .sold-credit', function() {
        const $row = $(this).closest('tr');
        recomputeRow($row);
        recomputeCommissions();
        recomputeTotals();
    });

    $('#actual_cash_received').on('input change', function() {
        recomputeTotals();
    });

    // Initial calculations
    $('#items-table tbody tr').each(function() {
        recomputeRow($(this));
    });
    recomputeCommissions();
    calculateTotalExpenses();
    recomputeTotals();

    // Signature pad
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    const existingSig = $('#driver_signature').val();
    
    if (existingSig && existingSig.length > 0) {
        const img = new Image();
        img.onload = function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        };
        img.src = existingSig;
    }

    let drawing = false;
    let lastX = 0;
    let lastY = 0;
    
    function getMousePos(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    canvas.addEventListener('mousedown', function(e) {
        drawing = true;
        const pos = getMousePos(e);
        lastX = pos.x;
        lastY = pos.y;
    });
    
    canvas.addEventListener('mouseup', () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);
    
    canvas.addEventListener('mousemove', function(e) {
        if (!drawing) return;
        
        const pos = getMousePos(e);
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(pos.x, pos.y);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';
        ctx.stroke();
        
        lastX = pos.x;
        lastY = pos.y;
    });

    $('#clear-signature').on('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        $('#driver_signature').val('');
    });

    // Form submission
    $('form').on('submit', function(e) {
        const actualCash = $('#actual_cash_received').val();
        
        // If admin didn't enter actual cash, use expected after deductions
        if (!actualCash || actualCash == '' || actualCash == '0') {
            const expectedAfterDeductions = parseFloat($('#expected_after_deductions_display').val().replace(/,/g, ''));
            $('#actual_cash_received').val(expectedAfterDeductions.toFixed(2));
        }
        
        // Ensure back_debt field is included in form submission even if readonly
        const backDebtValue = $('#back_debt_input').val();
        if (backDebtValue !== undefined && backDebtValue !== '') {
            $('#back_debt_hidden').val(backDebtValue);
            $('#back_debt_input').prop('readonly', false);
            setTimeout(() => {
                $('#back_debt_input').prop('readonly', true);
            }, 100);
        }
        
        // Save signature
        $('#driver_signature').val(canvas.toDataURL());
    });
});
</script>
@endpush