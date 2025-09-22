<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Chef Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <div class="bg-dark text-white p-3" style="width:220px; min-height:100vh;">
        <h4>Chef</h4>
        <a href="{{ route('chef.dashboard') }}" class="d-block text-white mb-2">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('chef.productions.index') }}" class="d-block text-white mb-2">
            <i class="bi bi-journal-text"></i> My Productions
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-danger w-100 mt-3">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
    <div class="flex-fill p-4">
        @yield('content')
    </div>
</div>
</body>
</html>
