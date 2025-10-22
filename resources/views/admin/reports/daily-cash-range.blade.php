@extends('admin.layouts.app')

@section('title', 'Daily Cash Reports')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-1">
            <i class="bi bi-calendar-range text-primary me-2"></i>Daily Cash Reports
        </h3>
        
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.reports.daily-cash') }}" class="btn btn-outline-secondary">Single Day</a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th class="text-end">Total Sales</th>
                    <th class="text-end">Cash Sales</th>
                    <th class="text-end">Mobile Sales</th>
                    <th class="text-end">Expenses</th>
                    <th class="text-end">Bank Deposits</th>
                    <th class="text-end">Driver Bankings</th>
                    <th class="text-end">Expected Cash</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dates as $date => $data)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                    <td class="text-end">{{ number_format($data['total_sales']) }}</td>
                    <td class="text-end">{{ number_format($data['cash_sales']) }}</td>
                    <td class="text-end">{{ number_format($data['mobile_sales']) }}</td>
                    <td class="text-end">{{ number_format($data['total_expenses']) }}</td>
                    <td class="text-end">{{ number_format($data['total_deposits']) }}</td>
                    <td class="text-end">{{ number_format($data['total_bankings']) }}</td>
                    <td class="text-end {{ $data['expected_cash'] < 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($data['expected_cash']) }}
                    </td>
                    <td>
                        <a href="{{ route('admin.reports.daily-cash', ['date' => $date]) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Details
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection