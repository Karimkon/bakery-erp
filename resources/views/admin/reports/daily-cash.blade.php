@extends('admin.layouts.app')

@section('title', 'Daily Cash Report - ' . $selectedDate->format('M d, Y'))

@section('content')
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-cash-stack text-success me-2"></i>Daily Cash Report
            </h3>
            <div class="text-muted">
                {{ $selectedDate->format('l, F d, Y') }}
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date" value="{{ $date }}" class="form-control" 
                       onchange="this.form.submit()">
                <a href="{{ route('admin.reports.daily-cash.range') }}" class="btn btn-outline-primary">
                    <i class="bi bi-calendar-range"></i> Date Range
                </a>
                <button type="button" class="btn btn-success" onclick="showDailyHistory()">
                    <i class="bi bi-clock-history me-1"></i> View History
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Sales</h6>
                    <h3>{{ number_format($totalSales) }} UGX</h3>
                    <small>
                        Cash: {{ number_format($cashSales) }} | 
                        Mobile: {{ number_format($mobileSales) }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Expenses</h6>
                    <h3>{{ number_format($totalExpenses) }} UGX</h3>
                    <small>{{ $expenses->count() }} records</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Banked</h6>
                    <h3>{{ number_format($totalBanked) }} UGX</h3>
                    <small>
                        Bakery: {{ number_format($totalBankDeposits) }} | 
                        Driver: {{ number_format($totalDriverBankings) }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Expected Cash</h6>
                    <h3>{{ number_format($expectedCash) }} UGX</h3>
                    <small>Cash Sales - Expenses - Banked</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Money at Bakery Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-cash-coin me-2"></i>Live Bakery Cash Desk
                        <span id="cashUpdateStatus" class="badge bg-light text-success ms-2">Live</span>
                    </h6>
                    <div>
                        <small class="me-2">Last updated: <span id="cashLastUpdate">{{ now()->format('H:i:s') }}</span></small>
                        <button class="btn btn-sm btn-light" onclick="updateBakeryCash()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Current Cash Balance -->
                        <div class="col-md-4 text-center">
                            <div class="display-4 fw-bold text-success mb-2" id="currentCashBalance">
                                {{ number_format($availableCash) }} UGX
                            </div>
                            <div class="text-muted small">
                                Physical cash in bakery desk
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-success" id="cashStatus">LIVE</span>
                            </div>
                        </div>

                        <!-- Today's Cash Flow -->
                        <div class="col-md-4">
                            <h6 class="mb-3">Today's Cash Flow</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Cash Sales:</span>
                                <strong class="text-success" id="todayCashSales">+ {{ number_format($todayCashSales) }} UGX</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Expenses:</span>
                                <strong class="text-danger" id="todayExpenses">- {{ number_format($todayExpenses) }} UGX</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Bankings:</span>
                                <strong class="text-primary" id="todayBankings">- {{ number_format($todayBankings) }} UGX</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Net Today:</strong></span>
                                <strong class="text-warning" id="todayNetCash">{{ number_format($todayNetCash) }} UGX</strong>
                            </div>
                        </div>

                        <!-- Cumulative Breakdown -->
                        <div class="col-md-4">
                            <h6 class="mb-3">Cumulative Breakdown</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Total Cash Sales:</span>
                                <strong class="text-success" id="totalCashSales">{{ number_format($totalCashSales) }} UGX</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Total Expenses:</span>
                                <strong class="text-danger" id="totalExpensesBreakdown">{{ number_format($totalExpensesBreakdown) }} UGX</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Total Bankings:</span>
                                <strong class="text-primary" id="totalBankings">{{ number_format($totalBankings) }} UGX</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Available Cash:</strong></span>
                                <strong class="text-success" id="availableCashBreakdown">{{ number_format($availableCashBreakdown) }} UGX</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Cash Movement Alert -->
                    <div id="cashMovementAlert" class="alert alert-info mt-3" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-info-circle me-2"></i>
                                <span id="movementMessage">Cash balance updated</span>
                            </div>
                            <button type="button" class="btn-close" onclick="hideMovementAlert()"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Daily History -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-week me-2"></i>Recent Days Summary
                        <small class="text-muted ms-2">Click on a day to view details</small>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="dailyHistoryLoading" class="text-center py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading recent days...</p>
                    </div>
                    <div id="dailyHistoryContent" class="row g-2" style="display: none;">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rest of your existing content remains exactly the same -->
    <!-- ... your existing detailed sections ... -->

</div>

<!-- Your existing modals remain the same -->
<!-- Daily Summary Modal -->
<!-- Daily Summary Modal -->
<div class="modal fade" id="dailySummaryModal" tabindex="-1" aria-labelledby="dailySummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="dailySummaryModalLabel">Daily Summary</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Initialize cash balance variables
let currentCashBalance = {{ $availableCash }};
let previousCashBalance = {{ $availableCash }};

// Function to update bakery cash in real-time
function updateBakeryCash() {
    const statusBadge = document.getElementById('cashUpdateStatus');
    const refreshBtn = document.querySelector('button[onclick="updateBakeryCash()"]');
    const cashBalance = document.getElementById('currentCashBalance');
    const cashStatus = document.getElementById('cashStatus');
    
    // Show loading state
    statusBadge.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
    statusBadge.className = 'badge bg-warning ms-2';
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Updating...';

    // Make AJAX call to get real-time cash balance
    fetch('/admin/api/cash-balance', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        previousCashBalance = currentCashBalance;
        currentCashBalance = data.available_cash;
        
        // Update all displays
        cashBalance.textContent = data.available_cash.toLocaleString() + ' UGX';
        document.getElementById('todayCashSales').textContent = '+ ' + data.today_cash_sales.toLocaleString() + ' UGX';
        document.getElementById('todayExpenses').textContent = '- ' + data.today_expenses.toLocaleString() + ' UGX';
        document.getElementById('todayBankings').textContent = '- ' + data.today_banked.toLocaleString() + ' UGX';
        document.getElementById('todayNetCash').textContent = data.today_net_cash.toLocaleString() + ' UGX';
        document.getElementById('totalCashSales').textContent = data.cash_sales.toLocaleString() + ' UGX';
        document.getElementById('totalExpensesBreakdown').textContent = data.total_expenses.toLocaleString() + ' UGX';
        document.getElementById('totalBankings').textContent = data.total_banked.toLocaleString() + ' UGX';
        document.getElementById('availableCashBreakdown').textContent = data.available_cash.toLocaleString() + ' UGX';
        document.getElementById('cashLastUpdate').textContent = new Date().toLocaleTimeString();
        
        // Show movement alert if there was a change
        const change = currentCashBalance - previousCashBalance;
        if (change !== 0) {
            showMovementAlert(change);
        }
        
        // Update status
        statusBadge.textContent = 'Live';
        statusBadge.className = 'badge bg-success ms-2';
        cashStatus.textContent = 'LIVE';
        refreshBtn.disabled = false;
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh';
    })
    .catch(error => {
        console.error('Error updating cash balance:', error);
        statusBadge.textContent = 'Error';
        statusBadge.className = 'badge bg-danger ms-2';
        refreshBtn.disabled = false;
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh';
    });
}

// Function to show cash movement alert
function showMovementAlert(change) {
    const alert = document.getElementById('cashMovementAlert');
    const message = document.getElementById('movementMessage');
    
    if (change > 0) {
        message.textContent = `Cash increased by ${Math.abs(change).toLocaleString()} UGX`;
        alert.className = 'alert alert-success mt-3';
    } else {
        message.textContent = `Cash decreased by ${Math.abs(change).toLocaleString()} UGX`;
        alert.className = 'alert alert-warning mt-3';
    }
    
    alert.style.display = 'block';
    
    // Auto-hide after 5 seconds
    setTimeout(hideMovementAlert, 5000);
}

// Function to hide movement alert
function hideMovementAlert() {
    document.getElementById('cashMovementAlert').style.display = 'none';
}

// Load recent days summary
function loadRecentDays() {
    const loading = document.getElementById('dailyHistoryLoading');
    const content = document.getElementById('dailyHistoryContent');
    
    loading.style.display = 'block';
    content.style.display = 'none';
    
    // Get last 7 days
    const dates = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        dates.push(date.toISOString().split('T')[0]);
    }
    
    // Fetch summaries for all dates
    Promise.all(dates.map(date => 
        fetch(`/admin/reports/daily-cash/api/summary?date=${date}`)
            .then(response => response.json())
    ))
    .then(results => {
        content.innerHTML = '';
        
        results.forEach(data => {
            const card = createDayCard(data);
            content.innerHTML += card;
        });
        
        loading.style.display = 'none';
        content.style.display = 'flex';
    })
    .catch(error => {
        console.error('Error loading daily history:', error);
        loading.innerHTML = '<p class="text-danger">Error loading data. Please try again.</p>';
    });
}

