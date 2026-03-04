<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - Legal Management System</title>

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

            /* Main Layout */
            .main-container {
                min-height: 100vh;
                display: grid;
                grid-template-columns: 1fr;
                padding: 20px;
                position: relative;
            }

            @media (min-width: 1024px) {
                .main-container {
                    grid-template-columns: 1.2fr 1fr;
                    gap: 40px;
                }
            }

            /* Left Hero Section */
            .hero-section {
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 40px;
                position: relative;
            }

            .hero-content {
                max-width: 600px;
                position: relative;
                z-index: 2;
            }

            /* Glass Card with Neumorphism */
            .glass-card {
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: 30px;
                padding: 40px;
                box-shadow: 
                    20px 20px 60px rgba(0, 0, 0, 0.1),
                    -20px -20px 60px rgba(255, 255, 255, 0.7),
                    inset 0 1px 0 rgba(255, 255, 255, 0.4),
                    inset 0 -1px 0 rgba(0, 0, 0, 0.1);
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

            /* Logo & Branding */
            .brand-logo {
                display: flex;
                align-items: center;
                gap: 15px;
                margin-bottom: 40px;
            }

            .logo-icon-wrapper {
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                border-radius: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                overflow: hidden;
                animation: pulse 2s infinite;
            }

            .logo-icon-wrapper::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
            }

            .logo-icon-wrapper.dark {
                background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
                box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
            }

            .brand-name {
                font-size: 32px;
                font-weight: 800;
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                text-shadow: 0 2px 10px rgba(79, 70, 229, 0.2);
            }

            .brand-name.dark-mode-text {
                background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                text-shadow: 0 2px 20px rgba(139, 92, 246, 0.4);
            }

            /* Hero Text */
            .hero-title {
                font-size: 48px;
                font-weight: 800;
                line-height: 1.2;
                margin-bottom: 20px;
                color: #1e293b;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .hero-title.dark-mode-text {
                color: #f8fafc;
                text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            }

            .hero-title span {
                color: #4f46e5;
            }

            body.dark-mode .hero-title span {
                background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .hero-subtitle {
                font-size: 20px;
                color: #64748b;
                margin-bottom: 40px;
                line-height: 1.6;
            }

            .hero-subtitle.dark-mode-text {
                color: #94a3b8;
            }

            /* Features Grid */
            .features-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin-top: 40px;
            }

            .feature-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 15px;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border-radius: 15px;
                transition: all 0.3s ease;
            }

            .feature-item:hover {
                transform: translateY(-5px);
                background: rgba(255, 255, 255, 0.2);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }

            .feature-item.dark-mode-bg {
                background: rgba(255, 255, 255, 0.05);
            }

            .feature-item.dark-mode-bg:hover {
                background: rgba(255, 255, 255, 0.1);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            }

            .feature-icon {
                width: 40px;
                height: 40px;
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .feature-icon.dark-mode-bg {
                background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
            }

            .feature-text h4 {
                font-size: 14px;
                font-weight: 600;
                margin-bottom: 4px;
                color: #1e293b;
            }

            .feature-text.dark-mode-text h4 {
                color: #f1f5f9;
            }

            .feature-text p {
                font-size: 12px;
                color: #64748b;
            }

            .feature-text.dark-mode-text p {
                color: #94a3b8;
            }

            /* Right Login Section */
            .login-section {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 40px;
            }

            .login-card {
                width: 100%;
                max-width: 480px;
                position: relative;
                z-index: 2;
            }

            .login-header {
                text-align: center;
                margin-bottom: 40px;
            }

            .login-title {
                font-size: 32px;
                font-weight: 700;
                margin-bottom: 10px;
                color: #1e293b;
            }

            .login-title.dark-mode-text {
                color: #f8fafc;
            }

            .login-subtitle {
                font-size: 16px;
                color: #64748b;
            }

            .login-subtitle.dark-mode-text {
                color: #94a3b8;
            }

            /* Form Elements */
            .form-group {
                margin-bottom: 25px;
            }

            .form-label {
                display: block;
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 8px;
                color: #475569;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .form-label.dark-mode-text {
                color: #cbd5e1;
            }

            .glass-input {
                width: 100%;
                padding: 16px 20px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border: 2px solid rgba(203, 213, 225, 0.2);
                border-radius: 15px;
                font-size: 16px;
                color: #1e293b;
                transition: all 0.3s ease;
                outline: none;
            }

            .glass-input:focus {
                border-color: #4f46e5;
                box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
                background: rgba(255, 255, 255, 1);
                transform: translateY(-2px);
            }

            .glass-input.dark-mode-bg {
                background: rgba(255, 255, 255, 0.05);
                border: 2px solid rgba(148, 163, 184, 0.2);
                color: #f1f5f9;
            }

            .glass-input.dark-mode-bg:focus {
                border-color: #8b5cf6;
                box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2);
                background: rgba(255, 255, 255, 0.1);
            }

            /* Checkbox */
            .checkbox-container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin: 25px 0 30px;
            }

            .checkbox-wrapper {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .custom-checkbox {
                width: 20px;
                height: 20px;
                border: 2px solid #cbd5e1;
                border-radius: 6px;
                cursor: pointer;
                position: relative;
                transition: all 0.2s ease;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(5px);
            }

            .custom-checkbox.checked {
                background: #4f46e5;
                border-color: #4f46e5;
            }

            .custom-checkbox.checked::after {
                content: '';
                position: absolute;
                top: 2px;
                left: 6px;
                width: 5px;
                height: 10px;
                border: solid white;
                border-width: 0 2px 2px 0;
                transform: rotate(45deg);
            }

            .custom-checkbox.dark-mode-bg {
                border-color: #475569;
                background: rgba(255, 255, 255, 0.05);
            }

            .custom-checkbox.dark-mode-bg.checked {
                background: #8b5cf6;
                border-color: #8b5cf6;
            }

            .checkbox-label {
                font-size: 14px;
                color: #475569;
                cursor: pointer;
                font-weight: 500;
            }

            .checkbox-label.dark-mode-text {
                color: #cbd5e1;
            }

            /* Login Button */
            .login-button {
                width: 100%;
                padding: 18px;
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                border: none;
                border-radius: 15px;
                color: white;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);
            }

            .login-button:hover {
                transform: translateY(-3px);
                box-shadow: 0 15px 40px rgba(79, 70, 229, 0.4);
            }

            .login-button:active {
                transform: translateY(-1px);
            }

            .login-button.dark-mode-bg {
                background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
                box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
            }

            .login-button.dark-mode-bg:hover {
                box-shadow: 0 15px 40px rgba(139, 92, 246, 0.5);
            }

            /* Links */
            .links-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 30px;
                padding-top: 25px;
                border-top: 1px solid rgba(203, 213, 225, 0.2);
            }

            .links-container.dark-mode-bg {
                border-top-color: rgba(148, 163, 184, 0.2);
            }

            .forgot-link {
                font-size: 14px;
                color: #4f46e5;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.2s ease;
                position: relative;
            }

            .forgot-link::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                width: 0;
                height: 2px;
                background: #4f46e5;
                transition: width 0.3s ease;
            }

            .forgot-link:hover::after {
                width: 100%;
            }

            .forgot-link.dark-mode-text {
                color: #a78bfa;
            }

            .forgot-link.dark-mode-text::after {
                background: #a78bfa;
            }

            .register-link {
                font-size: 14px;
                color: #64748b;
                font-weight: 500;
            }

            .register-link.dark-mode-text {
                color: #94a3b8;
            }

            .register-link a {
                color: #4f46e5;
                text-decoration: none;
                font-weight: 600;
                margin-left: 5px;
                position: relative;
            }

            .register-link a::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                width: 0;
                height: 2px;
                background: #4f46e5;
                transition: width 0.3s ease;
            }

            .register-link a:hover::after {
                width: 100%;
            }

            .register-link.dark-mode-text a {
                color: #a78bfa;
            }

            .register-link.dark-mode-text a::after {
                background: #a78bfa;
            }

            /* Error Messages */
            .error-message {
                font-size: 13px;
                color: #dc2626;
                margin-top: 8px;
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 10px;
                background: rgba(220, 38, 38, 0.1);
                border-radius: 10px;
                border-left: 3px solid #dc2626;
            }

            .error-message.dark-mode-text {
                color: #f87171;
                background: rgba(220, 38, 38, 0.15);
                border-left-color: #f87171;
            }

            /* Status Messages */
            .status-message {
                padding: 15px;
                background: rgba(34, 197, 94, 0.1);
                border: 1px solid rgba(34, 197, 94, 0.2);
                border-radius: 15px;
                margin-bottom: 25px;
                font-size: 14px;
                color: #166534;
                border-left: 4px solid #16a34a;
            }

            .status-message.dark-mode-text {
                background: rgba(34, 197, 94, 0.15);
                border-color: rgba(34, 197, 94, 0.3);
                color: #86efac;
                border-left-color: #22c55e;
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

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-slide {
                animation: slideIn 0.6s ease-out forwards;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .hero-title {
                    font-size: 36px;
                }
                
                .features-grid {
                    grid-template-columns: 1fr;
                }
                
                .main-container {
                    padding: 10px;
                }
                
                .hero-section, .login-section {
                    padding: 20px;
                }
                
                .glass-card {
                    padding: 30px;
                }
            }

            @media (max-width: 1024px) {
                .hero-section {
                    order: 2;
                }
                
                .login-section {
                    order: 1;
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
            <!-- Left Hero Section -->
            <section class="hero-section animate-slide" style="animation-delay: 0.1s;">
                <div class="glass-card hero-content" id="glassCard">
                    <!-- Brand -->
                    <div class="brand-logo">
                        <div class="logo-icon-wrapper" id="logoIcon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h4m-4 4h4m2-8h2m-2 4h2"/>
                            </svg>
                        </div>
                        <h1 class="brand-name" id="brandName">Legal Management System</h1>
                    </div>

                    <!-- Hero Text -->
                    <h2 class="hero-title" id="heroTitle">
                        Streamline Your<br>
                        <span>Document Management</span>
                    </h2>
                    
                    <p class="hero-subtitle" id="heroSubtitle">
                        Experience the next generation of contract workflow automation. 
                        Sign, manage, and track contracts effortlessly with our 
                        intelligent platform.
                    </p>

                    <!-- Features Grid -->
                    <div class="features-grid">
                        <div class="feature-item" id="feature1">
                            <div class="feature-icon" id="featureIcon1">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div class="feature-text" id="featureText1">
                                <h4>Lightning Fast</h4>
                                <p>Process contracts 10x faster</p>
                            </div>
                        </div>

                        <div class="feature-item" id="feature2">
                            <div class="feature-icon" id="featureIcon2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div class="feature-text" id="featureText2">
                                <h4>Bank-Level Security</h4>
                                <p>256-bit encryption</p>
                            </div>
                        </div>

                        <div class="feature-item" id="feature3">
                            <div class="feature-icon" id="featureIcon3">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="feature-text" id="featureText3">
                                <h4>Smart Tracking</h4>
                                <p>Real-time notifications</p>
                            </div>
                        </div>

                        <div class="feature-item" id="feature4">
                            <div class="feature-icon" id="featureIcon4">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="feature-text" id="featureText4">
                                <h4>Team Collaboration</h4>
                                <p>Work together seamlessly</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right Login Section -->
            <section class="login-section animate-slide" style="animation-delay: 0.2s;">
                <div class="glass-card login-card" id="loginGlassCard">
                    <!-- Login Header -->
                    <div class="login-header">
                        <h2 class="login-title" id="loginTitle">Welcome Back</h2>
                        <p class="login-subtitle" id="loginSubtitle">Sign in to your HRMS account</p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="status-message" id="statusMessage">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Login Form pemisah HRMS dan Local--> 
                    <form method="POST" action="{{ route('hrms.login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="form-group">
                            <label class="form-label" for="email" id="emailLabel">Email Address</label>
                            <input 
                                id="email" 
                                class="glass-input" 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus 
                                autocomplete="username"
                                placeholder="name@company.com"
                            >
                            @if ($errors->has('email'))
                                <div class="error-message" id="emailError">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label" for="password" id="passwordLabel">Password</label>
                            <input 
                                id="password" 
                                class="glass-input"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            >
                            @if ($errors->has('password'))
                                <div class="error-message" id="passwordError">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $errors->first('password') }}
                                </div>
                            @endif
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="checkbox-container">
                            <div class="checkbox-wrapper">
                                <div class="custom-checkbox" onclick="toggleCheckbox(this)" id="customCheckbox"></div>
                                <label class="checkbox-label" for="remember_me" onclick="toggleCheckbox(this.previousElementSibling)" id="checkboxLabel">
                                    Remember me
                                </label>
                                <input id="remember_me" type="checkbox" name="remember" class="hidden-checkbox" style="display: none;">
                            </div>
                            
                            @if (Route::has('password.request'))
                                <a class="forgot-link" href="{{ route('password.request') }}" id="forgotLink">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="login-button" id="loginButton">
                            Sign In
                        </button>
                    </form>

                    <!-- Register Link -->
                    @if (Route::has('register'))
                        <div class="links-container" id="linksContainer">
                            
                            <div class="register-link" id="backLink">
                                <a href="{{ url('/') }}">← Back to Home</a>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
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
                
                // Dapatkan semua elemen yang perlu diubah
                const glassCards = document.querySelectorAll('.glass-card');
                const logoIcons = document.querySelectorAll('.logo-icon-wrapper');
                const brandNames = document.querySelectorAll('.brand-name');
                const heroTitles = document.querySelectorAll('.hero-title');
                const heroSubtitles = document.querySelectorAll('.hero-subtitle');
                const featureItems = document.querySelectorAll('.feature-item');
                const featureIcons = document.querySelectorAll('.feature-icon');
                const featureTexts = document.querySelectorAll('.feature-text');
                const loginTitles = document.querySelectorAll('.login-title');
                const loginSubtitles = document.querySelectorAll('.login-subtitle');
                const formLabels = document.querySelectorAll('.form-label');
                const glassInputs = document.querySelectorAll('.glass-input');
                const customCheckboxes = document.querySelectorAll('.custom-checkbox');
                const checkboxLabels = document.querySelectorAll('.checkbox-label');
                const loginButtons = document.querySelectorAll('.login-button');
                const linksContainers = document.querySelectorAll('.links-container');
                const forgotLinks = document.querySelectorAll('.forgot-link');
                const registerLinks = document.querySelectorAll('.register-link');
                const statusMessages = document.querySelectorAll('.status-message');
                const errorMessages = document.querySelectorAll('.error-message');
                
                // Toggle classes untuk setiap grup elemen
                glassCards.forEach(el => el.classList.toggle('dark'));
                logoIcons.forEach(el => el.classList.toggle('dark-mode-bg'));
                brandNames.forEach(el => el.classList.toggle('dark-mode-text'));
                heroTitles.forEach(el => el.classList.toggle('dark-mode-text'));
                heroSubtitles.forEach(el => el.classList.toggle('dark-mode-text'));
                featureItems.forEach(el => el.classList.toggle('dark-mode-bg'));
                featureIcons.forEach(el => el.classList.toggle('dark-mode-bg'));
                featureTexts.forEach(el => el.classList.toggle('dark-mode-text'));
                loginTitles.forEach(el => el.classList.toggle('dark-mode-text'));
                loginSubtitles.forEach(el => el.classList.toggle('dark-mode-text'));
                formLabels.forEach(el => el.classList.toggle('dark-mode-text'));
                glassInputs.forEach(el => el.classList.toggle('dark-mode-bg'));
                customCheckboxes.forEach(el => el.classList.toggle('dark-mode-bg'));
                checkboxLabels.forEach(el => el.classList.toggle('dark-mode-text'));
                loginButtons.forEach(el => el.classList.toggle('dark-mode-bg'));
                linksContainers.forEach(el => el.classList.toggle('dark-mode-bg'));
                forgotLinks.forEach(el => el.classList.toggle('dark-mode-text'));
                registerLinks.forEach(el => el.classList.toggle('dark-mode-text'));
                statusMessages.forEach(el => el.classList.toggle('dark-mode-text'));
                errorMessages.forEach(el => el.classList.toggle('dark-mode-text'));
                
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

            // Checkbox Toggle
            function toggleCheckbox(element) {
                const checkbox = element;
                const hiddenCheckbox = document.getElementById('remember_me');
                
                checkbox.classList.toggle('checked');
                hiddenCheckbox.checked = !hiddenCheckbox.checked;
            }

            // Input Focus Effects
            document.querySelectorAll('.glass-input').forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.transform = '';
                });
            });

            // Button Effects
            const loginButton = document.querySelector('.login-button');
            if (loginButton) {
                loginButton.addEventListener('mousedown', function() {
                    this.style.transform = 'translateY(0)';
                });
                
                loginButton.addEventListener('mouseup', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                
                loginButton.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });

                // Ripple effect on button click
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
                
                .hidden-checkbox {
                    display: none;
                }
            `;
            document.head.appendChild(style);

            // Check for saved dark mode preference
            document.addEventListener('DOMContentLoaded', function() {
                const savedDarkMode = localStorage.getItem('darkMode') === 'true';
                const themeToggle = document.querySelector('.theme-toggle');
                
                if (savedDarkMode) {
                    // Panggil fungsi toggleDarkMode untuk mengaplikasikan semua perubahan
                    toggleDarkMode();
                    
                    // Set sun icon for dark mode
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
        </script>
    </body>
</html>