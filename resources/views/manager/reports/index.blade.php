@extends('manager.layouts.app')
@section('title', 'Manager Reports Dashboard')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Manager Reports Dashboard</h4>
                <div class="export-buttons">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-2"></i>Export Reports
                        </button>
                        <div class="dropdown-menu">
                            {{-- Dispatch Exports --}}
                            <!-- <a class="dropdown-item" href="{{ route('manager.dispatch.exportPdf') }}?{{ http_build_query(request()->query()) }}">
                                <i class="fas fa-file-pdf text-danger me-2"></i>Dispatch PDF
                            </a> -->
                            <a class="dropdown-item" href="{{ route('manager.dispatch.exportExcel') }}?{{ http_build_query(request()->query()) }}">
                                <i class="fas fa-file-excel text-success me-2"></i>Dispatch Excel
                            </a>
                            <div class="dropdown-divider"></div>
                            {{-- Production Exports --}}
                            <!-- <a class="dropdown-item" href="{{ route('manager.production.exportPdf') }}?{{ http_build_query(request()->query()) }}">
                                <i class="fas fa-file-pdf text-danger me-2"></i>Production PDF
                            </a> -->
                            <a class="dropdown-item" href="{{ route('manager.production.exportExcel') }}?{{ http_build_query(request()->query()) }}">
                                <i class="fas fa-file-excel text-success me-2"></i>Production Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Report Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.production.index') }}" class="row g-3" id="reportFilters">
                
                {{-- Dispatch Filters --}}
                <div class="col-12">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-truck me-2"></i>Dispatch Filters
                    </h6>
                </div>
                
                <div class="col-md-3">
                    <label for="from_date" class="form-label fw-semibold">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" 
                           value="{{ request('from_date') }}" max="{{ date('Y-m-d') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="to_date" class="form-label fw-semibold">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" 
                           value="{{ request('to_date') }}" max="{{ date('Y-m-d') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="driver" class="form-label fw-semibold">Driver Name</label>
                    <input type="text" name="driver" id="driver" class="form-control" 
                           placeholder="Enter driver name" value="{{ request('driver') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="product" class="form-label fw-semibold">Product</label>
                    <input type="text" name="product" id="product" class="form-control" 
                           placeholder="Enter product name" value="{{ request('product') }}">
                </div>

                {{-- Production Filters --}}
                <div class="col-12 mt-4">
                    <h6 class="text-success mb-3">
                        <i class="fas fa-industry me-2"></i>Production Filters
                    </h6>
                </div>
                
                <div class="col-md-3">
                    <label for="prod_from" class="form-label fw-semibold">Production From</label>
                    <input type="date" name="prod_from" id="prod_from" class="form-control" 
                           value="{{ request('prod_from') }}" max="{{ date('Y-m-d') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="prod_to" class="form-label fw-semibold">Production To</label>
                    <input type="date" name="prod_to" id="prod_to" class="form-control" 
                           value="{{ request('prod_to') }}" max="{{ date('Y-m-d') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="chef" class="form-label fw-semibold">Chef Name</label>
                    <input type="text" name="chef" id="chef" class="form-control" 
                           placeholder="Enter chef name" value="{{ request('chef') }}">
                </div>
                
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-75">
                        <i class="fas fa-search me-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('manager.production.index') }}" class="btn btn-outline-secondary w-25" 
                       title="Reset Filters">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Dispatch Summary --}}
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-header bg-info text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-truck-loading me-2"></i>Dispatch Summary
                <span class="badge bg-light text-dark ms-2">{{ $dispatches->count() }} records</span>
            </h5>
            <div class="total-summary">
                <small class="text-light">
                    Total Value: <strong>{{ number_format($dispatches->sum('total_value')) }}</strong>
                </small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 500px;">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th class="bg-dark text-white">Date</th>
                            <th class="bg-dark text-white">Driver</th>
                            <th class="bg-dark text-white text-end">Qty Dispatched</th>
                            <th class="bg-dark text-white text-end">Cash Sales</th>
                            <th class="bg-dark text-white text-end">Remaining</th>
                            <th class="bg-dark text-white text-end">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dispatches as $dispatch)
                            <tr class="align-middle">
                                <td>
    <strong>
        @php
            $date = $dispatch->date;
            // Check if it's a valid date string
            if ($date && $date !== 'N/A' && $date !== 'no-date') {
                try {
                    $parsedDate = \Carbon\Carbon::parse($date);
                    echo $parsedDate->format('d M Y');
                } catch (Exception $e) {
                    echo '<span class="text-muted">Invalid Date</span>';
                }
            } else {
                echo '<span class="text-muted">No Date</span>';
            }
        @endphp
    </strong>
