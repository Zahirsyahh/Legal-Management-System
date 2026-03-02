{{-- resources/views/departments/active-reviews.blade.php --}}
@php
    // Determine department and routes based on current admin
    $department = Auth::user()->department;
    $routePrefix = match($department->code) {
        'FIN' => 'finance',
        'ACC' => 'accounting',
        'TAX' => 'tax',
        default => 'finance'
    };
    
    // Department colors with extended palette
    $deptColors = [
        'FIN' => [
            'bg' => 'from-blue-500/20 to-cyan-600/20', 
            'text' => 'text-blue-400', 
            'border' => 'border-blue-500/20',
            'gradient' => 'from-blue-400 to-cyan-400',
            'name' => 'Finance',
            'status_color' => 'bg-blue-500/20 text-blue-300',
            'hover' => 'hover:border-blue-400/30',
            'ring' => 'ring-blue-400/20',
            'badge' => 'bg-blue-500/10 text-blue-400 border-blue-500/30'
        ],
        'ACC' => [
            'bg' => 'from-indigo-500/20 to-purple-600/20', 
            'text' => 'text-indigo-400', 
            'border' => 'border-indigo-500/20',
            'gradient' => 'from-indigo-400 to-purple-400',
            'name' => 'Accounting',
            'status_color' => 'bg-indigo-500/20 text-indigo-300',
            'hover' => 'hover:border-indigo-400/30',
            'ring' => 'ring-indigo-400/20',
            'badge' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30'
        ],
        'TAX' => [
            'bg' => 'from-red-500/20 to-pink-600/20', 
            'text' => 'text-red-400', 
            'border' => 'border-red-500/20',
            'gradient' => 'from-red-400 to-pink-400',
            'name' => 'Tax',
            'status_color' => 'bg-red-500/20 text-red-300',
            'hover' => 'hover:border-red-400/30',
            'ring' => 'ring-red-400/20',
            'badge' => 'bg-red-500/10 text-red-400 border-red-500/30'
        ],
    ];
    
    $currentColor = $deptColors[$department->code] ?? $deptColors['FIN'];
    $pageTitle = $currentColor['name'] . ' Active Reviews';
    
    // Calculate statistics
    $totalReviews = $reviews->count();
    $assignedCount = $reviews->where('status', 'assigned')->count();
    $inProgressCount = $reviews->where('status', 'under_review')->count();
    $pendingFeedbackCount = $reviews->where('status', 'pending_feedback')->count();
    $overdueReviews = $reviews->filter(function($review) {
        return $review->due_date && \Carbon\Carbon::parse($review->due_date)->isPast();
    })->count();
    
    // Calculate average review time
    $avgReviewTime = $reviews->filter(function($review) {
        return $review->assigned_at && $review->completed_at;
    })->avg(function($review) {
        return $review->assigned_at->diffInHours($review->completed_at);
    });
@endphp

