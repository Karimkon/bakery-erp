@extends('chef.layouts.app')

@section('title','My Productions')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-journal-text me-2"></i> My Productions</h4>
        <a href="{{ route('chef.productions.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Production
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive shadow-sm">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Date</th>
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
                    <td>{{ $p->production_date }}</td>
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
                    <td>
                        <a href="{{ route('chef.productions.show', $p->id) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-3">
    {{ $productions->links('pagination::bootstrap-5') }}
</div>  
@endsection
