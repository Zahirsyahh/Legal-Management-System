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
                
                <!-- Stats Cards -->
                <div class="flex flex-wrap gap-4 mt-6">
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Total Departments</p>
                            <p id="departmentCount" class="text-2xl font-bold">
                                @if($type === 'department')
                                    {{ $departments->total() }}
                                @else
                                    0
                                @endif
                            </p>
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
                            <p id="positionCount" class="text-2xl font-bold">
                                @if($type === 'jabatan')
                                    {{ $jabatans->total() }}
                                @else
                                    0
                                @endif
                            </p>
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
                            <p id="userCount" class="text-2xl font-bold">
                                @if($type === 'user')
                                    {{ $users->total() }}
                                @else
                                    0
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3">
                <!-- Add New Button (for department and user only) -->
                @if($type === 'department' || $type === 'user')
                <a href="{{ route('admin.master-departments.create', ['type' => $type]) }}" 
                   class="glass-card px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300 action-btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add {{ $type === 'department' ? 'Department' : 'User' }}
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

        <!-- Main Table - Dynamic Content -->
        <div id="dataTable" class="gradient-border rounded-xl overflow-hidden mb-8">
            @if($type === 'department')
                <!-- Departments Table -->
                <div id="departmentsTable" class="table-container">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-dark-800 to-dark-900 border-b border-gray-800">
                                    <th class="py-4 px-6 text-left">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-300">Code</span>
                                            <button class="text-gray-500 hover:text-cyan-400 transition-colors sort-btn" data-sort="kode_pendek">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-300">Department Name</span>
                                            <button class="text-gray-500 hover:text-cyan-400 transition-colors sort-btn" data-sort="nama_departemen">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left">
                                        <span class="font-semibold text-gray-300">Status</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/50" id="departmentsTableBody">
                                @forelse ($departments as $dept)
                                <tr class="table-row-hover group" data-id="{{ $dept->kode_departemen }}">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-lg bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
                                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="font-mono font-bold text-lg">{{ $dept->kode_pendek }}</span>
                                                <p class="text-xs text-gray-500 mt-1">{{ $dept->kode_departemen }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-white group-hover:text-cyan-300 transition-colors">
                                                {{ $dept->nama_departemen }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="relative flex h-3 w-3">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                            </span>
                                            <span class="status-active px-3 py-1.5 rounded-full text-sm font-medium">
                                                Active
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-12 px-6 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 mb-4">
                                                <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-semibold text-gray-300 mb-2">No Departments Found</h3>
                                            <p class="text-gray-500 max-w-md">No departments match your search criteria</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Table Footer with Pagination -->
                    @if($departments->hasPages())
                    <div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-400">
                            Showing <span class="font-medium text-white">{{ $departments->firstItem() ?? 0 }}</span> 
                            to <span class="font-medium text-white">{{ $departments->lastItem() ?? 0 }}</span> 
                            of <span class="font-medium text-white">{{ $departments->total() }}</span> records
                        </div>
                        
                        <div class="flex items-center gap-2">
                            @if($departments->onFirstPage())
                            <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            @else
                            <button onclick="changePage({{ $departments->currentPage() - 1 }})" 
                                    class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            @endif
                            
                            @php
                                $current = $departments->currentPage();
                                $last = $departments->lastPage();
                                $start = max(1, $current - 2);
                                $end = min($last, $current + 2);
                                
                                if($start > 1) {
                                    echo '<button onclick="changePage(1)" class="pagination-btn px-4 py-2 rounded-lg">1</button>';
                                    if($start > 2) {
                                        echo '<span class="px-2 text-gray-500">...</span>';
                                    }
                                }
                                
                                for($i = $start; $i <= $end; $i++) {
                                    if($i == $current) {
                                        echo '<button class="pagination-btn active px-4 py-2 rounded-lg">' . $i . '</button>';
                                    } else {
                                        echo '<button onclick="changePage(' . $i . ')" class="pagination-btn px-4 py-2 rounded-lg">' . $i . '</button>';
                                    }
                                }
                                
                                if($end < $last) {
                                    if($end < $last - 1) {
                                        echo '<span class="px-2 text-gray-500">...</span>';
                                    }
                                    echo '<button onclick="changePage(' . $last . ')" class="pagination-btn px-4 py-2 rounded-lg">' . $last . '</button>';
                                }
                            @endphp
                            
                            @if($departments->hasMorePages())
                            <button onclick="changePage({{ $departments->currentPage() + 1 }})" 
                                    class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            @else
                            <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            @elseif($type === 'jabatan')
                <!-- Jabatan Table (sama seperti sebelumnya) -->
                <div id="jabatanTable" class="table-container">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-dark-800 to-dark-900 border-b border-gray-800">
                        <th class="py-4 px-6 text-left">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-300">No</span>
                            </div>
                        </th>
                        <th class="py-4 px-6 text-left">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-300">Jabatan Name</span>
                                <button class="text-gray-500 hover:text-amber-400 transition-colors sort-btn" data-sort="nama_jabatan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </button>
                            </div>
                        </th>
                        <th class="py-4 px-6 text-center">
                            <span class="font-semibold text-gray-300">Status</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50" id="jabatanTableBody">
                    @forelse ($jabatans as $jabatan)
                    <tr class="table-row-hover group" data-id="{{ $jabatan->no_jabatan }}">
                        <!-- No Jabatan -->
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-mono font-bold text-lg">{{ $jabatan->no_jabatan }}</span>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Nama Jabatan -->
                        <td class="py-4 px-6">
                            <div class="flex flex-col">
                                <span class="font-medium text-white group-hover:text-amber-300 transition-colors">
                                    {{ $jabatan->nama_jabatan }}
                                </span>
                            </div>
                        </td>
                        
                        
                        <!-- Status -->
                        <td class="py-4 px-6 text-center">
                            <div class="inline-flex items-center justify-center gap-2">
                                @if($jabatan->status_jabatan === 'Aktif' || !isset($jabatan->status_jabatan))
                                <span class="status-dot-active"></span>
                                <span class="status-active px-3 py-1.5 rounded-full text-sm font-medium">
                                    Active
                                </span>
                                @else
                                <span class="status-dot-inactive"></span>
                                <span class="status-inactive px-3 py-1.5 rounded-full text-sm font-medium">
                                    Inactive
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 px-6 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 mb-4">
                                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-300 mb-2">No Positions Found</h3>
                                <p class="text-gray-500 max-w-md">No positions match your search criteria</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($jabatans->hasPages())
        <div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-400">
                Showing <span class="font-medium text-white">{{ $jabatans->firstItem() ?? 0 }}</span> 
                to <span class="font-medium text-white">{{ $jabatans->lastItem() ?? 0 }}</span> 
                of <span class="font-medium text-white">{{ $jabatans->total() }}</span> records
            </div>
            
            <div class="flex items-center gap-2">
                @if($jabatans->onFirstPage())
                <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                @else
                <button onclick="changePage({{ $jabatans->currentPage() - 1 }})" 
                        class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                @endif
                
                @php
                    $current = $jabatans->currentPage();
                    $last = $jabatans->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                    
                    if($start > 1) {
                        echo '<button onclick="changePage(1)" class="pagination-btn px-4 py-2 rounded-lg">1</button>';
                        if($start > 2) {
                            echo '<span class="px-2 text-gray-500">...</span>';
                        }
                    }
                    
                    for($i = $start; $i <= $end; $i++) {
                        if($i == $current) {
                            echo '<button class="pagination-btn active px-4 py-2 rounded-lg">' . $i . '</button>';
                        } else {
                            echo '<button onclick="changePage(' . $i . ')" class="pagination-btn px-4 py-2 rounded-lg">' . $i . '</button>';
                        }
                    }
                    
                    if($end < $last) {
                        if($end < $last - 1) {
                            echo '<span class="px-2 text-gray-500">...</span>';
                        }
                        echo '<button onclick="changePage(' . $last . ')" class="pagination-btn px-4 py-2 rounded-lg">' . $last . '</button>';
                    }
                @endphp
                
                @if($jabatans->hasMorePages())
                <button onclick="changePage({{ $jabatans->currentPage() + 1 }})" 
                        class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                @else
                <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                @endif
            </div>
        </div>
        @endif
    </div>
            @elseif($type === 'user')
                <!-- Users Table -->
                <div id="usersTable" class="table-container">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-dark-800 to-dark-900 border-b border-gray-800">
                                    <th class="py-4 px-6 text-left">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-300">User ID</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-300">Name</span>
                                            <button class="text-gray-500 hover:text-green-400 transition-colors sort-btn" data-sort="nama_user">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left">
                                        <span class="font-semibold text-gray-300">Username / Email</span>
                                    </th>
                                    <th class="py-4 px-6 text-left">
                                        <span class="font-semibold text-gray-300">Position</span>
                                    </th>
                                    <th class="py-4 px-6 text-left">
                                        <span class="font-semibold text-gray-300">Department</span>
                                    </th>
                                    <th class="py-4 px-6 text-left">
                                        <span class="font-semibold text-gray-300">Status</span>
                                    </th>
                                    <th class="py-4 px-6 text-center">
                                        <span class="font-semibold text-gray-300">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/50" id="usersTableBody">
                                @forelse ($users as $user)
                                <tr class="table-row-hover group" data-id="{{ $user->id_user }}">
                                    <!-- User ID -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="font-mono font-bold text-lg">{{ $user->id_user }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Name -->
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-white group-hover:text-green-300 transition-colors">
                                                {{ $user->nama_user ?? $user->username }}
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1">
                                                NIK: {{ $user->nik ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Username/Email -->
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-gray-300">
                                                {{ $user->username }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ $user->email ?? 'No email' }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Position -->
                                    <td class="py-4 px-6">
                                        <div class="inline-flex">
                                            <span class="px-3 py-1.5 text-sm font-medium rounded-full bg-gradient-to-br from-amber-500/10 to-orange-600/10 text-amber-300 border border-amber-500/20">
                                                {{ $user->jabatan ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Department -->
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-gray-300">
                                                {{ $user->department?->nama_departemen ?? 'N/A' }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ $user->kode_department ?? 'No code' }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="py-4 px-6">
                                        <div class="inline-flex items-center gap-2">
                                            @if($user->status_karyawan === 'AKTIF')
                                            <span class="status-dot-active"></span>
                                            <span class="status-active px-3 py-1.5 rounded-full text-sm font-medium">
                                                Active
                                            </span>
                                            @else
                                            <span class="status-dot-inactive"></span>
                                            <span class="status-inactive px-3 py-1.5 rounded-full text-sm font-medium">
                                                Inactive
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Edit Button -->
                                            <a href="{{ route('admin.master-departments.edit', ['master_department' => $user->id_user, 'type' => 'user']) }}" 
                                               class="p-2 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10 hover:from-blue-500/20 hover:to-cyan-600/20 transition-all duration-300"
                                               title="Edit User">
                                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            
                                            <!-- Delete Button -->
                                            <form action="{{ route('admin.master-departments.destroy', ['master_department' => $user->id_user, 'type' => 'user']) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirmDelete('{{ $user->display_name }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-2 rounded-lg bg-gradient-to-br from-red-500/10 to-pink-600/10 hover:from-red-500/20 hover:to-pink-600/20 transition-all duration-300"
                                                        title="Delete User">
                                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-12 px-6 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 mb-4">
                                                <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5l-5.5-5.5"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-semibold text-gray-300 mb-2">No Users Found</h3>
                                            <p class="text-gray-500 max-w-md">No users match your search criteria</p>
                                            <div class="mt-6">
                                                <a href="{{ route('admin.master-departments.create', ['type' => 'user']) }}" 
                                                   class="px-5 py-3 bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-500 hover:to-emerald-500 transition-all duration-300">
                                                    Add Your First User
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-400">
                            Showing <span class="font-medium text-white">{{ $users->firstItem() ?? 0 }}</span> 
                            to <span class="font-medium text-white">{{ $users->lastItem() ?? 0 }}</span> 
                            of <span class="font-medium text-white">{{ $users->total() }}</span> records
                        </div>
                        
                        <div class="flex items-center gap-2">
                            @if($users->onFirstPage())
                            <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            @else
                            <button onclick="changePage({{ $users->currentPage() - 1 }})" 
                                    class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            @endif
                            
                            @php
                                $current = $users->currentPage();
                                $last = $users->lastPage();
                                $start = max(1, $current - 2);
                                $end = min($last, $current + 2);
                                
                                if($start > 1) {
                                    echo '<button onclick="changePage(1)" class="pagination-btn px-4 py-2 rounded-lg">1</button>';
                                    if($start > 2) {
                                        echo '<span class="px-2 text-gray-500">...</span>';
                                    }
                                }
                                
                                for($i = $start; $i <= $end; $i++) {
                                    if($i == $current) {
                                        echo '<button class="pagination-btn active px-4 py-2 rounded-lg">' . $i . '</button>';
                                    } else {
                                        echo '<button onclick="changePage(' . $i . ')" class="pagination-btn px-4 py-2 rounded-lg">' . $i . '</button>';
                                    }
                                }
                                
                                if($end < $last) {
                                    if($end < $last - 1) {
                                        echo '<span class="px-2 text-gray-500">...</span>';
                                    }
                                    echo '<button onclick="changePage(' . $last . ')" class="pagination-btn px-4 py-2 rounded-lg">' . $last . '</button>';
                                }
                            @endphp
                            
                            @if($users->hasMorePages())
                            <button onclick="changePage({{ $users->currentPage() + 1 }})" 
                                    class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            @else
                            <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentCategory = '{{ $type }}';
            
            // ========================================
            // CONFIRM DELETE FUNCTION
            // ========================================
            window.confirmDelete = function(userName) {
                return confirm(`Are you sure you want to delete user "${userName}"?\nThis action cannot be undone.`);
            };
            
            // ========================================
            // CATEGORIES DROPDOWN
            // ========================================
            const categoriesBtn = document.getElementById('categoriesBtn');
            const categoriesDropdown = document.getElementById('categoriesDropdown');
            const categoriesArrow = document.getElementById('categoriesArrow');
            let isCategoriesOpen = false;
            
            if (categoriesBtn) {
                categoriesBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleCategoriesDropdown();
                });
                
                document.querySelectorAll('.category-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const category = this.dataset.category;
                        if (currentCategory !== category) {
                            changeCategory(category);
                        }
                        toggleCategoriesDropdown(false);
                    });
                });
                
                document.addEventListener('click', function(e) {
                    if (!categoriesBtn.contains(e.target) && !categoriesDropdown.contains(e.target)) {
                        toggleCategoriesDropdown(false);
                    }
                });
                
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && isCategoriesOpen) {
                        toggleCategoriesDropdown(false);
                    }
                });
            }
            
            function toggleCategoriesDropdown(show = null) {
                if (show === null) {
                    isCategoriesOpen = !isCategoriesOpen;
                } else {
                    isCategoriesOpen = show;
                }
                
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
            
            function changeCategory(category) {
                let url = new URL(window.location.href);
                url.searchParams.set('type', category);
                url.searchParams.set('page', '1');
                url.searchParams.delete('search');
                window.location.href = url.toString();
            }
            
            // ========================================
            // SEARCH FUNCTIONALITY
            // ========================================
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        performSearch(this.value);
                    }, 500);
                });
                
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        performSearch(this.value);
                    }
                });
            }
            
            function performSearch(searchTerm) {
                let url = new URL(window.location.href);
                if (searchTerm.trim()) {
                    url.searchParams.set('search', searchTerm);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }
            
            // ========================================
            // SORT FUNCTIONALITY
            // ========================================
            document.querySelectorAll('.sort-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const sortField = this.dataset.sort;
                    let sortOrder = 'asc';
                    
                    let url = new URL(window.location.href);
                    const currentSort = url.searchParams.get('sort');
                    const currentOrder = url.searchParams.get('order');
                    
                    if (currentSort === sortField && currentOrder === 'asc') {
                        sortOrder = 'desc';
                    }
                    
                    url.searchParams.set('sort', sortField);
                    url.searchParams.set('order', sortOrder);
                    window.location.href = url.toString();
                });
            });
            
            const sortFilter = document.getElementById('sortFilter');
            if (sortFilter) {
                sortFilter.addEventListener('change', function() {
                    let url = new URL(window.location.href);
                    const value = this.value;
                    
                    switch(value) {
                        case 'name':
                            if (currentCategory === 'department') {
                                url.searchParams.set('sort', 'nama_departemen');
                            } else if (currentCategory === 'jabatan') {
                                url.searchParams.set('sort', 'nama_jabatan');
                            } else if (currentCategory === 'user') {
                                url.searchParams.set('sort', 'nama_user');
                            }
                            url.searchParams.set('order', 'asc');
                            break;
                        case 'name_desc':
                            if (currentCategory === 'department') {
                                url.searchParams.set('sort', 'nama_departemen');
                            } else if (currentCategory === 'jabatan') {
                                url.searchParams.set('sort', 'nama_jabatan');
                            } else if (currentCategory === 'user') {
                                url.searchParams.set('sort', 'nama_user');
                            }
                            url.searchParams.set('order', 'desc');
                            break;
                    }
                    
                    window.location.href = url.toString();
                });
            }
            
            // ========================================
            // DEPARTMENT FILTER (USER VIEW ONLY)
            // ========================================
            const departmentFilter = document.getElementById('departmentFilter');
            if (departmentFilter) {
                departmentFilter.addEventListener('change', function() {
                    let url = new URL(window.location.href);
                    const deptCode = this.value;
                    
                    if (deptCode) {
                        url.searchParams.set('department', deptCode);
                    } else {
                        url.searchParams.delete('department');
                    }
                    
                    url.searchParams.set('page', '1');
                    window.location.href = url.toString();
                });
            }
            
            // ========================================
            // PAGINATION
            // ========================================
            window.changePage = function(page) {
                let url = new URL(window.location.href);
                url.searchParams.set('page', page);
                window.location.href = url.toString();
            };
            
            // ========================================
            // HIGHLIGHT SEARCH
            // ========================================
            const searchTerm = "{{ request('search', '') }}";
            if (searchTerm) {
                highlightSearch(searchTerm);
            }
            
            function highlightSearch(term) {
                const rows = document.querySelectorAll('tbody tr');
                const searchTermLower = term.toLowerCase();
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTermLower)) {
                        walkTextNodes(row, searchTermLower);
                    }
                });
            }
            
            function walkTextNodes(element, searchTerm) {
                const walker = document.createTreeWalker(
                    element,
                    NodeFilter.SHOW_TEXT,
                    null,
                    false
                );
                
                let node;
                const nodes = [];
                while (node = walker.nextNode()) {
                    nodes.push(node);
                }
                
                nodes.forEach(node => {
                    if (node.textContent.toLowerCase().includes(searchTerm)) {
                        const span = document.createElement('span');
                        span.className = 'search-highlight';
                        span.textContent = node.textContent;
                        node.parentNode.replaceChild(span, node);
                    }
                });
            }
            
            // ========================================
            // ANIMATE COUNTERS
            // ========================================
            animateCounters();
            
            function animateCounters() {
                const counters = document.querySelectorAll('.text-2xl.font-bold');
                counters.forEach(counter => {
                    const target = parseInt(counter.textContent);
                    if (isNaN(target)) return;
                    
                    const duration = 1000;
                    const step = target / (duration / 16);
                    let current = 0;
                    
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        counter.textContent = Math.floor(current);
                    }, 16);
                });
            }
        });
    </script>

</x-app-layout-dark>