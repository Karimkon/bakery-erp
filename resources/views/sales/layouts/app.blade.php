{{-- resources/views/sales/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Sales') - Bakery ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background:#f8fafc; }
        .pos-card{ cursor:pointer; transition:.15s; }
        .pos-card:hover{ transform:translateY(-2px); box-shadow:0 .5rem 1rem rgba(0,0,0,.08)!important; }
        .stat{ font-weight:600; }
        .sidebar{ min-height:100vh; }
        @media (max-width: 991.98px) { .sidebar{ min-height:auto; } }
    </style>
    @stack('head')
</head>
<body>
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <aside class="col-12 col-lg-2 bg-white border-end sidebar p-3">
            <h5 class="mb-4">Sales</h5>
            <div class="list-group">
                <a class="list-group-item list-group-item-action" href="{{ route('sales.sales.index') }}">
                    <i class="bi bi-cash-coin me-2"></i>Shop Sales
                </a>
                <a class="list-group-item list-group-item-action" href="{{ route('sales.sales.create') }}">
                    <i class="bi bi-bag-plus me-2"></i>New Sale (POS)
                </a>
                <a class="list-group-item list-group-item-action" href="{{ route('sales.stock.index') }}">
                    <i class="bi bi-box-seam me-2"></i>Shop Stock
                </a>
                <a class="list-group-item list-group-item-action" href="{{ route('sales.bankings.index') }}">
                    <i class="bi bi-bank me-2"></i>Bankings
                </a>
                <a class="list-group-item list-group-item-action" href="{{ route('sales.bankings.create') }}">
                    <i class="bi bi-receipt-cutoff me-2"></i>Record Banking
                </a>
                
            </div>
            <br>
            <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-button">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
        </aside>

        {{-- Main --}}
        <main class="col-12 col-lg-10 p-3 p-lg-4">
            @includeWhen(session('success') || session('error') || $errors->any(), 'sales.partials.flash')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
