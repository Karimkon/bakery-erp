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

<div class="table-responsive">
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>Date</th>
            <th>Chef</th>
            <th>Flour</th>
            <th>Outputs</th>
            <th>Total (UGX)</th>
            <th>Variance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($productions as $p)
        <tr>
            <td>{{ $p->production_date }}</td>
            <td>{{ $p->user->name }}</td>
            <td>{{ $p->flour_bags }} bags</td>
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
            <td>{{ number_format($p->total_value) }}</td>
            <td>
                @if($p->has_variance)
                    <span class="badge bg-danger">Variance</span>
                @else
                    <span class="badge bg-success">OK</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
{{ $productions->links() }}
@endsection
