<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bakery ERP - Bread Cravers</title>
    
    <!-- Tailwind Setup -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            overflow-x: hidden;
        }
        
        
        /* Background Animation */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.1;
        }
        
        /* Floating Ingredients */
        .ingredient {
            position: absolute;
            animation: float 6s ease-in-out infinite;
        }
        
        .ingredient:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .ingredient:nth-child(2) { top: 20%; right: 15%; animation-delay: -1s; }
        .ingredient:nth-child(3) { top: 60%; left: 5%; animation-delay: -2s; }
        .ingredient:nth-child(4) { top: 70%; right: 10%; animation-delay: -3s; }
        .ingredient:nth-child(5) { top: 40%; left: 85%; animation-delay: -4s; }
        .ingredient:nth-child(6) { top: 80%; left: 80%; animation-delay: -5s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        /* Chef Animation */
        .chef {
            position: absolute;
            bottom: 10%;
            right: 5%;
            font-size: 4rem;
            animation: chef-work 4s ease-in-out infinite;
        }
        
        @keyframes chef-work {
            0%, 100% { transform: scale(1) rotate(0deg); }
            25% { transform: scale(1.1) rotate(-2deg); }
            50% { transform: scale(1.05) rotate(2deg); }
            75% { transform: scale(1.1) rotate(-1deg); }
        }
        
        /* Mixing Bowl Animation */
        .mixing-bowl {
            position: absolute;
            bottom: 15%;
            left: 10%;
            font-size: 3rem;
            animation: mix 3s linear infinite;
        }
        
        @keyframes mix {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Particle Effects */
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #fbbf24;
            border-radius: 50%;
            animation: particle-float 8s linear infinite;
        }
        
        @keyframes particle-float {
            0% { 
                transform: translateY(100vh) translateX(0px);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { 
                transform: translateY(-20px) translateX(50px);
                opacity: 0;
            }
        }
        
        /* Card Hover Effects */
        .role-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .role-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        /* Logo Animation */
        .logo-bounce {
            animation: logo-bounce 2s ease-in-out infinite;
        }
        
        @keyframes logo-bounce {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-10px) scale(1.05); }
        }
        
        /* Text Glow Effect */
        .text-glow {
            text-shadow: 0 0 20px rgba(251, 191, 36, 0.5);
        }
        
        /* Cake Building Animation */
        .cake-builder {
            position: absolute;
            bottom: 20%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 2rem;
        }
        
        .cake-layer {
            animation: stack-cake 6s ease-in-out infinite;
        }
        
        .cake-layer:nth-child(1) { animation-delay: 0s; }
        .cake-layer:nth-child(2) { animation-delay: 2s; }
        .cake-layer:nth-child(3) { animation-delay: 4s; }
        
        @keyframes stack-cake {
            0%, 80% { transform: translateY(50px); opacity: 0; }
            20%, 60% { transform: translateY(0px); opacity: 1; }
            100% { transform: translateY(0px); opacity: 1; }
        }
        
        /* Gradient Background */
        .gradient-bg {
            background: linear-gradient(-45deg, #1a1a2e, #16213e, #0f3460, #1a1a2e);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
        
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Sparkle Effect */
        .sparkle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: #fbbf24;
            border-radius: 50%;
            animation: sparkle 4s linear infinite;
        }
        
        @keyframes sparkle {
            0%, 100% { 
                opacity: 0;
                transform: scale(0);
            }
            50% { 
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Generate random sparkles */
        .sparkle:nth-child(1) { top: 10%; left: 20%; animation-delay: 0s; }
        .sparkle:nth-child(2) { top: 30%; left: 70%; animation-delay: 1s; }
        .sparkle:nth-child(3) { top: 50%; left: 10%; animation-delay: 2s; }
        .sparkle:nth-child(4) { top: 70%; left: 80%; animation-delay: 3s; }
        .sparkle:nth-child(5) { top: 20%; left: 90%; animation-delay: 0.5s; }
        .sparkle:nth-child(6) { top: 80%; left: 30%; animation-delay: 1.5s; }
        .sparkle:nth-child(7) { top: 40%; left: 50%; animation-delay: 2.5s; }
        .sparkle:nth-child(8) { top: 60%; left: 5%; animation-delay: 3.5s; }
        
        /* Fade in animation for content */
        .fade-in-up {
            opacity: 0;
            transform: translateY(40px);
            animation: fadeInUp 1s ease forwards;
        }
        
        .fade-in-up:nth-child(1) { animation-delay: 0.2s; }
        .fade-in-up:nth-child(2) { animation-delay: 0.4s; }
        .fade-in-up:nth-child(3) { animation-delay: 0.6s; }
        .fade-in-up:nth-child(4) { animation-delay: 0.8s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="gradient-bg text-white transition-all duration-300">

    <!-- Background Animation Layer -->
    <div class="bg-animation">
        <!-- Floating Ingredients -->
        <div class="ingredient">🥖</div>
        <div class="ingredient">🧈</div>
        <div class="ingredient">🥛</div>
        <div class="ingredient">🥚</div>
        <div class="ingredient">🍯</div>
        <div class="ingredient">🌾</div>
        
        <!-- Chef Character -->
        <div class="chef">👨‍🍳</div>
        
        <!-- Mixing Bowl -->
        <div class="mixing-bowl">🥣</div>
        
        <!-- Cake Building Animation -->
        <div class="cake-builder">
            <div class="cake-layer">🎂</div>
        </div>
        
        <!-- Sparkle Effects -->
        <div class="sparkle"></div>
        <div class="sparkle"></div>
        <div class="sparkle"></div>
        <div class="sparkle"></div>
        <div class="sparkle"></div>
        <div class="sparkle"></div>
        <div class="sparkle"></div>
        <div class="sparkle"></div>
        
        <!-- Floating Particles -->
        <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; animation-delay: 1s;"></div>
        <div class="particle" style="left: 30%; animation-delay: 2s;"></div>
        <div class="particle" style="left: 40%; animation-delay: 3s;"></div>
        <div class="particle" style="left: 50%; animation-delay: 4s;"></div>
        <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
        <div class="particle" style="left: 70%; animation-delay: 6s;"></div>
        <div class="particle" style="left: 80%; animation-delay: 7s;"></div>
        <div class="particle" style="left: 90%; animation-delay: 0.5s;"></div>
    </div>

    <div class="min-h-screen flex flex-col items-center justify-center px-4 sm:px-8 py-12 relative z-10">
        
        <!-- Main Content -->
        <div class="text-center max-w-4xl w-full">
            
            <!-- Logo Section -->
            <div class="fade-in-up mb-8">
                 <div class="logo-bounce mb-6">
                    <img src="{{ asset('images/bakerylogo.jpg') }}" alt="Bakery ERP Logo"
                         class="mx-auto w-28 h-28 sm:w-36 sm:h-36 rounded-full border-4 border-yellow-400 shadow-2xl object-cover" />
                </div>
                
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-bold mb-4 text-glow">
                    <span class="bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 bg-clip-text text-transparent">
                        Bread Cravers
                    </span>
                </h1>
                
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-semibold mb-6 text-yellow-200">
                    Bakery ERP System
                </h2>
            </div>
            
            <!-- Description -->
            <div class="fade-in-up mb-12">
                <p class="text-lg sm:text-xl text-gray-300 mb-4 leading-relaxed max-w-2xl mx-auto">
                    Transform your bakery operations with our comprehensive management system. 
                    From production to sales, we've got you covered! 🍰
                </p>
            </div>
            
            <!-- Role Selection Cards -->
            <div class="fade-in-up mb-12">
                <h3 class="text-xl sm:text-2xl font-semibold mb-8 text-yellow-200">
                    Choose Your Role
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                    
                    <!-- Admin Card -->
                    <a href="/admin/login" class="role-card group p-6 rounded-2xl text-center relative overflow-hidden">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-300">👑</div>
                        <h4 class="text-xl font-bold mb-2 text-purple-300">Admin</h4>
                        <p class="text-sm text-gray-300 mb-4">Complete system control and oversight</p>
                        <div class="bg-purple-600 hover:bg-purple-500 px-4 py-2 rounded-lg font-semibold transition-colors duration-300">
                            Admin Login
                        </div>
                    </a>
                    
                    <!-- Chef Card -->
                    <a href="/chef/login" class="role-card group p-6 rounded-2xl text-center relative overflow-hidden">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-300">👨‍🍳</div>
                        <h4 class="text-xl font-bold mb-2 text-green-300">Chef</h4>
                        <p class="text-sm text-gray-300 mb-4">Recipe management and production planning</p>
                        <div class="bg-green-600 hover:bg-green-500 px-4 py-2 rounded-lg font-semibold transition-colors duration-300">
                            Start Cooking
                        </div>
                    </a>
                    
                    <!-- Sales Card -->
                    <a href="/sales/login" class="role-card group p-6 rounded-2xl text-center relative overflow-hidden">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-300">🛍️</div>
                        <h4 class="text-xl font-bold mb-2 text-orange-300">Shop Sales</h4>
                        <p class="text-sm text-gray-300 mb-4">Point of sale and customer management</p>
                        <div class="bg-orange-600 hover:bg-orange-500 px-4 py-2 rounded-lg font-semibold transition-colors duration-300">
                            Manage Sales
                        </div>
                    </a>
                    
                    <!-- Finance Card -->
                    <a href="/finance/login" class="role-card group p-6 rounded-2xl text-center relative overflow-hidden">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-300">💰</div>
                        <h4 class="text-xl font-bold mb-2 text-blue-300">Finance</h4>
                        <p class="text-sm text-gray-300 mb-4">Financial tracking and reporting</p>
                        <div class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-lg font-semibold transition-colors duration-300">
                            View Reports
                        </div>
                    </a>
                    
                </div>
            </div>
            
            <!-- Features Highlight -->
            <div class="fade-in-up mb-8">
                <div class="flex flex-wrap justify-center gap-4 text-sm sm:text-base">
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/20">
                        📊 Real-time Analytics
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/20">
                        🔄 Automated Workflows  
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/20">
                        📱 Mobile Friendly
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/20">
                        🔒 Secure & Reliable
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="fade-in-up text-center">
                <div class="text-sm text-gray-400 mb-2">
                    © 2025 Bread Cravers Bakery ERP. All rights reserved.
                </div>
                <div class="text-xs text-gray-500">
                    Baking success, one byte at a time! 🍪
                </div>
            </div>
            
        </div>
    </div>

    <!-- Additional Animation Script -->
    <script>
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Create more dynamic particles
            function createParticle() {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 8 + 's';
                particle.style.animationDuration = (Math.random() * 4 + 4) + 's';
                document.querySelector('.bg-animation').appendChild(particle);
                
                // Remove particle after animation
                setTimeout(() => {
                    if (particle.parentNode) {
                        particle.parentNode.removeChild(particle);
                    }
                }, 8000);
            }
            
            // Create particles periodically
            setInterval(createParticle, 2000);
            
            // Add mouse interaction for sparkles
            document.addEventListener('mousemove', function(e) {
                if (Math.random() > 0.95) { // 5% chance on mouse move
                    const sparkle = document.createElement('div');
                    sparkle.className = 'sparkle';
                    sparkle.style.left = e.clientX + 'px';
                    sparkle.style.top = e.clientY + 'px';
                    sparkle.style.position = 'fixed';
                    sparkle.style.pointerEvents = 'none';
                    sparkle.style.zIndex = '1000';
                    document.body.appendChild(sparkle);
                    
                    setTimeout(() => {
                        if (sparkle.parentNode) {
                            sparkle.parentNode.removeChild(sparkle);
                        }
                    }, 4000);
                }
            });
        });
    </script>

</body>
</html>