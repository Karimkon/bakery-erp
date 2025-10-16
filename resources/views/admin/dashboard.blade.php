@extends('admin.layouts.app')
@section('title','Admin Dashboard')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-3 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 me-2"></i> Bakery Admin Dashboard</h3>
            <div class="text-muted small">Overview — {{ $title ?? 'Today' }}</div>
        </div>

        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="me-1 small text-muted mb-0">Range</label>
            <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="today" {{ ($filter ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ ($filter ?? '') == 'week' ? 'selected' : '' }}>This week</option>
                <option value="month" {{ ($filter ?? '') == 'month' ? 'selected' : '' }}>This month</option>
            </select>
        </form>
    </div>

    <!-- Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Total Users</small>
                        <h4 class="mb-0">{{ number_format($totalUsers) }}</h4>
                    </div>
                    <div class="icon-circle bg-info text-white">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Total Productions</small>
                        <h4 class="mb-0">{{ number_format($totalProductions) }}</h4>
                    </div>
                    <div class="icon-circle bg-primary text-white"><i class="bi bi-journal-text"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
    <div class="card h-100 shadow-sm p-3">
        <div class="d-flex justify-content-between">
            <div>
                <small class="text-muted">Flour Used ({{ $title }})</small>
                <h4 class="mb-0">{{ number_format($flourUsed, 2) }} kg</h4>
            </div>
            <div class="icon-circle bg-warning text-white">
                <i class="bi bi-bag-fill"></i>
            </div>
        </div>
    </div>
</div>

 <div class="col-6 col-md-3">
    <div class="card h-100 shadow-sm p-3">
        <div class="d-flex justify-content-between">
            <div>
                <small class="text-muted">{{ $title }} Bakery Sales</small>
                <h4 class="mb-0 text-success">{{ number_format($bakerySales, 0) }} UGX</h4>
                <div class="text-muted small mt-1">
                    Cash: {{ number_format($bakerySalesCash,0) }} 
                </div>
            </div>
            <div class="icon-circle bg-success text-white">
                <i class="bi bi-shop"></i>
            </div>
        </div>
    </div>
</div>



        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Total Dispatches</small>
                        <h4 class="mb-0">{{ number_format($totalDispatches) }}</h4>
                    </div>
                    <div class="icon-circle bg-secondary text-white"><i class="bi bi-truck"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">{{ $title }} Total Value (Prod + Dispatch)</small>
                        <h4 class="mb-0 text-success">{{ number_format($combinedValue, 0) }}</h4>
                    </div>
                    <div class="icon-circle bg-success text-white"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>


        <div class="col-6 col-md-3">
    <div class="card h-100 shadow-sm p-3 position-relative overflow-hidden" style="transition: transform 0.3s; cursor: pointer;"
         onmouseover="this.style.transform='translateY(-5px)'" 
         onmouseout="this.style.transform='translateY(0)'">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">{{ $title }} Bank Deposits</small>
                <h4 class="mb-0 text-success">{{ number_format($bankedTotal ?? 0, 0) }} UGX</h4>
            </div>
            <div class="text-center">
                <div class="icon-circle bg-success text-white mb-2" 
                     style="width:45px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:50%; transition: transform 0.3s;"
                     onmouseover="this.style.transform='scale(1.2) rotate(5deg)'" 
                     onmouseout="this.style.transform='scale(1) rotate(0deg)'">
                    <i class="bi bi-bank2 fs-4"></i>
                </div>
                <a href="{{ route('admin.deposits.index') }}" class="btn btn-sm btn-outline-success animate-pulse" 
                   style="font-size:0.7rem; padding:0.25rem 0.5rem; transition: transform 0.2s;"
                   onmouseover="this.style.transform='translateY(-2px)'" 
                   onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-graph-up me-1"></i> View
                </a>
            </div>
        </div>
    </div>
</div>

       
<div class="col-6 col-md-3">
    <div class="card h-100 shadow-sm p-3 position-relative overflow-hidden" style="transition: transform 0.3s; cursor: pointer;"
         onmouseover="this.style.transform='translateY(-5px)'" 
         onmouseout="this.style.transform='translateY(0)'"
    >
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">{{ $title }} Expenses</small>
                <h4 class="mb-0 text-danger">{{ number_format($expensesTotal, 0) }} UGX</h4>
            </div>
            <div class="text-center">
                <div class="icon-circle bg-danger text-white mb-2" 
                     style="width:45px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:50%; transition: transform 0.3s;"
                     onmouseover="this.style.transform='scale(1.2) rotate(5deg)'" 
                     onmouseout="this.style.transform='scale(1) rotate(0deg)'">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <a href="{{ route('admin.expenses.dashboard') }}" class="btn btn-sm btn-outline-success animate-pulse" 
                   style="font-size:0.7rem; padding:0.25rem 0.5rem; transition: transform 0.2s;"
                   onmouseover="this.style.transform='translateY(-2px)'" 
                   onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-graph-up me-1"></i> View
                </a>
            </div>
        </div>
    </div>
</div>




    </div>

     
    <!-- Split cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <small class="text-muted">Production Value ({{ $title }})</small>
                <h4 class="mb-0">{{ number_format($productionValue, 0) }}</h4>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <small class="text-muted">Dispatch Value ({{ $title }})</small>
                <h4 class="mb-0">{{ number_format($dispatchValue, 0) }}</h4>
                <div class="text-muted small mt-1">Dispatched items: <strong>{{ number_format($dispatchItemsCount) }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Chart + Recent lists -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card p-3 shadow-sm">
                <h6 class="mb-3">Last 7 days — Production vs Dispatch</h6>
                <canvas id="prodDispatchChart" height="120"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 shadow-sm mb-3">
                <h6 class="mb-2">Recent Dispatches</h6>
                <div class="table-responsive" style="max-height:280px; overflow:auto;">
                    <table class="table table-sm mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th>Date</th><th>#</th><th>Driver</th><th class="text-end">Value</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentDispatches as $d)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($d->dispatch_date)->format('d M') }}</td>
                                    <td>{{ $d->dispatch_no }}</td>
                                    <td>{{ $d->driver?->name }}</td>
                                    <td class="text-end">{{ number_format($d->total_sales_value,0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

          


            <div class="card p-3 shadow-sm">
                <h6 class="mb-2">Recent Productions</h6>
                <div class="table-responsive" style="max-height:220px; overflow:auto;">
                    <table class="table table-sm mb-0">
           <thead class="table-light sticky-top">
    <tr>
        <th>Date</th>
        <th>Chef</th>
        <th class="text-end">Value</th>
    </tr>
</thead>
<tbody>
    @foreach($recentProductions as $p)
        <tr>
            <td>{{ \Carbon\Carbon::parse($p->production_date)->format('d M') }}</td>
            <td>{{ $p->user?->name ?? '—' }}</td>  <!-- Use 'user' relationship as chef -->
            <td class="text-end">{{ number_format($p->total_value, 0) }}</td>
        </tr>
    @endforeach
</tbody>

                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Stock alert / summary footer -->
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card p-3 shadow-sm">
                <h6>Stock Snapshot</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Product</th><th class="text-end">Qty</th></tr>
                        </thead>
                        <tbody>
                            @foreach($bakeryStocks as $s)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_',' ',$s->product)) }}</td>
                                    <td class="text-end">{{ number_format($s->quantity) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6>Short Metrics</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><small class="text-muted">Today productions</small><div class="fw-bold">{{ number_format($todayProductions) }}</div></li>
                    <li class="mb-2"><small class="text-muted">Dispatch items (range)</small><div class="fw-bold">{{ number_format($dispatchItemsCount) }}</div></li>
                    <li><small class="text-muted">Combined value</small><div class="fw-bold">{{ number_format($combinedValue,0) }}</div></li>
                </ul>
            </div>
        </div>
    </div>


    <!-- Stock Alert Modal -->
<div class="modal fade" id="stockAlertModal" tabindex="-1" aria-labelledby="stockAlertLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="stockAlertLabel">Recent Stock Additions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if($recentStockAdditions->isEmpty())
            <p class="text-muted">No recent stock additions.</p>
        @else
        <table class="table table-sm table-hover">
          <thead>
            <tr>
              <th>Ingredient</th>
              <th>Quantity</th>
              <th>Chef</th>
              <th>Added By</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentStockAdditions as $stock)
            <tr>
              <td>{{ $stock->ingredient->name ?? '-' }}</td>
              <td>{{ number_format($stock->quantity_added) }}</td>
              <td>{{ $stock->chef->name ?? '-' }}</td>
              <td>{{ $stock->addedBy->name ?? '-' }}</td>
              <td>{{ $stock->created_at->format('d M Y H:i') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
      <div class="modal-footer">
        <a href="{{ route('admin.ingredients.stock_history') }}" class="btn btn-primary btn-sm">View Full History</a>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


</div>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>

    document.addEventListener("DOMContentLoaded", function() {
    @if(!empty($showStockModal) && $showStockModal)
        var stockModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
        stockModal.show();
    @endif

});

    const labels = {!! json_encode($labels) !!};
    const prodData = {!! json_encode($prodSeries) !!};
    const dispData = {!! json_encode($dispSeries) !!};

    const ctx = document.getElementById('prodDispatchChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Production (UGX)',
                    data: prodData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.12)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4
                },
                {
                    label: 'Dispatch (UGX)',
                    data: dispData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255,193,7,0.12)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                x: { grid: { display: false } },
                y: {
                    ticks: {
                        callback: function(v) {
                            if (v >= 1000000) return (v/1000000) + 'M';
                            if (v >= 1000) return (v/1000) + 'k';
                            return v;
                        }
                    }
                }
            }
        }
    });
</script>

<!-- Small styles -->
<style>
.icon-circle { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
@media (max-width: 768px) {
    .icon-circle { width:40px; height:40px; }
}
</style>
@endsection
