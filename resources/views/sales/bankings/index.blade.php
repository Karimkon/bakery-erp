{{-- resources/views/sales/bankings/index.blade.php --}}
@extends('sales.layouts.app')
@section('title','Bankings')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i>My Bankings</h4>
        <a href="{{ route('sales.bankings.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Record Banking
        </a>
    </div>

    @php
        $q = \App\Models\Banking::where('user_id', auth()->id());
        $sum = (clone $q)->sum('amount');
        $bankings = $q->latest()->paginate(20);
    @endphp

    <div class="row g-3 mb-2">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted">Total Banked (All Time)</div>
                <div class="stat fs-4">{{ number_format($sum) }} UGX</div>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th class="text-end">Amount (UGX)</th>
                    <th>Receipt No.</th>
                    <th>Receipt File</th>
                    <th>Notes</th>
                    <th>When</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($bankings as $b)
                    <tr>
                        <td>{{ $b->id }}</td>
                        <td>{{ $b->date?->format('Y-m-d') }}</td>
                        <td class="text-end fw-semibold">{{ number_format($b->amount,2) }}</td>
                        <td>{{ $b->receipt_number ?: '—' }}</td>
                        <td>
                            @if($b->receipt_file)
                                @php $url = asset('storage/'.$b->receipt_file); @endphp
                                @if(Str::endsWith(strtolower($b->receipt_file), ['.jpg','.jpeg','.png']))
                                    <a href="{{ $url }}" target="_blank">
                                        <img src="{{ $url }}" alt="receipt" style="height:38px" class="border rounded">
                                    </a>
                                @else
                                    <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-file-earmark-pdf"></i> View
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $b->notes ?: '—' }}</td>
                        <td>{{ $b->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('sales.bankings.destroy',$b) }}" method="POST" onsubmit="return confirm('Delete this record?')">
                                @csrf @method('DELETE')
                                <a href="{{ route('sales.bankings.edit',$b) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No bankings yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $bankings->links() }}
        </div>
    </div>
@endsection
