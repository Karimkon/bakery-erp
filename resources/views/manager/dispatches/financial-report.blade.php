@extends('manager.layouts.app')
@section('title', 'Sales vs Bank Deposits Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-graph-up me-2"></i> Sales vs Bank Deposits Report</h4>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Driver</label>
                <select name="driver_id" class="form-select" required>
                    <option value="">-- Select Driver --</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" 
                            {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" 
                    value="{{ request('date_from') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" 
                    value="{{ request('date_to') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </form>
    </div>
</div>

@if(isset($reportData['driver']))
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    Financial Report for {{ $reportData['driver']->name }}
                    ({{ \Carbon\Carbon::parse($reportData['date_from'])->format('M d, Y') }} 
                    to {{ \Carbon\Carbon::parse($reportData['date_to'])->format('M d, Y') }})
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- In your blade file, replace the calculation display -->
<div class="card border-success mb-3">
    <div class="card-header bg-success text-white">
        <strong>Driver Settlement Summary</strong>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <tr>
                <td><strong>Actual Cash Received:</strong></td>
                <td class="text-end">{{ number_format($reportData['total_actual_cash_received'], 0) }} UGX</td>
            </tr>
            <tr>
                <td><strong>Total Commission:</strong></td>
                <td class="text-end">- {{ number_format($reportData['total_commission'], 0) }} UGX</td>
            </tr>
            <tr>
                <td><strong>Total Expenses:</strong></td>
                <td class="text-end">- {{ number_format($reportData['total_expenses'], 0) }} UGX</td>
            </tr>
            <tr class="table-warning fw-bold">
                <td><strong>Expected to Bank:</strong></td>
                <td class="text-end">{{ number_format($reportData['total_expected_after_deductions'], 0) }} UGX</td>
            </tr>
        </table>
        <small class="text-muted">
            <i class="bi bi-info-circle"></i> 
            Expected to Bank = Actual Cash Received - Commission - Expenses
            <br>
            <strong>Calculation: {{ number_format($reportData['total_actual_cash_received'], 0) }} - {{ number_format($reportData['total_commission'], 0) }} - {{ number_format($reportData['total_expenses'], 0) }} = {{ number_format($reportData['total_expected_after_deductions'], 0) }} UGX</strong>
        </small>
    </div>
</div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info mb-3">
                            <div class="card-header bg-info text-white">
                                <strong>Bank Deposits (Finance Verified)</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Actually Banked:</strong></td>
                                        <td class="text-end">{{ number_format($reportData['total_deposits'], 0) }} UGX</td>
                                    </tr>
                                    <tr class="table-light fw-bold">
                                        <td><strong>Shortage/Excess:</strong></td>
                                        <td class="text-end text-{{ $reportData['shortage_excess'] >= 0 ? 'danger' : 'success' }}">
                                            {{ number_format(abs($reportData['shortage_excess']), 0) }} UGX
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($reportData['shortage_excess'] > 0)
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Driver Owes: {{ number_format($reportData['shortage_excess'], 0) }} UGX</strong><br>
                        <small>
                            Driver should deposit {{ number_format($reportData['total_expected_after_deductions'], 0) }} UGX 
                            but only deposited {{ number_format($reportData['total_deposits'], 0) }} UGX.
                        </small>
                    </div>
                @elseif($reportData['shortage_excess'] < 0)
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>Overpayment: {{ number_format(abs($reportData['shortage_excess']), 0) }} UGX</strong><br>
                        <small>
                            Driver deposited {{ number_format(abs($reportData['shortage_excess']), 0) }} UGX more than expected.
                        </small>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>Fully Settled - No Balance Due</strong><br>
                        <small>
                            Driver has deposited exactly what was expected.
                        </small>
                    </div>
                @endif

                <!-- Sales Summary for Reference -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <strong>Sales Reference (For Information Only)</strong>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Total Sales Value:</strong></td>
                                <td class="text-end">{{ number_format($reportData['total_sales'], 0) }} UGX</td>
                            </tr>
                            <tr>
                                <td><em>Includes both cash and credit sales</em></td>
                                <td class="text-end"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Notes -->
<div class="card mt-3">
    <div class="card-body">
        <h6><i class="bi bi-lightbulb"></i> Understanding This Report</h6>
        <ul class="mb-0">
            <li><strong>Actual Cash Received:</strong> Money the driver actually collected (entered by manager)</li>
            <li><strong>Expected to Bank:</strong> What driver should deposit after deducting commission & expenses</li>
            <li><strong>Actually Banked:</strong> Verified deposits recorded by Finance department</li>
            <li><strong>Shortage:</strong> Driver needs to pay this amount to settle</li>
            <li><strong>Excess:</strong> Driver deposited more than required</li>
        </ul>
    </div>
</div>
@else
<div class="card mt-4">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-graph-up display-4 d-block mb-3"></i>
        <h5>No Report Generated</h5>
        <p>Select a driver and date range to view the financial report</p>
    </div>
</div>
@endif
@endsection