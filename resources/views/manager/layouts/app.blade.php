<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Bakery Manager Dashboard')</title>

<!-- Bootstrap + Icons + Fonts + Select2 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

<style>
:root {
  --primary: #C97C5D;
  --primary-hover: #DA9B7A;
  --sidebar-bg: #3E2C2C;
  --sidebar-text: #F5EDE6;
  --sidebar-hover: #fff;
  --bg: #FFF9F6;
  --card-bg: #FFF2EA;
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    margin: 0;
    transition: background 0.3s;
}

/* Sidebar */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, var(--sidebar-bg) 0%, #2A1E1E 100%);
    color: var(--sidebar-text);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.3s ease;
    z-index: 1000;
    overflow-y: auto;
    box-shadow: 2px 0 15px rgba(0,0,0,0.2);
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}
.sidebar-logo {
    text-align: center;
    font-weight: 700;
    font-family: 'Pacifico', cursive;
    font-size: 1.6rem;
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    color: var(--primary);
}
.sidebar a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--sidebar-text);
    padding: 0.8rem 1rem;
    border-radius: 8px;
    margin-bottom: 0.3rem;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
}
.sidebar a:hover, .sidebar a.active {
    background: var(--primary);
    color: var(--sidebar-hover);
    transform: translateX(3px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
}

.content {
    margin-left: 260px;
    padding: 2rem;
    min-height: 100vh;
    background: var(--bg);
}

/* Sidebar Footer */
.sidebar-footer {
    margin-top: auto;
    padding: 1rem;
}
.logout-button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem;
    border-radius: 8px;
    background: #EF4444;
    color: #fff;
    border: none;
    font-weight: 500;
    transition: all 0.3s ease;
}
.logout-button:hover {
    background: #DC2626;
    transform: translateY(-2px);
}

/* Mobile */
@media(max-width:1024px){ 
    .sidebar { width:240px; } 
    .content{margin-left:240px;} 
}
@media(max-width:768px){
    .sidebar { left:-260px; }
    .sidebar.active { left:0; box-shadow:4px 0 20px rgba(0,0,0,0.3); }
    .content { margin-left:0; padding:1rem; }
    .mobile-toggle {
        position: fixed;
        top: 10px;
        left: 10px;
        font-size: 1.8rem;
        color: var(--primary);
        z-index: 1100;
        cursor: pointer;
    }
}
</style>
</head>
<body>

<!-- Mobile toggle -->
<div class="mobile-toggle d-md-none"><i class="bi bi-list"></i></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">🥐 Bakery Manager</div>

    <div class="sidebar-content">
        <a href="{{ route('manager.dashboard') }}" class="{{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <!-- Only valid routes: Dispatch and Production -->
        <a href="{{ route('manager.dispatches.index') }}" class="{{ request()->routeIs('manager.dispatches.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Driver Dispatches
        </a>

        <a href="{{ route('manager.ingredients.index') }}" class="{{ request()->routeIs('manager.ingredients.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Ingredients
        </a>

        <a href="{{ route('manager.production.index') }}" class="{{ request()->routeIs('manager.production.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Production Reports
        </a>

        <!-- Removed any route that does not exist to avoid RouteNotFoundException -->
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-button">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</div>

<!-- Main Content -->
<main class="content">
    @yield('content')
</main>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const toggle = document.querySelector('.mobile-toggle');
const sidebar = document.getElementById('sidebar');
toggle.addEventListener('click', () => sidebar.classList.toggle('active'));
</script>

@stack('scripts')
</body>
</html>
