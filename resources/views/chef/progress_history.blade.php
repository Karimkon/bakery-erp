@extends('chef.layouts.app')
@section('title', 'My Progress History')

@section('content')
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-bar-chart-line me-2"></i> My Production Progress History
            </h3>
            <div class="text-muted small">Viewing: <strong>{{ $currentMonth }}</strong></div>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('chef.dashboard') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
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

    <!-- Target Info -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Current Target Settings</h6>
                    <p><strong>Daily Target:</strong> UGX {{ number_format($chefTarget->daily_target) }}</p>
                    <p><strong>Monthly Target:</strong> UGX {{ number_format($chefTarget->monthly_target) }}</p>
                </div>
                <div class="col-md-6">
                    <h6>Performance Summary ({{ $currentMonth }})</h6>
                    <p><strong>Total Production:</strong> UGX {{ number_format($summary['total_achieved']) }}</p>
                    <p><strong>Total Target:</strong> UGX {{ number_format($summary['total_target']) }}</p>
                    @php
                        $remaining = $summary['total_target'] - $summary['total_achieved'];
                        $monthProgress = $summary['total_target'] > 0 
                            ? round(($summary['total_achieved'] / $summary['total_target']) * 100, 1) 
                            : 0;
                    @endphp
                    <p>
                        <strong>Remaining:</strong> 
                        <span class="{{ $remaining > 0 ? 'text-warning' : 'text-success' }}">
                            UGX {{ number_format(abs($remaining)) }}
                        </span>
                    </p>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $monthProgress >= 100 ? 'bg-success' : 'bg-primary' }}" 
                             style="width: {{ min($monthProgress, 100) }}%">
                            <strong>{{ $monthProgress }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Month Quick Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="fw-bold me-2">Quick Select:</span>
                @php
                    $now = \Carbon\Carbon::now();
                    $months = [];
                    for ($i = 0; $i < 6; $i++) {
                        $month = $now->copy()->subMonths($i);
                        $months[] = [
                            'name' => $month->format('F Y'),
                            'start' => $month->copy()->startOfMonth()->format('Y-m-d'),
                            'end' => $month->copy()->endOfMonth()->format('Y-m-d'),
                            'isCurrent' => $i === 0
                        ];
                    }
                @endphp
                
                @foreach($months as $month)
                    <a href="{{ route('chef.progress-history', ['start_date' => $month['start'], 'end_date' => $month['end']]) }}" 
                       class="btn btn-sm {{ $month['isCurrent'] && !request('start_date') ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $month['name'] }}
                        @if($month['isCurrent'])
                            <i class="bi bi-check-circle ms-1"></i>
                        @endif
                    </a>
                @endforeach
            </div>

            <!-- Custom Date Range -->
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Custom Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Custom End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Progress History Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Daily Production Progress - {{ $currentMonth }}</h5>
        </div>
        <div class="card-body p-0">
            @if($progressHistory->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Target Amount</th>
                                <th>Achieved Amount</th>
                                <th>Progress</th>
                                <th>Status</th>
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
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="fw-bold">Month Total</td>
                                <td class="fw-bold text-primary">UGX {{ number_format($summary['total_target']) }}</td>
                                <td class="fw-bold text-success">UGX {{ number_format($summary['total_achieved']) }}</td>
                                <td colspan="2" class="fw-bold">
                                    @php
                                        $overallProgress = $summary['total_target'] > 0 
                                            ? round(($summary['total_achieved'] / $summary['total_target']) * 100, 1) 
                                            : 0;
                                    @endphp
                                    <span class="{{ $overallProgress >= 100 ? 'text-success' : 'text-warning' }}">
                                        {{ $overallProgress }}% Complete
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($progressHistory->hasPages())
                <div class="card-footer">
                    {{ $progressHistory->appends(['start_date' => $startDate, 'end_date' => $endDate])->links() }}
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No progress records found for {{ $currentMonth }}</h4>
                    <p class="text-muted">Your production progress history will appear here as you track your daily targets.</p>
                    <a href="{{ route('chef.dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-speedometer2 me-1"></i> Go to Dashboard
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
@endsection