<x-app-layout-dark title="{{ $pageTitle }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header with Breadcrumb -->
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="{{ route($routePrefix . '-admin.dashboard') }}" class="hover:text-white transition-colors">{{ $currentColor['name'] }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-white">Active Reviews</span>
            </div>
            
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <!-- Animated Icon -->
                    <div class="relative">
                        <div class="p-3 rounded-xl bg-gradient-to-br {{ $currentColor['bg'] }} border {{ $currentColor['border'] }} animate-pulse-slow">
                            <svg class="w-6 h-6 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        @if($totalReviews > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-gray-900"></span>
                        @endif
                    </div>
                    
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            {{ $pageTitle }}
                            <span class="text-sm font-normal px-3 py-1 rounded-full {{ $currentColor['badge'] }}">
                                {{ now()->format('l, d M Y') }}
                            </span>
                        </h1>
                        <p class="text-gray-400 mt-1">Contracts currently under review by {{ strtolower($currentColor['name']) }} department staff</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Quick Stats Pill -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full {{ $currentColor['bg'] }} border {{ $currentColor['border'] }} group hover:scale-105 transition-transform cursor-default">
                        <svg class="w-4 h-4 {{ $currentColor['text'] }} animate-bounce-subtle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium {{ $currentColor['text'] }}">
                            {{ $totalReviews }} Active {{ Str::plural('Review', $totalReviews) }}
                        </span>
                    </div>
                    
                    <!-- Back Button with Tooltip -->
                    <div class="relative group">
                        <a href="{{ route($routePrefix . '-admin.dashboard') }}" 
                           class="flex items-center gap-2 text-gray-400 hover:text-gray-300 transition-colors action-btn p-2 rounded-lg hover:bg-gray-800/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span class="text-sm font-medium">Back to Dashboard</span>
                        </a>
                        <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-xs text-white rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            Return to main dashboard
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <!-- Total Active -->
                <div class="glass-card p-4 rounded-xl hover:shadow-lg hover:shadow-{{ $department->code == 'FIN' ? 'blue' : ($department->code == 'ACC' ? 'indigo' : 'red') }}-500/5 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg {{ $currentColor['bg'] }} group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Total Active</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-2xl font-bold text-white">{{ $totalReviews }}</p>
                                <span class="text-xs {{ $currentColor['text'] }}">reviews</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 h-1 w-full bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r {{ $currentColor['gradient'] }} rounded-full" style="width: 100%"></div>
                    </div>
                </div>
                
                <!-- Assigned -->
                <div class="glass-card p-4 rounded-xl hover:shadow-lg hover:shadow-yellow-500/5 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-yellow-500/20 to-orange-600/20 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Assigned</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-2xl font-bold text-white">{{ $assignedCount }}</p>
                                <span class="text-xs text-yellow-400">{{ $assignedCount > 0 ? round(($assignedCount/$totalReviews)*100) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- In Progress -->
                <div class="glass-card p-4 rounded-xl hover:shadow-lg hover:shadow-green-500/5 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/20 to-emerald-600/20 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">In Progress</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-2xl font-bold text-white">{{ $inProgressCount }}</p>
                                <span class="text-xs text-green-400">{{ $inProgressCount > 0 ? round(($inProgressCount/$totalReviews)*100) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Feedback -->
                <div class="glass-card p-4 rounded-xl hover:shadow-lg hover:shadow-purple-500/5 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-purple-500/20 to-pink-600/20 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Pending Feedback</p>
                            <p class="text-2xl font-bold text-white">{{ $pendingFeedbackCount }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Overdue -->
                <div class="glass-card p-4 rounded-xl hover:shadow-lg hover:shadow-red-500/5 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-red-500/20 to-pink-600/20 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Overdue</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-2xl font-bold text-white">{{ $overdueReviews }}</p>
                                @if($overdueReviews > 0)
                                <span class="text-xs text-red-400 animate-pulse">!</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            @if($totalReviews > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
                <div class="glass-card p-4 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400">Average Review Time</span>
                        <span class="text-xs {{ $currentColor['text'] }}">{{ $avgReviewTime ? round($avgReviewTime, 1) . ' hours' : 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1 h-2 bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r {{ $currentColor['gradient'] }} rounded-full" style="width: {{ $avgReviewTime ? min(($avgReviewTime/48)*100, 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="glass-card p-4 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400">Completion Rate</span>
                        <span class="text-xs text-green-400">{{ $totalReviews > 0 ? round(($inProgressCount + $pendingFeedbackCount)/$totalReviews * 100) : 0 }}%</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1 h-2 bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-green-400 to-emerald-400 rounded-full" style="width: {{ $totalReviews > 0 ? ($inProgressCount + $pendingFeedbackCount)/$totalReviews * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="glass-card p-4 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400">Staff Workload</span>
                        <span class="text-xs {{ $currentColor['text'] }}">{{ $assignedCount }} assigned</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <div class="flex-1 flex gap-1">
                            @php
                                $staffCount = $reviews->pluck('assignedStaff.id')->unique()->count();
                                $avgPerStaff = $staffCount > 0 ? round($assignedCount / $staffCount, 1) : 0;
                            @endphp
                            <span class="text-sm text-gray-300">{{ $avgPerStaff }} per staff</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Enhanced Filters -->
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Status Filter with Icons -->
                    <div class="relative">
                        <select id="statusFilter" class="appearance-none bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pl-10 pr-8 text-white focus:outline-none focus:ring-2 focus:ring-{{ $department->code == 'FIN' ? 'blue' : ($department->code == 'ACC' ? 'indigo' : 'red') }}-500 focus:border-transparent min-w-[160px]">
                            <option value="all">📋 All Status</option>
                            <option value="assigned">👤 Assigned</option>
                            <option value="under_review">🔄 In Progress</option>
                            <option value="pending_feedback">💬 Pending Feedback</option>
                            <option value="overdue">⚠️ Overdue</option>
                            <option value="completed">✅ Completed</option>
                        </select>
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Date Filter -->
                    <div class="relative">
                        <select id="dateFilter" class="appearance-none bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pl-10 pr-8 text-white focus:outline-none focus:ring-2 focus:ring-{{ $department->code == 'FIN' ? 'blue' : ($department->code == 'ACC' ? 'indigo' : 'red') }}-500 focus:border-transparent min-w-[160px]">
                            <option value="all">📅 All Dates</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="overdue">Overdue</option>
                        </select>
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Staff Filter -->
                    <div class="relative">
                        <select id="staffFilter" class="appearance-none bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pl-10 pr-8 text-white focus:outline-none focus:ring-2 focus:ring-{{ $department->code == 'FIN' ? 'blue' : ($department->code == 'ACC' ? 'indigo' : 'red') }}-500 focus:border-transparent min-w-[180px]">
                            <option value="all">👥 All Staff</option>
                            @php
                                $uniqueStaff = $reviews->pluck('assignedStaff')->filter()->unique('id');
                            @endphp
                            @foreach($uniqueStaff as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Search Input with Clear Button -->
                    <div class="relative">
                        <input type="text" 
                               id="searchInput" 
                               placeholder="Search contracts..." 
                               class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pl-10 pr-10 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-{{ $department->code == 'FIN' ? 'blue' : ($department->code == 'ACC' ? 'indigo' : 'red') }}-500 focus:border-transparent w-64">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <button id="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Sort Options -->
                    <div class="relative">
                        <select id="sortFilter" class="appearance-none bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pl-10 pr-8 text-white focus:outline-none focus:ring-2 focus:ring-{{ $department->code == 'FIN' ? 'blue' : ($department->code == 'ACC' ? 'indigo' : 'red') }}-500 focus:border-transparent min-w-[160px]">
                            <option value="due_asc">📅 Due Date (Earliest)</option>
                            <option value="due_desc">📅 Due Date (Latest)</option>
                            <option value="assigned_desc">🕒 Recently Assigned</option>
                            <option value="assigned_asc">🕒 Oldest Assigned</option>
                            <option value="title_asc">📄 Title A-Z</option>
                            <option value="title_desc">📄 Title Z-A</option>
                        </select>
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- View Toggle -->
                    <div class="flex items-center bg-gray-800/50 rounded-lg border border-gray-700 p-1">
                        <button id="listViewBtn" class="p-2 rounded {{ $currentColor['bg'] }} {{ $currentColor['text'] }}" title="List View">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <button id="gridViewBtn" class="p-2 rounded text-gray-400 hover:text-white" title="Grid View">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Export Options -->
                    <div class="relative group">
                        <button id="exportBtn" class="action-btn p-2 rounded-lg bg-gray-800/50 hover:bg-gray-700/50 border border-gray-700/50">
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-xl border border-gray-700 hidden group-hover:block z-10">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Export as CSV</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Export as PDF</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Print View</a>
                        </div>
                    </div>
                    
                    <!-- Refresh Button with Animation -->
                    <button id="refreshBtn" class="action-btn p-2 rounded-lg bg-gray-800/50 hover:bg-gray-700/50 border border-gray-700/50 group">
                        <svg class="w-5 h-5 text-gray-300 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Active Filters Display -->
            <div id="activeFilters" class="flex flex-wrap items-center gap-2 mt-3">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Main Content with View Toggle -->
        <div class="space-y-6" id="mainContent">
            <!-- Active Reviews List (Default View) -->
            <div id="listView" class="glass-card rounded-2xl p-6 border {{ $currentColor['border'] }}">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold {{ $currentColor['text'] }} flex items-center gap-2">
                            Active Reviews
                            <span class="text-sm font-normal px-2 py-1 rounded-full bg-gray-800 text-gray-300">{{ $totalReviews }}</span>
                        </h2>
                        <p class="text-sm text-gray-400 mt-1">Contracts currently under review in {{ $currentColor['name'] }} department</p>
                    </div>
                    <div class="text-sm text-gray-400 bg-gray-800/50 px-3 py-1 rounded-full">
                        Showing <span class="font-medium text-white" id="visibleCount">{{ $totalReviews }}</span> of <span class="font-medium text-white">{{ $totalReviews }}</span> reviews
                    </div>
                </div>
                
                <div class="space-y-4" id="reviewsList">
                    @forelse($reviews as $review)
                    @php
                        // Determine review status colors
                        $statusColors = [
                            'assigned' => ['bg' => 'bg-yellow-500/20', 'text' => 'text-yellow-300', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                            'under_review' => ['bg' => 'bg-blue-500/20', 'text' => 'text-blue-300', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'pending_feedback' => ['bg' => 'bg-orange-500/20', 'text' => 'text-orange-300', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                            'completed' => ['bg' => 'bg-green-500/20', 'text' => 'text-green-300', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'overdue' => ['bg' => 'bg-red-500/20', 'text' => 'text-red-300', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ];
                        
                        $currentStatusColor = $statusColors[$review->status] ?? $statusColors['assigned'];
                        
                        // Check if review is overdue
                        $isOverdue = false;
                        if ($review->due_date) {
                            $dueDate = \Carbon\Carbon::parse($review->due_date);
                            $isOverdue = $dueDate->isPast();
                            $daysOverdue = $isOverdue ? $dueDate->diffInDays(now()) : 0;
                        }
                        
                        // Calculate progress based on time if not set
                        $progress = $review->progress_percentage ?? 0;
                        if ($review->assigned_at && $review->due_date && !$review->progress_percentage) {
                            $totalDays = \Carbon\Carbon::parse($review->assigned_at)->diffInDays(\Carbon\Carbon::parse($review->due_date));
                            $daysPassed = \Carbon\Carbon::parse($review->assigned_at)->diffInDays(now());
                            $progress = $totalDays > 0 ? min(round(($daysPassed / $totalDays) * 100), 100) : 0;
                        }
                        
                        // Get priority based on due date
                        $priority = 'normal';
                        $priorityColor = 'text-gray-400';
                        if ($review->due_date) {
                            $daysUntilDue = now()->diffInDays(\Carbon\Carbon::parse($review->due_date), false);
                            if ($daysUntilDue <= 2) {
                                $priority = 'high';
                                $priorityColor = 'text-red-400';
                            } elseif ($daysUntilDue <= 5) {
                                $priority = 'medium';
                                $priorityColor = 'text-yellow-400';
                            }
                        }
                    @endphp
                    
                    <div class="review-card group" 
                         data-id="{{ $review->id }}"
                         data-status="{{ $isOverdue ? 'overdue' : $review->status }}" 
                         data-duedate="{{ $review->due_date ?? '' }}"
                         data-assigned="{{ $review->assigned_at ?? '' }}"
                         data-contract-number="{{ $review->contract->contract_number ?? '' }}"
                         data-title="{{ $review->contract->title ?? '' }}"
                         data-assigned-staff="{{ $review->assignedStaff->name ?? '' }}"
                         data-staff-id="{{ $review->assignedStaff->id ?? '' }}"
                         data-priority="{{ $priority }}">
                        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between p-5 bg-white/5 rounded-xl hover:bg-white/10 transition-all border border-gray-700/50 hover:border-gray-600/50 relative overflow-hidden">
                            <!-- Priority Indicator -->
                            @if($priority != 'normal')
                            <div class="absolute top-0 right-0 w-16 h-16 overflow-hidden">
                                <div class="absolute top-0 right-0 transform translate-x-8 -translate-y-8 rotate-45 w-16 h-16 {{ $priority == 'high' ? 'bg-red-500/20' : 'bg-yellow-500/20' }}"></div>
                            </div>
                            @endif
                            
                            <div class="flex-1 min-w-0 w-full lg:w-auto">
                                <div class="flex items-start space-x-4">
                                    <!-- Animated Icon -->
                                    <div class="flex-shrink-0 relative">
                                        <div class="w-12 h-12 rounded-xl {{ $currentColor['bg'] }} flex items-center justify-center border {{ $currentColor['border'] }} group-hover:scale-110 transition-transform">
                                            <svg class="w-6 h-6 {{ $currentColor['text'] }} animate-pulse-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $currentStatusColor['icon'] }}" />
                                            </svg>
                                        </div>
                                        @if($isOverdue)
                                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping"></span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <!-- Document Title with Priority Badge -->
                                        @if ($review->contract)
                                            <div class="mb-3">
                                                <div class="flex items-center gap-3">
                                                    <h3 class="text-xl font-bold text-white group-hover:text-{{ $department->code == 'FIN' ? 'blue' : ($department->code == 'ACC' ? 'indigo' : 'red') }}-300 transition-colors truncate" 
                                                        title="{{ $review->contract->title }}">
                                                        {{ $review->contract->title }}
                                                    </h3>
                                                    @if($priority != 'normal')
                                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $priority == 'high' ? 'bg-red-500/20 text-red-300' : 'bg-yellow-500/20 text-yellow-300' }} animate-pulse">
                                                        {{ ucfirst($priority) }} Priority
                                                    </span>
                                                    @endif
                                                </div>
                                                
                                                <!-- Enhanced Contract Info Row -->
                                                <div class="flex flex-wrap items-center gap-3 mt-2">
                                                    <!-- Contract Number with Copy -->
                                                    @if($review->contract->contract_number)
                                                        <div class="flex items-center gap-1.5 text-sm text-gray-400 group/copy">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                            </svg>
                                                            <span class="font-medium">{{ $review->contract->contract_number }}</span>
                                                            <button class="opacity-0 group-hover/copy:opacity-100 transition-opacity" onclick="copyToClipboard('{{ $review->contract->contract_number }}')">
                                                                <svg class="w-3.5 h-3.5 text-gray-500 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Contract Type with Color -->
                                                    @if($review->contract->contract_type)
                                                        @php
                                                            $typeColors = [
                                                                'service' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                                                                'procurement' => 'bg-green-500/20 text-green-300 border-green-500/30',
                                                                'sales' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                                                                'partnership' => 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30',
                                                                'employment' => 'bg-pink-500/20 text-pink-300 border-pink-500/30',
                                                                'confidential' => 'bg-red-500/20 text-red-300 border-red-500/30',
                                                            ];
                                                            $typeClass = $typeColors[strtolower($review->contract->contract_type)] ?? 'bg-gray-500/20 text-gray-300 border-gray-500/30';
                                                        @endphp
                                                        <span class="px-2.5 py-0.5 text-xs rounded-full {{ $typeClass }} border">
                                                            {{ $review->contract->contract_type }}
                                                        </span>
                                                    @endif
                                                    
                                                    <!-- Counterparty with Company Icon -->
                                                    @if($review->contract->counterparty_name)
                                                        <div class="flex items-center gap-1.5 text-sm text-gray-400">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                            </svg>
                                                            <span class="truncate max-w-[200px]" title="{{ $review->contract->counterparty_name }}">{{ $review->contract->counterparty_name }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <h3 class="text-xl font-bold text-red-400 italic flex items-center gap-2">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    Contract Deleted
                                                </h3>
                                            </div>
                                        @endif
                                        
                                        <!-- Enhanced Status and Dates Row -->
                                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4">
                                            <!-- Left: Status Badge and Dates -->
                                            <div class="flex flex-wrap items-center gap-3">
                                                <!-- Status Badge with Icon -->
                                                <span class="px-3 py-1.5 text-xs rounded-full {{ $currentStatusColor['bg'] }} {{ $currentStatusColor['text'] }} flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $currentStatusColor['icon'] }}" />
                                                    </svg>
                                                    @if($isOverdue)
                                                        Overdue ({{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }})
                                                    @else
                                                        {{ ucfirst(str_replace('_', ' ', $review->status)) }}
                                                    @endif
                                                </span>
                                                
                                                <!-- Review Due Date with Countdown -->
                                                @if($review->due_date)
                                                    @php
                                                        $dueDateCarbon = \Carbon\Carbon::parse($review->due_date);
                                                        $daysUntilDue = now()->diffInDays($dueDateCarbon, false);
                                                    @endphp
                                                    <div class="flex items-center gap-1.5 text-sm {{ $isOverdue ? 'text-red-400' : ($daysUntilDue <= 2 ? 'text-yellow-400' : 'text-gray-400') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span title="Review Due Date">{{ $dueDateCarbon->format('M d, Y') }}</span>
                                                        @if(!$isOverdue && $daysUntilDue > 0)
                                                            <span class="text-xs">({{ $daysUntilDue }} days left)</span>
                                                        @elseif($isOverdue)
                                                            <span class="text-xs">(overdue)</span>
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                                <!-- Assigned Staff with Avatar -->
                                                @if($review->assignedStaff)
                                                    <div class="flex items-center gap-1.5 text-sm text-gray-400">
                                                        <div class="w-5 h-5 rounded-full bg-gradient-to-br {{ $currentColor['bg'] }} flex items-center justify-center">
                                                            <span class="text-xs font-medium {{ $currentColor['text'] }}">
                                                                {{ strtoupper(substr($review->assignedStaff->name, 0, 1)) }}
                                                            </span>
                                                        </div>
                                                        <span class="{{ $currentColor['text'] }}">{{ $review->assignedStaff->name }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Right: Time Information -->
                                            <div class="flex items-center gap-3 text-sm text-gray-400">
                                                @if($review->assigned_at)
                                                    <div class="flex items-center gap-1.5" title="Assigned at">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span>{{ $review->assigned_at->diffForHumans() }}</span>
                                                    </div>
                                                @endif
                                                
                                                @if($review->contract && $review->contract->contract_value)
                                                    <div class="flex items-center gap-1.5 text-green-400">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span>{{ $review->contract->currency ?? 'IDR' }} {{ number_format($review->contract->contract_value/1000000, 1) }}M</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if ($review->contract)
                                            <!-- Enhanced Detailed Information Grid -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4 text-sm">
                                                <!-- Creator with Tooltip -->
                                                <div class="flex items-center gap-2 group/creator">
                                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    <div class="truncate">
                                                        <div class="text-xs text-gray-500">Creator</div>
                                                        <div class="text-gray-300 flex items-center gap-1">
                                                            {{ $review->contract->user->name ?? 'Unknown' }}
                                                            @if($review->contract->user)
                                                                <span class="opacity-0 group-hover/creator:opacity-100 transition-opacity text-xs text-gray-500">({{ $review->contract->user->email }})</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Contract Value with Trend -->
                                                @if($review->contract->contract_value)
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div class="truncate">
                                                        <div class="text-xs text-gray-500">Value</div>
                                                        <div class="text-green-300 font-medium">{{ $review->contract->currency ?? 'IDR' }} {{ number_format($review->contract->contract_value, 0) }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <!-- Contract Period with Duration -->
                                                @if($review->contract->effective_date || $review->contract->expiry_date)
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <div class="truncate">
                                                        <div class="text-xs text-gray-500">Contract Period</div>
                                                        <div class="text-blue-300">
                                                            @if($review->contract->effective_date && $review->contract->expiry_date)
                                                                @php
                                                                    $start = \Carbon\Carbon::parse($review->contract->effective_date);
                                                                    $end = \Carbon\Carbon::parse($review->contract->expiry_date);
                                                                    $duration = $start->diffInMonths($end);
                                                                @endphp
                                                                {{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}
                                                                <span class="text-xs text-gray-500">({{ $duration }} months)</span>
                                                            @elseif($review->contract->effective_date)
                                                                From {{ \Carbon\Carbon::parse($review->contract->effective_date)->format('M d, Y') }}
                                                            @elseif($review->contract->expiry_date)
                                                                Until {{ \Carbon\Carbon::parse($review->contract->expiry_date)->format('M d, Y') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <!-- Department Specific Fields -->
                                                @if($department->code === 'TAX' && $review->contract->tax_implications)
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <div class="truncate">
                                                        <div class="text-xs text-gray-500">Tax Review</div>
                                                        <div class="text-purple-300 flex items-center gap-1">
                                                            Required
                                                            <span class="text-xs text-gray-500">({{ $review->contract->tax_implications }})</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                @if($department->code === 'FIN' && $review->contract->budget_code)
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 12h18M3 18h18" />
                                                    </svg>
                                                    <div class="truncate">
                                                        <div class="text-xs text-gray-500">Budget Code</div>
                                                        <div class="text-cyan-300">{{ $review->contract->budget_code }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Enhanced Progress Bar -->
                                            <div class="mt-4">
                                                <div class="flex items-center justify-between mb-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs text-gray-400">Review Progress</span>
                                                        @if($progress >= 75)
                                                            <span class="text-xs bg-green-500/20 text-green-300 px-2 py-0.5 rounded-full">Almost Done</span>
                                                        @elseif($progress <= 25)
                                                            <span class="text-xs bg-yellow-500/20 text-yellow-300 px-2 py-0.5 rounded-full">Just Started</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs font-medium {{ $currentColor['text'] }}">{{ $progress }}%</span>
                                                </div>
                                                <div class="relative">
                                                    <div class="w-full bg-gray-700 rounded-full h-2.5 overflow-hidden">
                                                        <div class="bg-gradient-to-r {{ $currentColor['gradient'] }} h-2.5 rounded-full transition-all duration-700 ease-out" 
                                                             style="width: {{ $progress }}%"></div>
                                                    </div>
                                                    <!-- Milestone Markers -->
                                                    <div class="absolute top-0 left-0 w-full flex justify-between px-1">
                                                        <div class="w-1 h-2.5 bg-gray-600/50 rounded-full"></div>
                                                        <div class="w-1 h-2.5 bg-gray-600/50 rounded-full"></div>
                                                        <div class="w-1 h-2.5 bg-gray-600/50 rounded-full"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Review Comments Preview -->
                                            @if($review->comments && $review->comments->count() > 0)
                                            <div class="mt-3 text-sm">
                                                <div class="flex items-start gap-2 p-2 bg-gray-800/30 rounded-lg border border-gray-700/50">
                                                    <svg class="w-4 h-4 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                                    </svg>
                                                    <div>
                                                        <span class="text-gray-400">{{ $review->comments->last()->user->name }}:</span>
                                                        <span class="text-gray-300">{{ Str::limit($review->comments->last->content, 100) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @else
                                            <div class="text-red-400 italic mb-4">
                                                Contract data not available. This contract may have been deleted.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Enhanced Action Buttons -->
                            <div class="flex items-center gap-3 mt-4 lg:mt-0 lg:ml-6">
                                @if ($review->contract)
                                    <a href="{{ route('contracts.show', $review->contract) }}" 
                                       class="px-4 py-2 text-sm bg-gray-800/50 hover:bg-gray-700/50 text-gray-300 rounded-lg transition-colors flex items-center gap-2 border border-gray-700/50 group/btn">
                                        <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>View Details</span>
                                    </a>
                                @endif
                                
                                @if($review->assignedStaff)
                                    <a href="mailto:{{ $review->assignedStaff->email }}" 
                                       class="px-4 py-2 text-sm {{ $currentColor['bg'] }} hover:opacity-90 text-white rounded-lg transition-colors flex items-center gap-2 border {{ $currentColor['border'] }} group/btn relative">
                                        <svg class="w-4 h-4 group-hover/btn:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span>Contact</span>
                                        <span class="absolute -top-1 -right-1 w-2 h-2 bg-green-500 rounded-full"></span>
                                    </a>
                                @endif
                                
                                <!-- Quick Actions Dropdown -->
                                <div class="relative group/dropdown">
                                    <button class="p-2 rounded-lg bg-gray-800/50 hover:bg-gray-700/50 text-gray-400 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-xl border border-gray-700 hidden group-hover/dropdown:block z-10">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Add Comment</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Reassign Staff</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Set Reminder</a>
                                        <hr class="border-gray-700">
                                        <a href="#" class="block px-4 py-2 text-sm text-red-400 hover:bg-gray-700">Mark as Urgent</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12 border-2 border-dashed border-gray-700/50 rounded-xl bg-gray-800/20">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full {{ $currentColor['bg'] }} mb-6 animate-bounce-subtle">
                            <svg class="w-8 h-8 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-medium text-gray-300 mb-2">No Active Reviews</h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">
                            There are no contracts currently under review in {{ strtolower($currentColor['name']) }} department. 
                            Assign contracts to staff to start the review process.
                        </p>
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route($routePrefix . '-admin.dashboard') }}" 
                               class="px-4 py-2 text-sm {{ $currentColor['text'] }} hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Dashboard
                            </a>
                            <span class="text-gray-500">•</span>
                            <a href="{{ route($routePrefix . '-admin.pending') }}" 
                               class="px-4 py-2 text-sm text-yellow-400 hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                View Pending Assignments
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>
                
                <!-- Bulk Actions -->
                @if($totalReviews > 0)
                <div class="mt-6 pt-4 border-t border-gray-700/50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="selectAll" class="rounded bg-gray-800 border-gray-600 text-{{ $department->code == 'FIN' ? 'blue' : ($department->code == 'ACC' ? 'indigo' : 'red') }}-500 focus:ring-0">
                            <span class="text-sm text-gray-300">Select All</span>
                        </label>
                        
                        <select id="bulkAction" class="text-sm bg-gray-800/50 border border-gray-700 rounded-lg px-3 py-1.5 text-gray-300">
                            <option value="">Bulk Actions</option>
                            <option value="assign">Reassign Selected</option>
                            <option value="priority">Mark as Priority</option>
                            <option value="export">Export Selected</option>
                            <option value="reminder">Set Reminder</option>
                        </select>
                        
                        <button id="applyBulkAction" class="text-sm {{ $currentColor['text'] }} hover:underline">Apply</button>
                    </div>
                    
                    <!-- Pagination -->
                    @if($reviews->hasPages())
                        {{ $reviews->links() }}
                    @endif
                </div>
                @endif
            </div>
            
            <!-- Grid View (Hidden by Default) -->
            <div id="gridView" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Grid items will be populated by JavaScript -->
            </div>

            <!-- Empty State Help Card (only show when empty) -->
            @if($reviews->isEmpty())
            <div class="glass-card rounded-2xl p-8 border {{ $currentColor['border'] }}">
                <div class="max-w-3xl mx-auto text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full {{ $currentColor['bg'] }} mb-6">
                        <svg class="w-10 h-10 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-white mb-4">How Active Reviews Work</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="space-y-3">
                            <div class="p-3 rounded-lg bg-gray-800/50 inline-block">
                                <svg class="w-6 h-6 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-white">Assign Contracts</h4>
                            <p class="text-sm text-gray-400">
                                Assign contracts to qualified staff members from pending assignments.
                            </p>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="p-3 rounded-lg bg-gray-800/50 inline-block">
                                <svg class="w-6 h-6 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-white">Staff Review</h4>
                            <p class="text-sm text-gray-400">
                                Staff members review assigned contracts and provide feedback or approvals.
                            </p>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="p-3 rounded-lg bg-gray-800/50 inline-block">
                                <svg class="w-6 h-6 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-white">Monitor Progress</h4>
                            <p class="text-sm text-gray-400">
                                Track review progress, provide guidance, and ensure timely completion.
                            </p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-gray-800/30 rounded-xl border border-gray-700/50">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r {{ $currentColor['bg'] }} flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-left">
                                <h4 class="font-semibold text-white mb-2">What to do when there are no active reviews?</h4>
                                <ul class="text-gray-400 space-y-2 text-sm">
                                    <li class="flex items-start gap-2">
                                        <svg class="w-4 h-4 {{ $currentColor['text'] }} mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Check pending assignments to assign contracts to staff members</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-4 h-4 {{ $currentColor['text'] }} mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Follow up with staff who have overdue or stalled reviews</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <svg class="w-4 h-4 {{ $currentColor['text'] }} mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Review completed contracts and provide feedback to staff</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📋 Active Reviews page loaded for {{ $currentColor["name"] }} department');
            
            // DOM Elements
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');
            const staffFilter = document.getElementById('staffFilter');
            const searchInput = document.getElementById('searchInput');
            const sortFilter = document.getElementById('sortFilter');
            const reviewsList = document.getElementById('reviewsList');
            const reviewCards = document.querySelectorAll('.review-card');
            const refreshBtn = document.getElementById('refreshBtn');
            const clearSearch = document.getElementById('clearSearch');
            const listViewBtn = document.getElementById('listViewBtn');
            const gridViewBtn = document.getElementById('gridViewBtn');
            const listView = document.getElementById('listView');
            const gridView = document.getElementById('gridView');
            const selectAll = document.getElementById('selectAll');
            const visibleCountSpan = document.getElementById('visibleCount');
            const activeFiltersDiv = document.getElementById('activeFilters');
            
            // State
            let currentView = 'list';
            let activeFilters = {};
            
            // Initialize grid view if exists
            function initializeGridView() {
                if (gridView) {
                    gridView.innerHTML = '';
                    reviewCards.forEach(card => {
                        const gridCard = card.cloneNode(true);
                        gridCard.classList.add('grid-card');
                        gridCard.querySelector('.flex-col').classList.add('flex-col', 'h-full');
                        gridView.appendChild(gridCard);
                    });
                }
            }
            
            // Filter reviews based on all criteria
            function filterReviews() {
                const statusValue = statusFilter.value;
                const dateValue = dateFilter.value;
                const staffValue = staffFilter.value;
                const searchValue = searchInput.value.toLowerCase();
                const sortValue = sortFilter.value;
                
                let visibleCount = 0;
                let filteredCards = [];
                
                reviewCards.forEach(card => {
                    const status = card.getAttribute('data-status') || 'assigned';
                    const dueDate = card.getAttribute('data-duedate');
                    const assignedDate = card.getAttribute('data-assigned');
                    const staffId = card.getAttribute('data-staff-id');
                    const contractNumber = card.getAttribute('data-contract-number') || '';
                    const title = card.getAttribute('data-title') || '';
                    const assignedStaff = card.getAttribute('data-assigned-staff') || '';
                    const priority = card.getAttribute('data-priority') || 'normal';
                    
                    // Status filter
                    let statusMatch = statusValue === 'all' || status === statusValue;
                    
                    // Date filter
                    let dateMatch = true;
                    if (dateValue !== 'all' && dueDate) {
                        const due = new Date(dueDate);
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        
                        switch(dateValue) {
                            case 'today':
                                dateMatch = due.toDateString() === today.toDateString();
                                break;
                            case 'week':
                                const weekEnd = new Date(today);
                                weekEnd.setDate(today.getDate() + 7);
                                dateMatch = due >= today && due <= weekEnd;
                                break;
                            case 'month':
                                const monthEnd = new Date(today);
                                monthEnd.setMonth(today.getMonth() + 1);
                                dateMatch = due >= today && due <= monthEnd;
                                break;
                            case 'overdue':
                                dateMatch = status === 'overdue';
                                break;
                        }
                    }
                    
                    // Staff filter
                    let staffMatch = staffValue === 'all' || staffId === staffValue;
                    
                    // Search filter
                    const searchMatch = searchValue === '' || 
                        contractNumber.toLowerCase().includes(searchValue) ||
                        title.toLowerCase().includes(searchValue) ||
                        assignedStaff.toLowerCase().includes(searchValue);
                    
                    if (statusMatch && dateMatch && staffMatch && searchMatch) {
                        card.style.display = 'block';
                        visibleCount++;
                        filteredCards.push(card);
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Update visible count
                if (visibleCountSpan) {
                    visibleCountSpan.textContent = visibleCount;
                }
                
                // Apply sorting
                if (filteredCards.length > 0) {
                    applySorting(filteredCards, sortValue);
                }
                
                // Update active filters display
                updateActiveFilters();
                
                return filteredCards;
            }
            
            // Apply sorting to filtered cards
            function applySorting(cards, sortValue) {
                const container = reviewsList;
                const sortedCards = [...cards].sort((a, b) => {
                    switch(sortValue) {
                        case 'due_asc':
                            return (a.dataset.duedate || '').localeCompare(b.dataset.duedate || '');
                        case 'due_desc':
                            return (b.dataset.duedate || '').localeCompare(a.dataset.duedate || '');
                        case 'assigned_desc':
                            return (b.dataset.assigned || '').localeCompare(a.dataset.assigned || '');
                        case 'assigned_asc':
                            return (a.dataset.assigned || '').localeCompare(b.dataset.assigned || '');
                        case 'title_asc':
                            return (a.dataset.title || '').localeCompare(b.dataset.title || '');
                        case 'title_desc':
                            return (b.dataset.title || '').localeCompare(a.dataset.title || '');
                        default:
                            return 0;
                    }
                });
                
                // Reorder DOM
                sortedCards.forEach(card => container.appendChild(card));
            }
            
            // Update active filters display
            function updateActiveFilters() {
                if (!activeFiltersDiv) return;
                
                activeFilters = {};
                
                if (statusFilter.value !== 'all') {
                    activeFilters.status = statusFilter.options[statusFilter.selectedIndex].text;
                }
                if (dateFilter.value !== 'all') {
                    activeFilters.date = dateFilter.options[dateFilter.selectedIndex].text;
                }
                if (staffFilter.value !== 'all') {
                    activeFilters.staff = staffFilter.options[staffFilter.selectedIndex].text;
                }
                if (searchInput.value) {
                    activeFilters.search = `"${searchInput.value}"`;
                }
                
                let html = '';
                for (const [key, value] of Object.entries(activeFilters)) {
                    html += `
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-${key === 'status' ? 'blue' : 'gray'}-500/10 text-xs text-gray-300 border border-gray-700">
                            ${key}: ${value}
                            <button class="remove-filter hover:text-white" data-filter="${key}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                    `;
                }
                
                activeFiltersDiv.innerHTML = html || '<span class="text-xs text-gray-600">No active filters</span>';
                
                // Add remove filter handlers
                document.querySelectorAll('.remove-filter').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const filter = this.dataset.filter;
                        switch(filter) {
                            case 'status':
                                statusFilter.value = 'all';
                                break;
                            case 'date':
                                dateFilter.value = 'all';
                                break;
                            case 'staff':
                                staffFilter.value = 'all';
                                break;
                            case 'search':
                                searchInput.value = '';
                                clearSearch.classList.add('hidden');
                                break;
                        }
                        filterReviews();
                    });
                });
            }
            
            // Toggle view
            function toggleView(view) {
                currentView = view;
                
                if (view === 'list') {
                    listView.classList.remove('hidden');
                    gridView.classList.add('hidden');
                    listViewBtn.classList.add(currentColor, 'text-white');
                    gridViewBtn.classList.remove(currentColor, 'text-white');
                    gridViewBtn.classList.add('text-gray-400');
                } else {
                    listView.classList.add('hidden');
                    gridView.classList.remove('hidden');
                    gridViewBtn.classList.add(currentColor, 'text-white');
                    listViewBtn.classList.remove(currentColor, 'text-white');
                    listViewBtn.classList.add('text-gray-400');
                    
                    // Populate grid view
                    if (gridView.children.length === 0) {
                        initializeGridView();
                    }
                }
            }
            
            // Copy to clipboard
            window.copyToClipboard = function(text) {
                navigator.clipboard.writeText(text).then(() => {
                    // Show toast notification
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg animate-fade-in-up';
                    toast.textContent = 'Copied to clipboard!';
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 2000);
                });
            };
            
            // Event Listeners
            if (statusFilter) statusFilter.addEventListener('change', filterReviews);
            if (dateFilter) dateFilter.addEventListener('change', filterReviews);
            if (staffFilter) staffFilter.addEventListener('change', filterReviews);
            if (sortFilter) sortFilter.addEventListener('change', filterReviews);
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    filterReviews();
                    if (this.value) {
                        clearSearch.classList.remove('hidden');
                    } else {
                        clearSearch.classList.add('hidden');
                    }
                });
            }
            
            if (clearSearch) {
                clearSearch.addEventListener('click', function() {
                    searchInput.value = '';
                    this.classList.add('hidden');
                    filterReviews();
                });
            }
            
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    this.classList.add('animate-spin');
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            }
            
            if (listViewBtn) {
                listViewBtn.addEventListener('click', () => toggleView('list'));
            }
            
            if (gridViewBtn) {
                gridViewBtn.addEventListener('click', () => toggleView('grid'));
            }
            
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    document.querySelectorAll('.review-card:not([style*="display: none"]) input[type="checkbox"]').forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            }
            
            // Initialize
            filterReviews();
            
            // Add animation to cards
            reviewCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.05}s`;
                card.classList.add('animate-fade-in-up');
            });
            
            // Auto-refresh every 5 minutes
            setInterval(() => {
                console.log('🔄 Auto-refreshing data...');
                // You can implement AJAX refresh here
            }, 300000);
        });
    </script>
    
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .action-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .animate-fade-in-up {
            animation: fade-in-up 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(10px);
        }
        
        @keyframes fade-in-up {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .7;
            }
        }
        
        .animate-bounce-subtle {
            animation: bounce-subtle 2s infinite;
        }
        
        @keyframes bounce-subtle {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-2px);
            }
        }
        
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Grid view styles */
        .grid-card {
            @apply transition-all duration-300;
        }
        
        .grid-card:hover {
            transform: translateY(-2px);
        }
        
        /* Tooltip styles */
        [data-tooltip] {
            position: relative;
            cursor: help;
        }
        
        [data-tooltip]:before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 4px 8px;
            background: #1f2937;
            color: white;
            font-size: 12px;
            border-radius: 4px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            z-index: 10;
        }
        
        [data-tooltip]:hover:before {
            opacity: 1;
        }
        
        /* Loading skeleton animation */
        .skeleton {
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0.05) 25%,
                rgba(255, 255, 255, 0.1) 50%,
                rgba(255, 255, 255, 0.05) 75%
            );
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }
        
        @keyframes skeleton-loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }
    </style>
</x-app-layout-dark>