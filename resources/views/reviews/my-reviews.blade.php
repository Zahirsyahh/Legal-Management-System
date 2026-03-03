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
        
        .badge-finance {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(22, 163, 74, 0.2));
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }
        
        .badge-accounting {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(139, 92, 246, 0.2));
            color: #a855f7;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        
        .badge-tax {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.2), rgba(219, 39, 119, 0.2));
            color: #f472b6;
            border: 1px solid rgba(244, 114, 182, 0.3);
        }
        
        .badge-pending {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        
        .badge-assigned {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(139, 92, 246, 0.05));
            color: #c084fc;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }
        
        .badge-progress {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(2, 132, 199, 0.05));
            color: #38bdf8;
            border: 1px solid rgba(14, 165, 233, 0.3);
        }
        
        .badge-revision {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(249, 115, 22, 0.05));
            color: #fb923c;
            border: 1px solid rgba(249, 115, 22, 0.3);
        }
        
        .badge-completed {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.05));
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.3);
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
        
        .filter-badge {
            transition: all 0.2s ease;
        }
        
        .filter-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
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
                    My Reviews
                </h1>
                <p class="text-gray-400 mt-1">Manage your assigned contract reviews</p>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="glass-card rounded-xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Total Assigned</p>
                    <p class="text-2xl font-bold text-white">{{ $totalAssignedCount ?? 0 }}</p>
                </div>
                <div class="p-3 rounded-lg bg-gradient-to-br from-purple-500/10 to-pink-600/10">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Active Reviews</p>
                    <p class="text-2xl font-bold text-white">{{ $activeReviewCount ?? 0 }}</p>
                </div>
                <div class="p-3 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    </svg>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-white">{{ $pendingReviewCount ?? 0 }}</p>
                </div>
                <div class="p-3 rounded-lg bg-gradient-to-br from-yellow-500/10 to-amber-600/10">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Completed</p>
                    <p class="text-2xl font-bold text-white">{{ $completedReviewCount ?? 0 }}</p>
                </div>
                <div class="p-3 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Filter Info -->
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-400">Showing:</span>
            <span class="px-3 py-1 rounded-full text-xs bg-blue-500/20 text-blue-300 border border-blue-500/30">
                Contract Status: under_review
            </span>
            <span class="text-sm text-gray-400 ml-2">Stage Status:</span>
            <span class="px-2 py-1 rounded-full text-xs bg-yellow-500/20 text-yellow-300">pending</span>
            <span class="px-2 py-1 rounded-full text-xs bg-purple-500/20 text-purple-300">assigned</span>
            <span class="px-2 py-1 rounded-full text-xs bg-blue-500/20 text-blue-300">in_progress</span>
            <span class="px-2 py-1 rounded-full text-xs bg-orange-500/20 text-orange-300">revision_requested</span>
        </div>
        
        <!-- Active Reviews Section (Under Review) -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Active Reviews (Under Review)</h2>
                <span class="px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 text-sm border border-blue-500/20">
                    {{ isset($underReviewStages) ? $underReviewStages->count() : 0 }} active
                </span>
            </div>
            
            @if(isset($underReviewStages) && $underReviewStages->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($underReviewStages as $stage)
                <a href="{{ route('contracts.show', $stage->contract->id) }}?stage={{ $stage->id }}" 
                   class="glass-card rounded-xl p-5 review-card border border-gray-700/50">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            @php
                                $badgeClass = 'badge-legal';
                                $stageType = $stage->stage_type ?? ($stage->department->code ?? 'unknown');
                                
                                if($stageType == 'finance' || $stageType == 'FIN') {
                                    $badgeClass = 'badge-finance';
                                } elseif($stageType == 'accounting' || $stageType == 'ACC') {
                                    $badgeClass = 'badge-accounting';
                                } elseif($stageType == 'tax' || $stageType == 'TAX') {
                                    $badgeClass = 'badge-tax';
                                } elseif($stageType == 'legal' || $stageType == 'LEG') {
                                    $badgeClass = 'badge-legal';
                                }
                            @endphp
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium {{ $badgeClass }} mb-2">
                                {{ ucfirst($stage->stage_name ?? $stageType) }} Review
                            </span>
                            <h3 class="font-semibold text-white">{{ $stage->contract->title ?? 'Untitled' }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ $stage->contract->contract_number ?? 'No Number' }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold text-sm">
                            {{ $stage->sequence ?? '?' }}
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Reviewer: {{ $stage->assignedUser->name ?? 'Not assigned' }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Assigned {{ $stage->assigned_at ? $stage->assigned_at->diffForHumans() : 'recently' }}</span>
                        </div>
                        
                        @if($stage->contract && $stage->contract->drafting_deadline)
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Due: {{ \Carbon\Carbon::parse($stage->contract->drafting_deadline)->format('d M Y') }}</span>
                        </div>
                        @endif
                        
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-xs text-gray-500">Stage Status</span>
                            @if($stage->status === 'pending')
                                <span class="px-2 py-1 rounded-full text-xs badge-pending">Pending</span>
                            @elseif($stage->status === 'assigned')
                                <span class="px-2 py-1 rounded-full text-xs badge-assigned">Assigned</span>
                            @elseif($stage->status === 'in_progress')
                                <span class="px-2 py-1 rounded-full text-xs badge-progress">In Progress</span>
                            @elseif($stage->status === 'revision_requested')
                                <span class="px-2 py-1 rounded-full text-xs badge-revision">Revision Needed</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-gray-500/20 text-gray-300">
                                    {{ ucfirst(str_replace('_', ' ', $stage->status)) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Contract Status Badge -->
                    @if($stage->contract && $stage->contract->status === 'under_review')
                    <div class="mt-3 pt-2 border-t border-gray-700/30 flex justify-between items-center">
                        <span class="text-xs text-blue-400/70 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Under Review
                        </span>
                        <span class="text-xs text-gray-500">#{{ $stage->contract->id ?? '' }}</span>
                    </div>
                    @endif
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
                <p class="text-gray-500 max-w-md mx-auto">
                    You don't have any active reviews for contracts with status "under_review". 
                    New assignments will appear here when available.
                </p>
                <div class="mt-6 flex justify-center gap-3">
                    <a href="{{ route('contracts.index') }}" class="px-4 py-2 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded-lg text-sm transition-colors">
                        Browse All Contracts
                    </a>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Other Assigned Stages (Not Under Review) -->
        @if(isset($assignedStages) && $assignedStages->count() > 0 && (!isset($underReviewStages) || $assignedStages->count() != $underReviewStages->count()))
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Other Assignments</h2>
                <span class="px-3 py-1 rounded-full bg-gray-500/10 text-gray-400 text-sm border border-gray-500/20">
                    {{ isset($underReviewStages) ? $assignedStages->count() - $underReviewStages->count() : $assignedStages->count() }} items
                </span>
            </div>
            
            <div class="gradient-border rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-800/50">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Contract</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Stage</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Department</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Contract Status</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/50">
                            @foreach($assignedStages as $stage)
                                @if(!isset($stage->contract) || !isset($underReviewStages) || !$underReviewStages->contains('id', $stage->id))
                                <tr class="table-row-hover">
                                    <td class="py-3 px-4">
                                        <span class="font-medium text-white">{{ $stage->contract->title ?? 'Unknown' }}</span>
                                        <p class="text-xs text-gray-500">{{ $stage->contract->contract_number ?? 'No Number' }}</p>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $badgeClass = 'badge-legal';
                                            $stageType = $stage->stage_type ?? ($stage->department->code ?? 'unknown');
                                            
                                            if($stageType == 'finance' || $stageType == 'FIN') {
                                                $badgeClass = 'badge-finance';
                                            } elseif($stageType == 'accounting' || $stageType == 'ACC') {
                                                $badgeClass = 'badge-accounting';
                                            } elseif($stageType == 'tax' || $stageType == 'TAX') {
                                                $badgeClass = 'badge-tax';
                                            }
                                        @endphp
                                        <span class="inline-block px-2 py-1 rounded-full text-xs {{ $badgeClass }}">
                                            {{ str_replace('_', ' ', $stage->stage_name ?? $stageType) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-400">{{ $stage->department->name ?? $stage->stage_type }}</td>
                                    <td class="py-3 px-4">
                                        @if($stage->status === 'pending')
                                            <span class="px-2 py-1 rounded-full text-xs badge-pending">Pending</span>
                                        @elseif($stage->status === 'assigned')
                                            <span class="px-2 py-1 rounded-full text-xs badge-assigned">Assigned</span>
                                        @elseif($stage->status === 'in_progress')
                                            <span class="px-2 py-1 rounded-full text-xs badge-progress">In Progress</span>
                                        @elseif($stage->status === 'completed')
                                            <span class="px-2 py-1 rounded-full text-xs badge-completed">Completed</span>
                                        @elseif($stage->status === 'revision_requested')
                                            <span class="px-2 py-1 rounded-full text-xs badge-revision">Revision</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs bg-gray-500/20 text-gray-300">
                                                {{ ucfirst(str_replace('_', ' ', $stage->status)) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($stage->contract)
                                        <span class="px-2 py-1 rounded-full text-xs 
                                            {{ $stage->contract->status === 'under_review' ? 'bg-blue-500/20 text-blue-300' : 
                                               ($stage->contract->status === 'draft' ? 'bg-gray-500/20 text-gray-300' : 
                                               ($stage->contract->status === 'completed' ? 'bg-green-500/20 text-green-300' : 
                                               'bg-yellow-500/20 text-yellow-300')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $stage->contract->status ?? 'unknown')) }}
                                        </span>
                                        @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-gray-500/20 text-gray-300">No Contract</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($stage->contract)
                                        <a href="{{ route('contracts.show', $stage->contract->id) }}" 
                                           class="text-blue-400 hover:text-blue-300 text-sm flex items-center gap-1">
                                            View
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Recently Completed -->
        @if(isset($completedStages) && $completedStages->count() > 0)
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
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Department</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Completed</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/50">
                            @foreach($completedStages as $stage)
                            <tr class="table-row-hover">
                                <td class="py-3 px-4">
                                    <span class="font-medium text-white">{{ $stage->contract->title ?? 'Unknown' }}</span>
                                    <p class="text-xs text-gray-500">{{ $stage->contract->contract_number ?? 'No Number' }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $badgeClass = 'badge-legal';
                                        $stageType = $stage->stage_type ?? ($stage->department->code ?? 'unknown');
                                        
                                        if($stageType == 'finance' || $stageType == 'FIN') {
                                            $badgeClass = 'badge-finance';
                                        } elseif($stageType == 'accounting' || $stageType == 'ACC') {
                                            $badgeClass = 'badge-accounting';
                                        } elseif($stageType == 'tax' || $stageType == 'TAX') {
                                            $badgeClass = 'badge-tax';
                                        }
                                    @endphp
                                    <span class="inline-block px-2 py-1 rounded-full text-xs {{ $badgeClass }}">
                                        {{ str_replace('_', ' ', $stage->stage_name ?? $stageType) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-400">{{ $stage->department->name ?? $stage->stage_type }}</td>
                                <td class="py-3 px-4 text-gray-400 text-sm">{{ $stage->completed_at ? $stage->completed_at->format('d M Y') : 'N/A' }}</td>
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