</td>
                                <td>
                                    <span class="badge bg-primary">{{ $dispatch->driver_name }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold">{{ number_format($dispatch->total_qty) }}</span>
                                </td>
                                <td class="text-end text-success">
                                    <strong>{{ number_format($dispatch->total_cash) }}</strong>
                                </td>
                                
                                <td class="text-end text-danger">
                                    <strong>{{ number_format($dispatch->total_remaining) }}</strong>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success fs-6">{{ number_format($dispatch->total_value) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <h5>No dispatch data available</h5>
                                        <p class="mb-0">Try adjusting your filters to see results</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                   @if($dispatches->isNotEmpty())
<tfoot class="table-secondary">
    <tr>
        <th colspan="2" class="text-end">Totals:</th>
        <th class="text-end">{{ number_format($dispatches->sum('total_qty')) }}</th>
        <th class="text-end">{{ number_format($dispatches->sum('total_cash')) }}</th>
        <th class="text-end">{{ number_format($dispatches->sum('total_remaining')) }}</th>
        <th class="text-end">{{ number_format($dispatches->sum('total_value')) }}</th>
    </tr>
</tfoot>
@endif
                </table>
            </div>
        </div>
    </div>

    {{-- Production Summary --}}
    <div class="card shadow-lg border-0">
        <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-bread-slice me-2"></i>Production Summary
                <span class="badge bg-light text-dark ms-2">{{ $productions->count() }} records</span>
            </h5>
            <div class="total-summary">
                <small class="text-light">
                    Total Value: <strong>{{ number_format($productions->sum('total_value')) }}</strong>
                </small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 500px;">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th class="bg-dark text-white">Date</th>
                            <th class="bg-dark text-white">Chef</th>
                            <th class="bg-dark text-white text-end">Flour Bags</th>
                            <th class="bg-dark text-white text-end">Total Value</th>
                            <th class="bg-dark text-white text-end">Buns</th>
                            <th class="bg-dark text-white text-end">Small Breads</th>
                            <th class="bg-dark text-white text-end">Big Breads</th>
                            <th class="bg-dark text-white text-end">Donuts</th>
                            <th class="bg-dark text-white text-end">Half Cakes</th>
                            <th class="bg-dark text-white text-end">Block Cakes</th>
                            <th class="bg-dark text-white text-end">Slab Cakes</th>
                            <th class="bg-dark text-white text-end">Birthday Cakes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productions as $production)
                            <tr class="align-middle">
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($production->production_date)->format('d M Y') }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $production->chef_name }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold">{{ number_format($production->total_flour_bags) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success fs-6">{{ number_format($production->total_value) }}</span>
                                </td>
                                <td class="text-end">{{ number_format($production->buns) }}</td>
                                <td class="text-end">{{ number_format($production->small_breads) }}</td>
                                <td class="text-end">{{ number_format($production->big_breads) }}</td>
                                <td class="text-end">{{ number_format($production->donuts) }}</td>
                                <td class="text-end">{{ number_format($production->half_cakes) }}</td>
                                <td class="text-end">{{ number_format($production->block_cakes) }}</td>
                                <td class="text-end">{{ number_format($production->slab_cakes) }}</td>
                                <td class="text-end">{{ number_format($production->birthday_cakes) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-industry fa-3x mb-3"></i>
                                        <h5>No production data available</h5>
                                        <p class="mb-0">Try adjusting your filters to see results</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($productions->isNotEmpty())
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="2" class="text-end">Totals:</th>
                            <th class="text-end">{{ number_format($productions->sum('total_flour_bags')) }}</th>
                            <th class="text-end">{{ number_format($productions->sum('total_value')) }}</th>
                            <th class="text-end">{{ number_format($productions->sum('buns')) }}</th>
                            <th class="text-end">{{ number_format($productions->sum('small_breads')) }}</th>
                            <th class="text-end">{{ number_format($productions->sum('big_breads')) }}</th>
                            <th class="text-end">{{ number_format($productions->sum('donuts')) }}</th>
                            <th class="text-end">{{ number_format($productions->sum('half_cakes')) }}</th>
                            <th class="text-end">{{ number_format($productions->sum('block_cakes')) }}</th>
                            <th class="text-end">{{ number_format($productions->sum('slab_cakes')) }}</th>
                            <th class="text-end">{{ number_format($productions->sum('birthday_cakes')) }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    .table th {
        border-top: none;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-responsive {
        border-radius: 0.375rem;
    }
    
    .sticky-top {
        top: 0;
        z-index: 10;
    }
    
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
        border-bottom: none;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.075);
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Date validation
        const fromDate = document.getElementById('from_date');
        const toDate = document.getElementById('to_date');
        const prodFrom = document.getElementById('prod_from');
        const prodTo = document.getElementById('prod_to');
        
        if (fromDate && toDate) {
            fromDate.addEventListener('change', function() {
                toDate.min = this.value;
            });
            
            toDate.addEventListener('change', function() {
                fromDate.max = this.value;
            });
        }
        
        if (prodFrom && prodTo) {
            prodFrom.addEventListener('change', function() {
                prodTo.min = this.value;
            });
            
            prodTo.addEventListener('change', function() {
                prodFrom.max = this.value;
            });
        }
        
        // Auto-submit on enter in filter fields
        const filterInputs = document.querySelectorAll('#reportFilters input[type="text"]');
        filterInputs.forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('reportFilters').submit();
                }
            });
        });
    });
</script>
@endpush
@endsection