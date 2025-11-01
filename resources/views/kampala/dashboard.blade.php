@extends('kampala.layouts.app')
@section('title','Dashboard')

@section('content')
    <h4 class="mb-4"><i class="bi bi-speedometer2 me-2"></i>Kampala Shop Dashboard
        <small class="text-muted fs-6">• {{ $shop->name ?? 'No Shop' }}</small>
    </h4>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted">Pending Dispatches</div>
                    <div class="stat fs-2 text-warning">{{ $pendingDispatches ?? 0 }}</div>
                    <small class="text-muted">Awaiting receipt</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted">Today's Sales</div>
                    <div class="stat fs-2 text-success">UGX {{ number_format($todaySales ?? 0) }}</div>
                    <small class="text-muted">Revenue today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted">Total Expenses</div>
                    <div class="stat fs-2 text-danger">UGX {{ number_format($totalExpenses ?? 0) }}</div>
                    <small class="text-muted">Shop expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted">Low Stock Items</div>
                    <div class="stat fs-2 text-danger">{{ $stockAlerts ?? 0 }}</div>
                    <small class="text-muted">Need restocking</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted">Available Cash</div>
                    <div class="stat fs-2 text-primary">UGX {{ number_format($availableCash ?? 0) }}</div>
                    <small class="text-muted">For banking</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('kampala.dispatches.index') }}" class="card stat-card text-decoration-none">
                <div class="card-body text-center">
                    <i class="bi bi-truck display-4 text-primary mb-3"></i>
                    <h5>Receive Dispatches</h5>
                    <p class="text-muted">Check and receive new dispatches from bakery</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('kampala.sales.create') }}" class="card stat-card text-decoration-none">
                <div class="card-body text-center">
                    <i class="bi bi-cash-coin display-4 text-success mb-3"></i>
                    <h5>New Sale</h5>
                    <p class="text-muted">Record customer sales and update stock</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('kampala.bankings.index') }}" class="card stat-card text-decoration-none">
                <div class="card-body text-center">
                    <i class="bi bi-bank display-4 text-info mb-3"></i>
                    <h5>Record Banking</h5>
                    <p class="text-muted">Deposit sales proceeds to bank</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Recent Dispatches</h5>
                </div>
                <div class="card-body">
                    @php
                        $recentDispatches = \App\Models\KampalaDispatch::with('manager')
                            ->where('shop_id', $shop->id)
                            ->latest()
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @forelse($recentDispatches as $dispatch)
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <div>
                            <strong>{{ $dispatch->dispatch_no }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $dispatch->dispatch_date->format('M d') }} • 
                                {{ $dispatch->total_items }} items
                            </small>
                        </div>
                        <span class="badge bg-{{ $dispatch->status == 'received' ? 'success' : ($dispatch->status == 'partial' ? 'primary' : 'warning') }}">
                            {{ ucfirst($dispatch->status) }}
                        </span>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">No recent dispatches</p>
                    @endforelse
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('kampala.dispatches.index') }}" class="btn btn-sm btn-outline-primary">
                            View All Dispatches
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Recent Sales</h5>
                </div>
                <div class="card-body">
                    @php
                        $recentSales = \App\Models\KampalaSale::with('user')
                            ->where('shop_id', $shop->id)
                            ->latest()
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @forelse($recentSales as $sale)
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <div>
                            <strong>{{ ucwords(str_replace('_', ' ', $sale->product_type)) }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $sale->quantity }} × UGX {{ number_format($sale->unit_price) }}
                            </small>
                        </div>
                        <span class="fw-bold text-success">
                            UGX {{ number_format($sale->total_price) }}
                        </span>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">No recent sales</p>
                    @endforelse
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('kampala.sales.index') }}" class="btn btn-sm btn-outline-success">
                            View All Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection