<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Finance Dashboard - Bakery ERP')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar {
            width: 220px;
            background: #6f42c1;
            min-height: 100vh;
        }
        .sidebar a {
            color: #fff;
            display: block;
            padding: .75rem 1rem;
            text-decoration: none;
            border-radius: .3rem;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background: #563d7c;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -220px;
                top: 0;
                transition: all .3s;
                z-index: 1050;
            }
            .sidebar.show {
                left: 0;
            }
            .content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar p-3">
        <h4 class="text-white mb-3">Finance</h4>
        <a href="{{ route('finance.dashboard') }}" 
           class="{{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
           <i class="bi bi-cash-coin me-2"></i> Dashboard
        </a>
        <a href="{{ route('finance.expenses.index') }}" 
           class="{{ request()->routeIs('finance.expenses.*') ? 'active' : '' }}">
           <i class="bi bi-wallet2 me-2"></i> Expenses
        </a>

        <a href="{{ route('finance.deposits.index') }}" 
        class="{{ request()->routeIs('finance.deposits.*') ? 'active' : '' }}">
        <i class="bi bi-bank me-2"></i> Bank Deposits
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf
            <button class="btn btn-light w-100">
                <i class="bi bi-box-arrow-left"></i> Logout
            </button>
        </form>
    </div>

    <!-- Content -->
    <div class="flex-fill content p-4">
        <!-- Mobile toggle button -->
        <button class="btn btn-sm btn-outline-secondary d-md-none mb-3" 
                onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="bi bi-list"></i> Menu
        </button>

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
