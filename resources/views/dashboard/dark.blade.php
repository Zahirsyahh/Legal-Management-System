<x-app-layout-dark title="Dashboard">
    <!-- Tambahkan CSS untuk light/dark mode -->
    @push('styles')
    <style>
        /* Dropdown animation */
        .group:hover .group-hover\:opacity-100 {
            opacity: 1;
        }
        
        .group:hover .group-hover\:visible {
            visibility: visible;
        }
        
        .group:hover .group-hover\:scale-100 {
            transform: scale(1);
        }
        
        /* Ensure proper dropdown positioning */
        .relative.group {
            min-height: 80px;
        }
        
        .dropdown-option {
            font-size: 0.875rem;
        }
        
        .z-50 {
            z-index: 50;
        }

        :root {
            --primary-light: #4f46e5;
            --primary-dark: #8b5cf6;
            --secondary-light: #7c3aed;
            --secondary-dark: #a855f7;
            --bg-light: #f5f7fa;
            --bg-dark: #0f172a;
            --text-light: #1f2937;
            --text-dark: #f9fafb;
            --text-muted-light: #6b7280;
            --text-muted-dark: #9ca3af;
            --card-light: rgba(255, 255, 255, 0.9);
            --card-dark: rgba(30, 41, 59, 0.8);
            --border-light: rgba(209, 213, 219, 0.5);
            --border-dark: rgba(75, 85, 99, 0.5);
        }

        /* Base Styles */
        body {
            transition: all 0.3s ease;
            background: var(--bg-light);
            color: var(--text-light);
        }

        body.dark-mode {
            background: var(--bg-dark);
            color: var(--text-dark);
        }

        /* Text Colors */
        .text-primary {
            color: var(--primary-light) !important;
        }

        .dark-mode .text-primary {
            color: var(--primary-dark) !important;
        }

        .text-secondary {
            color: var(--text-muted-light) !important;
        }

        .dark-mode .text-secondary {
            color: var(--text-muted-dark) !important;
        }

        .text-title {
            color: #111827 !important;
        }

        .dark-mode .text-title {
            color: #f9fafb !important;
        }

        .text-body {
            color: #374151 !important;
        }

        .dark-mode .text-body {
            color: #d1d5db !important;
        }

        /* Glass Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }

        .dark-mode .glass-card {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid var(--border-dark);
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }

        .dark-mode .stat-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid var(--border-dark);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary-light) 100%);
            color: white !important;
            transition: all 0.3s ease;
        }

        .dark-mode .btn-primary {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-light) !important;
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }

        .dark-mode .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-dark) !important;
            border: 1px solid var(--border-dark);
        }

        /* Tables */
        table th {
            color: var(--text-light) !important;
            border-color: var(--border-light) !important;
        }

        .dark-mode table th {
            color: var(--text-dark) !important;
            border-color: var(--border-dark) !important;
        }

        table td {
            color: var(--text-light) !important;
            border-color: var(--border-light) !important;
        }

        .dark-mode table td {
            color: var(--text-dark) !important;
            border-color: var(--border-dark) !important;
        }

        /* Links */
        a:not(.btn-primary):not(.btn-secondary) {
            color: var(--primary-light) !important;
        }

        .dark-mode a:not(.btn-primary):not(.btn-secondary) {
            color: var(--primary-dark) !important;
        }

        /* Status Colors */
        .bg-blue-500\/20 {
            background-color: rgba(59, 130, 246, 0.3) !important;
        }

        .text-blue-400 {
            color: #2563eb !important;
        }

        .dark-mode .text-blue-400 {
            color: #60a5fa !important;
        }

        .bg-green-500\/20 {
            background-color: rgba(34, 197, 94, 0.3) !important;
        }

        .text-green-400 {
            color: #16a34a !important;
        }

        .dark-mode .text-green-400 {
            color: #4ade80 !important;
        }

        .bg-yellow-500\/20 {
            background-color: rgba(234, 179, 8, 0.3) !important;
        }

        .text-yellow-400 {
            color: #ca8a04 !important;
        }

        .dark-mode .text-yellow-400 {
            color: #facc15 !important;
        }

        .bg-red-500\/20 {
            background-color: rgba(239, 68, 68, 0.3) !important;
        }

        .text-red-400 {
            color: #dc2626 !important;
        }

        .dark-mode .text-red-400 {
            color: #f87171 !important;
        }

        .bg-purple-500\/20 {
            background-color: rgba(168, 85, 247, 0.3) !important;
        }

        .text-purple-300 {
            color: #7c3aed !important;
        }

        .dark-mode .text-purple-300 {
            color: #a78bfa !important;
        }

        /* Hover Effects */
        .hover\:bg-white\/10:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
        }

        .dark-mode .hover\:bg-white\/10:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 24px;
            right: 24px;
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .theme-toggle:hover {
            transform: scale(1.1) rotate(15deg);
        }

        .dark-mode .theme-toggle {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid var(--border-dark);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }

        .dark-mode .navbar {
            background: rgba(15, 23, 42, 0.9);
            border-bottom: 1px solid var(--border-dark);
        }

        .nav-link {
            color: var(--text-light) !important;
            transition: all 0.3s ease;
        }

        .dark-mode .nav-link {
            color: var(--text-dark) !important;
        }

        .nav-link:hover {
            color: var(--primary-light) !important;
        }

        .dark-mode .nav-link:hover {
            color: var(--primary-dark) !important;
        }

        .nav-link.active {
            color: var(--primary-light) !important;
            font-weight: 600;
        }

        .dark-mode .nav-link.active {
            color: var(--primary-dark) !important;
        }

        /* User Dropdown */
        .user-dropdown {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-light);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .dark-mode .user-dropdown {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--border-dark);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% {
                opacity: 0.6;
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            50% {
                opacity: 1;
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3);
            }
        }

        .animate-pulse-glow {
            animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Badge Notification */
        .notification-badge {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary-light) 100%);
            color: white;
        }

        .dark-mode .notification-badge {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        .dark-mode ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 4px;
        }

        .dark-mode ::-webkit-scrollbar-thumb {
            background: var(--primary-dark);
        }

        /* Scrollbar styling untuk cards */
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(75, 85, 99, 0.2);
            border-radius: 10px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 10px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.7);
        }

        .scrollbar-thumb-gray-700::-webkit-scrollbar-thumb {
            background: rgba(75, 85, 99, 0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .theme-toggle {
                top: 16px;
                right: 16px;
                width: 40px;
                height: 40px;
            }
        }

        .text-gray-400 {
            color: #6b7280 !important;
        }

        .dark-mode .text-gray-400 {
            color: #9ca3af !important;
        }

        .text-gray-300 {
            color: #4b5563 !important;
        }

        .dark-mode .text-gray-300 {
            color: #d1d5db !important;
        }

        .text-gray-500 {
            color: #6b7280 !important;
        }

        .dark-mode .text-gray-500 {
            color: #9ca3af !important;
        }

        .border-gray-800 {
            border-color: #e5e7eb !important;
        }

        .dark-mode .border-gray-800 {
            border-color: #374151 !important;
        }

        .divide-gray-800 > * + * {
            border-color: #e5e7eb !important;
        }

        .dark-mode .divide-gray-800 > * + * {
            border-color: #374151 !important;
        }

        svg.text-gray-400 {
            color: #6b7280 !important;
        }

        .dark-mode svg.text-gray-400 {
            color: #9ca3af !important;
        }
    </style>
    @endpush

    <!-- Theme Toggle Button -->
    @push('scripts')
    <script>
        function toggleDarkMode() {
            const body = document.body;
            const themeToggle = document.querySelector('.theme-toggle svg');
            
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                themeToggle.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
                localStorage.setItem('theme', 'dark');
            } else {
                themeToggle.innerHTML = '<path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
                localStorage.setItem('theme', 'light');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const body = document.body;
            const themeToggle = document.querySelector('.theme-toggle svg');
            
            if (savedTheme === 'dark') {
                body.classList.add('dark-mode');
                themeToggle.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
            }
        });
    </script>
    @endpush

    <!-- Navbar Section -->
    @push('header')
    <div class="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center space-x-3">
                        <div class="logo-icon-wrapper w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-title">Legal Management System</span>
                    </div>

                    <nav class="hidden md:flex items-center space-x-6">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Dashboard</span>
                            </div>
                        </a>
                        
                        <a href="{{ route('contracts.index') }}" class="nav-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Contracts</span>
                            </div>
                        </a>

                        @if(Auth::user()->hasRole('admin'))
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5l-5.5-5.5"/>
                                </svg>
                                <span>Admin</span>
                            </div>
                        </a>
                        @endif

                        <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span>Notifications</span>
                            </div>
                        </a>

                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Settings</span>
                            </div>
                        </a>
                    </nav>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="theme-toggle hidden md:flex" onclick="toggleDarkMode()">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </div>

                    @include('components.notification-bell')

                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-3 focus:outline-none">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                <span class="text-white font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-title">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-secondary">
                                    {{ Auth::user()->getRoleNames()->first() ?? 'User' }}
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 user-dropdown rounded-lg shadow-lg py-1 z-50">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-medium text-title">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-secondary">{{ Auth::user()->email }}</p>
                            </div>
                            
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-body hover:bg-gray-100 dark:hover:bg-gray-700">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Your Profile</span>
                                </div>
                            </a>
                            
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-body hover:bg-gray-100 dark:hover:bg-gray-700">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Settings</span>
                                </div>
                            </a>
                            
                            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                            
                            <button onclick="toggleDarkMode()" class="w-full text-left block px-4 py-2 text-sm text-body hover:bg-gray-100 dark:hover:bg-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                        </svg>
                                        <span>Toggle Theme</span>
                                    </div>
                                    <span class="text-xs text-secondary">Dark/Light</span>
                                </div>
                            </button>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>Sign Out</span>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>

                    <button id="mobileMenuButton" class="md:hidden">
                        <svg class="w-6 h-6 text-title" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200 dark:border-gray-700 py-3">
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="nav-link block px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('contracts.index') }}" class="nav-link block px-3 py-2 rounded-lg {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                        Contracts
                    </a>
                    @if(Auth::user()->hasRole('admin'))
                    <a href="{{ route('admin.users.index') }}" class="nav-link block px-3 py-2 rounded-lg {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        Admin
                    </a>
                    @endif
                    <a href="{{ route('notifications.index') }}" class="nav-link block px-3 py-2 rounded-lg {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        Notifications
                    </a>
                    <a href="{{ route('profile.edit') }}" class="nav-link block px-3 py-2 rounded-lg {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="theme-toggle md:hidden" onclick="toggleDarkMode()">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </div>

    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }

        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userButton = event.target.closest('button[onclick="toggleUserMenu()"]');
            
            if (!userButton && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });

        document.getElementById('mobileMenuButton').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileButton = document.getElementById('mobileMenuButton');
            
            if (!mobileButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
    @endpush
    
    <!-- MAIN CONTENT -->
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Welcome Section -->
        <div class="mb-10 animate-fade-in">
            <h1 class="text-3xl md:text-4xl font-bold mb-2 text-title">
                @auth
                    @if(auth()->user()->hasRole('admin'))
                        System Overview 👑
                    @elseif(auth()->user()->hasRole('legal'))
                        Legal Review Dashboard ⚖️
                    @elseif(auth()->user()->hasRole('user'))
                        My Dashboard 👤
                    @endif
                @endauth
            </h1>
            <p class="text-body">
                Welcome back, <span class="text-primary font-semibold">{{ Auth::user()->name }}</span>
            </p>
        </div>

        <!-- ======================= -->
        <!-- ROLE-SPECIFIC STATS -->
        <!-- ======================= -->
        
        @if(Auth::user()->hasRole('user'))
            <!-- USER STATS -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10 animate-slide-up">
                <!-- Draft -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-gray-500/20">
                            <svg class="w-6 h-6 text-gray-700 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Draft</span>
                    </div>
                    @if(isset($draftContracts))
                        <h3 class="text-3xl font-bold text-title">{{ $draftContracts }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">0</h3>
                    @endif
                    <p class="text-body text-sm mt-2">Documents</p>
                </div>

                <!-- Submitted -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-yellow-500/20">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Submitted</span>
                    </div>
                    @if(isset($submittedContracts))
                        <h3 class="text-3xl font-bold text-title">{{ $submittedContracts }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">0</h3>
                    @endif
                    <p class="text-body text-sm mt-2">For Review</p>
                </div>

                <!-- Under Review -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-blue-500/20">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Reviewing</span>
                    </div>
                    @if(isset($reviewingContracts))
                        <h3 class="text-3xl font-bold text-title">{{ $reviewingContracts }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">0</h3>
                    @endif
                    <p class="text-body text-sm mt-2">In Progress</p>
                </div>

                <!-- Approved -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-green-500/20">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Approved</span>
                    </div>
                    @if(isset($approvedContracts))
                        <h3 class="text-3xl font-bold text-title">{{ $approvedContracts }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">0</h3>
                    @endif
                    <p class="text-body text-sm mt-2">Completed</p>
                </div>
            </div>

        @elseif(Auth::user()->hasRole('legal'))
            <!-- LEGAL TEAM STATS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 animate-slide-up">
                <!-- Assigned -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-blue-500/20">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Assigned</span>
                    </div>
                    @if(isset($myAssignedContracts))
                        <h3 class="text-3xl font-bold text-title">{{ $myAssignedContracts->count() }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">0</h3>
                    @endif
                    <p class="text-body text-sm mt-2">To You</p>
                </div>

                <!-- Pending Review -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-yellow-500/20">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Pending</span>
                    </div>
                    @if(isset($pendingLegalContracts))
                        <h3 class="text-3xl font-bold text-title">{{ $pendingLegalContracts }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">0</h3>
                    @endif
                    <p class="text-body text-sm mt-2">For Legal Review</p>
                </div>

                <!-- Completed Today -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-green-500/20">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Today</span>
                    </div>
                    @if(isset($completedToday))
                        <h3 class="text-3xl font-bold text-title">{{ $completedToday }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">0</h3>
                    @endif
                    <p class="text-body text-sm mt-2">Reviewed</p>
                </div>
            </div>

            <!-- LEGAL: TWO TABLES FOR DOCUMENTS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-slide-up mb-10">
                
                <!-- TABLE 1: SUBMITTED DOCUMENTS -->
                <div class="glass-card rounded-2xl p-6 border border-yellow-500/20 h-full">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-title flex items-center gap-2">
                                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Submitted Documents
                            </h2>
                            <p class="text-xs text-body mt-1">Waiting for Legal Approval</p>
                        </div>

                        <a href="{{ route('contracts.index', ['status' => 'submitted']) }}"
                           class="text-xs text-primary hover:opacity-80 transition-all flex items-center gap-1 bg-white/5 px-3 py-1.5 rounded-lg">
                            View All
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    @php
                        $submittedDocs = $submittedDocuments ?? collect();
                    @endphp

                    @if($submittedDocs->count() > 0)
                        <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-700">
                            @foreach($submittedDocs as $contract)
    @include('components.contract-card', [
        'contract'    => $contract,
        'compact'     => true,
        'showStatus'  => true,
        'actionLabel' => 'Review',
        'actionUrl'   => ($contract->contract_type === 'surat' && $contract->workflow_type === 'static')
            ? route('surat.show', $contract)
            : route('contracts.show', $contract),
        'extraInfo'   => [
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
            'label' => 'Submitted by',
            'value' => $contract->user->nama_user ?? '-',
            'extra' => $contract->submitted_at?->diffForHumans() ?? $contract->created_at->diffForHumans(),
        ],
    ])
@endforeach
                        </div>

                        @if($submittedDocs->count() >= 5)
                            <div class="mt-6 text-center border-t border-gray-700/30 pt-4">
                                <a href="{{ route('contracts.index', ['status' => 'submitted']) }}"
                                   class="text-xs text-primary hover:text-primary/80 transition-all inline-flex items-center gap-1 bg-white/5 px-4 py-2 rounded-lg">
                                    <span>View all {{ $submittedDocs->count() }} submitted documents</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12 border border-dashed border-gray-700 rounded-lg">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-400 text-base mb-2">No submitted documents</p>
                            <p class="text-gray-500 text-xs">All clear! No documents waiting for approval</p>
                        </div>
                    @endif
                </div>

                <!-- TABLE 2: ONGOING DOCUMENTS -->
                <div class="glass-card rounded-2xl p-6 border border-blue-500/20 h-full">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-title flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                Ongoing Reviews
                            </h2>
                            <p class="text-xs text-body mt-1">Still under review</p>
                        </div>

                        <a href="{{ route('contracts.index', ['status' => ['under_review', 'revision_needed']]) }}"
                           class="text-xs text-primary hover:opacity-80 transition-all flex items-center gap-1 bg-white/5 px-3 py-1.5 rounded-lg">
                            View All
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    @php
                        $ongoingDocs = $ongoingDocuments ?? collect();
                    @endphp

                    @if($ongoingDocs->count() > 0)
                        <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-700">
                            @foreach($ongoingDocs as $contract)
    @php
        $activeStage = $contract->activeStage();
        $reviewer    = $activeStage?->assignedUser?->nama_user ?? null;
        $stageTotal  = $contract->reviewStages()->count();
        $stageDone   = $contract->reviewStages()->where('status', 'completed')->count();
    @endphp

    @include('components.contract-card', [
        'contract'    => $contract,
        'compact'     => true,
        'showStatus'  => true,
        'actionLabel' => 'Continue',
        'actionUrl'   => ($contract->contract_type === 'surat' && !$contract->isInReviewStageSystem())
            ? route('surat.show', $contract)
            : route('contracts.show', $contract),
        'extraInfo'   => [
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
            'label' => 'Reviewer',
            'value' => $reviewer ?? 'Unassigned',
            'extra' => $stageTotal > 0 ? "Stage {$stageDone}/{$stageTotal}" : null,
        ],
    ])
@endforeach
                        </div>

                        @if($ongoingDocs->count() >= 5)
                            <div class="mt-6 text-center border-t border-gray-700/30 pt-4">
                                <a href="{{ route('contracts.index', ['status' => ['under_review', 'revision_needed']]) }}"
                                   class="text-xs text-primary hover:text-primary/80 transition-all inline-flex items-center gap-1 bg-white/5 px-4 py-2 rounded-lg">
                                    <span>View all {{ $ongoingDocs->count() }} ongoing reviews</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12 border border-dashed border-gray-700 rounded-lg">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-400 text-base mb-2">No ongoing reviews</p>
                            <p class="text-gray-500 text-xs">You don't have any documents in progress</p>
                        </div>
                    @endif
                </div>
            </div>

        @elseif(Auth::user()->hasRole('admin'))
            <!-- ADMIN STATS -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10 animate-slide-up">
                <!-- Total Users -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-red-500/20">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Users</span>
                    </div>
                    @if(isset($totalUsers))
                        <h3 class="text-3xl font-bold text-title">{{ $totalUsers }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">5</h3>
                    @endif
                    <p class="text-body text-sm mt-2">Total</p>
                </div>

                <!-- Total Contracts -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-blue-500/20">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Documents</span>
                    </div>
                    @if(isset($totalContracts))
                        <h3 class="text-3xl font-bold text-title">{{ $totalContracts }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">0</h3>
                    @endif
                    <p class="text-body text-sm mt-2">Total</p>
                </div>

                <!-- Pending Legal -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-yellow-500/20">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Pending</span>
                    </div>
                    @if(isset($pendingLegal))
                        <h3 class="text-3xl font-bold text-title">{{ $pendingLegal }}</h3>
                    @else
                        <h3 class="text-3xl font-bold text-title">0</h3>
                    @endif
                    <p class="text-body text-sm mt-2">Legal Review</p>
                </div>

                <!-- Pending Department -->
                <div class="stat-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-green-500/20">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-body">Pending</span>
                    </div>
                    @php
                        $pendingDepartment = \App\Models\ContractDepartment::where('status', 'pending_assignment')->count();
                    @endphp
                    <h3 class="text-3xl font-bold text-title">{{ $pendingDepartment }}</h3>
                    <p class="text-body text-sm mt-2">Department Review</p>
                </div>
            </div>

            <!-- ADMIN: System Management -->
            <div class="glass-card rounded-2xl p-6 mb-10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-title">System Management</h2>
                    <span class="px-3 py-1 bg-red-500/20 text-red-400 text-xs rounded-full">Admin Only</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.users.index') }}" class="p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-colors">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-500/20 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5l-5.5-5.5" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-title">User Management</p>
                                <p class="text-sm text-body">Manage system users</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="{{ route('reports.index') }}" class="p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-colors">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-500/20 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-title">Reports</p>
                                <p class="text-sm text-body">View system reports</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @endif

        <!-- ======================= -->
        <!-- COMMON CONTENT FOR ALL ROLES -->
        <!-- ======================= -->
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-slide-up">
            <!-- Main Content (70%) -->
            <div class="lg:col-span-2 space-y-8">
                @if(Auth::user()->hasRole('user'))
                    <!-- USER: Recent Contracts -->
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-title">My Recent Contracts</h2>
                            <a href="{{ route('contracts.index') }}" class="text-sm text-primary hover:opacity-80 transition-all bg-white/5 px-3 py-1.5 rounded-lg">View All →</a>
                        </div>
                        
                        @if(isset($recentContracts) && $recentContracts->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-800">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-body">Document #</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-body">Title</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-body">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-body">Last Updated</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-body">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800">
                                        @foreach($recentContracts as $contract)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-body">
                                                {{ $contract->contract_number ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-title">
                                                    {{ Str::limit($contract->title, 30) }}
                                                </div>
                                                <div class="text-xs text-secondary">
                                                    {{ $contract->contract_type ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $contract->status_color }}">
                                                {{ $contract->display_status }}
                                            </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-body">
                                                {{ $contract->updated_at->diffForHumans() }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <a href="{{ route('contracts.show', $contract) }}" 
                                                   class="text-primary hover:text-primary/80 text-sm transition-all">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-16 w-16 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-title">No contracts yet</h3>
                                <p class="mt-1 text-body">Create your first contract review request</p>
                                <a href="{{ route('contracts.create') }}" class="mt-6 inline-flex items-center px-4 py-2 btn-primary rounded-lg">
                                    Create New Contract
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Quick Actions (COMMON FOR ALL) -->
                <div class="glass-card rounded-2xl p-6">
                    <h2 class="text-xl font-semibold mb-6 text-title">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @if(Auth::user()->hasRole('user'))
                            <!-- New Document Button with Dropdown -->
                            <div class="relative group">
                                <button type="button" 
                                class="btn-primary flex items-center justify-between p-4 rounded-xl group w-full h-full">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2.5 rounded-lg bg-white/10">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                        <div class="text-left">
                                            <h3 class="font-semibold text-sm">New Document</h3>
                                            <p class="text-xs text-white/80">Start review request</p>
                                        </div>
                                    </div>
                                    <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div class="absolute left-0 mt-2 w-full glass-card rounded-lg shadow-2xl border border-gray-800/50 opacity-0 invisible transition-all duration-200 transform origin-top scale-95 z-50 group-hover:opacity-100 group-hover:visible group-hover:scale-100">
                                    <div class="p-2">
                                        <a href="{{ route('contracts.create', ['type' => 'contract']) }}" 
                                           class="dropdown-option w-full text-left px-3 py-3 rounded-md flex items-center gap-3 hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200 group">
                                            <div class="p-1.5 rounded-md bg-gradient-to-br from-blue-500/10 to-cyan-600/10 group-hover:from-blue-500/20 group-hover:to-cyan-600/20">
                                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-sm">Document Review</div>
                                                <div class="text-xs text-gray-500 group-hover:text-blue-300/70">Contract/Letter review</div>
                                            </div>
                                        </a>
                                        
                                        <a href="{{ route('surat.create') }}" 
                                           class="dropdown-option w-full text-left px-3 py-3 rounded-md flex items-center gap-3 hover:bg-purple-500/10 hover:text-purple-400 transition-all duration-200 group mt-2">
                                            <div class="p-1.5 rounded-md bg-gradient-to-br from-purple-500/10 to-pink-600/10 group-hover:from-purple-500/20 group-hover:to-pink-600/20">
                                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-sm">Request Letter Numbering</div>
                                                <div class="text-xs text-gray-500 group-hover:text-purple-300/70">Generate Letter Numbering</div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Other quick action buttons -->
                        <a href="{{ route('contracts.index') }}" 
                           class="btn-secondary flex items-center justify-between p-4 rounded-xl group h-full">
                            <div class="flex items-center space-x-3">
                                <div class="p-2.5 rounded-lg bg-white/5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <h3 class="font-semibold text-sm text-title">View Documents</h3>
                                    <p class="text-xs text-body">Browse all documents</p>
                                </div>
                            </div>
                            <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>

                        <!-- Notifications Button -->
                        <a href="{{ route('notifications.index') }}" 
                           class="btn-primary flex items-center justify-between p-4 rounded-xl group bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 h-full">
                            <div class="flex items-center space-x-3">
                                <div class="p-2.5 rounded-lg bg-white/10">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <h3 class="font-semibold text-sm">Notifications</h3>
                                    <p class="text-xs text-white/80">View all notifications</p>
                                </div>
                            </div>
                            <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar (30%) - COMMON FOR ALL -->
            <div class="space-y-8">
                <!-- User Status Panel -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-title">Your Status</h2>
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse-glow"></div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500/20 to-cyan-500/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Logged in as</p>
                                <p class="font-semibold text-white">{{ Auth::user()->name }}</p>
                            </div>
                        </div>

                        <div class="space-y-4 pt-2">
                            <div class="flex justify-between items-center py-3 border-b border-gray-700/50">
                                <span class="text-sm text-gray-400">Role</span>
                                <span class="text-sm font-medium px-3 py-1.5 rounded-full bg-gradient-to-r from-blue-500/20 to-cyan-500/20 text-blue-400">
                                    {{ Auth::user()->getRoleNames()->first() ?? 'User' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-3">
                                <span class="text-sm text-gray-400">Last login</span>
                                <span class="text-sm text-gray-300">{{ now()->subHours(2)->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-2xl border border-blue-500/20 p-6 hover:border-blue-500/40 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Need Help?</h3>
                    </div>
                    <p class="text-sm text-gray-400 mb-6">Get assistance with the contract system</p>
                    <div class="space-y-4">
                        <a href="#" class="flex items-center gap-3 p-3 bg-white/5 rounded-lg hover:bg-white/10 transition-all group">
                            <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-500/20 transition-all">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <span class="text-sm text-blue-400 group-hover:text-blue-300">User Guide</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 p-3 bg-white/5 rounded-lg hover:bg-white/10 transition-all group">
                            <div class="w-8 h-8 rounded-full bg-purple-500/10 flex items-center justify-center group-hover:bg-purple-500/20 transition-all">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-sm text-purple-400 group-hover:text-purple-300">Contact Support</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-16 pt-10 border-t border-gray-800">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-sm text-gray-500">
                    © {{ date('Y') }} Legal Management System. All rights reserved.
                </p>
                <div class="flex items-center gap-8">
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-400 transition-colors">Privacy Policy</a>
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-400 transition-colors">Terms of Service</a>
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-400 transition-colors">Contact</a>
                </div>
                <p class="text-xs text-gray-600">
                    Version 1.0 • {{ now()->format('F j, Y') }}
                </p>
            </div>
            <div class="mt-6 text-center">
                <span class="text-xs text-gray-600 bg-white/5 px-4 py-2 rounded-full">All systems operational</span>
            </div>
        </div>
    </div>
</x-app-layout-dark>