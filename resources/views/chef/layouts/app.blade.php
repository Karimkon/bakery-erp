<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Chef Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }
        /* Cards */
        .card {
            border: none;
            border-radius: .75rem;
            background: #fff;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .card h6 {
            font-size: .9rem;
            color: #6c757d;
            margin-bottom: .5rem;
        }
        .card h3 {
            font-weight: 600;
            color: #212529;
        }

        /* Tables */
        .table {
            border-radius: .5rem;
            overflow: hidden;
        }
        .table thead {
            background: #212529;
            color: #fff;
        }
        .table tbody tr:hover {
            background-color: #f1f3f5;
        }
        .table td, .table th {
            vertical-align: middle;
            padding: .75rem;
            font-size: .9rem;
        }

        /* Badges */
        .badge {
            font-size: .75rem;
            padding: .4em .7em;
            border-radius: .5rem;
        }

        /* Buttons */
        .btn {
            border-radius: .4rem;
            font-weight: 500;
        }
        .btn-sm {
            padding: .3rem .6rem;
            font-size: .8rem;
        }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #212529;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
        }
        .sidebar h4 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .6rem 1rem;
            border-radius: .375rem;
            text-decoration: none;
            transition: all .2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #343a40;
            color: #fff !important;
        }
        .content {
            margin-left: 240px;
            padding: 2rem;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar text-white p-3">
        <h4><i class="bi bi-egg-fried"></i> Chef</h4>

        <a href="{{ route('chef.dashboard') }}" 
           class="{{ request()->routeIs('chef.dashboard') ? 'active text-white' : 'text-white-50' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="{{ route('chef.productions.index') }}" 
           class="{{ request()->routeIs('chef.productions.*') ? 'active text-white' : 'text-white-50' }}">
            <i class="bi bi-journal-text"></i> My Productions
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="btn btn-danger w-100">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
