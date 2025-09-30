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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

<style>
:root {
  --primary: #C97C5D;          /* Cinnamon / caramel */
  --primary-hover: #DA9B7A;    /* Lighter cinnamon */
  --sidebar-bg: #3E2C2C;       /* Dark chocolate */
  --sidebar-text: #F5EDE6;     /* Whipped-cream text */
  --sidebar-hover: #fff;       
  --bg: #FFF9F6;               /* Very light cream background */
  --card-bg: #FFF2EA;          /* Subtle card background */
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
.sidebar {
  background: linear-gradient(180deg, var(--sidebar-bg) 0%, #2A1E1E 100%);
  border-top-right-radius: 12px;
  border-bottom-right-radius: 12px;
}


.sidebar a,
.accordion-button {
  border-radius: 10px;
}

.content {
  background: var(--bg);
  min-height: 100vh;
}

.card {
  background: var(--card-bg);
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  border: none;
}


.sidebar-logo {
    text-align: center;
    font-weight: 700;
    font-family: 'Pacifico', cursive; /* load Google font */
    font-size: 1.6rem;
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

/* === Make accordion links look identical to the top-level Dashboard link === */
/* Remove bootstrap white background / borders inside the sidebar accordion */
.sidebar .accordion .accordion-item,
.sidebar .accordion .accordion-collapse,
.sidebar .accordion .accordion-body {
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;          /* remove extra white gaps */
}

/* Style the links inside accordion-body to match .sidebar a (Dashboard) */
.sidebar .accordion .accordion-body > a {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--sidebar-text) !important;
  padding: 0.8rem 1rem;
  border-radius: 8px;
  margin-bottom: 0.3rem;
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.25s ease;
  background: transparent;
}

/* Hover / Active state — match Dashboard link */
.sidebar .accordion .accordion-body > a:hover,
.sidebar .accordion .accordion-body > a.active {
  background: var(--primary) !important;
  color: var(--sidebar-hover) !important;
  transform: translateX(4px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Make sure the accordion header when open still matches hover style */
.sidebar .accordion-button:not(.collapsed) {
  background: var(--primary) !important;
  color: var(--sidebar-hover) !important;
  transform: translateX(3px);
  box-shadow: 0 3px 6px rgba(0,0,0,0.08);
}

/* Icon colors inside links should inherit the text color */
.sidebar .accordion .accordion-body > a .bi {
  color: inherit !important;
}

/* Give all sidebar links and accordion headers consistent icon spacing */
.sidebar a,
.sidebar .accordion-button {
  display: flex;
  align-items: center;
  gap: 0.75rem;            /* uniform space between icon and text */
}

/* Make sure icons inherit the text colour and don't shrink */
.sidebar a .bi,
.sidebar .accordion-button .bi {
  color: inherit !important;
  font-size: 1rem;         /* adjust icon size if needed */
  flex-shrink: 0;          /* stops icon from squishing */
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

            <a href="{{ route('admin.reports.index') }}" 
                class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Reports
                </a>

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

@stack('scripts')
</body>
</html>
