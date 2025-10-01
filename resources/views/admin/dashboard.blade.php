@extends('admin.layouts.app')
@section('title','Admin Dashboard')

@section('content')
<h3 class="mb-4 fw-bold"><i class="bi bi-speedometer2 me-2"></i> Bakery Admin Dashboard</h3>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <!-- Total Users -->
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 p-4 dashboard-card h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase small mb-2">Total Users</h6>
                    <h3 class="fw-bold">{{ $totalusers }}</h3>  
                </div>
                <div class="icon-circle bg-info text-white">
                    <i class="bi bi-people"></i>    
                </div>
            </div>
        </div>
    </div>

    <!-- Total Productions -->
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 p-4 dashboard-card h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase small mb-2">Total Productions</h6>
                    <h3 class="fw-bold">{{ $totalProductions }}</h3>
                </div>
                <div class="icon-circle bg-primary text-white">
                    <i class="bi bi-journal-text"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Today’s Records -->
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 p-4 dashboard-card h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase small mb-2">Today’s Records</h6>
                    <h3 class="fw-bold">{{ $todayProductions }}</h3>
                </div>
                <div class="icon-circle bg-success text-white">
                    <i class="bi bi-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Value -->
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 p-4 dashboard-card h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase small mb-2">Total Value (UGX)</h6>
                    <h3 class="fw-bold">{{ number_format($totalValue) }}</h3>
                </div>
                <div class="icon-circle bg-warning text-white">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Variances -->
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 p-4 dashboard-card h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase small mb-2">Variances</h6>
                    <h3 class="fw-bold text-danger">{{ $varianceCount }}</h3>
                </div>
                <div class="icon-circle bg-danger text-white">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Alert Modal -->
<div class="modal fade" id="stockAlertModal" tabindex="-1" aria-labelledby="stockAlertLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold" id="stockAlertLabel">
          <i class="bi bi-bell-fill me-2"></i>Daily Stock Alert
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Here are the current stock levels as of today:</p>
        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>Product</th>
              <th>Quantity</th>
            </tr>
          </thead>
          <tbody>
            @foreach($bakeryStocks as $stock)
              <tr>
                <td>{{ ucfirst(str_replace('_',' ', $stock->product)) }}</td>
                <td>{{ number_format($stock->quantity) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>


<!-- Chart Card -->
<div class="card shadow-sm p-3">
    <h5 class="mb-3">Last 7 Days Production Value</h5>
    <canvas id="productionChart" height="120"></canvas>
</div>

<!-- Styles -->
<style>
.dashboard-card {
    border-radius: 12px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}
.icon-circle {
    width: 48px; height: 48px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    font-size: 1.3rem;
}
@media(max-width:768px){
    .dashboard-card { padding: 20px 16px; }
    h3 { font-size: 1.5rem; }
}
</style>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('productionChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData->keys()->map(fn($d)=>\Carbon\Carbon::parse($d)->format('M d'))) !!},
        datasets: [{
            label: 'Total Value (UGX)',
            data: {!! json_encode($chartData->values()) !!},
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.15)',
            tension: 0.3,
            fill: true,
            pointBackgroundColor: '#0d6efd',
            pointBorderColor: '#fff',
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { drawBorder: false } }
        }
    }
});

document.addEventListener("DOMContentLoaded", function () {
    // Only show modal if there are stocks
    @if($bakeryStocks->count() > 0)
        var myModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
        myModal.show();
    @endif
});
</script>


@endsection
