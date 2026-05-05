<x-app-layout-dark title="Master Data">
    
    <style>
        /* Custom styles for Master Data */
        .gradient-border {
            position: relative;
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
            border: 1px solid transparent;
            border-radius: 0.75rem;
        }
        
        .gradient-border::before {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            bottom: -1px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(59, 130, 246, 0.1), rgba(14, 165, 233, 0.3));
            border-radius: 0.75rem;
            z-index: -1;
            opacity: 0.5;
        }
        
        .search-highlight {
            background: linear-gradient(120deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.1));
            border-radius: 0.25rem;
            padding: 0.1rem 0.25rem;
        }
        
        .status-active {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.05));
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .status-inactive {
            background: linear-gradient(135deg, rgba(248, 113, 113, 0.15), rgba(248, 113, 113, 0.05));
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, 0.3);
        }
        
        .action-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.3);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        .action-btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }
        
        .table-row-hover {
            transition: all 0.2s ease;
        }
        
        .table-row-hover:hover {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.05), rgba(59, 130, 246, 0.02));
            transform: translateY(-1px);
        }
        
        .pagination-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .pagination-btn:hover:not(.disabled) {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(14, 165, 233, 0.3);
            transform: translateY(-1px);
        }
        
        .pagination-btn.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.2));
            border-color: rgba(14, 165, 233, 0.4);
            color: white;
        }
        
        .badge-count {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(168, 85, 247, 0.1));
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: #a855f7;
        }
        
        .loading-shimmer {
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.03) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
        
        /* Animations */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            20% {
                transform: scale(25, 25);
                opacity: 0.3;
            }
            100% {
                opacity: 0;
                transform: scale(40, 40);
            }
        }
        
        @keyframes fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes scale-in {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fade-in 0.2s ease-out;
        }
        
        .animate-scale-in {
            animation: scale-in 0.2s ease-out;
        }
        
        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }
        
        /* Glass effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Button styles */
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.2);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
        }
        
        /* Category badge */
        .category-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .category-badge.department {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.2));
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }
        
        .category-badge.position {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(139, 92, 246, 0.2));
            color: #a855f7;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        
        .category-badge.user {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(21, 128, 61, 0.2));
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }
        
        .category-badge.jabatan {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2));
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }
        
        /* Status indicator */
        .status-dot-active {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            box-shadow: 0 0 8px rgba(74, 222, 128, 0.5);
        }
        
        .status-dot-inactive {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f87171, #ef4444);
            box-shadow: 0 0 8px rgba(248, 113, 113, 0.5);
        }

        /* Table container transition */
        #tableContainer {
            transition: opacity 0.2s ease;
        }
    </style>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-cyan-500/10 to-blue-600/10 border border-cyan-500/20">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 via-blue-400 to-cyan-400 bg-clip-text text-transparent">
                            Master Data
                        </h1>
                        <p class="text-gray-400 mt-1">Manage departments, positions, and users</p>
                    </div>
                </div>
                
                <!-- ======================================== -->
                <!-- BAGIAN 1: HEADER STATS (DIPERBAIKI)      -->
                <!-- ======================================== -->
                <div class="flex flex-wrap gap-4 mt-6">
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Total Departments</p>
                            <p class="text-2xl font-bold">{{ $departmentCount }}</p>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Total Positions</p>
                            <p class="text-2xl font-bold">{{ $jabatanCount }}</p>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Total Users</p>
                            <p class="text-2xl font-bold">{{ $userCount }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3">
                <!-- ======================================== -->
                <!-- BAGIAN 4: TOMBOL ADD DENGAN ID          -->
                <!-- ======================================== -->
                @if($type === 'user')
                <a id="addBtn"
                   href="{{ route('admin.master-departments.create', ['type' => $type]) }}" 
                   class="glass-card px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300 action-btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add {{ $type === 'department' ? 'Department' : 'User' }}</span>
                </a>
                @else
                <a id="addBtn" class="hidden glass-card px-6 py-3 rounded-xl flex items-center gap-2 btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span></span>
                </a>
                @endif
                
                <!-- Categories Button with Dropdown -->
                <div class="relative">
                    <button id="categoriesBtn" class="glass-card px-4 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300 action-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        Categories
                        <svg class="w-4 h-4 transition-transform duration-200" id="categoriesArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <!-- Categories Dropdown Menu -->
                    <div id="categoriesDropdown" class="absolute right-0 mt-2 w-56 glass-card rounded-xl shadow-2xl border border-gray-800/50 opacity-0 invisible transition-all duration-200 transform origin-top-right scale-95 z-50">
                        <div class="p-2">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 border-b border-gray-800/50">
                                View Data Category
                            </div>
                            
                            <div class="space-y-1">
                                <button class="category-option w-full text-left px-3 py-2.5 rounded-lg flex items-center gap-3 hover:bg-cyan-500/10 hover:text-cyan-400 transition-all duration-200 group {{ $type === 'department' ? 'active-category' : '' }}"
                                        data-category="department">
                                    <div class="p-1.5 rounded-md bg-gradient-to-br from-cyan-500/10 to-blue-600/10 group-hover:from-cyan-500/20 group-hover:to-blue-600/20">
                                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium">Departments</div>
                                        <div class="text-xs text-gray-500 group-hover:text-cyan-300/70">View all departments</div>
                                    </div>
                                    @if($type === 'department')
                                    <div class="w-2 h-2 rounded-full bg-cyan-500"></div>
                                    @endif
                                </button>
                                
                                <button class="category-option w-full text-left px-3 py-2.5 rounded-lg flex items-center gap-3 hover:bg-amber-500/10 hover:text-amber-400 transition-all duration-200 group {{ $type === 'jabatan' ? 'active-category' : '' }}"
                                        data-category="jabatan">
                                    <div class="p-1.5 rounded-md bg-gradient-to-br from-amber-500/10 to-orange-600/10 group-hover:from-amber-500/20 group-hover:to-orange-600/20">
                                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium">Jabatan</div>
                                        <div class="text-xs text-gray-500 group-hover:text-amber-300/70">View all job positions</div>
                                    </div>
                                    @if($type === 'jabatan')
                                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                                    @endif
                                </button>
                                
                                <button class="category-option w-full text-left px-3 py-2.5 rounded-lg flex items-center gap-3 hover:bg-green-500/10 hover:text-green-400 transition-all duration-200 group {{ $type === 'user' ? 'active-category' : '' }}"
                                        data-category="user">
                                    <div class="p-1.5 rounded-md bg-gradient-to-br from-green-500/10 to-emerald-600/10 group-hover:from-green-500/20 group-hover:to-emerald-600/20">
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium">Users</div>
                                        <div class="text-xs text-gray-500 group-hover:text-green-300/70">View all users/employees</div>
                                    </div>
                                    @if($type === 'user')
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    @endif
                                </button>
                            </div>
                            
                            <div class="mt-2 pt-2 border-t border-gray-800/50">
                                <div class="px-3 py-2 text-xs text-gray-500">
                                    Currently viewing: <span id="currentCategory" class="text-cyan-400">
                                        @if($type === 'department')
                                            Departments
                                        @elseif($type === 'jabatan')
                                            Position
                                        @elseif($type === 'user')
                                            Users
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Category Display -->
        <div id="categoryDisplay" class="glass-card rounded-xl p-4 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div id="categoryIcon" class="p-2 rounded-lg bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
                    @if($type === 'department')
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    @elseif($type === 'jabatan')
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    @elseif($type === 'user')
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    @endif
                </div>
                <div>
                    <h2 id="categoryTitle" class="text-xl font-bold text-white">
                        @if($type === 'department')
                            Departments
                        @elseif($type === 'jabatan')
                            Jabatan
                        @elseif($type === 'user')
                            Users
                        @endif
                    </h2>
                    <p id="categoryDescription" class="text-gray-400 text-sm">
                        @if($type === 'department')
                            Manage and organize your company departments
                        @elseif($type === 'jabatan')
                            Manage job positions and their hierarchies
                        @elseif($type === 'user')
                            Manage employee accounts and information
                        @endif
                    </p>
                </div>
            </div>
            <div id="categoryBadge" class="category-badge {{ $type === 'department' ? 'department' : ($type === 'jabatan' ? 'jabatan' : 'user') }}">
                @if($type === 'department')
                    Department
                @elseif($type === 'jabatan')
                    Jabatan
                @elseif($type === 'user')
                    User
                @endif
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="glass-card rounded-xl p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                <div class="relative w-full md:w-auto">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           id="searchInput"
                           placeholder="Search..." 
                           class="pl-10 pr-4 py-3 w-full md:w-96 bg-dark-800/50 border border-gray-700 rounded-xl focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all duration-300 outline-none"
                           value="{{ request('search', '') }}">
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Department Filter (only for user view) -->
                    @if($type === 'user')
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 text-sm">Department:</span>
                        <select id="departmentFilter" class="bg-dark-800/50 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all duration-300 outline-none">
                            <option value="">All Departments</option>
                            @if(isset($departments))
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->kode_pendek }}" {{ request('department') == $dept->kode_pendek ? 'selected' : '' }}>
                                        {{ $dept->nama_departemen }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif
                    
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 text-sm">Sort by:</span>
                        <select id="sortFilter" class="bg-dark-800/50 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all duration-300 outline-none">
                            <option value="name" {{ request('sort', 'nama_departemen') === 'nama_departemen' || request('sort') === 'nama_user' || request('sort') === 'nama_jabatan' ? 'selected' : '' }}>Name A-Z</option>
                            <option value="name_desc" {{ request('order') === 'desc' ? 'selected' : '' }}>Name Z-A</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================== -->
        <!-- BAGIAN 2: WRAPPER TABEL DENGAN ID       -->
        <!-- ======================================== -->
        <div id="dataTable" class="gradient-border rounded-xl overflow-hidden mb-8">
            <div id="tableContainer">
                @include('departments.admin.master-departments._table_partial')
            </div>
        </div>
    </div>

    <!-- ======================================== -->
    <!-- BAGIAN 3: SCRIPT AJAX + EVENT DELEGATION -->
    <!-- ======================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentCategory = '{{ $type }}';
            let currentUrl = new URL(window.location.href);
            let isLoading = false;

            // ── AJAX LOAD TABLE ───────────────────────────────────────────
            function loadTable(url, pushState = true) {
                if (isLoading) return;
                isLoading = true;

                const container = document.getElementById('tableContainer');
                container.style.opacity = '0.5';
                container.style.pointerEvents = 'none';

                // Update URL di browser tanpa reload
                if (pushState) {
                    history.pushState(null, '', url);
                }
                currentUrl = new URL(url);
                currentCategory = currentUrl.searchParams.get('type') || currentCategory;

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.getElementById('tableContainer');
                    if (newTable) {
                        container.innerHTML = newTable.innerHTML;
                    }
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                    isLoading = false;

                    // Update category display UI
                    updateCategoryDisplay(currentCategory);
                    
                    // Update category icon
                    updateCategoryIcon(currentCategory);
                })
                .catch(() => {
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                    isLoading = false;
                });
            }

            // Handle browser back/forward
            window.addEventListener('popstate', function() {
                loadTable(window.location.href, false);
            });

            // ── BUILD URL HELPER ──────────────────────────────────────────
            function buildUrl(params = {}) {
                const url = new URL(window.location.href);
                Object.entries(params).forEach(([k, v]) => {
                    if (v === null || v === '') {
                        url.searchParams.delete(k);
                    } else {
                        url.searchParams.set(k, v);
                    }
                });
                return url.toString();
            }

            // ── CHANGE CATEGORY ───────────────────────────────────────────
            function changeCategory(category) {
                const url = buildUrl({
                    type: category,
                    page: '1',
                    search: null,
                });
                loadTable(url);
                toggleCategoriesDropdown(false);
            }

            // ── UPDATE CATEGORY ICON ──────────────────────────────────────
            function updateCategoryIcon(cat) {
                const iconContainer = document.getElementById('categoryIcon');
                if (!iconContainer) return;
                
                const icons = {
                    department: `<svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>`,
                    jabatan: `<svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>`,
                    user: `<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>`
                };
                
                iconContainer.innerHTML = icons[cat] || icons.department;
            }

            // ── CATEGORIES DROPDOWN ───────────────────────────────────────
            const categoriesBtn = document.getElementById('categoriesBtn');
            const categoriesDropdown = document.getElementById('categoriesDropdown');
            const categoriesArrow = document.getElementById('categoriesArrow');
            let isCategoriesOpen = false;

            function toggleCategoriesDropdown(show = null) {
                isCategoriesOpen = show === null ? !isCategoriesOpen : show;
                if (isCategoriesOpen) {
                    categoriesDropdown.classList.remove('opacity-0', 'invisible', 'scale-95');
                    categoriesDropdown.classList.add('opacity-100', 'visible', 'scale-100');
                    categoriesArrow.style.transform = 'rotate(180deg)';
                } else {
                    categoriesDropdown.classList.remove('opacity-100', 'visible', 'scale-100');
                    categoriesDropdown.classList.add('opacity-0', 'invisible', 'scale-95');
                    categoriesArrow.style.transform = 'rotate(0deg)';
                }
            }

            if (categoriesBtn) {
                categoriesBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleCategoriesDropdown();
                });
                document.addEventListener('click', function(e) {
                    if (!categoriesBtn.contains(e.target) && !categoriesDropdown?.contains(e.target)) {
                        toggleCategoriesDropdown(false);
                    }
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') toggleCategoriesDropdown(false);
                });
            }

            // Category option click — pakai delegation karena dropdown di DOM awal
            document.addEventListener('click', function(e) {
                const opt = e.target.closest('.category-option');
                if (opt) {
                    changeCategory(opt.dataset.category);
                }
            });

            // ── UPDATE CATEGORY DISPLAY UI ─────────────────────────────────
            function updateCategoryDisplay(cat) {
                // Update teks current category di dropdown
                const currentCatEl = document.getElementById('currentCategory');
                if (currentCatEl) {
                    currentCatEl.textContent = cat === 'department' ? 'Departments' : cat === 'jabatan' ? 'Position' : 'Users';
                }

                // Update judul kategori
                const categoryTitle = document.getElementById('categoryTitle');
                const categoryDesc  = document.getElementById('categoryDescription');
                const categoryBadge = document.getElementById('categoryBadge');
                if (categoryTitle) {
                    categoryTitle.textContent = cat === 'department' ? 'Departments' : cat === 'jabatan' ? 'Jabatan' : 'Users';
                }
                if (categoryDesc) {
                    const descs = {
                        department: 'Manage and organize your company departments',
                        jabatan: 'Manage job positions and their hierarchies',
                        user: 'Manage employee accounts and information',
                    };
                    categoryDesc.textContent = descs[cat] || '';
                }
                if (categoryBadge) {
                    categoryBadge.className = 'category-badge ' + cat;
                    categoryBadge.textContent = cat === 'department' ? 'Department' : cat === 'jabatan' ? 'Jabatan' : 'User';
                }

                // Update Add button visibility
                const addBtn = document.getElementById('addBtn');
                if (addBtn) {
                    if (cat === 'user') {
                        addBtn.classList.remove('hidden');
                        addBtn.href = `/admin/master-departments/create?type=${cat}`;
                        addBtn.querySelector('span').textContent = 'Add ' + (cat === 'department' ? 'Department' : 'User');
                    } else {
                        addBtn.classList.add('hidden');
                    }
                }
                
                // Toggle department filter visibility
                const deptFilterContainer = document.getElementById('departmentFilter')?.closest('.flex');
                if (deptFilterContainer) {
                    deptFilterContainer.style.display = cat === 'user' ? 'flex' : 'none';
                }
            }

            // ── SEARCH — pakai debounce ──────────────────────────────────
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadTable(buildUrl({ search: this.value.trim() || null, page: '1' }));
                    }, 500);
                });
            }

            // ── SORT DROPDOWN ─────────────────────────────────────────────
            const sortFilter = document.getElementById('sortFilter');
            if (sortFilter) {
                sortFilter.addEventListener('change', function() {
                    const isDesc = this.value.endsWith('_desc');
                    const sortFields = { department: 'nama_departemen', jabatan: 'nama_jabatan', user: 'nama_user' };
                    loadTable(buildUrl({
                        sort: sortFields[currentCategory] || 'nama_departemen',
                        order: isDesc ? 'desc' : 'asc',
                    }));
                });
            }

            // ── DEPARTMENT FILTER ─────────────────────────────────────────
            const deptFilter = document.getElementById('departmentFilter');
            if (deptFilter) {
                deptFilter.addEventListener('change', function() {
                    loadTable(buildUrl({ department: this.value || null, page: '1' }));
                });
            }

            // ── EVENT DELEGATION: sort-btn, pagination ────────────────────
            document.addEventListener('click', function(e) {
                // Sort button di dalam tabel
                const sortBtn = e.target.closest('.sort-btn');
                if (sortBtn) {
                    e.preventDefault();
                    const sortField = sortBtn.dataset.sort;
                    const curSort  = currentUrl.searchParams.get('sort');
                    const curOrder = currentUrl.searchParams.get('order');
                    const newOrder = (curSort === sortField && curOrder === 'asc') ? 'desc' : 'asc';
                    loadTable(buildUrl({ sort: sortField, order: newOrder, page: '1' }));
                }

                // Pagination button
                const pageBtn = e.target.closest('[data-page]');
                if (pageBtn && !pageBtn.disabled) {
                    e.preventDefault();
                    loadTable(buildUrl({ page: pageBtn.dataset.page }));
                }
            });

            // ── CONFIRM DELETE ─────────────────────────────────────────────
            window.confirmDelete = function(userName) {
                return confirm(`Are you sure you want to delete user "${userName}"?\nThis action cannot be undone.`);
            };

            // ── ANIMATE COUNTERS (sekali saat load) ────────────────────────
            document.querySelectorAll('.text-2xl.font-bold').forEach(counter => {
                const target = parseInt(counter.textContent);
                if (isNaN(target) || target === 0) return;
                counter.textContent = '0';
                const step = target / 40;
                let current = 0;
                const timer = setInterval(() => {
                    current = Math.min(current + step, target);
                    counter.textContent = Math.floor(current);
                    if (current >= target) clearInterval(timer);
                }, 16);
            });
        });
    </script>

</x-app-layout-dark>