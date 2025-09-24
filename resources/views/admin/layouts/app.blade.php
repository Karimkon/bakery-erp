<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Bakery Admin Dashboard')</title>

<!-- Bootstrap + Icons + Fonts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

<style>
:root {
    --primary: #D97706;        /* Warm golden-orange accent */
    --primary-hover: #F59E0B;  /* Slightly lighter for hover */
    --sidebar-bg: #4B342C;     /* Rich chocolate brown */
    --sidebar-text: #F3E0D8;   /* Soft cream text */
    --sidebar-hover: #fff;      /* White for hover */
    --bg: #FFF8F0;             /* Light bakery background */
    --card-bg: #FFF4E6;        /* Subtle card background */
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
    background: var(--sidebar-bg);
    color: var(--sidebar-text);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.3s ease;
    z-index: 1000;
    overflow-y: auto;
    box-shadow: 2px 0 15px rgba(0,0,0,0.2);
}
.sidebar-logo {
    text-align: center;
    font-weight: 700;
    font-size: 1.4rem;
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    color: var(--primary);
}
.sidebar a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
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
    transform: translateX(4px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Accordion Buttons */
.accordion-button {
    background: transparent !important;
    color: var(--sidebar-text) !important;
    padding: 0.8rem 1rem;
    font-size: 0.95rem;
    font-weight: 500;
    border: none !important;
    border-radius: 8px;
    transition: all 0.3s ease;
}
.accordion-button:hover, .accordion-button:not(.collapsed) {
    background: var(--primary) !important;
    color: var(--sidebar-hover) !important;
    transform: translateX(3px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
}
.accordion-body a {
    padding-left: 2.5rem;
    font-size: 0.85rem;
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

/* Main Content */
.content {
    margin-left: 260px;
    padding: 2rem;
    transition: margin-left 0.3s ease;
}

/* Scrollbar */
.sidebar::-webkit-scrollbar { width:6px; }
.sidebar::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.2); border-radius:3px; }
.sidebar::-webkit-scrollbar-track { background:transparent; }

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
    <div class="sidebar-logo">🥐 Bakery Admin</div>

    <div class="sidebar-content">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="accordion" id="adminAccordion">
            <!-- User Management -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#usersCollapse">
                        <i class="bi bi-people-fill"></i> User Management
                    </button>
                </h2>
                <div id="usersCollapse" class="accordion-collapse collapse {{ request()->is('admin/users*') ? 'show' : '' }}">
                    <div class="accordion-body">
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge-fill"></i> All Users
                        </a>
                    </div>
                </div>
            </div>

            <!-- Chef Production -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#chefCollapse">
                        <i class="bi bi-journal-text"></i> Chef Production
                    </button>
                </h2>
                <div id="chefCollapse" class="accordion-collapse collapse {{ request()->is('admin/productions*') ? 'show' : '' }}">
                    <div class="accordion-body">
                        <a href="{{ route('admin.productions.index') }}" class="{{ request()->routeIs('admin.productions.*') ? 'active' : '' }}">
                            <i class="bi bi-bag-check"></i> Daily Productions
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ingredients -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ingredientsCollapse">
                        <i class="bi bi-box-seam"></i> Ingredients
                    </button>
                </h2>
                <div id="ingredientsCollapse" class="accordion-collapse collapse {{ request()->is('admin/ingredients*') ? 'show' : '' }}">
                    <div class="accordion-body">
                        <a href="{{ route('admin.ingredients.index') }}" class="{{ request()->routeIs('admin.ingredients.*') ? 'active' : '' }}">
                            <i class="bi bi-box"></i> Ingredient Stock
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dispatch -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dispatchCollapse">
                        <i class="bi bi-truck"></i> Dispatch
                    </button>
                </h2>
                <div id="dispatchCollapse" class="accordion-collapse collapse 
                    {{ request()->is('admin/dispatches*') || request()->is('admin/shop-dispatch*') || request()->is('admin/shop-report*') ? 'show' : '' }}">
                    <div class="accordion-body">
                        <a href="{{ route('admin.dispatches.index') }}" class="{{ request()->routeIs('admin.dispatches.*') ? 'active' : '' }}">
                            <i class="bi bi-list-check"></i> Driver Dispatches
                        </a>
                        <a href="{{ route('admin.shop-dispatch.index') }}" class="{{ request()->routeIs('admin.shop-dispatch.*') ? 'active' : '' }}">
                            <i class="bi bi-shop"></i> Shop Dispatch
                        </a>
                        <a href="{{ route('admin.shop.report') }}" class="{{ request()->routeIs('admin.shop.report') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart-line"></i> Shop Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Banking -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bankingCollapse">
                        <i class="bi bi-bank"></i> Banking
                    </button>           
                </h2>
                <div id="bankingCollapse" class="accordion-collapse collapse {{ request()->is('admin/bankings*') ? 'show' : '' }}">
                    <div class="accordion-body">
                        <a href="{{ route('admin.bankings.index') }}" class="{{ request()->routeIs('admin.bankings.*') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i> All Bankings
                        </a>    
                    </div>
                </div>
            </div>
        </div>
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
</body>
</html>
