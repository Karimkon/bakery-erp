{{-- resources/views/sales/partials/flash.blade.php --}}
@if(session('success'))
    <div class="alert alert-success d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <div>{{ session('error') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-warning">
        <strong>Fix the following:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif
