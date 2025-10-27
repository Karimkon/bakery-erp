@extends('manager.layouts.app')
@section('title', 'Production Approvals')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-check2-square me-2"></i>Production Approvals</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Status Filter Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a href="{{ route('manager.productions.approval.index', ['status' => 'pending']) }}" 
               class="nav-link {{ $status == 'pending' ? 'active' : '' }}">
                <i class="bi bi-clock-history me-1"></i>Pending
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('manager.productions.approval.index', ['status' => 'approved']) }}" 
               class="nav-link {{ $status == 'approved' ? 'active' : '' }}">
                <i class="bi bi-check-circle me-1"></i>Approved
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('manager.productions.approval.index', ['status' => 'rejected']) }}" 
               class="nav-link {{ $status == 'rejected' ? 'active' : '' }}">
                <i class="bi bi-x-circle me-1"></i>Rejected
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('manager.productions.approval.index', ['status' => 'all']) }}" 
               class="nav-link {{ $status == 'all' ? 'active' : '' }}">
                <i class="bi bi-list me-1"></i>All
            </a>
        </li>
    </ul>

    <!-- Productions Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Chef</th>
                            <th>Production Date</th>
                            <th>Total Products</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productions as $production)
                        <tr>
                            <td><strong>#{{ $production->id }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-2">
                                        {{ substr($production->chef->name ?? 'U', 0, 1) }}
                                    </div>
                                    {{ $production->chef->name ?? 'Unknown' }}
                                </div>
                            </td>
                            <td>{{ $production->production_date->format('d M Y') }}</td>
                            <td>
                                @php
                                    $total = collect([
                                        $production->buns, $production->small_breads, $production->big_breads,
                                        $production->donuts, $production->half_cakes, $production->block_cakes,
                                        $production->slab_cakes, $production->quarter_breads, 
                                        $production->birthday_cakes30k, $production->birthday_cakes50k,
                                        $production->mandazis, $production->musiba_tayi, $production->scornes,
                                        $production->chapatys, $production->toasted_bread, $production->spring_donuts,
                                        $production->cream_donuts, $production->cinnamon_rolls
                                    ])->sum();
                                @endphp
                                <span class="badge bg-info">{{ number_format($total) }} items</span>
                            </td>
                            <td>
                                @if($production->status == 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i>Pending
                                    </span>
                                @elseif($production->status == 'approved')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Approved
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i>Rejected
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('manager.productions.approval.show', $production->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No productions found for this status
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $productions->appends(['status' => $status])->links() }}
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
</style>
@endsection