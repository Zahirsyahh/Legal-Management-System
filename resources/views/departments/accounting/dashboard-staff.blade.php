<x-app-layout-dark title="Accounting Review Dashboard">
    @push('header')
        <div class="flex items-center space-x-4">
            @include('components.notification-bell')
        </div>
    @endpush
    
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Welcome Section -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">
                Accounting Review Dashboard <span class="text-blue-400">📊</span>
            </h1>
            <p class="text-gray-400">
                Welcome back, <span class="text-blue-400">{{ Auth::user()->name }}</span>
                @if(Auth::user()->hasRole('admin_acc'))
                    <span class="ml-2 px-2 py-1 text-xs bg-blue-500/20 text-blue-300 rounded-full">Accounting Admin</span>
                @elseif(Auth::user()->hasRole('staff_acc'))
                    <span class="ml-2 px-2 py-1 text-xs bg-blue-500/20 text-blue-300 rounded-full">Accounting Staff</span>
                @endif
            </p>
        </div>

        <!-- Accounting Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-slide-up">
            <!-- Assigned to Me -->
            <div class="stat-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-blue-500/20">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">Assigned</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $totalAssignedCount ?? 0 }}</h3>
                <p class="text-gray-400 text-sm mt-2">Review Stages</p>
            </div>

            <!-- In Progress -->
            <div class="stat-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-yellow-500/20">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">In Progress</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $activeReviewCount ?? 0 }}</h3>
                <p class="text-gray-400 text-sm mt-2">Active Reviews</p>
            </div>

            <!-- Pending -->
            <div class="stat-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-indigo-500/20">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400">Pending</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $pendingReviewCount ?? 0 }}</h3>
                <p class="text-gray-400 text-sm mt-2">Need Action</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up">
            <!-- Main Content (70%) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- My Assigned Reviews -->
                <div class="glass-card rounded-2xl p-6 border border-blue-500/20">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-blue-300">My Assigned Reviews</h2>
                            <p class="text-sm text-gray-400 mt-1">Contracts assigned to you for accounting review</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if(($activeReviewCount ?? 0) > 0)
                                <span class="px-3 py-1 bg-blue-500/20 text-blue-300 text-xs rounded-full">
                                    {{ $activeReviewCount ?? 0 }} Under Review
                                </span>
                            @endif
                            <a href="{{ route('reviews.my-reviews') }}" 
                               class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-medium transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                View All
                            </a>
                        </div>
                    </div>
                    
                    <!-- Assigned Reviews List - Using underReviewStages -->
                    <div class="space-y-3">
                        @if(isset($underReviewStages) && $underReviewStages->count() > 0)
                            @foreach($underReviewStages->take(5) as $stage)
                                @php
                                    $deadlineClass = '';
                                    if($stage->contract && $stage->contract->drafting_deadline) {
                                        $deadline = \Carbon\Carbon::parse($stage->contract->drafting_deadline);
                                        $daysToDeadline = $deadline->diffInDays(now(), false);
                                        
                                        if($daysToDeadline <= 3 && $daysToDeadline >= 0) {
                                            $deadlineClass = 'border-red-500/50 animate-pulse';
                                        } elseif($deadline->isPast()) {
                                            $deadlineClass = 'border-red-500/30';
                                        }
                                    }
                                @endphp
                                
                                <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-all group border {{ $deadlineClass }}">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500/20 to-indigo-500/20 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                    </div>
                                                    <span class="px-2 py-1 text-xs rounded-full ml-2 bg-blue-500/20 text-blue-300">
                                                        Under Review
                                                    </span>
                                                </div>
                                                
                                                <p class="text-sm text-gray-400 truncate mt-1">
                                                    {{ $stage->contract->title }}
                                                </p>
                                                
                                                <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-gray-500">
                                                    <span class="flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        {{ $stage->contract->user->name }}
                                                    </span>
                                                    
                                                    @if($stage->contract->contract_value)
                                                        <span class="flex items-center text-blue-400">
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
                                                            Due {{ $deadline->format('M d') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="ml-4 flex-shrink-0">
                                        <a href="{{ route('contracts.show', $stage->contract->id) }}" 
                                           class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Review
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-12 border-2 border-dashed border-gray-700 rounded-xl">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-800 mb-4">
                                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-medium text-gray-300">No Documents Under Review</h3>
                                <p class="text-gray-500 mt-2 max-w-md mx-auto">
                                    You don't have any documents currently under review at the moment.
                                </p>
                                <div class="mt-6 space-y-3">
                                    <p class="text-sm text-gray-400">What you can do:</p>
                                    <ul class="text-sm text-gray-500 space-y-1">
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Wait for new assignments from department admin
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Browse all documents to see available ones
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="glass-card rounded-2xl p-6">
                    <h2 class="text-xl font-semibold mb-6">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ route('reviews.my-reviews') }}" 
                           class="btn-primary flex items-center justify-between p-5 rounded-xl group bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-lg bg-white/10">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">My Reviews</h3>
                                    <p class="text-sm text-blue-200/70">View all assigned contracts</p>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">All documents</h3>
                                    <p class="text-sm text-gray-400">Browse all documents</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>

                        <a href="{{ route('notifications.index') }}" 
                           class="btn-primary flex items-center justify-between p-5 rounded-xl group bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 rounded-lg bg-white/10">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Notifications</h3>
                                    <p class="text-sm text-purple-200/70">View all notifications</p>
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
                <!-- User Status Panel -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold">Your Status</h2>
                        <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse-glow"></div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500/20 to-indigo-500/20 flex items-center justify-center">
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
                                <span class="font-medium px-3 py-1 rounded-full bg-gradient-to-r from-blue-500/20 to-indigo-500/20 text-blue-300">
                                    @if(Auth::user()->hasRole('admin_acc'))
                                        Accounting Admin
                                    @elseif(Auth::user()->hasRole('staff_acc'))
                                        Accounting Staff
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

                <!-- Department Info -->
                <div class="glass-card rounded-2xl p-6">
                    <h2 class="text-xl font-semibold mb-4">Accounting Department</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Your Assignments</span>
                            <span class="text-sm font-medium text-gray-300">
                                {{ $totalAssignedCount ?? 0 }} contracts
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Active Reviews</span>
                            <span class="text-sm font-medium text-blue-400">{{ $activeReviewCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Pending</span>
                            <span class="text-sm font-medium text-indigo-400">{{ $pendingReviewCount ?? 0 }}</span>
                        </div>
                        @if(($urgentCount ?? 0) > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Urgent</span>
                            <span class="text-sm font-medium text-red-400">{{ $urgentCount ?? 0 }}</span>
                        </div>
                        @endif
                        @if(($highValueCount ?? 0) > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">High Value</span>
                            <span class="text-sm font-medium text-yellow-400">{{ $highValueCount ?? 0 }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-2xl border border-blue-500/20 p-6">
                    <h3 class="text-lg font-semibold mb-2">Need Help?</h3>
                    <p class="text-sm text-gray-400 mb-4">Get assistance with accounting reviews</p>
                    <div class="space-y-2">
                        <a href="#" class="block text-sm text-blue-400 hover:text-blue-300">Accounting Review Guide</a>
                        <a href="#" class="block text-sm text-blue-400 hover:text-blue-300">Contact Accounting Admin</a>
                        <a href="#" class="block text-sm text-blue-400 hover:text-blue-300">Financial Compliance</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800 text-center text-gray-500 text-sm">
            <p>Accounting Department • {{ now()->format('F j, Y') }} • <span class="text-blue-400">All systems operational</span></p>
        </div>
    </div>
</x-app-layout-dark>