@extends('admin.layouts.app')
@section('title','Chef Targets')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4><i class="bi bi-bullseye me-2"></i> Chef Targets</h4>
    <a href="{{ route('admin.chef_targets.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Target
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

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
            <td>{{ $t->chef->name }}</td>
            <td>UGX {{ number_format($t->daily_target) }}</td>
            <td>UGX {{ number_format($t->monthly_target) }}</td>
            <td><strong class="text-success">UGX {{ number_format($t->fixed_salary) }}</strong></td>
            <td>{{ implode(', ', $t->days_off ?? []) ?: '-' }}</td>
            <td>{{ $t->commission_percentage }}%</td>
            <td>
                <a href="{{ route('admin.chef_targets.edit', $t) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('admin.chef_targets.destroy', $t) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this target?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $targets->links() }}
@endsection