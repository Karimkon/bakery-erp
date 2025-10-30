<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kampala Shop Login - Bread Cravers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #059669, #10b981, #34d399);
            color: #fff;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* floating orb background */
        .animated-orb {
            position: absolute;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, #10b981, #059669, #047857);
            border-radius: 50%;
            filter: blur(120px);
            opacity: .4;
            z-index: 0;
            animation: floatOrb 18s infinite linear, shiftColors 10s infinite linear;
        }
        @keyframes floatOrb {
            0%   {transform: translate(10vw,60vh)}
            25%  {transform: translate(60vw,30vh)}
            50%  {transform: translate(40vw,10vh)}
            75%  {transform: translate(70vw,70vh)}
            100% {transform: translate(10vw,60vh)}
        }
        @keyframes shiftColors {
            0%   {background:radial-gradient(circle,#10b981,#059669,#047857)}
            33%  {background:radial-gradient(circle,#059669,#10b981,#34d399)}
            66%  {background:radial-gradient(circle,#047857,#10b981,#34d399)}
            100% {background:radial-gradient(circle,#10b981,#059669,#047857)}
        }

        /* login box */
        .login-container {
            max-width: 440px;
            margin: auto;
            padding: 2.5rem;
            border-radius: 18px;
            background-color: #fff;
            color: #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            position: relative;
            z-index: 2;
            animation: slideUp 0.7s ease-out;
        }

        /* logo */
        .login-logo {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px solid #10b981;
            animation: pulse 2s infinite, fadeIn 1s ease-out;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
        }

        .login-title {
            font-weight: 700;
            color: #065f46;
            margin-top: 1rem;
            animation: fadeIn 1.2s ease-out;
        }

        .btn-green {
            background-color: #10b981;
            border: none;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn-green:hover {
            background-color: #059669;
            transform: translateY(-2px);
        }

        /* animations */
        @keyframes pulse {
            0%,100% {transform:scale(1)}
            50%     {transform:scale(1.05)}
        }
        @keyframes fadeIn {
            from {opacity:0; transform: translateY(-10px)}
            to   {opacity:1; transform: translateY(0)}
        }
        @keyframes slideUp {
            from {opacity:0; transform:translateY(30px)}
            to   {opacity:1; transform:translateY(0)}
        }

        .shop-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }

        @media(max-width: 576px){
            .login-container { padding: 1.5rem; }
            .login-logo { width: 85px; height: 85px; border-width: 3px; }
            .login-title { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
<div class="animated-orb"></div>

<div class="d-flex align-items-center justify-content-center vh-100 px-3">
    <div class="login-container">
        <div class="text-center mb-4">
            <div class="shop-badge">
                <i class="bi bi-shop me-2"></i>KAMPALA SHOP
            </div>
            <img src="{{ asset('images/bakerylogo.jpg') }}" alt="Bread Cravers Logo" class="login-logo mb-2">
            <h4 class="login-title">Kampala Shop Login</h4>
            <p class="text-muted">Access your shop management system</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('kampala.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email" class="form-control border-start-0" 
                           placeholder="your.email@breadcravers.com" required 
                           value="{{ old('email') }}">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" class="form-control border-start-0" 
                           placeholder="Enter your password" required>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <div class="d-grid">
                <button class="btn btn-green text-white fw-bold py-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Login to Shop Dashboard
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <div class="border-top pt-3">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    For Kampala shop staff only
                </small>
                <br>
                <small class="text-muted">© {{ date('Y') }} Bread Cravers. All rights reserved.</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>