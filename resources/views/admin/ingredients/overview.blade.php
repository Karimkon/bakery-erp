@extends('admin.layouts.app')

@section('title', 'Ingredients Overview')

@section('content')
<div class="container-fluid py-4">

  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1">Ingredients Overview</h3>
      <p class="text-muted">Monitor ingredient stocks and usage across all bakery teams.</p>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-uppercase text-muted mb-1">Total Ingredients</h6>
        <h4>{{ $summary['total_items'] }}</h4>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-uppercase text-muted mb-1">Total Stock Value</h6>
        <h4>UGX {{ number_format($summary['total_stock_value'], 0) }}</h4>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-uppercase text-muted mb-1">Low Stock Items</h6>
        <h4>{{ $summary['low_stock'] }}</h4>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm text-center p-3">
        <h6 class="text-uppercase text-muted mb-1">Total Chefs</h6>
        <h4>{{ $summary['total_chefs'] }}</h4>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <!-- Filters -->
<form method="GET" class="row mb-4">
    <div class="col-md-2 mb-2">
        <select name="chef_id" class="form-select">
            <option value="">All Chefs</option>
            @foreach($chefs as $chef)
            <option value="{{ $chef->id }}" {{ request('chef_id') == $chef->id ? 'selected' : '' }}>
                {{ $chef->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 mb-2">
        <select name="ingredient_name" class="form-select">
            <option value="">All Ingredients</option>
            @foreach($ingredientNames as $name)
            <option value="{{ $name }}" {{ request('ingredient_name') == $name ? 'selected' : '' }}>
                {{ ucfirst($name) }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 mb-2">
        <input type="number" name="min_stock" placeholder="Min Stock" class="form-control" value="{{ request('min_stock') }}">
    </div>
    <div class="col-md-2 mb-2">
        <input type="number" name="max_stock" placeholder="Max Stock" class="form-control" value="{{ request('max_stock') }}">
    </div>
    <div class="col-md-2 mb-2">
        <input type="date" name="date_filter" class="form-control" value="{{ request('date_filter') }}">
    </div>
    <div class="col-md-2 mb-2">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
        @if(request('date_filter'))
            <a href="{{ route('admin.ingredients.overview') }}" class="btn btn-outline-secondary w-100 mt-1">Clear Date</a>
        @endif
    </div>
</form>

  <!-- Ingredient Totals Cards -->
  <div class="row mb-4">
    @foreach($totals as $item)
    <div class="col-md-2 col-sm-4 mb-3">
      <div class="card text-center shadow-sm">
        <div class="card-body p-2">
          <h6 class="fw-bold text-uppercase small">{{ $item->name }}</h6>
          <h5 class="text-success mb-0">{{ number_format($item->total_qty, 2) }} {{ $item->unit }}</h5>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <!-- Table -->
  <div class="card shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Ingredient Details</h5>
      <div>
        <a href="#" class="btn btn-sm btn-success">Export Excel</a>
        <a href="#" class="btn btn-sm btn-danger">Export PDF</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Ingredient</th>
            <th>Unit</th>
            <th>Total Remaining</th>
            <th>Avg. Unit Cost</th>
            <th>Total Value</th>
            <th>Used By (Chefs)</th>
            <th>Last Updated</th>
          </tr>
        </thead>
        <tbody>
          @forelse($overview as $item)
          <tr>
            <td>{{ ucfirst($item->name) }}</td>
            <td>{{ $item->unit }}</td>
            <td>{{ number_format($item->total_qty, 2) }}</td>
            <td>UGX {{ number_format($item->avg_cost, 0) }}</td>
            <td>UGX {{ number_format($item->total_value, 0) }}</td>
            <td>{{ $item->chef_count }}</td>
            <td>{{ \Carbon\Carbon::parse($item->last_updated)->format('d M Y') }}</td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center text-muted">No records found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Chart Section -->
  <div class="card shadow-sm mt-4">
    <div class="card-header bg-light">
      <h5 class="mb-0">Stock Distribution Chart</h5>
    </div>
    <div class="card-body">
      <canvas id="ingredientChart" height="120"></canvas>
    </div>
  </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('ingredientChart');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: @json($overview->pluck('name')),
    datasets: [{
      label: 'Remaining Stock',
      data: @json($overview->pluck('total_qty')),
    }]
  },
  options: {
    responsive: true,
    scales: { y: { beginAtZero: true } }
  }
});
</script>
@endsection
