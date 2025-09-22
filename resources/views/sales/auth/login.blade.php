<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Login - Bakery ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:linear-gradient(135deg,#0f172a,#334155); }
        .login-container { max-width:420px; margin:auto; padding:2rem; background:#fff; color:#000; border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,.25); }
        .btn-orange { background:#fd7e14; border:none; } .btn-orange:hover{background:#e8590c;}
    </style>
</head>
<body>
<div class="d-flex align-items-center justify-content-center vh-100 px-3">
    <div class="login-container">
        <div class="text-center mb-4">
            <img src="{{ asset('images/sales.png') }}" alt="Sales Icon" class="login-logo mb-2">
            <h4 class="login-title">Sales Staff Login</h4>
        </div>

        @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

        <form method="POST" action="{{ route('sales.login.submit') }}">
            @csrf
            <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
            <div class="d-grid"><button class="btn btn-orange text-white fw-bold"><i class="bi bi-box-arrow-in-right me-1"></i> Login</button></div>
        </form>
    </div>
</div>
</body>
</html>
