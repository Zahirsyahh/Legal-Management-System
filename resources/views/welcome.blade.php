<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Legal Management System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: #f5f5f7;
            color: #1d1d1f;
            line-height: 1.5;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark-mode {
            background: #0a0a0c;
            color: #f5f5f7;
        }

        /* Elegant Liquid Background */
        .liquid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .liquid-blob {
            position: absolute;
            filter: blur(60px);
            opacity: 0.5;
            animation: liquidMove 25s infinite alternate ease-in-out;
        }

        .blob-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #ff6b6b 0%, #f03e3e 100%);
            top: -150px;
            right: -100px;
            animation-delay: 0s;
        }

        .blob-2 {
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            bottom: -200px;
            left: -150px;
            animation-delay: -5s;
            animation-duration: 30s;
        }

        .blob-3 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -10s;
            animation-duration: 35s;
            filter: blur(80px);
        }

        body.dark-mode .blob-1 {
            background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
            opacity: 0.3;
        }

        body.dark-mode .blob-2 {
            background: linear-gradient(135deg, #4c1d95 0%, #5b21b6 100%);
            opacity: 0.3;
        }

        body.dark-mode .blob-3 {
            background: linear-gradient(135deg, #155e75 0%, #164e63 100%);
            opacity: 0.3;
        }

        @keyframes liquidMove {
            0% {
                border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
                transform: translate(0, 0) scale(1);
            }
            25% {
                border-radius: 40% 60% 70% 30% / 40% 60% 30% 70%;
                transform: translate(50px, 50px) scale(1.1);
            }
            50% {
                border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
                transform: translate(-30px, 80px) scale(0.9);
            }
            75% {
                border-radius: 60% 40% 30% 70% / 40% 50% 60% 30%;
                transform: translate(80px, -30px) scale(1.05);
            }
            100% {
                border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
                transform: translate(0, 0) scale(1);
            }
        }

        /* Main Container */
        .container {
            width: 100%;
            max-width: 560px;
            padding: 24px;
            position: relative;
            z-index: 10;
        }

        /* Premium Glass Card - Liquid Glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-radius: 48px;
            padding: 56px 48px;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.3),
                inset 0 0 0 1px rgba(255, 255, 255, 0.7),
                inset 0 0 40px rgba(255, 255, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Liquid Border Effect */
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 48px;
            padding: 2px;
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.8),
                rgba(255, 255, 255, 0.2),
                rgba(255, 255, 255, 0.8),
                rgba(255, 255, 255, 0.2)
            );
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            animation: borderFlow 8s linear infinite;
        }

        /* Inner Glow */
        .glass-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -50%;
            width: 200%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transform: skewX(-15deg);
            animation: shimmer 8s infinite;
            pointer-events: none;
        }

        body.dark-mode .glass-card {
            background: rgba(10, 10, 12, 0.3);
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.5),
                inset 0 0 0 1px rgba(255, 255, 255, 0.1),
                inset 0 0 40px rgba(255, 255, 255, 0.05);
        }

        body.dark-mode .glass-card::before {
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.2),
                rgba(255, 255, 255, 0.05),
                rgba(255, 255, 255, 0.2),
                rgba(255, 255, 255, 0.05)
            );
        }

        @keyframes borderFlow {
            0% {
                opacity: 0.8;
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0.8;
            }
        }

        @keyframes shimmer {
            0% {
                left: -50%;
            }
            100% {
                left: 150%;
            }
        }

        /* Premium Logo */
        .logo-wrapper {
            margin-bottom: 32px;
            position: relative;
            display: inline-block;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
            box-shadow: 
                0 20px 30px -10px rgba(79, 70, 229, 0.4),
                inset 0 2px 4px rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05) rotate(2deg);
            box-shadow: 
                0 25px 35px -12px rgba(79, 70, 229, 0.5),
                inset 0 2px 4px rgba(255, 255, 255, 0.6);
        }

        body.dark-mode .logo {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            box-shadow: 
                0 20px 30px -10px rgba(99, 102, 241, 0.3),
                inset 0 2px 4px rgba(255, 255, 255, 0.2);
        }

        .logo svg {
            width: 40px;
            height: 40px;
            fill: white;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        /* Title */
        h1 {
            font-size: 40px;
            font-weight: 800;
            background: linear-gradient(135deg, #1e1e2f 0%, #2d2d44 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
            line-height: 1.2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        body.dark-mode h1 {
            background: linear-gradient(135deg, #ffffff 0%, #e0e0ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            text-shadow: 0 2px 15px rgba(255, 255, 255, 0.1);
        }

        /* Subtitle */
        .subtitle {
            font-size: 15px;
            font-weight: 500;
            color: rgba(0, 0, 0, 0.5);
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 3px;
            position: relative;
            display: inline-block;
            padding: 0 20px;
        }

        .subtitle::before,
        .subtitle::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 20px;
            height: 1px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            opacity: 0.3;
        }

        .subtitle::before {
            left: -10px;
            transform: translateX(-100%);
        }

        .subtitle::after {
            right: -10px;
            transform: translateX(100%);
        }

        body.dark-mode .subtitle {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Premium Button */
        .btn {
            display: inline-block;
            padding: 16px 52px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            border-radius: 40px;
            color: white;
            font-size: 17px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.3, 1.1);
            box-shadow: 
                0 15px 25px -8px rgba(79, 70, 229, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.3) inset;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 
                0 20px 30px -8px rgba(79, 70, 229, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.4) inset;
        }

        .btn:hover::before {
            left: 100%;
        }

        body.dark-mode .btn {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            box-shadow: 
                0 15px 25px -8px rgba(99, 102, 241, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        }

        /* Footer */
        .footer {
            margin-top: 32px;
            font-size: 14px;
            color: rgba(0, 0, 0, 0.4);
            font-weight: 400;
        }

        .footer span {
            color: #4f46e5;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        .footer span::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 1px;
            background: currentColor;
            opacity: 0.3;
        }

        body.dark-mode .footer {
            color: rgba(255, 255, 255, 0.4);
        }

        body.dark-mode .footer span {
            color: #818cf8;
        }

        /* Theme Toggle - Elegant */
        .theme-toggle {
            position: fixed;
            top: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.3, 1.1);
            z-index: 100;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .theme-toggle:hover {
            transform: rotate(180deg) scale(1.1);
            border-color: rgba(79, 70, 229, 0.6);
            background: rgba(255, 255, 255, 0.3);
        }

        body.dark-mode .theme-toggle {
            background: rgba(10, 10, 12, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .theme-toggle svg {
            width: 22px;
            height: 22px;
            fill: #4f46e5;
            transition: all 0.3s ease;
        }

        body.dark-mode .theme-toggle svg {
            fill: #818cf8;
        }

        /* Animations */
        @keyframes floatIn {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .float-in {
            animation: floatIn 0.8s cubic-bezier(0.2, 0.9, 0.3, 1.1) forwards;
        }

        .delay-1 {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .delay-2 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .delay-3 {
            animation-delay: 0.3s;
            opacity: 0;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .glass-card {
                padding: 40px 24px;
                border-radius: 36px;
            }

            h1 {
                font-size: 32px;
            }

            .subtitle {
                font-size: 13px;
                letter-spacing: 2px;
            }

            .btn {
                padding: 14px 44px;
                font-size: 16px;
            }

            .logo {
                width: 70px;
                height: 70px;
                border-radius: 20px;
            }

            .logo svg {
                width: 35px;
                height: 35px;
            }

            .theme-toggle {
                top: 20px;
                right: 20px;
                width: 42px;
                height: 42px;
            }
        }
    </style>
</head>
<body>
    <!-- Liquid Background -->
    <div class="liquid-bg">
        <div class="liquid-blob blob-1"></div>
        <div class="liquid-blob blob-2"></div>
        <div class="liquid-blob blob-3"></div>
    </div>

    <!-- Theme Toggle -->
    <div class="theme-toggle" onclick="toggleTheme()">
        <svg viewBox="0 0 24 24">
            <path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
    </div>

    <div class="container">
        <div class="glass-card float-in">
            <!-- Logo -->
            <div class="logo-wrapper float-in delay-1">
                <div class="logo">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h4m-4 4h4m2-8h2m-2 4h2"/>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h1 class="float-in delay-1">Legal Management System</h1>

            <!-- Subtitle with decorative lines -->
            <div class="subtitle float-in delay-2">GUNBUSTER NICKEL INDUSTRY</div>

            <!-- Login Button -->
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn float-in delay-3">Go to Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn float-in delay-3">Log In</a>
                @endauth
            @endif

            <!-- Footer -->
            <div class="footer float-in delay-3">
                Powered by <span>IT Division</span>
            </div>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const body = document.body;
            const toggleIcon = document.querySelector('.theme-toggle svg');
            
            // Toggle dark mode with smooth transition
            body.classList.toggle('dark-mode');
            
            // Update icon with animation
            if (body.classList.contains('dark-mode')) {
                toggleIcon.innerHTML = '<path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
            } else {
                toggleIcon.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
            }
            
            // Save preference
            localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedDarkMode = localStorage.getItem('darkMode') === 'true';
            const toggleIcon = document.querySelector('.theme-toggle svg');
            
            if (savedDarkMode) {
                document.body.classList.add('dark-mode');
                toggleIcon.innerHTML = '<path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
            }
        });
    </script>
</body>
</html>