// Create a day summary card
function createDayCard(data) {
    const isToday = data.date === new Date().toISOString().split('T')[0];
    const cardClass = isToday ? 'border-primary' : '';
    
    return `
        <div class="col-md-3">
            <div class="card ${cardClass} h-100" style="cursor: pointer;" onclick="showDaySummary('${data.date}')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">${data.formatted_date}</h6>
                        ${isToday ? '<span class="badge bg-primary">Today</span>' : ''}
                    </div>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Cash Sales:</span>
                            <strong class="text-success">${data.summary.cash_sales.toLocaleString()}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Expenses:</span>
                            <strong class="text-danger">${data.summary.total_expenses.toLocaleString()}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Banked:</span>
                            <strong class="text-primary">${data.summary.total_banked.toLocaleString()}</strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <strong>Expected:</strong>
                            <strong class="text-warning">${data.summary.expected_cash.toLocaleString()}</strong>
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <small class="text-muted">
                            ${data.records.sales_count} sales | 
                            ${data.records.expenses_count} expenses | 
                            ${data.records.bankings_count} bankings
                        </small>
                    </div>
                    <div class="mt-2 text-center">
                        <span class="badge bg-info">Click for details</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Show detailed summary modal for a specific day
function showDaySummary(date) {
    const modal = new bootstrap.Modal(document.getElementById('dailySummaryModal'));
    const modalTitle = document.getElementById('dailySummaryModalLabel');
    const modalBody = document.querySelector('#dailySummaryModal .modal-body');
    
    // Show loading state
    modalTitle.textContent = 'Loading...';
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading day details...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch detailed data
    fetch(`/admin/reports/daily-cash/api/summary?date=${date}`)
        .then(response => response.json())
        .then(data => {
            modalTitle.textContent = `Daily Summary - ${data.formatted_date}`;
            modalBody.innerHTML = createDetailedSummary(data);
        })
        .catch(error => {
            console.error('Error loading day summary:', error);
            modalBody.innerHTML = '<div class="alert alert-danger">Error loading data. Please try again.</div>';
        });
}

// Create detailed summary HTML
function createDetailedSummary(data) {
    return `
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-1">Cash Sales</h6>
                        <h4 class="mb-0">${data.summary.cash_sales.toLocaleString()} UGX</h4>
                        <small>${data.records.sales_count} transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-1">Expenses</h6>
                        <h4 class="mb-0">${data.summary.total_expenses.toLocaleString()} UGX</h4>
                        <small>${data.records.expenses_count} records</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-1">Banked</h6>
                        <h4 class="mb-0">${data.summary.total_banked.toLocaleString()} UGX</h4>
                        <small>${data.records.bankings_count} deposits</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-1">Expected Cash</h6>
                        <h4 class="mb-0">${data.summary.expected_cash.toLocaleString()} UGX</h4>
                        <small>Net amount</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs for detailed records -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales-content" type="button">
                    <i class="bi bi-cart-check me-1"></i>Sales (${data.records.sales_count})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses-content" type="button">
                    <i class="bi bi-receipt me-1"></i>Expenses (${data.records.expenses_count})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bankings-tab" data-bs-toggle="tab" data-bs-target="#bankings-content" type="button">
                    <i class="bi bi-bank me-1"></i>Bankings (${data.records.bankings_count})
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Sales Tab -->
            <div class="tab-pane fade show active" id="sales-content">
                ${data.records.sales.length > 0 ? `
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.records.sales.map(sale => `
                                    <tr>
                                        <td><small>${sale.time}</small></td>
                                        <td>${sale.product}</td>
                                        <td>${sale.quantity}</td>
                                        <td class="text-success fw-bold">${sale.amount} UGX</td>
                                        <td><span class="badge ${sale.method === 'cash' ? 'bg-success' : 'bg-info'}">${sale.method}</span></td>
                                        <td><small>${sale.user}</small></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    ${data.records.sales_count > 10 ? `
                        <p class="text-muted text-center mb-0">
                            <small>Showing first 10 of ${data.records.sales_count} transactions</small>
                        </p>
                    ` : ''}
                ` : '<p class="text-center text-muted py-4">No sales recorded for this day</p>'}
            </div>

            <!-- Expenses Tab -->
            <div class="tab-pane fade" id="expenses-content">
                ${data.records.expenses.length > 0 ? `
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Receipt</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.records.expenses.map(expense => `
                                    <tr>
                                        <td><span class="badge bg-secondary">${expense.category}</span></td>
                                        <td>${expense.description}</td>
                                        <td class="text-danger fw-bold">${expense.amount} UGX</td>
                                        <td>
                                            ${expense.has_receipt ? 
                                                '<i class="bi bi-file-earmark-check text-success"></i>' : 
                                                '<i class="bi bi-file-earmark-x text-muted"></i>'}
                                        </td>
                                        <td><small>${expense.recorded_by}</small></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    ${data.records.expenses_count > 10 ? `
                        <p class="text-muted text-center mb-0">
                            <small>Showing first 10 of ${data.records.expenses_count} expenses</small>
                        </p>
                    ` : ''}
                ` : '<p class="text-center text-muted py-4">No expenses recorded for this day</p>'}
            </div>

            <!-- Bankings Tab -->
            <div class="tab-pane fade" id="bankings-content">
                ${data.records.bankings.length > 0 ? `
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>User/Driver</th>
                                    <th>Amount</th>
                                    <th>Receipt #</th>
                                    <th>Receipt</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.records.bankings.map(banking => `
                                    <tr>
                                        <td>${banking.user}</td>
                                        <td class="text-primary fw-bold">${banking.amount} UGX</td>
                                        <td><code>${banking.receipt_number}</code></td>
                                        <td>
                                            ${banking.has_receipt ? 
                                                '<i class="bi bi-file-earmark-check text-success"></i>' : 
                                                '<i class="bi bi-file-earmark-x text-muted"></i>'}
                                        </td>
                                        <td><small>${banking.notes || '-'}</small></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    ${data.records.bankings_count > 10 ? `
                        <p class="text-muted text-center mb-0">
                            <small>Showing first 10 of ${data.records.bankings_count} banking records</small>
                        </p>
                    ` : ''}
                ` : '<p class="text-center text-muted py-4">No bankings recorded for this day</p>'}
            </div>
        </div>

        <!-- View Full Report Button -->
        <div class="text-center mt-4">
            <a href="?date=${data.date}" class="btn btn-primary">
                <i class="bi bi-file-text me-1"></i>View Full Report for This Day
            </a>
        </div>
    `;
}

// Show daily history (redirect to range view)
function showDailyHistory() {
    window.location.href = '{{ route("admin.reports.daily-cash.range") }}';
}

// Auto-refresh every 30 seconds
setInterval(updateBakeryCash, 30000);

// Load recent days when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadRecentDays();
});
</script>

<style>
/* Animation for cash balance updates */
@keyframes cashUpdate {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.cash-updated {
    animation: cashUpdate 0.5s ease-in-out;
}

/* Style for the live cash section */
.card-border-success {
    border-left: 4px solid #198754 !important;
}

.display-4 {
    font-size: 2.5rem;
    font-weight: 700;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }
}
</style>
@endpush