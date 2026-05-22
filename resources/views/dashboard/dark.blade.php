<x-app-layout-dark title="Dashboard">
    <!-- Dashboard styles are now in resources/css/dashboard.css -->
    @push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
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
                                <a href="{{ route('contracts.index', ['status' => ['under_review', 'revision_needed', 'final_approved']]) }}"
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
                    <a href="{{ route('admin.master-departments.index') }}" class="p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-colors">
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
                    <!-- USER: Recent Contracts - Modern Premium Design -->
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                            <div>
                                <h2 class="text-xl font-bold text-title flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    My Recent Contracts
                                </h2>
                                <p class="text-xs text-body mt-1">Latest documents and their current status</p>
                            </div>
                            <a href="{{ route('contracts.index') }}" 
                            class="inline-flex items-center gap-2 text-sm text-primary hover:text-primary-dark transition-all bg-primary/10 hover:bg-primary/20 px-4 py-2 rounded-xl">
                                <span>View All</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                        
                        @if(isset($recentContracts) && $recentContracts->count() > 0)
                            <div class="overflow-x-auto rounded-xl">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider rounded-l-xl">
                                                Document #
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Title
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Last Updated
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider rounded-r-xl">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($recentContracts as $contract)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-all duration-200 group">
                                            <!-- Document # -->
                                            <td class="px-4 py-4">
                                                <span class="font-mono text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $contract->contract_number ?? 'N/A' }}
                                                </span>
                                            </td>
                                            
                                            <!-- Title with type badge -->
                                            <td class="px-4 py-4">
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 line-clamp-2">
                                                        {{ $contract->title }}
                                                    </span>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            {{ ucfirst($contract->contract_type ?? 'Contract') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Status with modern badge -->
                                            <td class="px-4 py-4">
                                                @php
                                                    $statusConfig = [
                                                        'draft' => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-400', 'icon' => '<path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>'],
                                                        'submitted' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/30', 'text' => 'text-yellow-700 dark:text-yellow-400', 'icon' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                                                        'under_review' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'icon' => '<path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>'],
                                                        'approved' => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-400', 'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                                                        'archived' => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-500 dark:text-gray-400', 'icon' => '<path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'],
                                                    ];
                                                    $status = $contract->status ?? 'draft';
                                                    $config = $statusConfig[$status] ?? $statusConfig['draft'];
                                                @endphp
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        {!! $config['icon'] !!}
                                                    </svg>
                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                </span>
                                            </td>
                                            
                                            <!-- Last Updated with relative time -->
                                            <td class="px-4 py-4">
                                                <div class="flex flex-col">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $contract->updated_at->diffForHumans() }}
                                                    </span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                                        {{ $contract->updated_at->format('d M Y, H:i') }}
                                                    </span>
                                                </div>
                                            </td>
                                            
                                            <!-- Actions with modern buttons -->
                                            <td class="px-4 py-4">
                                                <a href="{{ route('contracts.show', $contract) }}" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-primary bg-primary/10 hover:bg-primary/20 transition-all duration-200 group-hover:scale-105">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    <span>View</span>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Card footer with summary -->
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                    <span>{{ $recentContracts->count() }} active contracts</span>
                                </div>
                                <a href="{{ route('contracts.index') }}" class="text-xs text-primary hover:text-primary-dark transition-all">
                                    Browse all documents →
                                </a>
                            </div>
                            
                        @else
                            <!-- Empty State - Modern Design -->
                            <div class="text-center py-16">
                                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-title mb-2">No contracts yet</h3>
                                <p class="text-sm text-body mb-6">Create your first contract review request</p>
                                <a href="{{ route('contracts.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary to-secondary text-white rounded-xl hover:shadow-lg transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
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