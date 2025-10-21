@extends('admin.layouts.app')
@section('title','Chef Targets')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4><i class="bi bi-bullseye me-2"></i> Chef & Manager Targets</h4>
    <div>
        <a href="{{ route('admin.chef_targets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Chef Target
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Manager Targets Section -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i> Manager Targets & Progress</h5>
    </div>
    <div class="card-body">
        @if($managerTargetsWithProgress->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Manager</th>
                            <th>Daily Target & Progress</th>
                            <th>Monthly Target & Progress</th>
                            <th>Fixed Salary</th>
                            <th>Commission %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($managerTargetsWithProgress as $mt)
                        <tr>
                            <td>
                                <strong>{{ $mt->manager->name }}</strong>
                                <br><small class="text-muted">{{ $mt->manager->email }}</small>
                            </td>
                            <td>
                                <div class="mb-2">
                                    <span class="badge bg-primary fs-6">UGX {{ number_format($mt->daily_target) }}</span>
                                </div>
                                <div class="progress mb-1" style="height: 20px;">
                                    <div class="progress-bar 
                                        @if($mt->daily_progress >= 100) bg-success
                                        @elseif($mt->daily_progress >= 75) bg-info
                                        @elseif($mt->daily_progress >= 50) bg-warning
                                        @else bg-danger @endif
                                        progress-bar-striped" 
                                        style="width: {{ min($mt->daily_progress, 100) }}%"
                                        role="progressbar">
                                        <small class="fw-bold">{{ $mt->daily_progress }}%</small>
                                    </div>
                                </div>
                                <div class="small text-muted">
                                    Achieved: <strong>UGX {{ number_format($mt->daily_achieved) }}</strong>
                                    @if($mt->daily_remaining > 0)
                                    | Remaining: <strong>UGX {{ number_format($mt->daily_remaining) }}</strong>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="mb-2">
                                    <span class="badge bg-info fs-6">UGX {{ number_format($mt->monthly_target) }}</span>
                                </div>
                                <div class="progress mb-1" style="height: 20px;">
                                    <div class="progress-bar 
                                        @if($mt->monthly_progress >= 100) bg-success
                                        @elseif($mt->monthly_progress >= 75) bg-info
                                        @elseif($mt->monthly_progress >= 50) bg-warning
                                        @else bg-danger @endif
                                        progress-bar-striped" 
                                        style="width: {{ min($mt->monthly_progress, 100) }}%"
                                        role="progressbar">
                                        <small class="fw-bold">{{ $mt->monthly_progress }}%</small>
                                    </div>
                                </div>
                                <div class="small text-muted">
                                    Achieved: <strong>UGX {{ number_format($mt->monthly_achieved) }}</strong>
                                    @if($mt->monthly_remaining > 0)
                                    | Remaining: <strong>UGX {{ number_format($mt->monthly_remaining) }}</strong>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <strong class="text-success">UGX {{ number_format($mt->fixed_salary) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark fs-6">{{ $mt->commission_percentage }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Progress Legend -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert alert-light border">
                        <h6 class="mb-2">Progress Legend:</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <span class="badge bg-success">100%+ - Target Achieved</span>
                            <span class="badge bg-info">75-99% - Almost There</span>
                            <span class="badge bg-warning text-dark">50-74% - Halfway</span>
                            <span class="badge bg-danger">Below 50% - Needs Improvement</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3">No manager targets set yet.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#managerTargetModal">
                    <i class="bi bi-plus-lg"></i> Set First Manager Target
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Chef Targets Section -->
<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="bi bi-egg-fried me-2"></i> Chef Targets</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Chef</th>
                        <th>Daily Target</th>
                        <th>Monthly Target</th>
                        <th>Fixed Salary</th>
                        <th>Days Off</th>
                        <th>Commission %</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($targets as $t)
                    <tr>
                        <td>
                            <strong>{{ $t->chef->name }}</strong>
                            <br><small class="text-muted">{{ $t->chef->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary">UGX {{ number_format($t->daily_target) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info">UGX {{ number_format($t->monthly_target) }}</span>
                        </td>
                        <td><strong class="text-success">UGX {{ number_format($t->fixed_salary) }}</strong></td>
                        <td>{{ implode(', ', $t->days_off ?? []) ?: '-' }}</td>
                        <td>
                            <span class="badge bg-warning text-dark">{{ $t->commission_percentage }}%</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.chef_targets.edit', $t) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('admin.chef_targets.destroy', $t) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this target?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{ $targets->links() }}
    </div>
</div>


<style>
.progress {
    border-radius: 10px;
    overflow: hidden;
}
.progress-bar {
    transition: width 0.6s ease;
}
</style>
@endsection