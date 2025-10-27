@extends('admin.layouts.app')
@section('title', 'Daily Production Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-bar-chart-line me-2"></i>Daily Production & Sales Report</h4>
            <p class="text-muted mb-0">Comprehensive daily overview of production, sales, and stock</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Date Selector -->
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ $selectedDate->format('Y-m-d') }}" style="width: 200px;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>View Report
                </button>
            </form>
            
            <!-- Export & Send Buttons -->
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-send me-1"></i>Send Report
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#whatsappModal">
                            <i class="bi bi-whatsapp me-2"></i>Send via WhatsApp
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#emailModal">
                            <i class="bi bi-envelope me-2"></i>Send via Email
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.reports.daily-production.export-pdf', ['date' => $selectedDate->format('Y-m-d')]) }}">
                            <i class="bi bi-file-pdf me-2"></i>Download PDF
                        </a>
                    </li>
                    <li>
                    <a class="dropdown-item" href="{{ route('admin.reports.daily-production.export-html', ['date' => $selectedDate->format('Y-m-d')]) }}">
                            <i class="bi bi-file-code me-2"></i>Download HTML
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Production Value</h6>
                    <h4 class="text-primary mb-0">UGX {{ number_format($totalProduction) }}</h4>
                    <small class="text-muted">{{ $productions->count() }} productions</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total Sales</h6>
                    <h4 class="text-success mb-0">UGX {{ number_format($totalDispatch + $totalSales) }}</h4>
                    <small class="text-muted">Drivers + Bakery</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Bank Deposits</h6>
                    <h4 class="text-info mb-0">UGX {{ number_format($totalBankDeposits) }}</h4>
                    <small class="text-muted">{{ $bankDeposits->count() }} deposits</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Expenses</h6>
                    <h4 class="text-warning mb-0">UGX {{ number_format($totalExpenses) }}</h4>
                    <small class="text-muted">{{ $driverExpenses->count() }} expenses</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Net Cash</h6>
                    <h4 class="text-secondary mb-0">UGX {{ number_format($financialSummary['net_cash']) }}</h4>
                    <small class="text-muted">Sales - Expenses</small>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-{{ $financialSummary['cash_shortage_excess'] >= 0 ? 'success' : 'danger' }}">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Cash Balance</h6>
                    <h4 class="text-{{ $financialSummary['cash_shortage_excess'] >= 0 ? 'success' : 'danger' }} mb-0">
                        UGX {{ number_format(abs($financialSummary['cash_shortage_excess'])) }}
                    </h4>
                    <small class="text-muted">
                        {{ $financialSummary['cash_shortage_excess'] >= 0 ? 'Excess' : 'Shortage' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Breakdown -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-grid me-2"></i>Product Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Produced</th>
                            <th class="text-center">Dispatched</th>
                            <th class="text-center">Sold</th>
                            <th class="text-center">Returned</th>
                            <th class="text-center">Remaining</th>
                            <th class="text-center">Sales Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productReport as $product => $data)
                        @if($data['produced'] > 0 || $data['dispatched'] > 0 || $data['sold'] > 0)
                        <tr>
                            <td><strong>{{ ucfirst(str_replace('_', ' ', $product)) }}</strong></td>
                            <td class="text-center">{{ number_format($data['produced']) }}</td>
                            <td class="text-center">{{ number_format($data['dispatched']) }}</td>
                            <td class="text-center">{{ number_format($data['sold']) }}</td>
                            <td class="text-center">{{ number_format($data['returned']) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $data['remaining'] > 0 ? 'warning' : 'success' }}">
                                    {{ number_format($data['remaining']) }}
                                </span>
                            </td>
                            <td class="text-center text-success">
                                <strong>UGX {{ number_format($data['total_value']) }}</strong>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td><strong>TOTALS</strong></td>
                            <td class="text-center"><strong>{{ number_format(collect($productReport)->sum('produced')) }}</strong></td>
                            <td class="text-center"><strong>{{ number_format(collect($productReport)->sum('dispatched')) }}</strong></td>
                            <td class="text-center"><strong>{{ number_format(collect($productReport)->sum('sold')) }}</strong></td>
                            <td class="text-center"><strong>{{ number_format(collect($productReport)->sum('returned')) }}</strong></td>
                            <td class="text-center"><strong>{{ number_format(collect($productReport)->sum('remaining')) }}</strong></td>
                            <td class="text-center"><strong>UGX {{ number_format(collect($productReport)->sum('total_value')) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Driver Stock -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Driver Stock Remaining</h5>
                </div>
                <div class="card-body">
                    @if(count($driverStock) > 0)
                        @foreach($driverStock as $driver => $products)
                        @php $totalStock = array_sum($products); @endphp
                        @if($totalStock > 0)
                        <div class="mb-3 p-3 border rounded">
                            <h6 class="mb-2">
                                <i class="bi bi-person-circle me-1"></i>{{ $driver }}
                                <span class="badge bg-primary float-end">{{ $totalStock }} items</span>
                            </h6>
                            <div class="row g-1">
                                @foreach($products as $product => $qty)
                                @if($qty > 0)
                                <div class="col-6">
                                    <small class="text-muted">
                                        {{ ucfirst(str_replace('_', ' ', $product)) }}: 
                                        <span class="fw-bold text-dark">{{ $qty }}</span>
                                    </small>
                                </div>
                                @endif
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Sales: <strong class="text-success">UGX {{ number_format($driverSales[$driver] ?? 0) }}</strong> | 
                                    Deposits: <strong class="text-info">UGX {{ number_format($driverDeposits[$driver] ?? 0) }}</strong>
                                </small>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-2 mb-0">No driver stock remaining</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted">Total Production Value</small>
                            <h5 class="text-primary">UGX {{ number_format($financialSummary['total_production_value']) }}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total Sales Value</small>
                            <h5 class="text-success">UGX {{ number_format($financialSummary['total_sales_value']) }}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total Bank Deposits</small>
                            <h5 class="text-info">UGX {{ number_format($financialSummary['total_bank_deposits']) }}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total Expenses</small>
                            <h5 class="text-warning">UGX {{ number_format($financialSummary['total_expenses']) }}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Net Cash (Sales - Expenses)</small>
                            <h5 class="text-secondary">UGX {{ number_format($financialSummary['net_cash']) }}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Cash {{ $financialSummary['cash_shortage_excess'] >= 0 ? 'Excess' : 'Shortage' }}</small>
                            <h5 class="text-{{ $financialSummary['cash_shortage_excess'] >= 0 ? 'success' : 'danger' }}">
                                UGX {{ number_format(abs($financialSummary['cash_shortage_excess'])) }}
                            </h5>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Bank Deposits List -->
                    <h6 class="mt-3 mb-2"><i class="bi bi-bank me-1"></i>Bank Deposits</h6>
                    @if($bankDeposits->count() > 0)
                        @foreach($bankDeposits as $deposit)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <div>
                                <small class="fw-bold">{{ $deposit->depositor->name ?? 'Unknown' }}</small>
                                <br>
                                <small class="text-muted">{{ $deposit->receipt ?? 'No receipt' }}</small>
                            </div>
                            <div class="text-success fw-bold">UGX {{ number_format($deposit->amount) }}</div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center mb-0">No bank deposits today</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Production Details -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Production Details</h5>
        </div>
        <div class="card-body">
            @if($productions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Chef</th>
                                <th>Products</th>
                                <th class="text-center">Total Value</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productions as $production)
                            <tr>
                                <td>{{ $production->chef->name ?? 'Unknown' }}</td>
                                <td>
                                    @php
                                        $productList = [];
                                        foreach (config('bakery_products') as $product => $price) {
                                            if ($production->$product > 0) {
                                                $productList[] = ucfirst(str_replace('_', ' ', $product)) . ': ' . $production->$product;
                                            }
                                        }
                                    @endphp
                                    <small>{{ implode(', ', $productList) }}</small>
                                </td>
                                <td class="text-center text-success fw-bold">UGX {{ number_format($production->total_value) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success">Approved</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center mb-0">No productions for this date</p>
            @endif
        </div>
    </div>
</div>

<!-- WhatsApp Modal -->
<div class="modal fade" id="whatsappModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-whatsapp me-2"></i>Send via WhatsApp</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.reports.daily-production.send-whatsapp') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" 
                               value="{{ env('BOSS_WHATSAPP_NUMBER', '') }}" 
                               placeholder="+256XXXXXXXXX" required>
                        <small class="text-muted">Include country code (e.g., +256...)</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        A summary report will be sent via WhatsApp message.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-whatsapp me-1"></i>Send WhatsApp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-envelope me-2"></i>Send via Email</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.reports.daily-production.send-email') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" 
                               value="{{ env('BOSS_EMAIL', '') }}" 
                               placeholder="boss@company.com" required>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        A detailed PDF report will be sent via email attachment.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-envelope me-1"></i>Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}
.border-primary { border-left: 4px solid #007bff !important; }
.border-success { border-left: 4px solid #28a745 !important; }
.border-info { border-left: 4px solid #17a2b8 !important; }
.border-warning { border-left: 4px solid #ffc107 !important; }
.border-secondary { border-left: 4px solid #6c757d !important; }
</style>
@endsection