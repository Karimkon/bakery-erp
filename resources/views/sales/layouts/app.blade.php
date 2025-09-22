<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Sales Dashboard - Bakery ERP')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar { width:220px; background:#fd7e14; min-height:100vh; }
        .sidebar a { color:#fff; display:block; padding:.75rem 1rem; text-decoration:none; }
        .sidebar a:hover { background:#e8590c; }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="sidebar p-3">
        <h4 class="text-white">Sales</h4>
        <a href="{{ route('sales.dashboard') }}"><i class="bi bi-bar-chart-line me-2"></i> Dashboard</a>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf
            <button class="btn btn-dark w-100"><i class="bi bi-box-arrow-left"></i> Logout</button>
        </form>
    </div>
    <div class="flex-fill p-4">@yield('content')</div>
</div>
</body>
</html>
