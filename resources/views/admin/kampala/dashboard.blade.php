@extends('admin.layouts.app')
@section('title', 'Kampala Shops Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-shop me-2"></i>Kampala Shops Dashboard</h4>
</div>

<!-- Overall Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Total Shops</div>
                <div class="stat fs-2">{{ $stats['total_shops'] }}</div>
                <small class="text-muted">{{ $stats['active_shops'] }} active</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Total Sales</div>
                <div class="stat fs-2 text-success">UGX {{ number_format($stats['total_sales']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Total Expenses</div>
                <div class="stat fs-2 text-danger">UGX {{ number_format($stats['total_expenses']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Total Banked</div>
                <div class="stat fs-2 text-info">UGX {{ number_format($stats['total_banked']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Today's Sales</div>
                <div class="stat fs-2 text-primary">UGX {{ number_format($todayStats['sales']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="text-muted">Pending Dispatches</div>
                <div class="stat fs-2 text-warning">{{ $stats['pending_dispatches'] }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Shops List -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Kampala Shops</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Shop Name</th>
                        <th>Location</th>
                        <th>Manager</th>
                        <th class="text-end">Total Sales</th>
                        <th class="text-end">Total Expenses</th>
                        <th class="text-end">Total Banked</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shops as $shop)
                    @php
                        // Use fully qualified namespace or pass shop stats from controller
                        $shopSales = \App\Models\KampalaSale::where('shop_id', $shop->id)->sum('total_price');
                        $shopExpenses = \App\Models\KampalaExpense::where('shop_id', $shop->id)->sum('amount');
                        $shopBankings = \App\Models\KampalaBanking::where('shop_id', $shop->id)->sum('amount');
                    @endphp
                    <tr>
                        <td>{{ $shop->name }}</td>
                        <td>{{ $shop->location }}</td>
                        <td>{{ $shop->manager->name ?? 'N/A' }}</td>
                        <td class="text-end text-success">UGX {{ number_format($shopSales) }}</td>
                        <td class="text-end text-danger">UGX {{ number_format($shopExpenses) }}</td>
                        <td class="text-end text-info">UGX {{ number_format($shopBankings) }}</td>
                        <td>
                            <span class="badge bg-{{ $shop->status == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($shop->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.kampala.shop-activities', $shop->id) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View Activities
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Recent Sales</h5>
            </div>
            <div class="card-body">
                @foreach($recentSales as $sale)
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <div>
                        <strong>{{ $sale->shop->name }}</strong><br>
                        <small>{{ ucwords(str_replace('_', ' ', $sale->product_type)) }}</small>
                    </div>
                    <div class="text-end">
                        <div>UGX {{ number_format($sale->total_price) }}</div>
                        <small class="text-muted">{{ $sale->created_at->format('M d, H:i') }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Recent Bankings</h5>
            </div>
            <div class="card-body">
                @foreach($recentBankings as $banking)
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <div>
                        <strong>{{ $banking->shop->name }}</strong><br>
                        <small>{{ $banking->receipt_number ?? 'No Receipt' }}</small>
                    </div>
                    <div class="text-end">
                        <div class="text-success">UGX {{ number_format($banking->amount) }}</div>
                        <small class="text-muted">{{ $banking->date->format('M d, Y') }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- More Recent Activities -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Recent Dispatches</h5>
            </div>
            <div class="card-body">
                @foreach($recentDispatches as $dispatch)
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <div>
                        <strong>{{ $dispatch->shop->name }}</strong><br>
                        <small>{{ $dispatch->dispatch_no }} • {{ $dispatch->total_items }} items</small>
                    </div>
                    <div class="text-end">
                        <div>UGX {{ number_format($dispatch->total_value) }}</div>
                        <small class="text-muted">{{ $dispatch->status }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Recent Expenses</h5>
            </div>
            <div class="card-body">
                @foreach($recentExpenses as $expense)
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <div>
                        <strong>{{ $expense->shop->name }}</strong><br>
                        <small>{{ $expense->description }}</small>
                    </div>
                    <div class="text-end">
                        <div class="text-danger">-UGX {{ number_format($expense->amount) }}</div>
                        <small class="text-muted">{{ $expense->expense_date->format('M d, Y') }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection