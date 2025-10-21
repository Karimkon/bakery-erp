@extends('manager.layouts.app')
@section('title', 'My Progress History')

@section('content')
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-bar-chart-line me-2"></i> My Progress History
            </h3>
            <div class="text-muted small">Track your daily performance and progress over time</div>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('manager.dashboard') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
            <button class="btn btn-success" onclick="exportToExcel()">
                <i class="bi bi-download me-1"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Total Days Tracked</small>
                        <h4 class="mb-0">{{ number_format($summary['total_days']) }}</h4>
                    </div>
                    <div class="icon-circle bg-primary text-white">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Average Progress</small>
                        <h4 class="mb-0">{{ number_format($summary['average_progress'], 1) }}%</h4>
                    </div>
                    <div class="icon-circle bg-info text-white">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Target Achieved Days</small>
                        <h4 class="mb-0">{{ number_format($summary['target_achieved_days']) }}</h4>
                    </div>
                    <div class="icon-circle bg-success text-white">
                        <i class="bi bi-trophy"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted">Success Rate</small>
                        <h4 class="mb-0">
                            @if($summary['total_days'] > 0)
                                {{ number_format(($summary['target_achieved_days'] / $summary['total_days']) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </h4>
                    </div>
                    <div class="icon-circle bg-warning text-white">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Or Search Specific Date</label>
                    <input type="date" name="search_date" class="form-control" value="{{ $searchDate }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Progress History Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Daily Progress Records</h5>
        </div>
        <div class="card-body p-0">
            @if($progressHistory->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="progressTable">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Target Amount</th>
                                <th>Achieved Amount</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($progressHistory as $progress)
                            <tr>
                                <td>
                                    <strong>{{ $progress->progress_date->format('M d, Y') }}</strong>
                                </td>
                                <td>
                                    {{ $progress->progress_date->format('l') }}
                                    @if($progress->progress_date->isToday())
                                        <span class="badge bg-primary">Today</span>
                                    @elseif($progress->progress_date->isYesterday())
                                        <span class="badge bg-secondary">Yesterday</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">UGX {{ number_format($progress->target_amount) }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold 
                                        @if($progress->achieved_amount >= $progress->target_amount) text-success
                                        @else text-warning @endif">
                                        UGX {{ number_format($progress->achieved_amount) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 20px;">
                                            <div class="progress-bar 
                                                @if($progress->progress_percentage >= 100) bg-success
                                                @elseif($progress->progress_percentage >= 75) bg-info
                                                @elseif($progress->progress_percentage >= 50) bg-warning
                                                @else bg-danger @endif" 
                                                style="width: {{ min($progress->progress_percentage, 100) }}%"
                                                role="progressbar">
                                                <small class="fw-bold">{{ $progress->progress_percentage }}%</small>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($progress->progress_percentage >= 100)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i> Target Achieved
                                        </span>
                                    @elseif($progress->progress_percentage >= 80)
                                        <span class="badge bg-info">
                                            <i class="bi bi-arrow-up-circle me-1"></i> Excellent
                                        </span>
                                    @elseif($progress->progress_percentage >= 60)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-dash-circle me-1"></i> Good
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-exclamation-circle me-1"></i> Needs Improvement
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="showProgressDetails({{ $progress->id }})">
                                        <i class="bi bi-eye"></i> Details
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer">
                    {{ $progressHistory->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No progress records found</h4>
                    <p class="text-muted">Your progress history will appear here as you track your daily targets.</p>
                    <a href="{{ route('manager.dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-speedometer2 me-1"></i> Go to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Progress Details Modal -->
<div class="modal fade" id="progressDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Progress Details - <span id="modalDate"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="progressDetailsContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle { 
    width: 48px; height: 48px; border-radius: 50%; 
    display: flex; align-items: center; justify-content: center; 
    font-size: 1.15rem; 
}
.progress { border-radius: 10px; overflow: hidden; }
</style>

<script>
function showProgressDetails(progressId) {
    // Show loading
    $('#progressDetailsContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading details...</p>
        </div>
    `);
    
    $('#progressDetailsModal').modal('show');
    
    // In a real implementation, you'd fetch detailed data via AJAX
    // For now, we'll show basic info
    setTimeout(() => {
        $('#progressDetailsContent').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6>Performance Summary</h6>
                    <p><strong>Target:</strong> UGX 3,000,000</p>
                    <p><strong>Achieved:</strong> UGX 2,500,000</p>
                    <p><strong>Progress:</strong> 83.33%</p>
                </div>
                <div class="col-md-6">
                    <h6>Breakdown</h6>
                    <p><strong>Included Dispatches:</strong> UGX 1,800,000</p>
                    <p><strong>Bakery Sales:</strong> UGX 700,000</p>
                    <p><strong>Excluded Dispatches:</strong> UGX 200,000</p>
                </div>
            </div>
            <div class="mt-3">
                <h6>Notes</h6>
                <p class="text-muted">Detailed breakdown information would be loaded here via AJAX request.</p>
            </div>
        `);
    }, 500);
}

function exportToExcel() {
    // Simple table export (you can enhance this with a proper library)
    const table = document.getElementById('progressTable');
    let csv = [];
    
    // Headers
    let headers = [];
    for (let i = 0; i < table.rows[0].cells.length - 1; i++) { // -1 to exclude Actions column
        headers.push(table.rows[0].cells[i].innerText);
    }
    csv.push(headers.join(','));
    
    // Data
    for (let i = 1; i < table.rows.length; i++) {
        let row = [];
        for (let j = 0; j < table.rows[i].cells.length - 1; j++) { // -1 to exclude Actions column
            row.push(table.rows[i].cells[j].innerText.replace(/,/g, ''));
        }
        csv.push(row.join(','));
    }
    
    // Download
    const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "progress_history.csv");
    document.body.appendChild(link);
    link.click();
}
</script>
@endsection