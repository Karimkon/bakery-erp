@extends('admin.layouts.app')
@section('title','Chef Productions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-journal-text me-2"></i> Chef Productions</h4>
    <a href="{{ route('admin.productions.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> New Record
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive shadow-sm">
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>Date</th>
                <th>Chef</th>
                <th>Flour</th>
                <th>Outputs</th>
                <th>Total (UGX)</th>
                <th>Variance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productions as $p)
            <tr>
                <td class="text-center">{{ $p->production_date }}</td>
                <td>{{ $p->user->name }}</td>
                <td class="text-center">{{ $p->flour_bags }} bags</td>
                <td>
                    Buns: {{ $p->buns }},
                    Small: {{ $p->small_breads }},
                    Big: {{ $p->big_breads }},
                    Donuts: {{ $p->donuts }},
                    Half: {{ $p->half_cakes }},
                    Block: {{ $p->block_cakes }},
                    Slab: {{ $p->slab_cakes }},
                    Birthday: {{ $p->birthday_cakes }}
                </td>
                <td class="text-end">{{ number_format($p->total_value) }}</td>
                <td class="text-center">
                    @if($p->has_variance)
                        <span class="badge bg-danger">Variance</span>
                    @else
                        <span class="badge bg-success">OK</span>
                    @endif
                </td>
                <td class="text-center">
                    <a href="{{ route('admin.productions.show', $p->id) }}" class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i> View
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $productions->links('pagination::bootstrap-5') }}
</div>
@endsection
