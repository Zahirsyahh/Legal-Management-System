@props(['title' => 'DocumentFlow'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Tailwind via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        },
                        dark: {
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#334155',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'pulse-glow': 'pulseGlow 2s infinite',
                        'ping-once': 'pingOnce 1s ease-in-out',
                        'bounce-in': 'bounceIn 0.5s ease-out',
                        'float': 'float 3s ease-in-out infinite',
                        'shimmer': 'shimmer 2s infinite',
                        'ripple': 'ripple 0.6s linear',
                        'scale-in': 'scaleIn 0.3s ease-out',
                        'slide-down': 'slideDown 0.2s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' },
                        },
                        pulseGlow: {
                            '0%, 100%': { boxShadow: '0 0 20px rgba(14, 165, 233, 0.3)' },
                            '50%': { boxShadow: '0 0 30px rgba(14, 165, 233, 0.5)' },
                        },
                        pingOnce: {
                            '75%, 100%': {
                                transform: 'scale(1.5)',
                                opacity: '0',
                            },
                        },
                        bounceIn: {
                            '0%': { transform: 'scale(0.3)', opacity: '0' },
                            '50%': { transform: 'scale(1.05)', opacity: '0.7' },
                            '70%': { transform: 'scale(0.9)', opacity: '0.9' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '-1000px 0' },
                            '100%': { backgroundPosition: '1000px 0' },
                        },
                        ripple: {
                            '0%': { transform: 'scale(0)', opacity: '1' },
                            '100%': { transform: 'scale(4)', opacity: '0' },
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.95)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        slideDown: {
                            '0%': { transform: 'translateY(-10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            margin-left: 4rem; /* Default untuk sidebar collapsed */
            overflow-x: hidden;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #0ea5e9, #0284c7);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #0284c7, #0369a1);
        }
        
        /* Sidebar styling */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 4rem; /* Collapsed width */
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.97) 0%, rgba(30, 41, 59, 0.97) 100%);
            backdrop-filter: blur(15px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
            box-shadow: 5px 0 30px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.05) 0%, transparent 100%);
            pointer-events: none;
            z-index: -1;
        }
        
        .sidebar.expanded {
            width: 17rem; /* Expanded width */
        }
        
        .main-content {
            margin-left: 4rem; /* Default untuk sidebar collapsed */
            transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .main-content.expanded {
            margin-left: 17rem; /* Untuk sidebar expanded */
        }
        
        /* Sidebar items styling */
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #cbd5e1;
            border-radius: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin: 0.25rem 0.75rem;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
        }
        
        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.1), transparent);
            border-radius: 0.75rem;
            transition: width 0.3s ease;
            z-index: -1;
        }
        
        .sidebar-item:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }
        
        .sidebar-item:hover::before {
            width: 100%;
        }
        
        .sidebar-item.active {
            color: white;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.2));
            border-left: 4px solid #0ea5e9;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.15);
        }
        
        .sidebar-item.active::after {
            content: '';
            position: absolute;
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: #0ea5e9;
            border-radius: 50%;
            box-shadow: 0 0 10px #0ea5e9;
        }
        
        .sidebar-item .icon {
            flex-shrink: 0;
            width: 1.5rem;
            height: 1.5rem;
            margin-right: 0.75rem;
            transition: transform 0.3s ease;
        }
        
        .sidebar-item:hover .icon {
            transform: scale(1.1);
        }
        
        .sidebar-item .label {
            opacity: 0;
            transition: opacity 0.3s ease 0.1s;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
        }
        
        .sidebar.expanded .sidebar-item .label {
            opacity: 1;
        }
        
        /* Sidebar section titles */
        .sidebar-section {
            padding: 0.5rem 1rem;
            color: #94a3b8;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            transition: all 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .sidebar.expanded .sidebar-section {
            opacity: 1;
        }
        
        /* Top navbar styling */
        .top-navbar {
            position: fixed;
            top: 0;
            left: 4rem; /* Default untuk sidebar collapsed */
            right: 0;
            height: 4.5rem;
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.97) 0%, rgba(30, 41, 59, 0.97) 100%);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            z-index: 40;
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .top-navbar::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(14, 165, 233, 0.3), transparent);
        }
        
        .top-navbar.expanded {
            left: 17rem; /* Untuk sidebar expanded */
        }
        
        /* Dropdown styles */
        .documents-dropdown {
            position: absolute;
            left: 0;
            top: 100%;
            margin-top: 10px;
            min-width: 220px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 60;
            overflow: hidden;
        }
        
        .documents-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.1) 0%, transparent 100%);
            color: white;
            border-left-color: #0ea5e9;
            transform: translateX(3px);
        }
        
        .dropdown-item .icon {
            width: 16px;
            height: 16px;
            margin-right: 10px;
            color: #94a3b8;
        }
        
        .dropdown-item:hover .icon {
            color: #0ea5e9;
        }
        
        .dropdown-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 5px 16px;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
        }
        
        .stat-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(14, 165, 233, 0.3);
            box-shadow: 0 15px 35px rgba(14, 165, 233, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        .btn-primary:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.08));
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #0ea5e9, #22d3ee, #0ea5e9);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 3s infinite linear;
        }
        
        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: 0px;
            right: 1px;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
            border: 2px solid rgba(30, 41, 59, 0.9);
            animation: bounceIn 0.5s ease-out;
        }
        
        /* User avatar pulse effect */
        .user-avatar-pulse {
            position: relative;
        }
        
        .user-avatar-pulse::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid #0ea5e9;
            opacity: 0;
            animation: pulse-glow 2s infinite;
        }
        
        /* Search box */
        .search-box {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .search-box:focus-within {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(14, 165, 233, 0.3);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 17rem;
                box-shadow: 10px 0 40px rgba(0, 0, 0, 0.3);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .top-navbar {
                left: 0 !important;
                padding: 0 1rem;
            }
            
            .top-navbar h1 {
                font-size: 1.1rem;
            }
        }
        
        /* Dark mode improvements */
        @media (prefers-color-scheme: dark) {
            .sidebar {
                background: linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            }
            
            .top-navbar {
                background: linear-gradient(90deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            }
        }
    </style>

    {{ $styles ?? '' }}
</head>
<body class="font-inter text-gray-100">
    <!-- Sidebar Navigation -->
    <div id="sidebar" class="sidebar">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-800">
            <div class="flex items-center">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg animate-pulse-glow">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span id="sidebarLogoText" class="text-s font-bold gradient-text ml-3 opacity-0 transition-opacity">Legal Management System</span>
            </div>
            <div class="hidden sidebar.expanded:block">
                <span class="text-xs bg-gradient-to-r from-cyan-500/20 to-blue-500/20 text-cyan-300 px-2 py-1 rounded-md font-medium">PRO</span>
            </div>
        </div>

        <!-- Navigation Items -->
        <div class="py-4 overflow-y-auto h-[calc(100vh-9rem)] custom-scrollbar">
            <!-- MAIN SECTION -->
            <div class="sidebar-section">
                <span class="label opacity-0">MAIN</span>
            </div>
            
            <!-- Dashboard (Semua Role) -->
            <a href="{{ route('dashboard') }}" 
               class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="label">Dashboard</span>
            </a>

            <!-- REPORTS - Untuk SEMUA Role -->
            <a href="{{ route('reports.contracts') }}" 
               class="sidebar-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="label">Reports</span>
            </a>

            <!-- Notifications (Semua Role) -->
            <a href="{{ route('notifications.index') }}" 
               class="sidebar-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="label">Notifications</span>
                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                @endphp
                @if($unreadCount > 0)
                    <span class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a>

            <!-- Documents Section - Different behavior based on role -->
            @php
                $user = auth()->user();
            @endphp

            @if($user->hasRole('user'))
                <!-- USER: Documents with Dropdown (3 options) -->
                <div class="relative group">
                    <button onclick="toggleDocumentsDropdown()" 
                            class="sidebar-item w-full text-left {{ request()->routeIs('contracts.*') || request()->routeIs('surat.*') ? 'active' : '' }}">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="label">Documents</span>
                        <svg class="w-4 h-4 ml-auto text-gray-400 group-hover:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Documents Dropdown Menu for USER (3 options) -->
                    <div id="documentsDropdown" class="documents-dropdown left-4 mt-2">
                        <div class="p-2">
                            <!-- My Documents -->
                            <a href="{{ route('contracts.index') }}" class="dropdown-item">
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                My Documents
                            </a>
                            
                            <div class="dropdown-divider"></div>
                            
                            <!-- Document Review (Create Contract) -->
                            <a href="{{ route('contracts.create') }}" class="dropdown-item">
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Document Review
                            </a>
                            
                            <!-- Request Letter Numbering -->
                            <a href="{{ route('surat.create') }}" class="dropdown-item">
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Request Letter Numbering
                            </a>
                        </div>
                    </div>
                </div>

            @elseif($user->hasAnyRole(['admin', 'admin_acc', 'admin_fin', 'admin_tax', 'staff_acc', 'staff_fin', 'staff_tax', 'legal']))
                <!-- OTHER ROLES (including ADMIN): Direct link to All Documents (NO DROPDOWN) -->
                <a href="{{ route('contracts.index') }}" 
                   class="sidebar-item {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="label">All Documents</span>
                </a>
            @endif

            <!-- ======================= -->
            <!-- ROLE-SPECIFIC MENUS -->
            <!-- ======================= -->

            @if(auth()->user()->hasRole('admin'))
                <!-- ADMIN MENU -->
                <div class="sidebar-section mt-4">
                    <span class="label opacity-0">ADMINISTRATION</span>
                </div>
                
                <!-- Master Department -->
                <a href="{{ route('admin.master-departments.index') }}"
                   class="sidebar-item {{ request()->routeIs('admin.master-departments.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span class="label">Master Data</span>
                </a>

            @elseif(auth()->user()->hasAnyRole(['admin_acc', 'admin_fin', 'admin_tax']))
                <!-- DEPARTMENT ADMIN MENU - HANYA TERSISA DI SINI (TIDAK DI DROPDOWN) -->
                <div class="sidebar-section mt-4">
                    <span class="label opacity-0">DEPARTMENT</span>
                </div>
                
                @php
                    $role = auth()->user()->getRoleNames()->first();
                    $prefix = '';
                    $color = '';
                    
                    if ($role === 'admin_acc') {
                        $prefix = 'accounting-admin';
                        $color = 'text-amber-400';
                    } elseif ($role === 'admin_fin') {
                        $prefix = 'finance-admin';
                        $color = 'text-emerald-400';
                    } elseif ($role === 'admin_tax') {
                        $prefix = 'tax-admin';
                        $color = 'text-purple-400';
                    }
                @endphp
                


            @elseif(auth()->user()->hasRole('legal'))
                <!-- LEGAL MENU -->
                <div class="sidebar-section mt-4">
                    <span class="label opacity-0">LEGAL</span>
                </div>
                
                <!-- Legal Archive -->
                <a href="{{ route('archives.index') }}"
                   class="sidebar-item {{ request()->routeIs('legal.contracts.*') ? 'active' : '' }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                    <span class="label">Legal Archive</span>
                </a>

            @elseif(auth()->user()->hasAnyRole(['staff_acc', 'staff_fin', 'staff_tax']))
                <!-- STAFF MENU - Tidak perlu menu tambahan -->
            @endif

            <!-- Profile (Semua Role) -->
            <div class="sidebar-section mt-6">
                <span class="label opacity-0">ACCOUNT</span>
            </div>
            
            <a href="{{ route('profile.edit.dark') }}" 
               class="sidebar-item {{ request()->routeIs('profile.edit.dark') ? 'active' : '' }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="label">Profile Settings</span>
            </a>
            
            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="sidebar-item hover:bg-red-500/10 hover:text-red-300 cursor-pointer" onclick="event.preventDefault(); this.closest('form').submit();">
                @csrf
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="label">Sign Out</span>
            </form>
        </div>

        <!-- Sidebar Toggle Button -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800 bg-gradient-to-t from-dark-800/50 to-transparent">
            <button id="sidebarToggle" class="w-full flex items-center justify-center p-3 rounded-xl glass-card hover:bg-white/5 transition-all duration-300 group">
                <svg id="toggleIcon" class="w-5 h-5 text-gray-400 group-hover:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
                <span id="toggleLabel" class="ml-3 text-sm text-gray-400 group-hover:text-cyan-300 opacity-0 transition-all duration-300">Collapse Sidebar</span>
            </button>
        </div>
    </div>

    <!-- Top Navbar -->
    <div id="topNavbar" class="top-navbar">
        <!-- Left: Page Title & Breadcrumb -->
        <div class="flex items-center">
            <button id="mobileSidebarToggle" class="md:hidden mr-4 p-2 rounded-lg glass-card hover:bg-white/5 transition-all duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            
            <!-- Breadcrumb -->
            <div class="flex items-center space-x-2 text-sm">
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-cyan-400 transition-colors">
                    <i class="fas fa-home"></i>
                </a>
                <span class="text-gray-500">/</span>
                <span class="text-gray-300 font-medium">
                    @auth
                        @if(request()->routeIs('dashboard'))
                            Dashboard
                        @elseif(request()->routeIs('reports.*'))
                            Reports
                        @elseif(request()->routeIs('notifications.*'))
                            Notifications
                        @elseif(request()->routeIs('contracts.*'))
                            Contracts
                        @elseif(request()->routeIs('admin.*'))
                            Administration
                        @elseif(request()->routeIs('accounting-admin.*'))
                            Accounting
                        @elseif(request()->routeIs('finance-admin.*'))
                            Finance
                        @elseif(request()->routeIs('tax-admin.*'))
                            Tax
                        @elseif(request()->routeIs('legal.*'))
                            Legal
                        @elseif(request()->routeIs('reviews.*'))
                            Reviews
                        @elseif(request()->routeIs('profile.*'))
                            Profile
                        @else
                            {{ $title ?? 'DocumentFlow' }}
                        @endif
                    @endauth
                </span>
            </div>
        </div>

        <!-- Right: User Dropdown & Actions -->
        <div class="flex items-center space-x-4">
            <!-- Quick Actions -->
            <div class="hidden md:flex items-center space-x-2">
                <!-- Quick Actions kosong setelah menghapus Reports dan Documents buttons -->
            </div>
            
            <!-- User Dropdown -->
            <div class="relative group">
                <button class="flex items-center space-x-3 px-4 py-2 rounded-xl glass-card hover:bg-white/5 transition-all duration-200 active:scale-95">
                    <div class="user-avatar-pulse">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg">
                            <span class="font-semibold text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                    </div>
                    <div class="text-left hidden lg:block">
                        <p class="text-sm font-medium">{{ Auth::user()->name }}</p>
                        <div class="flex items-center space-x-1">
                            <span class="text-xs text-gray-400">{{ ucfirst(Auth::user()->getRoleNames()->first() ?? 'User') }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-gradient-to-r from-cyan-500/20 to-blue-500/20 text-cyan-300">Online</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div class="absolute right-0 mt-3 w-64 py-3 glass-card rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 origin-top-right animate-scale-in border border-gray-800">
                    <div class="px-4 py-3 border-b border-gray-800">
                        <p class="font-medium">{{ Auth::user()->name }}</p>
                        <p class="text-sm text-gray-400">{{ Auth::user()->email }}</p>
                        <div class="mt-2">
                            <span class="text-xs px-2 py-1 rounded-full bg-gradient-to-r from-cyan-500/20 to-blue-500/20 text-cyan-300">
                                {{ ucfirst(Auth::user()->getRoleNames()->first() ?? 'User') }}
                            </span>
                            @php
                                $user = Auth::user();
                                $departmentName = $user->department->nama_departemen ?? $user->nama_departemen ?? null;
                                $departmentCode = $user->department->kode_pendek ?? $user->kode_pendek ?? null;
                            @endphp
                            
                            @if($departmentName || $departmentCode)
                                <div class="mt-1 flex flex-col space-y-1">
                                    @if($departmentName)
                                        <span class="text-xs text-gray-300 font-medium">
                                            {{ $departmentName }}
                                        </span>
                                    @endif
                                    @if($departmentCode)
                                        <span class="text-xs text-gray-400">
                                            <span class="text-gray-500">Kode:</span> {{ $departmentCode }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="py-2">
                        <a href="{{ route('profile.edit.dark') }}" class="flex items-center px-4 py-3 text-sm hover:bg-white/5 transition-colors group/item">
                            <svg class="w-4 h-4 mr-3 text-gray-400 group-hover/item:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile Settings
                        </a>
                        <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 text-sm hover:bg-white/5 transition-colors group/item">
                            <svg class="w-4 h-4 mr-3 text-gray-400 group-hover/item:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Laporan
                        </a>
                        <a href="{{ route('notifications.index') }}" class="flex items-center px-4 py-3 text-sm hover:bg-white/5 transition-colors group/item">
                            <svg class="w-4 h-4 mr-3 text-gray-400 group-hover/item:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Notifications
                            @if($unreadCount > 0)
                                <span class="ml-auto notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm hover:bg-white/5 transition-colors group/item">
                            <svg class="w-4 h-4 mr-3 text-gray-400 group-hover/item:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </a>
                    </div>
                    
                    <div class="border-t border-gray-800 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-4 py-3 text-sm text-red-400 hover:bg-red-500/10 transition-colors group/item">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main id="mainContent" class="main-content pt-24 pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto animate-fade-in">
        {{ $slot }}
    </main>

    <!-- Floating Action Button (Mobile) -->
    @if(Auth::user()->hasRole('user'))
    <div class="fixed bottom-8 right-8 md:hidden z-30">
        <a href="{{ route('contracts.create') }}" 
           class="btn-primary w-16 h-16 rounded-full flex items-center justify-center shadow-2xl animate-float relative overflow-hidden">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
        </a>
    </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const topNavbar = document.getElementById('topNavbar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const toggleIcon = document.getElementById('toggleIcon');
            const toggleLabel = document.getElementById('toggleLabel');
            const sidebarLogoText = document.getElementById('sidebarLogoText');
            const themeToggle = document.getElementById('themeToggle');
            
            // Load sidebar state from localStorage
            const isExpanded = localStorage.getItem('sidebarExpanded') !== 'false';
            
            // Set initial state
            if (isExpanded) {
                expandSidebar();
            } else {
                collapseSidebar();
            }
            
            // Desktop toggle
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                if (sidebar.classList.contains('expanded')) {
                    collapseSidebar();
                    localStorage.setItem('sidebarExpanded', 'false');
                } else {
                    expandSidebar();
                    localStorage.setItem('sidebarExpanded', 'true');
                }
            });
            
            // Mobile toggle
            mobileSidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
            });
            
            // Close mobile sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(event.target) && 
                    !mobileSidebarToggle.contains(event.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            });
            
            // Theme toggle
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                    
                    // Ripple effect
                    const ripple = document.createElement('span');
                    const rect = themeToggle.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = event.clientX - rect.left - size / 2;
                    const y = event.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.3);
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        width: ${size}px;
                        height: ${size}px;
                        top: ${y}px;
                        left: ${x}px;
                        pointer-events: none;
                    `;
                    
                    themeToggle.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            }
            
            // Check for existing theme preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            }
            
            // Add animation to sidebar items on load
            const sidebarItems = document.querySelectorAll('.sidebar-item');
            sidebarItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.05}s`;
                item.classList.add('animate-slide-in');
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl + K for search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    document.querySelector('.search-box input')?.focus();
                }
                
                // Ctrl + B to toggle sidebar
                if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                    e.preventDefault();
                    sidebarToggle.click();
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('mobile-open');
                }
            });
            
            // Check for new notifications
            checkNotifications();
            
            // Check for new notifications every 30 seconds
            setInterval(checkNotifications, 30000);
            
            function expandSidebar() {
                sidebar.classList.add('expanded');
                mainContent.classList.add('expanded');
                topNavbar.classList.add('expanded');
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />';
                toggleLabel.textContent = 'Collapse Sidebar';
                setTimeout(() => {
                    toggleLabel.style.opacity = '1';
                    sidebarLogoText.style.opacity = '1';
                }, 150);
            }
            
            function collapseSidebar() {
                sidebar.classList.remove('expanded');
                mainContent.classList.remove('expanded');
                topNavbar.classList.remove('expanded');
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />';
                toggleLabel.textContent = 'Expand Sidebar';
                toggleLabel.style.opacity = '0';
                sidebarLogoText.style.opacity = '0';
            }
        });

        // Function to toggle documents dropdown in sidebar (only for USER role)
        function toggleDocumentsDropdown() {
            const dropdown = document.getElementById('documentsDropdown');
            if (dropdown) {
                dropdown.classList.toggle('show');
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function closeDropdown(event) {
                    if (!dropdown.contains(event.target) && !event.target.closest('.sidebar-item[onclick="toggleDocumentsDropdown()"]')) {
                        dropdown.classList.remove('show');
                        document.removeEventListener('click', closeDropdown);
                    }
                });
            }
        }

        // Function to check for new notifications
        function checkNotifications() {
            fetch('{{ route("notifications.check") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.unread_count > 0) {
                    updateNotificationBadge(data.unread_count);
                    
                    // Play notification sound if new notifications
                    if (data.new_notifications > 0) {
                        playNotificationSound();
                        
                        // Show subtle notification toast
                        showNotificationToast(data.new_notifications);
                    }
                } else {
                    // Hide badge if no notifications
                    const badge = document.querySelector('.notification-badge');
                    if (badge) {
                        badge.remove();
                    }
                }
            })
            .catch(error => console.error('Error checking notifications:', error));
        }

        // Function to update notification badge
        function updateNotificationBadge(count) {
            let badge = document.querySelector('.notification-badge');
            if (!badge) {
                // Create badge if it doesn't exist
                const bell = document.querySelector('.sidebar-item[href*="notifications"]');
                if (bell) {
                    badge = document.createElement('span');
                    badge.className = 'notification-badge';
                    bell.appendChild(badge);
                }
            }
            
            if (badge) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.classList.remove('hidden');
                
                // Add animation for new notifications
                if (count > 0) {
                    badge.classList.add('animate-bounce-in');
                    setTimeout(() => {
                        badge.classList.remove('animate-bounce-in');
                    }, 500);
                }
            }
        }

        // Function to play notification sound
        function playNotificationSound() {
            try {
                const audio = new Audio('{{ asset("sounds/notification.mp3") }}');
                audio.volume = 0.3;
                audio.play().catch(e => console.log('Audio play failed:', e));
            } catch (e) {
                console.log('Notification sound error:', e);
            }
        }

        // Function to show notification toast
        function showNotificationToast(count) {
            // Remove existing toast
            const existingToast = document.getElementById('notification-toast');
            if (existingToast) {
                existingToast.remove();
            }
            
            const toast = document.createElement('div');
            toast.id = 'notification-toast';
            toast.className = 'fixed bottom-24 right-8 z-50 animate-slide-up';
            toast.innerHTML = `
                <div class="bg-gradient-to-r from-dark-800 to-dark-900 border border-cyan-500/30 rounded-xl p-4 shadow-2xl max-w-xs">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">${count} new notification${count > 1 ? 's' : ''}</p>
                            <p class="text-sm text-gray-400">Click to view</p>
                        </div>
                        <button onclick="this.closest('#notification-toast').remove()" class="ml-4 text-gray-500 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.add('opacity-0', 'translate-y-4');
                    setTimeout(() => {
                        if (toast.parentNode) toast.remove();
                    }, 300);
                }
            }, 5000);
            
            // Click to go to notifications
            toast.addEventListener('click', function(e) {
                if (!e.target.closest('button')) {
                    window.location.href = '{{ route("notifications.index") }}';
                }
            });
        }
    </script>

    {{ $scripts ?? '' }}
</body>
</html>