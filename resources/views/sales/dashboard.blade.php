@extends('sales.layouts.app')
@section('title','Dashboard')

@section('content')
    <h4 class="mb-4"><i class="bi bi-speedometer2 me-2"></i>Bakery Cash Desk 
        <small class="text-muted fs-6">• Live Physical Cash Tracking</small>
        <span id="lastUpdate" class="badge bg-success ms-2">Live</span>
    </h4>

    {{-- Physical Cash Overview --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title mb-1">
                                <i class="bi bi-cash-coin me-2"></i>Physical Cash in Bakery Desk
                            </h5>
                            <p class="text-muted mb-0 small">
                                Real-time calculation of actual cash in drawer
                            </p>
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="bi bi-arrow-down-circle me-1"></i>
                                    Today's Flow: 
                                    <span id="todaySales" class="{{ $todayFlow['today_cash_sales'] > 0 ? 'text-success' : 'text-muted' }}">
                                        +UGX {{ number_format($todayFlow['today_cash_sales']) }}
                                    </span> 
                                    <span id="todayExpenses" class="{{ $todayFlow['today_expenses'] > 0 ? 'text-danger' : 'text-muted' }}">
                                        -UGX {{ number_format($todayFlow['today_expenses']) }}
                                    </span>
                                    <span id="todayBanked" class="{{ $todayFlow['today_banked'] > 0 ? 'text-primary' : 'text-muted' }}">
                                        -UGX {{ number_format($todayFlow['today_banked']) }}
                                    </span>
                                </small>
                                @if($todayFlow['today_cash_sales'] == 0 && $todayFlow['today_expenses'] == 0 && $todayFlow['today_banked'] == 0)
                                <div class="mt-1">
                                    <small class="text-warning">
                                        <i class="bi bi-info-circle me-1"></i>
                                        No transactions recorded today yet
                                    </small>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <h2 class="text-success mb-0" id="availableCash">UGX {{ number_format($balance['available_cash']) }}</h2>
                            <small class="text-muted">Actual cash in bakery desk • Updated: <span id="updateTime">{{ now()->format('H:i:s') }}</span></small>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="updateCashBalance()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh Cash
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Today's Transactions</div>
                    <div class="stat fs-4">{{ number_format($summary['count']) }}</div>
                    @if($summary['count'] == 0)
                    <small class="text-warning">No sales today</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Units Sold Today</div>
                    <div class="stat fs-4">{{ number_format($summary['qty']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Today's Cash Revenue</div>
                    <div class="stat fs-4">{{ number_format($summary['total']) }} UGX</div>
                    @if($summary['total'] == 0)
                    <small class="text-warning">No cash sales</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <div class="text-muted">Desk Cash</div>
                    <div class="stat fs-4 text-success" id="availableCashCard">UGX {{ number_format($balance['available_cash']) }}</div>
                    <small class="text-muted">Physical cash in drawer</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Bakery Cash Flow</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total Cash Sales:</span>
                        <strong class="text-success">+ UGX {{ number_format($balance['cash_sales']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Bakery Bankings:</span>
                        <strong class="text-danger">- UGX {{ number_format($balance['total_banked']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Expenses Paid:</span>
                        <strong class="text-warning">- UGX {{ number_format($balance['total_expenses']) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <span><strong>Physical Cash in Desk:</strong></span>
                        <strong class="text-success">UGX {{ number_format($balance['available_cash']) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">
                        @if($topProducts->count() > 0)
                            Today's Top Products
                        @else
                            Product Sales Today
                        @endif
                    </h6>
                    <div style="height: 300px;">
                        @if($topProducts->count() > 0)
                            <canvas id="productsChart"></canvas>
                        @else
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="text-center text-muted">
                                    <i class="bi bi-bar-chart display-4"></i>
                                    <p class="mt-2">No product sales today</p>
                                    <small>Sales data will appear here</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    @if($summary['count'] == 0)
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-body text-center py-4">
                    <i class="bi bi-cart-plus display-4 text-warning mb-3"></i>
                    <h5 class="text-warning">Ready to Start Selling?</h5>
                    <p class="text-muted mb-3">No sales recorded for today yet. Start recording sales to see real-time cash updates.</p>
                    <a href="{{ route('sales.sales.create') }}" class="btn btn-warning">
                        <i class="bi bi-plus-circle me-2"></i>Record First Sale
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if($topProducts->count() > 0)
    // Chart initialization
    const ctx = document.getElementById('productsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($topProducts->keys()) !!},
            datasets: [{
                label: 'Units Sold',
                data: {!! json_encode($topProducts->values()) !!},
                backgroundColor: '#3b82f6',
                borderColor: '#2563eb',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 } }
            },
            plugins: { legend: { display: false } },
            layout: { padding: 10 }
        }
    });
    @endif

    // Real-time cash balance updates
    function updateCashBalance() {
        const lastUpdate = document.getElementById('lastUpdate');
        
        lastUpdate.textContent = 'Updating...';
        lastUpdate.className = 'badge bg-warning ms-2';

        fetch('{{ route("sales.cash-balance") }}')
            .then(response => response.json())
            .then(data => {
                // Update all cash balance elements
                document.getElementById('availableCash').textContent = 'UGX ' + data.available_cash.toLocaleString();
                document.getElementById('availableCashCard').textContent = 'UGX ' + data.available_cash.toLocaleString();
                
                // Update today's breakdown with conditional styling
                const todaySales = document.getElementById('todaySales');
                const todayExpenses = document.getElementById('todayExpenses');
                const todayBanked = document.getElementById('todayBanked');
                
                todaySales.textContent = '+UGX ' + data.today_cash_sales.toLocaleString();
                todayExpenses.textContent = '-UGX ' + data.today_expenses.toLocaleString();
                todayBanked.textContent = '-UGX ' + data.today_banked.toLocaleString();
                
                // Apply conditional styling
                todaySales.className = data.today_cash_sales > 0 ? 'text-success' : 'text-muted';
                todayExpenses.className = data.today_expenses > 0 ? 'text-danger' : 'text-muted';
                todayBanked.className = data.today_banked > 0 ? 'text-primary' : 'text-muted';
                
                // Update time
                document.getElementById('updateTime').textContent = data.updated_at;
                
                // Update status
                lastUpdate.textContent = 'Live';
                lastUpdate.className = 'badge bg-success ms-2';
            })
            .catch(error => {
                console.error('Error updating cash balance:', error);
                lastUpdate.textContent = 'Error';
                lastUpdate.className = 'badge bg-danger ms-2';
            });
    }

    // Auto-refresh every 30 seconds
    setInterval(updateCashBalance, 30000);

    // Initial update after page load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(updateCashBalance, 2000);
    });
</script>
@endpush