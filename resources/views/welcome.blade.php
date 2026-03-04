<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Legal Management System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <style>
            /* Reset & Base */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
                min-height: 100vh;
                background: linear-gradient(135deg, #f5f7fa 0%, #e4e7eb 100%);
                color: #333;
                line-height: 1.5;
                overflow-x: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            body.dark-mode {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                color: #e2e8f0;
            }

            /* Animated Background */
            .bg-animation {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                overflow: hidden;
            }

            .floating-shape {
                position: absolute;
                border-radius: 50%;
                filter: blur(40px);
                opacity: 0.3;
                animation: float 20s infinite linear;
            }

            .shape-1 {
                width: 300px;
                height: 300px;
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                top: 10%;
                left: 10%;
                animation-delay: 0s;
            }

            .shape-2 {
                width: 400px;
                height: 400px;
                background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
                bottom: 10%;
                right: 10%;
                animation-delay: -5s;
                animation-duration: 25s;
            }

            .shape-3 {
                width: 250px;
                height: 250px;
                background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
                top: 50%;
                left: 80%;
                animation-delay: -10s;
                animation-duration: 30s;
            }

            .shape-4 {
                width: 350px;
                height: 350px;
                background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
                bottom: 30%;
                left: 5%;
                animation-delay: -15s;
                animation-duration: 35s;
            }

            @keyframes float {
                0% {
                    transform: translate(0, 0) rotate(0deg);
                }
                25% {
                    transform: translate(100px, 100px) rotate(90deg);
                }
                50% {
                    transform: translate(0, 200px) rotate(180deg);
                }
                75% {
                    transform: translate(-100px, 100px) rotate(270deg);
                }
                100% {
                    transform: translate(0, 0) rotate(360deg);
                }
            }

            /* Main Container */
            .main-container {
                width: 100%;
                max-width: 800px;
                padding: 20px;
                position: relative;
                z-index: 2;
            }

            /* Glass Card */
            .glass-card {
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: 30px;
                padding: 60px 40px;
                box-shadow: 
                    20px 20px 60px rgba(0, 0, 0, 0.1),
                    -20px -20px 60px rgba(255, 255, 255, 0.7),
                    inset 0 1px 0 rgba(255, 255, 255, 0.4),
                    inset 0 -1px 0 rgba(0, 0, 0, 0.1);
                text-align: center;
            }

            .glass-card.dark {
                background: rgba(30, 41, 59, 0.25);
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 
                    20px 20px 60px rgba(0, 0, 0, 0.3),
                    -20px -20px 60px rgba(255, 255, 255, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.08),
                    inset 0 -1px 0 rgba(0, 0, 0, 0.3);
            }

            /* Logo */
            .logo-wrapper {
                display: flex;
                justify-content: center;
                margin-bottom: 30px;
            }

            .logo-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                overflow: hidden;
                animation: pulse 2s infinite;
            }

            .logo-icon::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
            }

            .logo-icon.dark {
                background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
                box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
            }

            .logo-icon svg {
                width: 40px;
                height: 40px;
                fill: white;
            }

            /* Main Title - PERBAIKAN UTAMA */
            .main-title {
                font-size: 64px;
                font-weight: 800;
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
                -webkit-text-fill-color: transparent;
                text-shadow: 0 2px 10px rgba(79, 70, 229, 0.2);
                margin-bottom: 16px;
                line-height: 1.2;
            }

            .main-title.dark-mode-text {
                background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
                -webkit-text-fill-color: transparent;
                text-shadow: 0 2px 20px rgba(139, 92, 246, 0.4);
            }

            /* Subtitle */
            .subtitle {
                font-size: 20px;
                color: #64748b;
                margin-bottom: 50px;
                font-weight: 400;
                letter-spacing: 2px;
                text-transform: uppercase;
            }

            .subtitle.dark-mode-text {
                color: #94a3b8;
            }

            /* Divider */
            .divider {
                width: 100px;
                height: 2px;
                background: linear-gradient(90deg, transparent, #4f46e5, transparent);
                margin: 0 auto 50px;
            }

            .divider.dark-mode-bg {
                background: linear-gradient(90deg, transparent, #8b5cf6, transparent);
            }

            /* Login Button */
            .login-button {
                display: inline-block;
                padding: 18px 60px;
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                border: none;
                border-radius: 50px;
                color: white;
                font-size: 18px;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);
                letter-spacing: 1px;
            }

            .login-button:hover {
                transform: translateY(-3px) scale(1.05);
                box-shadow: 0 20px 40px rgba(79, 70, 229, 0.4);
            }

            .login-button:active {
                transform: translateY(-1px) scale(1.02);
            }

            .login-button.dark-mode-bg {
                background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
                box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
            }

            .login-button.dark-mode-bg:hover {
                box-shadow: 0 20px 50px rgba(139, 92, 246, 0.5);
            }

            /* Company Name */
            .company-name {
                margin-top: 40px;
                font-size: 16px;
                color: #64748b;
                font-weight: 400;
                font-style: italic;
            }

            .company-name.dark-mode-text {
                color: #94a3b8;
            }

            .company-name span {
                color: #4f46e5;
                font-weight: 600;
                font-style: normal;
            }

            .company-name.dark-mode-text span {
                color: #8b5cf6;
            }

            /* Theme Toggle */
            .theme-toggle {
                position: fixed;
                top: 24px;
                right: 24px;
                width: 50px;
                height: 50px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(15px);
                -webkit-backdrop-filter: blur(15px);
                border: 1px solid rgba(203, 213, 225, 0.3);
                border-radius: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                z-index: 100;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            }

            .theme-toggle:hover {
                transform: scale(1.1) rotate(15deg);
            }

            .theme-toggle.dark-mode-bg {
                background: rgba(30, 41, 59, 0.8);
                border: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            }

            /* Animations */
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fade-in {
                animation: fadeInUp 0.8s ease-out forwards;
            }

            .delay-1 {
                animation-delay: 0.2s;
                opacity: 0;
            }

            .delay-2 {
                animation-delay: 0.4s;
                opacity: 0;
            }

            .delay-3 {
                animation-delay: 0.6s;
                opacity: 0;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .main-title {
                    font-size: 42px;
                }
                
                .subtitle {
                    font-size: 16px;
                }
                
                .glass-card {
                    padding: 40px 20px;
                }
                
                .login-button {
                    padding: 16px 40px;
                    font-size: 16px;
                }
            }

            @media (max-width: 480px) {
                .main-title {
                    font-size: 32px;
                }
                
                .subtitle {
                    font-size: 14px;
                }
                
                .logo-icon {
                    width: 60px;
                    height: 60px;
                }
                
                .logo-icon svg {
                    width: 30px;
                    height: 30px;
                }
            }
        </style>
    </head>
    <body>
        <!-- Animated Background -->
        <div class="bg-animation">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
            <div class="floating-shape shape-4"></div>
        </div>

        <!-- Theme Toggle -->
        <div class="theme-toggle" onclick="toggleDarkMode()">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </div>

        <div class="main-container">
            <div class="glass-card animate-fade-in" id="glassCard">
                <!-- Logo -->
                <div class="logo-wrapper delay-1 animate-fade-in">
                    <div class="logo-icon" id="logoIcon">
                        <svg viewBox="0 0 24 24" fill="white">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h4m-4 4h4m2-8h2m-2 4h2"/>
                        </svg>
                    </div>
                </div>

                <!-- Main Title -->
                <h1 class="main-title delay-1 animate-fade-in" id="mainTitle">
                    Legal Management System
                </h1>

                <!-- Subtitle -->
                <div class="subtitle delay-2 animate-fade-in" id="subtitle">
                    GUNBUSTER NICKEL INDUSTRY
                </div>

                <!-- Divider -->
                <div class="divider delay-2 animate-fade-in" id="divider"></div>

                <!-- Login Button -->
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="login-button delay-3 animate-fade-in" id="loginButton">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="login-button delay-3 animate-fade-in" id="loginButton">
                            Log In
                        </a>
                    @endauth
                @endif

                <!-- Company Name -->
                <div class="company-name delay-3 animate-fade-in" id="companyName">
                    Powered by <span>Gunbuster Nickel Industry</span>
                </div>
            </div>
        </div>

        <script>
            // Dark Mode Toggle - PERBAIKAN
            function toggleDarkMode() {
                const body = document.body;
                const themeToggle = document.querySelector('.theme-toggle');
                
                // Toggle dark mode pada body
                body.classList.toggle('dark-mode');
                
                // Toggle class untuk theme toggle
                themeToggle.classList.toggle('dark-mode-bg');
                
                // Element yang perlu diubah
                const glassCard = document.getElementById('glassCard');
                const logoIcon = document.getElementById('logoIcon');
                const mainTitle = document.getElementById('mainTitle');
                const subtitle = document.getElementById('subtitle');
                const divider = document.getElementById('divider');
                const loginButton = document.getElementById('loginButton');
                const companyName = document.getElementById('companyName');
                
                // Toggle classes untuk setiap element
                glassCard.classList.toggle('dark');
                
                if (logoIcon) logoIcon.classList.toggle('dark');
                if (mainTitle) mainTitle.classList.toggle('dark-mode-text');
                if (subtitle) subtitle.classList.toggle('dark-mode-text');
                if (divider) divider.classList.toggle('dark-mode-bg');
                if (loginButton) loginButton.classList.toggle('dark-mode-bg');
                if (companyName) companyName.classList.toggle('dark-mode-text');
                
                // Toggle moon/sun icon
                const svg = themeToggle.querySelector('svg');
                if (body.classList.contains('dark-mode')) {
                    svg.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
                    themeToggle.style.transform = 'rotate(180deg)';
                    setTimeout(() => {
                        themeToggle.style.transform = '';
                    }, 300);
                } else {
                    svg.innerHTML = '<path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
                    themeToggle.style.transform = 'rotate(180deg)';
                    setTimeout(() => {
                        themeToggle.style.transform = '';
                    }, 300);
                }
                
                // Store preference in localStorage
                localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
            }

            // Check for saved dark mode preference
            document.addEventListener('DOMContentLoaded', function() {
                const savedDarkMode = localStorage.getItem('darkMode') === 'true';
                const themeToggle = document.querySelector('.theme-toggle');
                
                if (savedDarkMode) {
                    // Panggil fungsi toggleDarkMode untuk mengaplikasikan semua perubahan
                    toggleDarkMode();
                    
                    // Set icon yang sesuai
                    const svg = themeToggle.querySelector('svg');
                    svg.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
                }
                
                // Add parallax effect to floating shapes
                document.addEventListener('mousemove', function(e) {
                    const shapes = document.querySelectorAll('.floating-shape');
                    const mouseX = e.clientX / window.innerWidth;
                    const mouseY = e.clientY / window.innerHeight;
                    
                    shapes.forEach((shape, index) => {
                        const speed = 0.3 + (index * 0.1);
                        const x = (mouseX - 0.5) * 50 * speed;
                        const y = (mouseY - 0.5) * 50 * speed;
                        
                        shape.style.transform = `translate(${x}px, ${y}px) rotate(${index * 45}deg)`;
                    });
                });
            });

            // Ripple effect on button click
            const loginButton = document.getElementById('loginButton');
            if (loginButton) {
                loginButton.addEventListener('click', function(e) {
                    // Remove any existing ripples
                    const existingRipples = this.querySelectorAll('.ripple');
                    existingRipples.forEach(ripple => ripple.remove());
                    
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.5);
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        pointer-events: none;
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            }

            // Add ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                
                .login-button {
                    position: relative;
                    overflow: hidden;
                }
            `;
            document.head.appendChild(style);
        </script>
    </body>
</html>