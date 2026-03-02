{{-- resources/views/departments/pending-reviews.blade.php --}}
@php
    // Determine department and routes based on current admin
    $department = Auth::user()->department;
    $routePrefix = match($department->code) {
        'FIN' => 'finance',
        'ACC' => 'accounting',
        'TAX' => 'tax',
        default => 'finance'
    };
    
    // Department colors
    $deptColors = [
        'FIN' => [
            'bg' => 'from-green-500/20 to-emerald-600/20', 
            'text' => 'text-green-400', 
            'border' => 'border-green-500/20',
            'gradient' => 'from-green-400 to-emerald-400',
            'name' => 'Finance',
            'hover' => 'hover:border-green-500/30',
            'badge' => 'bg-green-500/10 text-green-400'
        ],
        'ACC' => [
            'bg' => 'from-blue-500/20 to-cyan-600/20', 
            'text' => 'text-blue-400', 
            'border' => 'border-blue-500/20',
            'gradient' => 'from-blue-400 to-cyan-400',
            'name' => 'Accounting',
            'hover' => 'hover:border-blue-500/30',
            'badge' => 'bg-blue-500/10 text-blue-400'
        ],
        'TAX' => [
            'bg' => 'from-purple-500/20 to-pink-600/20', 
            'text' => 'text-purple-400', 
            'border' => 'border-purple-500/20',
            'gradient' => 'from-purple-400 to-pink-400',
            'name' => 'Tax',
            'hover' => 'hover:border-purple-500/30',
            'badge' => 'bg-purple-500/10 text-purple-400'
        ],
    ];
    
    $currentColor = $deptColors[$department->code] ?? $deptColors['FIN'];
    $pageTitle = $currentColor['name'] . ' Pending Reviews';
@endphp

