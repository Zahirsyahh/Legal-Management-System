<x-app-layout-dark title="My Reviews">
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .gradient-border {
            position: relative;
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
            border: 1px solid transparent;
            border-radius: 0.75rem;
        }
        
        .review-card {
            transition: all 0.3s ease;
        }
        
        .review-card:hover {
            transform: translateY(-2px);
            border-color: rgba(14, 165, 233, 0.3);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        
        .badge-legal {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.2));
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }
        
        .badge-fat {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(139, 92, 246, 0.2));
            color: #a855f7;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        
        .status-assigned {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        
        .status-progress {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(2, 132, 199, 0.05));
            color: #38bdf8;
            border: 1px solid rgba(14, 165, 233, 0.3);
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            box-shadow: 0 0 8px rgba(74, 222, 128, 0.5);
        }
        
        .table-row-hover:hover {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.05), rgba(59, 130, 246, 0.02));
        }
    </style>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header dengan gradient title -->
        <div class="flex items-center gap-3 mb-8">
            <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500/10 to-cyan-600/10 border border-blue-500/20">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 via-cyan-400 to-blue-400 bg-clip-text text-transparent">
                    Review Assignments
                </h1>
                <p class="text-gray-400 mt-1">Manage your assigned contract reviews</p>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="glass-card rounded-xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Active Reviews</p>
                    <p class="text-2xl font-bold text-white">{{ $assignedStages->count() }}</p>
                </div>
                <div class="p-3 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Completed</p>
                    <p class="text-2xl font-bold text-white">{{ $completedStages->count() }}</p>
                </div>
                <div class="p-3 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Avg. Completion</p>
                    <p class="text-2xl font-bold text-white">2.5 days</p>
                </div>
                <div class="p-3 rounded-lg bg-gradient-to-br from-purple-500/10 to-pink-600/10">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Active Reviews Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Active Reviews</h2>
                <span class="px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 text-sm border border-blue-500/20">
                    {{ $assignedStages->count() }} pending
                </span>
            </div>
            
            @if($assignedStages->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($assignedStages as $stage)
                <a href="{{ route('review-stages.show', [$stage->contract, $stage]) }}" 
                   class="glass-card rounded-xl p-5 review-card border border-gray-700/50">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium {{ $stage->stage_type === 'legal' ? 'badge-legal' : 'badge-fat' }} mb-2">
                                {{ ucfirst($stage->stage_type) }} Review
                            </span>
                            <h3 class="font-semibold text-white">{{ $stage->contract->title }}</h3>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold text-sm">
                            {{ $stage->sequence }}
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>{{ $stage->stage_name }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Assigned {{ $stage->assigned_at->diffForHumans() }}</span>
                        </div>
                        
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-xs text-gray-500">Status</span>
                            @if($stage->status === 'assigned')
                            <span class="px-2 py-1 rounded-full text-xs status-assigned">Ready to Start</span>
                            @else
                            <span class="px-2 py-1 rounded-full text-xs status-progress">In Progress</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="gradient-border rounded-xl p-12 text-center">
                <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-300 mb-2">No Active Reviews</h3>
                <p class="text-gray-500">You're all caught up! No pending reviews at the moment.</p>
            </div>
            @endif
        </div>
        
        <!-- Recently Completed -->
        @if($completedStages->count() > 0)
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Recently Completed</h2>
                <span class="text-sm text-gray-400">{{ $completedStages->count() }} total</span>
            </div>
            
            <div class="gradient-border rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-800/50">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Contract</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Stage</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Completed</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/50">
                            @foreach($completedStages as $stage)
                            <tr class="table-row-hover">
                                <td class="py-3 px-4">
                                    <span class="font-medium text-white">{{ $stage->contract->title }}</span>
                                    <p class="text-xs text-gray-500">{{ $stage->contract->contract_number }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-1 rounded-full text-xs {{ $stage->stage_type === 'legal' ? 'badge-legal' : 'badge-fat' }}">
                                        {{ str_replace('_', ' ', $stage->stage_name) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-400 text-sm">{{ $stage->completed_at->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="status-dot"></span>
                                        <span class="text-green-400 text-sm">Completed</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout-dark>