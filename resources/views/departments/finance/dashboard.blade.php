<x-app-layout-dark title="Finance Department Admin Dashboard">
    @push('header')
        <div class="flex items-center space-x-4">
            @include('components.notification-bell')
        </div>
    @endpush
    
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Welcome Section -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">
                Finance Department Admin Dashboard 💰
            </h1>
            <p class="text-gray-400">
                Welcome back, <span class="gradient-text">{{ Auth::user()->name }}</span>
            </p>
        </div>

        <!-- Department Admin Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate-slide-up">
            <!-- Pending Assignments -->
            <div class="stat-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-yellow-500/20">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">Pending</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $pendingCount }}</h3>
                <p class="text-gray-400 text-sm mt-2">Assignments</p>
                <a href="{{ route('finance-admin.pending') }}" class="text-yellow-400 hover:text-yellow-300 text-xs mt-2 block">
                    View all →
                </a>
            </div>

            <!-- Active Reviews -->
            <div class="stat-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-blue-500/20">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">Active</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $activeCount }}</h3>
                <p class="text-gray-400 text-sm mt-2">Reviews</p>
                <a href="{{ route('finance-admin.active') }}" class="text-blue-400 hover:text-blue-300 text-xs mt-2 block">
                    View all →
                </a>
            </div>

            <!-- Completed Reviews -->
            <div class="stat-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-green-500/20">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">Completed</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $completedCount }}</h3>
                <p class="text-gray-400 text-sm mt-2">Reviews</p>
                <a href="{{ route('finance-admin.completed') }}" class="text-green-400 hover:text-green-300 text-xs mt-2 block">
                    View all →
                </a>
            </div>

            <!-- Finance Staff -->
            <div class="stat-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-purple-500/20">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">Staff</span>
                </div>
                <h3 class="text-3xl font-bold">
                    {{ \App\Models\TblUser::whereHas('roles', fn($q) => $q->where('nama_user', 'staff_fin'))->where('status_karyawan', true)->count() }}
                </h3>
                <p class="text-gray-400 text-sm mt-2">Available</p>
                <a href="#" class="text-purple-400 hover:text-purple-300 text-xs mt-2 block">
                    Manage →
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up">
            <!-- Main Content (70%) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Pending Assignments -->
                <div class="glass-card rounded-2xl p-6 border border-yellow-500/20">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-yellow-300">Pending Assignments</h2>
                            <p class="text-sm text-gray-400 mt-1">Contracts waiting for staff assignment</p>
                        </div>
                        <a href="{{ route('finance-admin.pending') }}" class="text-sm text-yellow-400 hover:text-yellow-300">
                            View All →
                        </a>
                    </div>
                    
                    <div class="space-y-3">
                        @forelse($pendingAssignments as $assignment)
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-all group">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-yellow-500/20 to-orange-500/20 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                @if ($assignment->contract)
                                                    <p class="text-sm font-semibold text-gray-300 group-hover:text-white truncate">
                                                        {{ $assignment->contract->contract_number 
                                                            ?? 'CTR-' . str_pad($assignment->contract->id, 4, '0', STR_PAD_LEFT) }}
                                                    </p>
                                                @else
                                                    <p class="text-sm font-semibold text-red-400 italic">
                                                        Contract deleted
                                                    </p>
                                                @endif

                                                <span class="px-2 py-0.5 text-xs bg-gray-500/20 text-gray-300 rounded-full">
                                                    {{ $assignment->contract->contract_type }}
                                                </span>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-300">
                                                Pending
                                            </span>
                                        </div>
                                        
                                        <p class="text-sm text-gray-400 truncate mt-1">
                                            {{ $assignment->contract->title }}
                                        </p>
                                        
                                        <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-gray-500">
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ $assignment->contract->user->name }}
                                            </span>
                                            @if($assignment->contract->contract_value)
                                                <span class="flex items-center text-green-400">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $assignment->contract->currency }} {{ number_format($assignment->contract->contract_value, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="ml-4 flex-shrink-0">
                                <a href="{{ route('finance-admin.assign', $assignment) }}" 
                                   class="px-3 py-1.5 text-xs bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    Assign Staff
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 border-2 border-dashed border-gray-700 rounded-xl">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-800 mb-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-300">All caught up!</h3>
                            <p class="text-gray-500 mt-1">No pending assignments for finance department</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Active Reviews -->
                <div class="glass-card rounded-2xl p-6 border border-blue-500/20">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-blue-300">Active Finance Reviews</h2>
                            <p class="text-sm text-gray-400 mt-1">Currently being reviewed by finance staff</p>
                        </div>
                        <a href="{{ route('finance-admin.active') }}" class="text-sm text-blue-400 hover:text-blue-300">
                            View All →
                        </a>
                    </div>
                    
                    <div class="space-y-3">
                        @forelse($activeReviews as $review)
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-all group">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500/20 to-cyan-500/20 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-300 group-hover:text-white truncate">
                                                {{ $review->contract->contract_number ?? 'CTR-' . str_pad($review->contract->id, 4, '0', STR_PAD_LEFT) }}
                                            </p>
                                            <span class="px-2 py-1 text-xs rounded-full ml-2 {{ $review->status == 'assigned' ? 'bg-yellow-500/20 text-yellow-300' : 'bg-blue-500/20 text-blue-300' }}">
                                                {{ ucfirst($review->status) }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-sm text-gray-400 truncate mt-1">
                                            {{ $review->contract->title }}
                                        </p>
                                        
                                        <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-gray-500">
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ $review->assignedAdmin->name ?? 'Unassigned' }}
                                            </span>
                                            <span class="text-gray-400">
                                                {{ $review->assigned_at ? $review->assigned_at->diffForHumans() : 'Not assigned' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="ml-4 flex-shrink-0">
                                <a href="{{ route('contracts.show', $review->contract) }}" 
                                   class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View Details
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 border-2 border-dashed border-gray-700 rounded-xl">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-800 mb-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-300">No active reviews</h3>
                            <p class="text-gray-500 mt-1">Assign contracts to staff to start reviews</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar (30%) -->
            <div class="space-y-6">
                <!-- Admin Tools -->
                <div class="glass-card rounded-2xl p-6">
                    <h2 class="text-xl font-semibold mb-6">Finance Admin Tools</h2>
                    <div class="space-y-4">
                        <a href="{{ route('finance-admin.pending') }}" 
                           class="btn-primary flex items-center justify-between p-5 rounded-xl group">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-lg bg-white/10">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Assign Staff</h3>
                                    <p class="text-sm text-blue-200/70">Assign contracts to reviewers</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>

                        <a href="{{ route('finance-admin.active') }}" 
                           class="btn-secondary flex items-center justify-between p-5 rounded-xl group">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-lg bg-white/5">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Monitor Progress</h3>
                                    <p class="text-sm text-gray-400">Track review progress</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>

                        <a href="{{ route('finance-admin.completed') }}" 
                           class="btn-primary flex items-center justify-between p-5 rounded-xl group bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-lg bg-white/10">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Completed Reviews</h3>
                                    <p class="text-sm text-green-200/70">View finished reviews</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Department Status -->
                <div class="glass-card rounded-2xl p-6">
                    <h2 class="text-xl font-semibold mb-4">Finance Department Status</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Total Staff</span>
                            <span class="text-sm font-medium text-gray-300">
                                {{ \App\Models\TblUser::whereHas('roles', fn($q) => $q->where('nama_user', 'staff_fin'))->where('status_karyawan', true)->count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">This Month</span>
                            <span class="text-sm font-medium text-green-400">{{ $completedCount }} completed</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Avg. Review Time</span>
                            <span class="text-sm font-medium text-blue-400">3.2 days</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-700">
                        <h3 class="text-sm font-medium text-gray-300 mb-2">Priority Tasks</h3>
                        <div class="space-y-2">
                            @if($pendingCount > 0)
                            <div class="flex items-center text-sm">
                                <span class="h-2 w-2 bg-yellow-500 rounded-full mr-2"></span>
                                <span class="text-gray-300">{{ $pendingCount }} contracts need assignment</span>
                            </div>
                            @endif
                            @if($activeCount > 0)
                            <div class="flex items-center text-sm">
                                <span class="h-2 w-2 bg-blue-500 rounded-full mr-2"></span>
                                <span class="text-gray-300">{{ $activeCount }} reviews in progress</span>
                            </div>
                            @endif
                            @if($pendingCount == 0 && $activeCount == 0)
                            <div class="flex items-center text-sm">
                                <span class="h-2 w-2 bg-green-500 rounded-full mr-2"></span>
                                <span class="text-gray-300">All tasks up to date</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800 text-center text-gray-500 text-sm">
            <p>Finance Department Admin • {{ now()->format('F j, Y') }}</p>
        </div>
    </div>
</x-app-layout-dark>