<x-app-layout-dark title="{{ $pageTitle }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header - Tetap Besar -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-gradient-to-br {{ $currentColor['bg'] }} border {{ $currentColor['border'] }}">
                        <svg class="w-6 h-6 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ $pageTitle }}</h1>
                        <p class="text-gray-400 mt-1">Contracts assigned by legal department waiting for staff assignment</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full {{ $currentColor['bg'] }} border {{ $currentColor['border'] }}">
                        <svg class="w-4 h-4 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-sm font-medium {{ $currentColor['text'] }}">
                            {{ $pendingAssignments->count() }} Pending {{ Str::plural('Assignment', $pendingAssignments->count()) }}
                        </span>
                    </div>
                    
                    <a href="{{ route($routePrefix . '-admin.dashboard') }}" 
                       class="group flex items-center gap-2 text-gray-400 hover:text-gray-300 transition-colors action-btn p-2 rounded-lg hover:bg-gray-800/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="text-sm font-medium">Back to Dashboard</span>
                    </a>
                </div>
            </div>
            
            <!-- Stats Cards - Tetap Besar -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="glass-card p-4 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg {{ $currentColor['bg'] }}">
                            <svg class="w-5 h-5 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Total Pending</p>
                            <p class="text-2xl font-bold text-white">{{ $pendingAssignments->count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="glass-card p-4 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-yellow-500/20 to-orange-600/20">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Due This Week</p>
                            <p class="text-2xl font-bold text-white">{{ $dueThisWeek }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="glass-card p-4 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-red-500/20 to-pink-600/20">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Overdue</p>
                            <p class="text-2xl font-bold text-white">{{ $overdueAssignments }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="glass-card p-4 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-blue-500/20 to-cyan-600/20">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Available Staff</p>
                            <p class="text-2xl font-bold text-white">{{ $availableStaff }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content - Tabel Lebih Ringkas -->
        <div class="space-y-6">
            <!-- Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" 
                               id="searchInput"
                               placeholder="Search contracts..." 
                               class="w-64 bg-gray-800/50 border border-gray-700/50 rounded-lg pl-9 pr-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-{{ $department->code == 'FIN' ? 'green' : ($department->code == 'ACC' ? 'blue' : 'purple') }}-500/50">
                    </div>
                    
                    <!-- Filter -->
                    <select id="statusFilter" class="bg-gray-800/50 border border-gray-700/50 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-{{ $department->code == 'FIN' ? 'green' : ($department->code == 'ACC' ? 'blue' : 'purple') }}-500/50">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="due_soon">Due Soon</option>
                        <option value="overdue">Overdue</option>
                    </select>
                    
                    <!-- Sort -->
                    <select id="sortBy" class="bg-gray-800/50 border border-gray-700/50 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-{{ $department->code == 'FIN' ? 'green' : ($department->code == 'ACC' ? 'blue' : 'purple') }}-500/50">
                        <option value="due_date">Sort by Due Date</option>
                        <option value="title">Sort by Title</option>
                        <option value="contract_number">Sort by Contract #</option>
                    </select>
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- Bulk Actions -->
                    <button id="bulkAssignBtn" class="px-3 py-2 text-sm bg-gray-800/50 hover:bg-gray-700/50 text-gray-300 rounded-lg transition-colors flex items-center gap-2 border border-gray-700/50 opacity-50 cursor-not-allowed" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Bulk Assign
                    </button>
                    
                    <!-- Export -->
                    <button id="exportBtn" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition-colors" title="Export to CSV">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </button>
                    
                    <!-- Refresh -->
                    <button id="refreshBtn" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition-colors" title="Refresh">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Pending Assignments Table - Lebih Ringkas -->
            <div class="glass-card rounded-2xl border {{ $currentColor['border'] }} overflow-hidden">
                <!-- Table Header -->
                <div class="grid grid-cols-12 gap-3 px-4 py-3 bg-gray-800/50 border-b border-gray-700/50 text-xs font-medium text-gray-400 uppercase tracking-wider">
                    <div class="col-span-5 flex items-center gap-2">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-600 bg-gray-700 text-{{ $department->code == 'FIN' ? 'green' : ($department->code == 'ACC' ? 'blue' : 'purple') }}-500 focus:ring-0">
                        <span>Contract Information</span>
                    </div>
                    <div class="col-span-2">Status</div>
                    <div class="col-span-2">Due Date</div>
                    <div class="col-span-2">Assigned By</div>
                    <div class="col-span-1 text-right">Actions</div>
                </div>
                
                <!-- Table Body -->
                <div class="divide-y divide-gray-700/50" id="assignmentsList">
                    @forelse($pendingAssignments as $assignment)
                    @php
                        $dueDate = $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date) : null;
                        $isOverdue = $dueDate && $dueDate->isPast();
                        $isDueSoon = $dueDate && $dueDate->isFuture() && $dueDate->diffInDays(now()) <= 3;
                        
                        $statusClass = $isOverdue ? 'text-red-400' : ($isDueSoon ? 'text-yellow-400' : 'text-gray-400');
                        $statusBg = $isOverdue ? 'bg-red-500/10' : ($isDueSoon ? 'bg-yellow-500/10' : 'bg-gray-500/10');
                    @endphp
                    
                    <div class="assignment-card group hover:bg-white/5 transition-colors" 
                         data-status="{{ $assignment->status }}"
                         data-duedate="{{ $assignment->due_date ?? '' }}"
                         data-contract-number="{{ $assignment->contract->contract_number ?? '' }}"
                         data-title="{{ $assignment->contract->title ?? '' }}">
                        
                        <div class="grid grid-cols-12 gap-3 px-4 py-3 items-center text-sm">
                            <!-- Contract Info (col-span-5) -->
                            <div class="col-span-5 flex items-start gap-3">
                                <input type="checkbox" class="row-checkbox rounded border-gray-600 bg-gray-700 text-{{ $department->code == 'FIN' ? 'green' : ($department->code == 'ACC' ? 'blue' : 'purple') }}-500 focus:ring-0">
                                
                                <div class="flex-1 min-w-0">
                                    @if ($assignment->contract)
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg {{ $currentColor['bg'] }} flex items-center justify-center border {{ $currentColor['border'] }} flex-shrink-0">
                                                <svg class="w-4 h-4 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-medium text-white truncate" title="{{ $assignment->contract->title }}">
                                                    {{ Str::limit($assignment->contract->title, 40) }}
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                                    @if($assignment->contract->contract_number)
                                                        <span>#{{ $assignment->contract->contract_number }}</span>
                                                    @endif
                                                    @if($assignment->contract->contract_type)
                                                        <span>• {{ $assignment->contract->contract_type }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-red-400 italic text-sm">Contract Deleted</div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Status (col-span-2) -->
                            <div class="col-span-2">
                                @if($isOverdue)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs bg-red-500/10 text-red-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Overdue
                                    </span>
                                @elseif($isDueSoon)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs bg-yellow-500/10 text-yellow-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Due Soon
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs bg-yellow-500/10 text-yellow-400">
                                        Pending
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Due Date (col-span-2) -->
                            <div class="col-span-2">
                                @if($dueDate)
                                    <div class="flex items-center gap-1.5 {{ $statusClass }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm">{{ $dueDate->format('M d, Y') }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-600">—</span>
                                @endif
                            </div>
                            
                            <!-- Assigned By (col-span-2) -->
                            <div class="col-span-2">
                                @if($assignment->assignedBy)
                                    <div class="flex items-center gap-1.5 text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="text-sm">{{ $assignment->assignedBy->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-600">—</span>
                                @endif
                            </div>
                            
                            <!-- Actions (col-span-1 text-right) -->
                            <div class="col-span-1 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($assignment->contract)
                                        <a href="{{ route('contracts.show', $assignment->contract) }}" 
                                           class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded-lg transition-colors"
                                           title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route($routePrefix . '-admin.assign', $assignment) }}" 
                                       class="p-1.5 {{ $currentColor['text'] }} hover:bg-{{ $department->code == 'FIN' ? 'green' : ($department->code == 'ACC' ? 'blue' : 'purple') }}-500/10 rounded-lg transition-colors"
                                       title="Assign Staff">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Info Row (Muncul saat hover) -->
                        @if($assignment->contract && ($assignment->contract->counterparty_name || $assignment->contract->contract_value))
                        <div class="hidden group-hover:block px-4 pb-2 ml-[52px] text-xs text-gray-500 border-t border-gray-700/30 mt-1 pt-1">
                            <div class="flex items-center gap-4">
                                @if($assignment->contract->counterparty_name)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ $assignment->contract->counterparty_name }}
                                    </span>
                                @endif
                                @if($assignment->contract->contract_value)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $assignment->contract->currency ?? 'IDR' }} {{ number_format($assignment->contract->contract_value/1000000, 1) }}M
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full {{ $currentColor['bg'] }} mb-4">
                            <svg class="w-8 h-8 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-300 mb-1">All caught up!</h3>
                        <p class="text-gray-500 text-sm">No pending assignments for {{ strtolower($currentColor['name']) }} department.</p>
                    </div>
                    @endforelse
                </div>
                
                <!-- Table Footer -->
                <div class="px-4 py-3 bg-gray-800/30 border-t border-gray-700/50 flex items-center justify-between text-sm">
                    <div class="text-gray-400">
                        <span id="selectedCount">0</span> of <span>{{ $pendingAssignments->count() }}</span> selected
                    </div>
                    
                    @if($pendingAssignments->hasPages())
                    <div class="flex items-center gap-2">
                        {{ $pendingAssignments->links() }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats Row -->
            <div class="grid grid-cols-3 gap-4">
                <div class="glass-card rounded-xl p-3 border {{ $currentColor['border'] }}">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Avg. Response Time</span>
                        <span class="text-sm font-medium text-white">2.4 days</span>
                    </div>
                </div>
                <div class="glass-card rounded-xl p-3 border {{ $currentColor['border'] }}">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">This Month</span>
                        <span class="text-sm font-medium text-white">{{ $pendingAssignments->count() + 5 }} assignments</span>
                    </div>
                </div>
                <div class="glass-card rounded-xl p-3 border {{ $currentColor['border'] }}">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Completion Rate</span>
                        <span class="text-sm font-medium text-green-400">94%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select All functionality
            const selectAllCheckbox = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const selectedCountSpan = document.getElementById('selectedCount');
            const bulkAssignBtn = document.getElementById('bulkAssignBtn');
            
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    rowCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                    updateSelectedCount();
                    updateBulkAssignButton();
                });
            }
            
            rowCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
                checkbox.addEventListener('change', updateBulkAssignButton);
            });
            
            function updateSelectedCount() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                if (selectedCountSpan) {
                    selectedCountSpan.textContent = checkedCount;
                }
            }
            
            function updateBulkAssignButton() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                if (bulkAssignBtn) {
                    if (checkedCount > 0) {
                        bulkAssignBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        bulkAssignBtn.disabled = false;
                    } else {
                        bulkAssignBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        bulkAssignBtn.disabled = true;
                    }
                }
            }
            
            // Filter functionality
            const statusFilter = document.getElementById('statusFilter');
            const searchInput = document.getElementById('searchInput');
            const sortBy = document.getElementById('sortBy');
            const assignmentCards = document.querySelectorAll('.assignment-card');
            const refreshBtn = document.getElementById('refreshBtn');
            const exportBtn = document.getElementById('exportBtn');
            
            function filterAssignments() {
                const statusValue = statusFilter.value;
                const searchValue = searchInput.value.toLowerCase();
                let visibleCount = 0;
                
                assignmentCards.forEach(card => {
                    const status = card.getAttribute('data-status') || 'pending';
                    const contractNumber = card.getAttribute('data-contract-number') || '';
                    const title = card.getAttribute('data-title') || '';
                    const dueDate = card.getAttribute('data-duedate');
                    
                    let statusMatch = true;
                    if (statusValue !== 'all') {
                        if (statusValue === 'overdue') {
                            statusMatch = dueDate && new Date(dueDate) < new Date();
                        } else if (statusValue === 'due_soon') {
                            const due = new Date(dueDate);
                            const now = new Date();
                            const diffDays = Math.ceil((due - now) / (1000 * 60 * 60 * 24));
                            statusMatch = dueDate && diffDays <= 3 && diffDays >= 0;
                        } else {
                            statusMatch = status === statusValue;
                        }
                    }
                    
                    const searchMatch = searchValue === '' || 
                        contractNumber.toLowerCase().includes(searchValue) ||
                        title.toLowerCase().includes(searchValue);
                    
                    if (statusMatch && searchMatch) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
            
            if (statusFilter) statusFilter.addEventListener('change', filterAssignments);
            if (searchInput) searchInput.addEventListener('input', filterAssignments);
            
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    this.classList.add('animate-spin');
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            }
            
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    // Simulate export
                    this.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
                    
                    setTimeout(() => {
                        this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
                        
                        // Show toast
                        showToast('Export started', 'success');
                    }, 1000);
                });
            }
            
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-sm ${
                    type === 'success' ? 'bg-green-600 text-white' : 'bg-blue-600 text-white'
                }`;
                toast.textContent = message;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }
            
            filterAssignments();
        });
    </script>
    
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Checkbox styling */
        input[type="checkbox"] {
            border-radius: 4px;
            border-color: rgba(75, 85, 99, 0.5);
            background-color: rgba(31, 41, 55, 0.5);
        }
        
        input[type="checkbox"]:checked {
            background-color: currentColor;
            border-color: currentColor;
        }
    </style>
</x-app-layout-dark>