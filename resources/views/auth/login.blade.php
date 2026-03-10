<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Legal Management System</title>
    
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
            padding: 20px;
        }

        body.dark-mode {
            background: #0a0a0c;
            color: #f5f5f7;
        }

        /* Liquid Background */
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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            top: -150px;
            right: -100px;
            animation-delay: 0s;
        }

        .blob-2 {
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            bottom: -200px;
            left: -150px;
            animation-delay: -5s;
            animation-duration: 30s;
        }

        .blob-3 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -10s;
            animation-duration: 35s;
            filter: blur(80px);
        }

        body.dark-mode .blob-1 {
            background: linear-gradient(135deg, #4c1d95 0%, #5b21b6 100%);
            opacity: 0.3;
        }

        body.dark-mode .blob-2 {
            background: linear-gradient(135deg, #155e75 0%, #164e63 100%);
            opacity: 0.3;
        }

        body.dark-mode .blob-3 {
            background: linear-gradient(135deg, #831843 0%, #4a1d96 100%);
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
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        /* Premium Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-radius: 48px;
            padding: 60px;
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
            0% { opacity: 0.8; }
            50% { opacity: 1; }
            100% { opacity: 0.8; }
        }

        @keyframes shimmer {
            0% { left: -50%; }
            100% { left: 150%; }
        }

        /* Content Layout - Integrated */
        .integrated-content {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 60px;
            align-items: start;
        }

        /* Left Side - Hero */
        .hero-section {
            padding-right: 40px;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        body.dark-mode .hero-section {
            border-right-color: rgba(255, 255, 255, 0.05);
        }

        /* Brand */
        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 32px;
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 25px -8px rgba(79, 70, 229, 0.4);
            transition: all 0.3s ease;
        }

        .brand-logo:hover {
            transform: scale(1.05) rotate(2deg);
        }

        body.dark-mode .brand-logo {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .brand-name {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #1e1e2f 0%, #2d2d44 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
        }

        body.dark-mode .brand-name {
            background: linear-gradient(135deg, #ffffff 0%, #e0e0ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        /* Hero Title */
        .hero-title {
            font-size: 42px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 16px;
            color: #1e1e2f;
        }

        body.dark-mode .hero-title {
            color: #ffffff;
        }

        .hero-title span {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        body.dark-mode .hero-title span {
            background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 16px;
            color: rgba(0, 0, 0, 0.5);
            margin-bottom: 32px;
            line-height: 1.6;
            max-width: 500px;
        }

        body.dark-mode .hero-subtitle {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Features */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-item:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        body.dark-mode .feature-item {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        body.dark-mode .feature-icon {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .feature-text h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
            color: #1e1e2f;
        }

        body.dark-mode .feature-text h4 {
            color: #ffffff;
        }

        .feature-text p {
            font-size: 12px;
            color: rgba(0, 0, 0, 0.4);
        }

        body.dark-mode .feature-text p {
            color: rgba(255, 255, 255, 0.4);
        }

        /* Right Side - Login */
        .login-section {
            padding-left: 20px;
        }

        .login-header {
            margin-bottom: 32px;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1e1e2f;
        }

        body.dark-mode .login-title {
            color: #ffffff;
        }

        .login-subtitle {
            font-size: 15px;
            color: rgba(0, 0, 0, 0.4);
            font-weight: 400;
        }

        body.dark-mode .login-subtitle {
            color: rgba(255, 255, 255, 0.4);
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            color: rgba(0, 0, 0, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        body.dark-mode .form-label {
            color: rgba(255, 255, 255, 0.6);
        }

        .glass-input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            font-size: 15px;
            color: #1e1e2f;
            transition: all 0.3s ease;
            outline: none;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        body.dark-mode .glass-input {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        body.dark-mode .glass-input:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.15);
            background: rgba(255, 255, 255, 0.05);
        }

        .glass-input::placeholder {
            color: rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        /* Checkbox & Links */
        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 24px 0 32px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(79, 70, 229, 0.3);
            border-radius: 6px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.1);
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

        body.dark-mode .custom-checkbox {
            border-color: rgba(129, 140, 248, 0.3);
        }

        body.dark-mode .custom-checkbox.checked {
            background: #818cf8;
            border-color: #818cf8;
        }

        .checkbox-label {
            font-size: 14px;
            color: rgba(0, 0, 0, 0.5);
            cursor: pointer;
        }

        body.dark-mode .checkbox-label {
            color: rgba(255, 255, 255, 0.5);
        }

        .forgot-link {
            font-size: 14px;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            position: relative;
        }

        .forgot-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background: #4f46e5;
            transition: width 0.3s ease;
        }

        .forgot-link:hover::after {
            width: 100%;
        }

        body.dark-mode .forgot-link {
            color: #818cf8;
        }

        body.dark-mode .forgot-link::after {
            background: #818cf8;
        }

        /* Premium Button */
        .login-button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            border-radius: 30px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.3, 1.1);
            box-shadow: 0 15px 25px -8px rgba(79, 70, 229, 0.4);
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .login-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .login-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 20px 30px -8px rgba(79, 70, 229, 0.5);
        }

        .login-button:hover::before {
            left: 100%;
        }

        body.dark-mode .login-button {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            box-shadow: 0 15px 25px -8px rgba(99, 102, 241, 0.3);
        }

        /* Footer Links */
        .login-footer {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        body.dark-mode .login-footer {
            border-top-color: rgba(255, 255, 255, 0.1);
        }

        .back-link {
            font-size: 14px;
        }

        .back-link a {
            color: rgba(0, 0, 0, 0.4);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .back-link a:hover {
            color: #4f46e5;
            transform: translateX(-3px);
        }

        body.dark-mode .back-link a {
            color: rgba(255, 255, 255, 0.4);
        }

        body.dark-mode .back-link a:hover {
            color: #818cf8;
        }

        .register-link {
            font-size: 14px;
            color: rgba(0, 0, 0, 0.4);
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
            height: 1px;
            background: #4f46e5;
            transition: width 0.3s ease;
        }

        .register-link a:hover::after {
            width: 100%;
        }

        body.dark-mode .register-link {
            color: rgba(255, 255, 255, 0.4);
        }

        body.dark-mode .register-link a {
            color: #818cf8;
        }

        body.dark-mode .register-link a::after {
            background: #818cf8;
        }

        /* Messages */
        .status-message {
            padding: 16px;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 20px;
            margin-bottom: 30px;
            font-size: 14px;
            color: #16a34a;
            backdrop-filter: blur(10px);
        }

        body.dark-mode .status-message {
            background: rgba(34, 197, 94, 0.05);
            border-color: rgba(34, 197, 94, 0.1);
            color: #4ade80;
        }

        .error-message {
            font-size: 13px;
            color: #dc2626;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: rgba(220, 38, 38, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        body.dark-mode .error-message {
            background: rgba(220, 38, 38, 0.05);
            color: #f87171;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
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
        }

        body.dark-mode .theme-toggle {
            background: rgba(10, 10, 12, 0.4);
            border-color: rgba(255, 255, 255, 0.1);
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

        .delay-1 { animation-delay: 0.1s; opacity: 0; }
        .delay-2 { animation-delay: 0.2s; opacity: 0; }
        .delay-3 { animation-delay: 0.3s; opacity: 0; }

        /* Responsive */
        @media (max-width: 1024px) {
            .glass-card {
                padding: 40px;
            }

            .integrated-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-section {
                padding-right: 0;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
                padding-bottom: 40px;
            }

            body.dark-mode .hero-section {
                border-bottom-color: rgba(255, 255, 255, 0.05);
            }

            .login-section {
                padding-left: 0;
            }

            .hero-title {
                font-size: 36px;
            }
        }

        @media (max-width: 640px) {
            .glass-card {
                padding: 32px 24px;
                border-radius: 36px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .brand-name {
                font-size: 20px;
            }

            .hero-title {
                font-size: 32px;
            }

            .theme-toggle {
                top: 20px;
                right: 20px;
                width: 42px;
                height: 42px;
            }
        }

        .hidden-checkbox {
            display: none;
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
            <div class="integrated-content">
                <!-- Left Side - Hero Section -->
                <div class="hero-section float-in delay-1">
                    <!-- Brand -->
                    <div class="brand">
                        <div class="brand-logo">
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="white">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h4m-4 4h4m2-8h2m-2 4h2"/>
                            </svg>
                        </div>
                        <h1 class="brand-name">Legal Management System</h1>
                    </div>

                    <!-- Hero Text -->
                    <h2 class="hero-title">
                        Streamline Your<br>
                        <span>Document Management</span>
                    </h2>
                    
                    <p class="hero-subtitle">
                        Experience the next generation of contract workflow automation. 
                        Sign, manage, and track contracts effortlessly with our 
                        intelligent platform.
                    </p>

                    <!-- Features Grid -->
                    <div class="features-grid">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Lightning Fast</h4>
                                <p>Process contracts 10x faster</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Secure File Sharing</h4>
                                <p>Controlled document sharing</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Smart Tracking</h4>
                                <p>Real-time notifications</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <h4>Team Collaboration</h4>
                                <p>Work together seamlessly</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Login Section -->
                <div class="login-section float-in delay-2">
                    <!-- Login Header -->
                    <div class="login-header">
                        <h2 class="login-title">Welcome Back</h2>
                        <p class="login-subtitle">Sign in to your account</p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="status-message">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('hrms.login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
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
                                <div class="error-message">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
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
                                <div class="error-message">
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
                                <label class="checkbox-label" for="remember_me" onclick="toggleCheckbox(this.previousElementSibling)">
                                    Remember me
                                </label>
                                <input id="remember_me" type="checkbox" name="remember" class="hidden-checkbox">
                            </div>
                            
                            @if (Route::has('password.request'))
                                <a class="forgot-link" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="login-button">
                            Sign In
                        </button>
                    </form>

                    <!-- Footer Links -->
                    <div class="login-footer">
                        <div class="back-link">
                            <a href="{{ url('/') }}">
                                ← Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const body = document.body;
            const toggleIcon = document.querySelector('.theme-toggle svg');
            
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                toggleIcon.innerHTML = '<path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
            } else {
                toggleIcon.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
            }
            
            localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
        }

        function toggleCheckbox(element) {
            element.classList.toggle('checked');
            const hiddenCheckbox = document.getElementById('remember_me');
            hiddenCheckbox.checked = !hiddenCheckbox.checked;
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

        // Input focus effects
        document.querySelectorAll('.glass-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.style.transform = '';
            });
        });
    </script>
</body>
</html>