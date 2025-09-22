<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard - Bakery ERP')</title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    <style>
        body { background:#f1f5f9; font-family:'Inter',sans-serif; }
        .sidebar {
            background:linear-gradient(180deg,#0f172a,#1e293b);
            color:#fff; height:100vh; width:260px;
            position:fixed; top:0; left:0;
            overflow-y:auto; transition:left 0.3s ease;
            box-shadow:2px 0 10px rgba(0,0,0,0.15);
            display:flex; flex-direction:column;
        }
        .sidebar-logo {
            text-align:center; font-weight:700; font-size:1.2rem;
            padding:1rem; border-bottom:1px solid rgba(255,255,255,0.1);
        }
        .sidebar a {
            color:#9ca3af; display:flex; align-items:center;
            padding:12px 20px; text-decoration:none;
            border-left:4px solid transparent; transition:0.2s;
            font-size:14px; font-weight:500;
        }
        .sidebar a:hover, .sidebar a.active {
            background:rgba(255,255,255,0.1);
            color:#fff; border-left-color:#3b82f6;
        }
        .accordion-button {
            background:transparent!important; color:#9ca3af!important;
            padding:12px 20px; font-size:14px; font-weight:500;
            border:none!important; border-left:4px solid transparent;
            transition:0.2s;
        }
        .accordion-button:hover, .accordion-button:not(.collapsed) {
            background:rgba(255,255,255,0.1)!important; color:#fff!important;
            border-left-color:#3b82f6;
        }
        .accordion-body a { padding-left:45px; font-size:13px; }
        .content { margin-left:260px; padding:2rem; }
        .logout-button {
            margin:20px; background:#dc3545; border:none; color:#fff;
            padding:12px; border-radius:6px; font-weight:500;
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        @media(max-width:768px){
            .sidebar{left:-260px;}
            .sidebar.active{left:0;}
            .content{margin-left:0;padding:1rem;}
        }
    </style>
</head>
<body>
    <!-- 📚 Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            🥐 Bakery Admin
        </div>
        <div class="sidebar-content">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>

            <!-- User Management -->
            <div class="accordion" id="adminAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#usersCollapse">
                            <i class="bi bi-people-fill me-2"></i> User Management
                        </button>
                    </h2>
                    <div id="usersCollapse" class="accordion-collapse collapse {{ request()->is('admin/users*') ? 'show' : '' }}">
                        <div class="accordion-body">
                            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="bi bi-person-badge-fill me-2"></i> All Users
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Chef Production -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#chefCollapse">
                            <i class="bi bi-journal-text me-2"></i> Chef Production
                        </button>
                    </h2>
                    <div id="chefCollapse" class="accordion-collapse collapse {{ request()->is('admin/productions*') ? 'show' : '' }}">
                        <div class="accordion-body">
                            <a href="{{ route('admin.productions.index') }}" class="{{ request()->routeIs('admin.productions.*') ? 'active' : '' }}">
                                <i class="bi bi-bag-check me-2"></i> Daily Productions
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Ingredients -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ingredientsCollapse">
                            <i class="bi bi-box-seam me-2"></i> Ingredients
                        </button>
                    </h2>
                    <div id="ingredientsCollapse" class="accordion-collapse collapse {{ request()->is('admin/ingredients*') ? 'show' : '' }}">
                        <div class="accordion-body">
                            <a href="{{ route('admin.ingredients.index') }}" class="{{ request()->routeIs('admin.ingredients.*') ? 'active' : '' }}">
                                <i class="bi bi-box me-2"></i> Ingredient Stock
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Dispatch -->
            <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dispatchCollapse">
                <i class="bi bi-truck me-2"></i> Dispatch
                </button>
            </h2>
            <div id="dispatchCollapse" class="accordion-collapse collapse {{ request()->is('admin/dispatches*') ? 'show' : '' }}">
                <div class="accordion-body">
                <a href="{{ route('admin.dispatches.index') }}" class="{{ request()->routeIs('admin.dispatches.*') ? 'active' : '' }}">
                    <i class="bi bi-list-check me-2"></i> Driver Dispatches
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

    <!-- Content -->
    <main class="content">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
