@extends('admin.layouts.app')
@section('title', 'Manager Progress History')

@section('content')
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-bar-chart-line me-2"></i> Progress History - {{ $manager->name }}
            </h3>
            <div class="text-muted small">
                Track daily performance and progress for {{ $manager->name }}
                <br><strong>Email:</strong> {{ $manager->email }} | 
                <strong>Daily Target:</strong> UGX {{ number_format($managerTarget->daily_target) }}
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.chef_targets.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Targets
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
                        <h4 class="mb-0">{{ $summary['success_rate'] }}%</h4>
                    </div>
                    <div class="icon-circle bg-warning text-white">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i> Financial Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">Total Target</small>
                            <h5 class="text-primary">UGX {{ number_format($summary['total_target']) }}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total Achieved</small>
                            <h5 class="text-success">UGX {{ number_format($summary['total_achieved']) }}</h5>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Overall Performance</small>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar 
                                @if($summary['average_progress'] >= 100) bg-success
                                @elseif($summary['average_progress'] >= 75) bg-info
                                @elseif($summary['average_progress'] >= 50) bg-warning
                                @else bg-danger @endif" 
                                style="width: {{ min($summary['average_progress'], 100) }}%"
                                role="progressbar">
                                <strong>{{ number_format($summary['average_progress'], 1) }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i> Manager Details</h6>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $manager->name }}</p>
                    <p><strong>Email:</strong> {{ $manager->email }}</p>
                    <p><strong>Daily Target:</strong> UGX {{ number_format($managerTarget->daily_target) }}</p>
                    <p><strong>Monthly Target:</strong> UGX {{ number_format($managerTarget->monthly_target) }}</p>
                    <p><strong>Fixed Salary:</strong> UGX {{ number_format($managerTarget->fixed_salary) }}</p>
                    <p><strong>Commission:</strong> {{ $managerTarget->commission_percentage }}%</p>
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
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daily Progress Records</h5>
            <span class="badge bg-primary">Total Records: {{ $progressHistory->total() }}</span>
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
                                <th>Variance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($progressHistory as $progress)
                            @php
                                $variance = $progress->achieved_amount - $progress->target_amount;
                            @endphp
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
                                    <span class="fw-bold 
                                        @if($variance >= 0) text-success
                                        @else text-danger @endif">
                                        UGX {{ number_format(abs($variance)) }}
                                        @if($variance >= 0)
                                            <i class="bi bi-arrow-up-circle text-success"></i>
                                        @else
                                            <i class="bi bi-arrow-down-circle text-danger"></i>
                                        @endif
                                    </span>
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
                    <p class="text-muted">No progress history available for the selected date range.</p>
                    <a href="{{ route('admin.chef_targets.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Targets
                    </a>
                </div>
            @endif
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
function exportToExcel() {
    // Simple table export
    const table = document.getElementById('progressTable');
    let csv = [];
    
    // Headers
    let headers = [];
    for (let i = 0; i < table.rows[0].cells.length; i++) {
        headers.push(table.rows[0].cells[i].innerText);
    }
    csv.push(headers.join(','));
    
    // Data
    for (let i = 1; i < table.rows.length; i++) {
        let row = [];
        for (let j = 0; j < table.rows[i].cells.length; j++) {
            row.push(table.rows[i].cells[j].innerText.replace(/,/g, ''));
        }
        csv.push(row.join(','));
    }
    
    // Download
    const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "{{ $manager->name }}_progress_history.csv");
    document.body.appendChild(link);
    link.click();
}
</script>
@endsection