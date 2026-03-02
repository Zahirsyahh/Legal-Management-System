<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Legal Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                        'pulse-glow': 'pulseGlow 2s infinite',
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
                        pulseGlow: {
                            '0%, 100%': { boxShadow: '0 0 20px rgba(14, 165, 233, 0.3)' },
                            '50%': { boxShadow: '0 0 30px rgba(14, 165, 233, 0.5)' },
                        }
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
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .stat-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(14, 165, 233, 0.3);
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
        }
        .btn-secondary {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.08));
            border-color: rgba(255, 255, 255, 0.2);
        }
        .nav-gradient {
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95));
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .gradient-text {
            background: linear-gradient(135deg, #0ea5e9, #22d3ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring__circle {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(90deg);
            transform-origin: 50% 50%;
        }
    </style>
</head>
<body class="font-inter text-gray-100">
    <!-- Enhanced Navigation -->
    <nav class="nav-gradient fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold gradient-text">Legal Management System</span>
                    </a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <button class="flex items-center space-x-3 px-4 py-2 rounded-lg glass-card hover:bg-white/5 transition-all duration-200">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                                <span class="font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <div class="text-left hidden md:block">
                                <p class="text-sm font-medium">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-400">{{ Auth::user()->roles->first()->name ?? 'User' }}</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 py-2 glass-card rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 text-sm hover:bg-white/5 transition-colors">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile Settings
                            </a>
                            <div class="border-t border-gray-800 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-3 text-sm text-red-400 hover:bg-white/5 transition-colors">
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
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-20 pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Welcome Section -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">Welcome back, <span class="gradient-text">{{ Auth::user()->name }}</span> 👋</h1>
            <p class="text-gray-400">Here's what's happening with your contracts today.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-slide-up">
            <!-- Total Contracts Card -->
            <div class="stat-card rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-blue-500/10 to-cyan-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500/20 to-cyan-500/20">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-sm text-gray-400">Total</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">{{ $totalContracts ?? 0 }}</h3>
                    <p class="text-gray-400 text-sm">Contracts</p>
                    <div class="mt-4 pt-4 border-t border-gray-800">
                        <span class="text-xs text-green-400">↗ All time</span>
                    </div>
                </div>
            </div>

            <!-- Pending Legal Card -->
            <div class="stat-card rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-amber-500/10 to-orange-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-amber-500/20 to-orange-500/20">
                            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-gray-400">Pending</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">{{ $pendingLegal ?? 0 }}</h3>
                    <p class="text-gray-400 text-sm">Legal Review</p>
                    <div class="mt-4 pt-4 border-t border-gray-800">
                        <span class="text-xs text-amber-400">⏳ Requires attention</span>
                    </div>
                </div>
            </div>

            <!-- Assigned Card -->
            <div class="stat-card rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-emerald-500/10 to-green-500/10 rounded-full -translate-y-8 translate-x-8"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-500/20 to-green-500/20">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-gray-400">Assigned</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">{{ $assignedContracts ?? 0 }}</h3>
                    <p class="text-gray-400 text-sm">To You</p>
                    <div class="mt-4 pt-4 border-t border-gray-800">
                        <span class="text-xs text-emerald-400">✓ Ready for review</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up">
            <!-- Quick Actions -->
            <div class="lg:col-span-2 glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold">Quick Actions</h2>
                    <span class="text-xs text-gray-400 px-3 py-1 rounded-full bg-white/5">Proactive</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('contracts.create') }}" 
                       class="btn-primary flex items-center justify-between p-5 rounded-xl group">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 rounded-lg bg-white/10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold">New Contract</h3>
                                <p class="text-sm text-blue-200/70">Start review request</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>

                    <a href="{{ route('contracts.index') }}" 
                       class="btn-secondary flex items-center justify-between p-5 rounded-xl group">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 rounded-lg bg-white/5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold">My Contracts</h3>
                                <p class="text-sm text-gray-400">View all requests</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>

                    <a href="#" 
                       class="btn-secondary flex items-center justify-between p-5 rounded-xl group">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 rounded-lg bg-white/5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold">Analytics</h3>
                                <p class="text-sm text-gray-400">View insights</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>

                    <a href="#" 
                       class="btn-secondary flex items-center justify-between p-5 rounded-xl group">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 rounded-lg bg-white/5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold">Settings</h3>
                                <p class="text-sm text-gray-400">Manage preferences</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- User Status Panel -->
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold">Your Status</h2>
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse-glow"></div>
                </div>
                
                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500/20 to-cyan-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Logged in as</p>
                            <p class="font-semibold">{{ Auth::user()->name }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Role</span>
                            <span class="font-medium px-3 py-1 rounded-full bg-gradient-to-r from-blue-500/20 to-cyan-500/20 text-blue-300">
                                {{ Auth::user()->roles->first()->name ?? 'User' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Last login</span>
                            <span class="text-gray-300">{{ now()->subHours(2)->diffForHumans() }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Active sessions</span>
                            <span class="text-emerald-400">1 device</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-400">System Health</span>
                            <span class="text-xs text-emerald-400">Optimal</span>
                        </div>
                        <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full" style="width: 92%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800 text-center text-gray-500 text-sm">
            <p>Legal Management System v1.0 • {{ now()->format('F j, Y') }} • <span class="text-blue-400">All systems operational</span></p>
        </div>
    </div>

    <!-- Floating Action Button (Mobile) -->
    <div class="fixed bottom-6 right-6 md:hidden">
        <a href="{{ route('contracts.create') }}" 
           class="btn-primary w-14 h-14 rounded-full flex items-center justify-center shadow-xl animate-pulse-glow">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </a>
    </div>

    <script>
        // Simple animations on load
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });

        // User menu toggle
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.group')) {
                document.querySelectorAll('.group .absolute').forEach(menu => {
                    menu.classList.remove('opacity-100', 'visible');
                    menu.classList.add('opacity-0', 'invisible');
                });
            }
        });
    </script>
</body>
</html>