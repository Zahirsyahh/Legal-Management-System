<x-app-layout-dark title="Finance Review Dashboard">
    @push('header')
        <div class="flex items-center space-x-4">
            <!-- Search Bar -->
            <div class="relative hidden md:block">
                <input type="text" 
                       placeholder="Search contracts, vendors..." 
                       class="w-64 pl-10 pr-4 py-2 bg-white/5 border border-gray-700 rounded-xl text-sm focus:outline-none focus:border-green-500/50 focus:ring-1 focus:ring-green-500/50">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            
            <!-- Quick Actions Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-gray-900 rounded-xl shadow-lg py-1 z-50 border border-gray-800">
                    <a href="#" class="block px-4 py-2 text-sm hover:bg-white/10">Export Report</a>
                    <a href="#" class="block px-4 py-2 text-sm hover:bg-white/10">Bulk Assign</a>
                    <a href="#" class="block px-4 py-2 text-sm hover:bg-white/10">Generate Summary</a>
                    <div class="border-t border-gray-800 my-1"></div>
                    <a href="#" class="block px-4 py-2 text-sm hover:bg-white/10">Settings</a>
                </div>
            </div>
            
            @include('components.notification-bell')
        </div>
    @endpush
    
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Welcome Section with Date Filter -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between animate-fade-in">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    Finance Review Dashboard 💰
                </h1>
                <p class="text-gray-400">
                    Welcome back, <span class="text-green-400">{{ Auth::user()->name }}</span>
                    @if(Auth::user()->hasRole('admin_fin'))
                        <span class="ml-2 px-2 py-1 text-xs bg-green-500/20 text-green-300 rounded-full">Finance Admin</span>
                    @elseif(Auth::user()->hasRole('staff_fin'))
                        <span class="ml-2 px-2 py-1 text-xs bg-green-500/20 text-green-300 rounded-full">Finance Staff</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Enhanced Stats with Progress -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate-slide-up">
            <!-- Assigned to Me -->
            <div class="stat-card rounded-2xl p-6 hover:scale-105 transition-transform duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-purple-500/20">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">Total Assigned</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $totalAssignedCount ?? 0 }}</h3>
                <div class="mt-3">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-400">This month</span>
                        <span class="text-green-400">+12%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-1.5">
                        <div class="bg-purple-500 h-1.5 rounded-full" style="width: 45%"></div>
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="stat-card rounded-2xl p-6 hover:scale-105 transition-transform duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-yellow-500/20">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">In Progress</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $activeReviewCount ?? 0 }}</h3>
                <p class="text-gray-400 text-sm mt-2 flex items-center">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></span>
                    {{ $activeReviewCount ?? 0 }} active reviews
                </p>
            </div>

            <!-- Pending -->
            <div class="stat-card rounded-2xl p-6 hover:scale-105 transition-transform duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-blue-500/20">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">Pending</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $pendingReviewCount ?? 0 }}</h3>
                <p class="text-gray-400 text-sm mt-2">Need your action</p>
            </div>

            <!-- Completed -->
            <div class="stat-card rounded-2xl p-6 hover:scale-105 transition-transform duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-green-500/20">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">Completed</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $completedCount ?? 0 }}</h3>
                <p class="text-green-400 text-sm mt-2">This month</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up">
            <!-- Main Content (70%) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- My Assigned Reviews with Tabs -->
                <div class="glass-card rounded-2xl p-6 border border-green-500/20">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-green-300">My Assigned Reviews</h2>
                            <p class="text-sm text-gray-400 mt-1">Contracts assigned to you for finance review</p>
                        </div>
                        
                        <!-- Tabs -->
                        <div class="flex space-x-2 bg-white/5 rounded-lg p-1">
                            <button class="px-3 py-1.5 text-sm font-medium rounded-md bg-green-500 text-white">All</button>
                            <button class="px-3 py-1.5 text-sm font-medium rounded-md hover:bg-white/10">Urgent</button>
                            <button class="px-3 py-1.5 text-sm font-medium rounded-md hover:bg-white/10">High Value</button>
                        </div>
                    </div>
                    
                    <!-- Assigned Reviews List with Enhanced UI -->
                    <div class="space-y-3">
                        @if(isset($underReviewStages) && $underReviewStages->count() > 0)
                            @foreach($underReviewStages->take(5) as $stage)
                                @php
                                    $deadlineClass = '';
                                    $priorityBadge = '';
                                    if($stage->contract && $stage->contract->drafting_deadline) {
                                        $deadline = \Carbon\Carbon::parse($stage->contract->drafting_deadline);
                                        $daysToDeadline = $deadline->diffInDays(now(), false);
                                        
                                        if($daysToDeadline <= 3 && $daysToDeadline >= 0) {
                                            $deadlineClass = 'border-l-4 border-l-red-500';
                                            $priorityBadge = '<span class="px-2 py-1 text-xs bg-red-500/20 text-red-300 rounded-full">Urgent</span>';
                                        } elseif($deadline->isPast()) {
                                            $deadlineClass = 'border-l-4 border-l-red-500 opacity-50';
                                            $priorityBadge = '<span class="px-2 py-1 text-xs bg-red-500/20 text-red-300 rounded-full">Overdue</span>';
                                        } elseif($daysToDeadline <= 7) {
                                            $priorityBadge = '<span class="px-2 py-1 text-xs bg-orange-500/20 text-orange-300 rounded-full">Due Soon</span>';
                                        }
                                    }
                                    
                                    if($stage->contract && $stage->contract->contract_value > 1000000000) {
                                        $priorityBadge = '<span class="px-2 py-1 text-xs bg-purple-500/20 text-purple-300 rounded-full">High Value</span>';
                                    }
                                @endphp
                                
                                <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-all group {{ $deadlineClass }}">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500/20 to-emerald-500/20 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-2">
                                                        <p class="text-sm font-semibold text-gray-300 group-hover:text-white truncate">
                                                            {{ $stage->contract->contract_number ?? 'CTR-' . str_pad($stage->contract->id, 4, '0', STR_PAD_LEFT) }}
                                                        </p>
                                                        @if($stage->contract->contract_type)
                                                            <span class="px-2 py-0.5 text-xs bg-purple-500/20 text-purple-300 rounded-full">
                                                                {{ $stage->contract->contract_type }}
                                                            </span>
                                                        @endif
                                                        {!! $priorityBadge ?? '' !!}
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-300">
                                                            Under Review
                                                        </span>
                                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-500/20 text-blue-300">
                                                            Stage {{ $stage->sequence }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <p class="text-sm text-gray-400 truncate mt-1">
                                                    {{ $stage->contract->title }}
                                                </p>
                                                
                                                <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-500">
                                                    <span class="flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        {{ $stage->contract->user->name }}
                                                    </span>
                                                    
                                                    @if($stage->contract->contract_value)
                                                        <span class="flex items-center text-green-400">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            {{ $stage->contract->currency ?? 'IDR' }} {{ number_format($stage->contract->contract_value, 2) }}
                                                        </span>
                                                    @endif
                                                    
                                                    @if($stage->contract->drafting_deadline)
                                                        @php
                                                            $deadlineColor = 'text-gray-400';
                                                            $deadline = \Carbon\Carbon::parse($stage->contract->drafting_deadline);
                                                            $daysToDeadline = $deadline->diffInDays(now(), false);
                                                            
                                                            if($deadline->isPast()) {
                                                                $deadlineColor = 'text-red-400';
                                                            } elseif($daysToDeadline <= 3 && $daysToDeadline >= 0) {
                                                                $deadlineColor = 'text-red-400';
                                                            } elseif($daysToDeadline <= 7) {
                                                                $deadlineColor = 'text-orange-400';
                                                            }
                                                        @endphp
                                                        <span class="flex items-center {{ $deadlineColor }}">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Due {{ $deadline->format('M d, Y') }}
                                                            @if(!$deadline->isPast())
                                                                <span class="ml-1">({{ $daysToDeadline }} days left)</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                                        <button class="p-2 hover:bg-white/10 rounded-lg transition-colors" title="Add Note">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('contracts.show', $stage->contract->id) }}" 
                                           class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Review
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($underReviewStages->count() > 5)
                                <div class="text-center pt-4">
                                    <a href="{{ route('reviews.my-reviews') }}" class="text-sm text-green-400 hover:text-green-300">
                                        View all {{ $underReviewStages->count() }} reviews →
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-12 border-2 border-dashed border-gray-700 rounded-xl">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-800 mb-4">
                                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-medium text-gray-300">No Documents Under Review</h3>
                                <p class="text-gray-500 mt-2 max-w-md mx-auto">
                                    You don't have any documents currently under review at the moment.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold">Recent Activity</h2>
                        <a href="#" class="text-sm text-green-400 hover:text-green-300">View all</a>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm"><span class="font-semibold">You</span> approved contract #CTR-2024-001</p>
                                <p class="text-xs text-gray-500">2 minutes ago</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm">New contract assigned to <span class="font-semibold">John Doe</span></p>
                                <p class="text-xs text-gray-500">15 minutes ago</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm">Contract #CTR-2024-045 deadline tomorrow</p>
                                <p class="text-xs text-gray-500">1 hour ago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions with Icons -->
                <div class="glass-card rounded-2xl p-6">
                    <h2 class="text-xl font-semibold mb-6">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('reviews.my-reviews') }}" 
                           class="btn-primary flex items-center justify-between p-5 rounded-xl group bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-lg bg-white/10">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">My Reviews</h3>
                                    <p class="text-sm text-green-200/70">View all assigned</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>

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

                        <a href="{{ route('reports.contracts') }}" 
                           class="btn-primary flex items-center justify-between p-5 rounded-xl group bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-lg bg-white/10">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Reports</h3>
                                    <p class="text-sm text-purple-200/70">View analytics</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar (30%) -->
            <div class="space-y-6">
                <!-- User Status Panel with Progress -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold">Your Status</h2>
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse-glow"></div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500/20 to-emerald-500/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Logged in as</p>
                                <p class="font-semibold">{{ Auth::user()->name }}</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>

                        <div class="space-y-3 pt-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Role</span>
                                <span class="font-medium px-3 py-1 rounded-full bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-300">
                                    @if(Auth::user()->hasRole('admin_fin'))
                                        Finance Admin
                                    @elseif(Auth::user()->hasRole('staff_fin'))
                                        Finance Staff
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Performance</span>
                                <span class="font-medium text-blue-400">
                                    {{ $avgCompletionTime ?? '0' }} hrs avg
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Notifications</span>
                                <span class="font-medium text-purple-400">
                                    {{ $unreadNotifications ?? 0 }} unread
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Department Stats -->
                <div class="glass-card rounded-2xl p-6">
                    <h2 class="text-xl font-semibold mb-4">Department Overview</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Total Assignments</span>
                            <span class="text-sm font-medium text-gray-300">{{ $totalAssignedCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Active Reviews</span>
                            <span class="text-sm font-medium text-green-400">{{ $activeReviewCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Pending</span>
                            <span class="text-sm font-medium text-blue-400">{{ $pendingReviewCount ?? 0 }}</span>
                        </div>
                        
                        <!-- Alert Badges -->
                        @if(($urgentCount ?? 0) > 0)
                        <div class="flex items-center justify-between p-3 bg-red-500/10 rounded-lg">
                            <span class="text-sm text-red-400">⚠️ Urgent Reviews</span>
                            <span class="text-sm font-medium text-red-400">{{ $urgentCount ?? 0 }}</span>
                        </div>
                        @endif
                        
                        @if(($highValueCount ?? 0) > 0)
                        <div class="flex items-center justify-between p-3 bg-purple-500/10 rounded-lg">
                            <span class="text-sm text-purple-400">💰 High Value</span>
                            <span class="text-sm font-medium text-purple-400">{{ $highValueCount ?? 0 }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Deadlines -->
                <div class="glass-card rounded-2xl p-6">
                    <h2 class="text-xl font-semibold mb-4">Upcoming Deadlines</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                            <div>
                                <p class="text-sm font-medium">CTR-2024-045</p>
                                <p class="text-xs text-gray-400">Due tomorrow</p>
                            </div>
                            <span class="px-2 py-1 text-xs bg-red-500/20 text-red-300 rounded-full">Urgent</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                            <div>
                                <p class="text-sm font-medium">CTR-2024-032</p>
                                <p class="text-xs text-gray-400">Due in 3 days</p>
                            </div>
                            <span class="px-2 py-1 text-xs bg-orange-500/20 text-orange-300 rounded-full">Soon</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                            <div>
                                <p class="text-sm font-medium">CTR-2024-028</p>
                                <p class="text-xs text-gray-400">Due in 5 days</p>
                            </div>
                            <span class="px-2 py-1 text-xs bg-blue-500/20 text-blue-300 rounded-full">Normal</span>
                        </div>
                    </div>
                    <a href="#" class="block text-center text-sm text-green-400 hover:text-green-300 mt-4">
                        View all deadlines →
                    </a>
                </div>

                <!-- Help Card -->
                <div class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-2xl border border-green-500/20 p-6">
                    <h3 class="text-lg font-semibold mb-2">Need Help?</h3>
                    <p class="text-sm text-gray-400 mb-4">Get assistance with finance reviews</p>
                    
                    <div class="space-y-3">
                        <a href="#" class="flex items-center space-x-3 text-sm text-green-400 hover:text-green-300 p-2 rounded-lg hover:bg-white/5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Finance Review Guide</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 text-sm text-green-400 hover:text-green-300 p-2 rounded-lg hover:bg-white/5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Contact Finance Admin</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 text-sm text-green-400 hover:text-green-300 p-2 rounded-lg hover:bg-white/5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Budget Guidelines</span>
                        </a>
                    </div>
                    
                    <!-- Live Chat Button -->
                    <button class="w-full mt-4 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Live Chat Support
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800">
            <div class="flex flex-col sm:flex-row justify-between items-center text-gray-500 text-sm">
                <p>Finance Department • {{ now()->format('F j, Y') }}</p>
                <div class="flex items-center space-x-4 mt-2 sm:mt-0">
                    <span class="flex items-center">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        All systems operational
                    </span>
                    <span>v2.0.1</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Add any interactive JavaScript here
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                init() {
                    // Initialize dashboard features
                }
            }))
        })
    </script>
    @endpush
</x-app-layout-dark>