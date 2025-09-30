<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chef Login - Bakery ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg,#1e293b,#0f172a);
            font-family: 'Inter', sans-serif;
            color: #fff;
            overflow-x: hidden;
        }
        .login-container {
            max-width: 440px;
            margin: auto;
            padding: 2rem;
            background: #fff;
            color: #000;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0,0,0,.25);
            animation: slideUp 0.7s ease-out;
        }
        .login-logo {
            width: 120px;
            height: auto;
            animation: fadeIn 1s ease-out;
        }
        .login-title {
            font-weight: 700;
            font-size: 1.75rem;
            animation: fadeIn 1.2s ease-out;
        }
        .btn-green {
            background: #198754;
            border: none;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn-green:hover {
            background: #157347;
            transform: translateY(-2px);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px);}
            to   { opacity: 1; transform: translateY(0);}
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px);}
            to   { opacity: 1; transform: translateY(0);}
        }
        @media(max-width: 576px){
            .login-container { padding: 1.5rem; }
            .login-logo { width: 90px; }
            .login-title { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
<div class="d-flex align-items-center justify-content-center vh-100 px-3">
    <div class="login-container text-center">
        <img src="{{ asset('images/bakerylogo.jpg') }}" alt="Bakery ERP Logo" class="login-logo mb-3">
        <h4 class="login-title mb-4">Chef Login</h4>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('chef.login.submit') }}">
            @csrf
            <div class="mb-3 text-start">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-grid">
                <button class="btn btn-green text-white fw-bold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS (optional for animations) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
