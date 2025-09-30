<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Finance Login - Bakery ERP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg,#1e293b,#0f172a);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Inter', sans-serif;
    }

    .login-container {
      max-width: 440px;
      width: 100%;
      padding: 2rem;
      background: #fff;
      color: #000;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,.25);
      animation: fadeInUp 0.8s ease both;
    }

    .login-logo {
      width: 120px;
      height: auto;
      animation: pulseLogo 2s infinite;
    }

    .login-title {
      font-weight: 700;
      margin-top: .5rem;
    }

    .btn-purple {
      background: #6f42c1;
      border: none;
      transition: background .3s ease, transform .2s ease;
    }
    .btn-purple:hover {
      background: #5a32a3;
      transform: translateY(-1px);
    }

    @keyframes fadeInUp {
      from {opacity: 0; transform: translateY(30px);}
      to {opacity: 1; transform: translateY(0);}
    }

    @keyframes pulseLogo {
      0%, 100% {transform: scale(1);}
      50% {transform: scale(1.05);}
    }
  </style>
</head>
<body>
  <div class="login-container text-center">
    <img src="{{ asset('images/bakerylogo.jpg') }}" alt="Finance Icon" class="login-logo mb-3">
    <h4 class="login-title">Finance Login</h4>

    @if(session('error'))
      <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('finance.login.submit') }}" class="mt-4 text-start">
      @csrf
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="d-grid">
        <button class="btn btn-purple text-white fw-bold">
          <i class="bi bi-box-arrow-in-right me-1"></i> Login
        </button>
      </div>
    </form>
  </div>
</body>
</html>
