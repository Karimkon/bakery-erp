<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Sales') - Bakery ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    {{-- Bootstrap 5 & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />


    <style>
        /* === SALES LAYOUT ENHANCEMENTS === */

        /* Accent colour for sales area – change to match your brand */
        :root {
            --sales-accent: #e09b3d; /* warm bakery orange */
            --sales-hover: #fff;
            --sales-text: #333;
        }

        body {
            background:#f8fafc;
        }

        /* Sidebar base */
        .sidebar {
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            min-height: 100vh;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                min-height: auto;
            }
        }

        /* Section title */
        .sidebar h5 {
            font-weight: 700;
            color: var(--sales-accent);
        }

        /* List group links */
        .sidebar .list-group-item {
            border: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;         /* consistent icon/text spacing */
            padding: 0.75rem 1rem;
            color: var(--sales-text);
            font-weight: 500;
            border-radius: 6px;
            background: transparent;
            transition: all .2s ease;
        }

        /* Hover + active states */
        .sidebar .list-group-item:hover,
        .sidebar .list-group-item.active {
            background: var(--sales-accent);
            color: var(--sales-hover);
            transform: translateX(4px);
            box-shadow: 0 3px 6px rgba(0,0,0,.05);
        }

        /* Make icons inherit colour */
        .sidebar .list-group-item .bi {
            color: inherit;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* Logout button in footer */
        .sidebar-footer {
            margin-top: 1rem;
        }
        .sidebar-footer .logout-button {
            width: 100%;
            border: none;
            background: transparent;
            color: var(--sales-text);
            padding: 0.75rem 1rem;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all .2s ease;
        }
        .sidebar-footer .logout-button:hover {
            background: #fbe9da;
            color: var(--sales-accent);
        }

        /* POS cards on main panel */
        .pos-card {
            cursor: pointer;
            border-radius: 8px;
            transition: .2s ease;
        }
        .pos-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
        }

        .stat {
            font-weight: 600;
        }
    </style>

    @stack('head')
</head>
<body>
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <aside class="col-12 col-lg-2 sidebar p-3">
            <h5 class="mb-4">Sales</h5>
            <div class="list-group">
                <a class="list-group-item list-group-item-action" href="{{ route('sales.sales.index') }}">
                    <i class="bi bi-cash-coin"></i> Shop Sales
                </a>
                <a class="list-group-item list-group-item-action" href="{{ route('sales.sales.create') }}">
                    <i class="bi bi-bag-plus"></i> New Sale (POS)
                </a>
                <a class="list-group-item list-group-item-action" href="{{ route('sales.stock.index') }}">
                    <i class="bi bi-box-seam"></i> Shop Stock
                </a>
                <a class="list-group-item list-group-item-action" href="{{ route('sales.bankings.index') }}">
                    <i class="bi bi-bank"></i> Bankings
                </a>
                <a class="list-group-item list-group-item-action" href="{{ route('sales.bankings.create') }}">
                    <i class="bi bi-receipt-cutoff"></i> Record Banking
                </a>
            </div>
            <div class="sidebar-footer mt-3">
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
