{{-- dashboard.blade.php (tax) --}}
<x-app-layout-dark title="Tax Department Admin Dashboard">
    @push('header')
        <div class="flex items-center space-x-4">
            @include('components.notification-bell')
        </div>
        <style>
            .glass-card {
                background: rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
            }
            
            .glass-card:hover {
                background: rgba(255, 255, 255, 0.05);
                border-color: rgba(255, 255, 255, 0.15);
                box-shadow: 0 12px 48px 0 rgba(0, 0, 0, 0.3);
            }
            
            .glass-stat {
                background: rgba(255, 255, 255, 0.02);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.05);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .glass-stat:hover {
                background: rgba(255, 255, 255, 0.04);
                border-color: rgba(255, 255, 255, 0.12);
                transform: translateY(-2px);
            }
            
            .gradient-text-animated {
                background: linear-gradient(135deg, #f59e0b, #ef4444, #dc2626);
                background-size: 200% 200%;
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
                animation: gradient 3s ease infinite;
            }
            
            @keyframes gradient {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            
            .glossy-btn {
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
            }
            
            .glossy-btn::after {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
                transition: left 0.5s ease;
            }
            
            .glossy-btn:hover::after {
                left: 100%;
            }
        </style>
    @endpush
    
    <div class="relative pb-8 px-4 sm:px-6 lg:px-8">
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-orange-600/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-red-600/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-amber-600/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold">
                        <span class="gradient-text-animated">Tax Dashboard</span>
                    </h1>
                    <p class="text-gray-300 mt-2 backdrop-blur-sm inline-block px-3 py-1 rounded-full bg-white/5">
                        Welcome back, <span class="text-orange-300 font-medium">{{ Auth::user()->name }}</span>
                    </p>
                </div>
                <div class="text-right glass-stat px-5 py-3 rounded-2xl">
                    <p class="text-sm text-gray-300">{{ now()->format('l, F j, Y') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ now()->format('g:i A') }}</p>
                </div>
            </div>
        </div>

        <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Pending Assignments</p>
                        <p class="text-4xl font-bold text-white">{{ $pendingCount }}</p>
                        <p class="text-gray-400 text-xs mt-2">Waiting for staff</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-yellow-500/20">
                        <svg class="w-6 h-6 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('tax-admin.pending') }}" class="text-xs text-yellow-300/80 hover:text-yellow-300 mt-4 inline-flex items-center gap-1 transition-all">
                    View details 
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Active Reviews</p>
                        <p class="text-4xl font-bold text-white">{{ $activeCount }}</p>
                        <p class="text-gray-400 text-xs mt-2">In progress</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-orange-500/20">
                        <svg class="w-6 h-6 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('tax-admin.active') }}" class="text-xs text-orange-300/80 hover:text-orange-300 mt-4 inline-flex items-center gap-1 transition-all">
                    View details 
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Completed (This Month)</p>
                        <p class="text-4xl font-bold text-white">{{ $completedCount }}</p>
                        <p class="text-gray-400 text-xs mt-2">Finished reviews</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-green-500/20">
                        <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('tax-admin.completed') }}" class="text-xs text-green-300/80 hover:text-green-300 mt-4 inline-flex items-center gap-1 transition-all">
                    View details 
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Tax Staff</p>
                        <p class="text-4xl font-bold text-white">
                            {{ \App\Models\TblUser::whereHas('roles', fn($q) => $q->where('nama_user', 'staff_tax'))->where('status_karyawan', true)->count() }}
                        </p>
                        <p class="text-gray-400 text-xs mt-2">Active members</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-purple-500/20">
                        <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Pending Assignments</h2>
                        <p class="text-xs text-gray-400 mt-1">Documents waiting for reviewer assignment</p>
                    </div>
                    <a href="{{ route('tax-admin.pending') }}" class="text-sm text-yellow-300/80 hover:text-yellow-300 transition-all">
                        View All →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-white/5">
                            <tr class="text-left text-gray-300 text-sm">
                                <th class="px-6 py-3 font-medium">Doc Number</th>
                                <th class="px-6 py-3 font-medium">Title</th>
                                <th class="px-6 py-3 font-medium">Client</th>
                                <th class="px-6 py-3 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($pendingAssignments->take(5) as $assignment)
                            <tr class="hover:bg-white/5 transition-colors duration-200">
                                <td class="px-6 py-4 text-sm font-mono text-gray-300">
                                    {{ $assignment->contract->contract_number ?? 'DOC-' . str_pad($assignment->contract->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300 max-w-[200px] truncate">
                                    {{ $assignment->contract->title }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ $assignment->contract->user->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('tax-admin.assign', $assignment) }}" 
                                       class="glossy-btn px-3 py-1.5 text-xs bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300 rounded-lg transition-all inline-flex items-center gap-1 border border-yellow-500/30">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                        </svg>
                                        Assign
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-500 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p>No pending assignments</p>
                                        <p class="text-xs mt-1 opacity-60">All documents have been assigned</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pendingAssignments->count() > 5)
                <div class="px-6 py-3 border-t border-white/10 text-center">
                    <a href="{{ route('tax-admin.pending') }}" class="text-xs text-yellow-300/70 hover:text-yellow-300 transition-all">
                        + {{ $pendingAssignments->count() - 5 }} more pending assignments
                    </a>
                </div>
                @endif
            </div>

            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Active Reviews</h2>
                        <p class="text-xs text-gray-400 mt-1">Currently being reviewed by tax staff</p>
                    </div>
                    <a href="{{ route('tax-admin.active') }}" class="text-sm text-orange-300/80 hover:text-orange-300 transition-all">
                        View All →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-white/5">
                            <tr class="text-left text-gray-300 text-sm">
                                <th class="px-6 py-3 font-medium">Doc Number</th>
                                <th class="px-6 py-3 font-medium">Title</th>
                                <th class="px-6 py-3 font-medium">Assigned To</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($activeReviews->take(5) as $review)
                            <tr class="hover:bg-white/5 transition-colors duration-200">
                                <td class="px-6 py-4 text-sm font-mono text-gray-300">
                                    {{ $review->contract->contract_number ?? 'DOC-' . str_pad($review->contract->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300 max-w-[150px] truncate">
                                    {{ $review->contract->title }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ $review->assignedAdmin->name ?? 'Unassigned' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium backdrop-blur-sm {{ $review->status == 'assigned' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : 'bg-orange-500/20 text-orange-300 border border-orange-500/30' }}">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('contracts.show', $review->contract) }}" 
                                       class="glossy-btn px-3 py-1.5 text-xs bg-orange-500/20 hover:bg-orange-500/30 text-orange-300 rounded-lg transition-all inline-flex items-center gap-1 border border-orange-500/30">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-500 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p>No active reviews</p>
                                        <p class="text-xs mt-1 opacity-60">All reviews have been completed</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($activeReviews->count() > 5)
                <div class="px-6 py-3 border-t border-white/10 text-center">
                    <a href="{{ route('tax-admin.active') }}" class="text-xs text-orange-300/70 hover:text-orange-300 transition-all">
                        + {{ $activeReviews->count() - 5 }} more active reviews
                    </a>
                </div>
                @endif
            </div>
        </div>

        <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="glass-card rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Tax Admin Tools</h2>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('tax-admin.pending') }}" 
                       class="flex items-center justify-between p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-all group backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-500/10 rounded-xl flex items-center justify-center border border-yellow-500/20">
                                <svg class="w-5 h-5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-white text-sm">Assign Staff</h3>
                                <p class="text-xs text-gray-400">Assign reviewers to review documents</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="{{ route('tax-admin.active') }}" 
                       class="flex items-center justify-between p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-all group backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-500/10 rounded-xl flex items-center justify-center border border-orange-500/20">
                                <svg class="w-5 h-5 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-white text-sm">Monitor Progress</h3>
                                <p class="text-xs text-gray-400">Track review progress</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="{{ route('tax-admin.completed') }}" 
                       class="flex items-center justify-between p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-all group backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-500/10 rounded-xl flex items-center justify-center border border-green-500/20">
                                <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-white text-sm">Completed Reviews</h3>
                                <p class="text-xs text-gray-400">View finished reviews</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Department Status</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/5 rounded-xl p-3 backdrop-blur-sm border border-white/5">
                            <p class="text-xs text-gray-400 mb-1">Total Staff</p>
                            <p class="text-2xl font-bold text-white">
                                {{ \App\Models\TblUser::whereHas('roles', fn($q) => $q->where('nama_user', 'staff_tax'))->where('status_karyawan', true)->count() }}
                            </p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 backdrop-blur-sm border border-white/5">
                            <p class="text-xs text-gray-400 mb-1">This Month</p>
                            <p class="text-2xl font-bold text-green-300">{{ $completedCount }}</p>
                            <p class="text-xs text-gray-500">completed</p>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-300">Completion Rate</span>
                            <span class="text-white font-medium">
                                @php
                                    $total = $pendingCount + $activeCount + $completedCount;
                                    $rate = $total > 0 ? round(($completedCount / $total) * 100) : 0;
                                @endphp
                                {{ $rate }}%
                            </span>
                        </div>
                        <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-400 to-red-400 h-2 rounded-full transition-all duration-700" style="width: {{ $rate }}%"></div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/10">
                        <h3 class="text-sm font-medium text-gray-300 mb-3">Priority Tasks</h3>
                        <div class="space-y-2">
                            @if($pendingCount > 0)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                                    <span class="text-gray-300">Contracts need assignment</span>
                                </div>
                                <span class="text-yellow-300 font-medium">{{ $pendingCount }}</span>
                            </div>
                            @endif
                            @if($activeCount > 0)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-orange-400 rounded-full"></div>
                                    <span class="text-gray-300">Reviews in progress</span>
                                </div>
                                <span class="text-orange-300 font-medium">{{ $activeCount }}</span>
                            </div>
                            @endif
                            @if($pendingCount == 0 && $activeCount == 0)
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-300">All tasks up to date!</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout-dark>