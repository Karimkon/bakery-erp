@extends('admin.layouts.app')
@section('title','Financial Reports')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Financial Reports</h5>
        <div>
            <a href="/admin/reports/financial/export?{{ http_build_query(request()->query()) }}" 
               class="btn btn-success btn-sm">
                <i class="bi bi-file-excel me-1"></i> Export to Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="/admin/reports/financial" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="report_type" class="form-select">
                        <option value="income_statement" {{ $reportType == 'income_statement' ? 'selected' : '' }}>Income Statement</option>
                        <option value="balance_sheet" {{ $reportType == 'balance_sheet' ? 'selected' : '' }}>Balance Sheet</option>
                        <option value="cash_flow" {{ $reportType == 'cash_flow' ? 'selected' : '' }}>Cash Flow Statement</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select">
                        <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Today</option>
                        <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>This Week</option>
                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>This Month</option>
                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i> Generate Report
                    </button>
                </div>
            </div>
        </form>

        <!-- Report Display -->
        <div class="mt-4">
            @if($reportType == 'income_statement')
                @include('admin.reports.financial.partials.income-statement')
            @elseif($reportType == 'balance_sheet')
                @include('admin.reports.financial.partials.balance-sheet')
            @elseif($reportType == 'cash_flow')
                @include('admin.reports.financial.partials.cash-flow')
            @endif
            
            <!-- Export Button at Bottom -->
            <div class="text-center mt-4">
                <a href="/admin/reports/financial/export?{{ http_build_query(request()->query()) }}" 
                   class="btn btn-success btn-lg">
                    <i class="bi bi-file-excel me-2"></i> Export Current Report to Excel
                </a>
            </div>
        </div>
    </div>
</div>
@endsection