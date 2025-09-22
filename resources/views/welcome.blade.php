<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bakery ERP</title>

    <!-- Tailwind Setup -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>

    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet" />

    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-in { opacity: 0; transform: translateY(40px); transition: opacity 1s ease, transform 1s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }
        .pulse-ring { position: relative; }
        .pulse-ring::before {
            content:""; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
            width:100%; height:100%; border-radius:9999px;
            background-color:rgba(255,255,255,0.1); animation:pulse 2.5s infinite;
        }
        @keyframes pulse { 0%{transform:translate(-50%,-50%) scale(1); opacity:.7;} 100%{transform:translate(-50%,-50%) scale(1.5); opacity:0;} }
        .electric-icon { color:#facc15; animation:flicker 1.5s infinite; }
        @keyframes flicker { 0%,100%{opacity:1; text-shadow:0 0 8px #facc15;} 50%{opacity:.7; text-shadow:0 0 4px #facc15;} }
    </style>
</head>

<body class="bg-gradient-to-br from-black via-gray-900 to-gray-800 text-white transition-all duration-300 dark:from-white dark:to-gray-100 dark:text-black">

<div class="min-h-screen flex flex-col items-center justify-center px-4 sm:px-8 py-12">
    <!-- Logo -->
    <div class="text-center max-w-3xl w-full">
        <div class="pulse-ring mb-5">
            <img src="{{ asset('images/bakerylogo.jpg') }}" alt="Bakery ERP Logo"
                 class="mx-auto w-20 sm:w-24 h-20 sm:h-24 rounded-full border-4 border-yellow-400 shadow-xl object-contain" />
        </div>

        <h1 class="text-3xl sm:text-5xl font-extrabold mb-3">
            <span class="electric-icon">🥐</span> Bread Cravers Bakery ERP
        </h1>

        <p class="text-sm sm:text-lg text-white/70 dark:text-gray-800 mb-8">
            Manage production, sales, finance, and chef operations — all in one place.
        </p>

        <!-- Role Buttons -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 text-sm sm:text-base">
            <a href="{{ route('admin.login') }}" class="bg-purple-600 hover:bg-purple-700 px-3 py-2 sm:px-5 sm:py-3 rounded-xl shadow-lg text-center font-semibold transition">Admin</a>
            <a href="{{ route('chef.login') }}" class="bg-green-600 hover:bg-green-700 px-3 py-2 sm:px-5 sm:py-3 rounded-xl shadow-lg text-center font-semibold transition">Chef</a>
            <a href="{{ route('sales.login') }}" class="bg-orange-600 hover:bg-orange-700 px-3 py-2 sm:px-5 sm:py-3 rounded-xl shadow-lg text-center font-semibold transition">Sales</a>
            <a href="{{ route('finance.login') }}" class="bg-blue-600 hover:bg-blue-700 px-3 py-2 sm:px-5 sm:py-3 rounded-xl shadow-lg text-center font-semibold transition">Finance</a>
        </div>

        <!-- Footer -->
        <div class="mt-10 text-xs sm:text-sm text-gray-300 dark:text-gray-700">
            © {{ date('Y') }} Bakery ERP. All rights reserved.
        </div>
    </div>
</div>

<!-- Animation Script -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll('.fade-in').forEach((el, i) => {
            setTimeout(() => el.classList.add('visible'), i * 200);
        });
    });
</script>

</body>
</html>
