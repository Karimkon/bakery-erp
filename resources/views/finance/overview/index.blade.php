@extends('finance.layouts.app')

@section('title','Finance Overview')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-wallet2 me-2"></i> Finance Overview — {{ $title }}</h4>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="me-1 small text-muted mb-0">Range</label>
            <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="today" {{ $filter=='today'?'selected':'' }}>Today</option>
                <option value="week" {{ $filter=='week'?'selected':'' }}>This Week</option>
                <option value="month" {{ $filter=='month'?'selected':'' }}>This Month</option>
            </select>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <small class="text-muted">General Expenses</small>
                <h4 class="mb-0 text-danger">{{ number_format($totalGeneral,0) }} UGX</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <small class="text-muted">Driver Expenses</small>
                <h4 class="mb-0 text-danger">{{ number_format($totalDriver,0) }} UGX</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <small class="text-muted">Total Expenses</small>
                <h4 class="mb-0 text-danger">{{ number_format($combinedTotal,0) }} UGX</h4>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card p-3 shadow-sm">
                <h6 class="mb-2">General Expenses</h6>
                <div class="table-responsive" style="max-height:400px; overflow:auto;">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th>Recorded By</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($generalExpenses as $exp)
                            <tr>
                                <td>{{ $exp->expense_date }}</td>
                                <td>{{ $exp->category }}</td>
                                <td>{{ $exp->description }}</td>
                                <td class="text-end">{{ number_format($exp->amount) }}</td>
                                <td>{{ $exp->recorder->name }}</td>
                                <td>
                                    @if($exp->receipt)
                                        @if(Str::endsWith($exp->receipt, ['pdf']))
                                            <a href="{{ asset('storage/'.$exp->receipt) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i></a>
                                        @else
                                            <a href="{{ asset('storage/'.$exp->receipt) }}" target="_blank"><img src="{{ asset('storage/'.$exp->receipt) }}" width="40" alt="Receipt"></a>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center">No general expenses found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-3 shadow-sm">
                <h6 class="mb-2">Driver Expenses</h6>
                <div class="table-responsive" style="max-height:400px; overflow:auto;">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th>Driver</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($driverExpenses as $exp)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($exp->created_at)->format('d M Y') }}</td>
                                <td>{{ $exp->driver?->name }}</td>
                                <td>{{ $exp->expense_type }}</td>
                                <td>{{ $exp->description }}</td>
                                <td class="text-end">{{ number_format($exp->amount) }}</td>
                                <td>
                                    @if($exp->receipt_image)
                                        @if(Str::endsWith($exp->receipt_image, ['pdf']))
                                            <a href="{{ asset('storage/'.$exp->receipt_image) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i></a>
                                        @else
                                            <a href="{{ asset('storage/'.$exp->receipt_image) }}" target="_blank"><img src="{{ asset('storage/'.$exp->receipt_image) }}" width="40" alt="Receipt"></a>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center">No driver expenses found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
