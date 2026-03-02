@php
    use Illuminate\Support\Facades\Auth;
    use Spatie\Permission\Models\Role;

    $user = Auth::user();
    $allRoles = Role::all();
    $userRoles = $user?->roles->pluck('name')->toArray() ?? [];
@endphp

<x-app-layout-dark title="My Contracts">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        
        <!-- Success Notification -->
        @if(session('success'))
        <div id="success-notification" class="mb-6 animate-fade-in">
            <div class="glass-card border border-green-500/30 bg-green-900/10 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-300">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('success-notification').remove()" 
                            class="ml-auto -mx-1.5 -my-1.5 bg-green-900/20 text-green-400 rounded-lg p-1.5 hover:bg-green-900/40 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Error Notification -->
        @if(session('error'))
        <div id="error-notification" class="mb-6 animate-fade-in">
            <div class="glass-card border border-red-500/30 bg-red-900/10 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-300">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('error-notification').remove()" 
                            class="ml-auto -mx-1.5 -my-1.5 bg-red-900/20 text-red-400 rounded-lg p-1.5 hover:bg-red-900/40 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Info Notification (optional) -->
        @if(session('info'))
        <div id="info-notification" class="mb-6 animate-fade-in">
            <div class="glass-card border border-blue-500/30 bg-blue-900/10 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-300">{{ session('info') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('info-notification').remove()" 
                            class="ml-auto -mx-1.5 -my-1.5 bg-blue-900/20 text-blue-400 rounded-lg p-1.5 hover:bg-blue-900/40 transition-colors">
                        <svg class="h-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">Documents</h1>
                    <p class="text-gray-400">Manage and track your document review requests</p>
                </div>
                
                <!-- New Document Dropdown -->
<div class="relative group">
    <!-- Main Button -->
    <button class="glass-btn-primary px-4 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 
                   hover:from-blue-700 hover:to-cyan-700 rounded-xl font-medium 
                   flex items-center gap-2 transition-all duration-300 
                   hover:scale-[1.02] active:scale-[0.98] group/main">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="font-medium text-sm">New Document</span>
        <svg class="w-4 h-4 ml-1 transition-transform duration-300 group-hover/main:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    
    <!-- Dropdown Menu -->
    <div class="absolute right-0 mt-2 w-80 glass-card rounded-lg shadow-2xl border border-gray-800/50 opacity-0 invisible transition-all duration-200 transform origin-top-right scale-95 z-50 group-hover:opacity-100 group-hover:visible group-hover:scale-100">
        <div class="p-2">
            <!-- Document Review Option -->
            <a href="{{ route('contracts.create', ['type' => 'contract']) }}" 
               class="dropdown-option w-full text-left px-3 py-3 rounded-md flex items-center gap-3 hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200 group">
                <div class="p-1.5 rounded-md bg-gradient-to-br from-blue-500/10 to-cyan-600/10 group-hover:from-blue-500/20 group-hover:to-cyan-600/20">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-sm">Document Review</div>
                    <div class="text-xs text-gray-500 group-hover:text-blue-300/70">Submit contract or letter for review</div>
                </div>
                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
            
            <!-- Separator -->
            <div class="my-2 border-t border-gray-800/50"></div>
            
            <!-- Request Letter Numbering Option -->
            <a href="{{ route('surat.create') }}" 
               class="dropdown-option w-full text-left px-3 py-3 rounded-md flex items-center gap-3 hover:bg-purple-500/10 hover:text-purple-400 transition-all duration-200 group">
                <div class="p-1.5 rounded-md bg-gradient-to-br from-purple-500/10 to-pink-600/10 group-hover:from-purple-500/20 group-hover:to-pink-600/20">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-sm">Request Letter Numbering</div>
                    <div class="text-xs text-gray-500 group-hover:text-purple-300/70">Generate official letter number</div>
                </div>
                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
            
            <!-- Optional: Quick Stats or Info -->
            <div class="mt-2 px-3 py-2 rounded-md bg-gray-800/30 border border-gray-800/50">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">Need help?</span>
                    <a href="#" class="text-blue-400 hover:text-blue-300 transition-colors">View Guide →</a>
                </div>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-7 gap-3 mb-6">
            <div class="glass-card rounded-xl p-3 text-center">
                <p class="text-xs text-gray-400">Total</p>
                <p class="text-lg font-bold mt-1">{{ $contracts->total() }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center">
                <p class="text-xs text-gray-400">Draft</p>
                <p class="text-lg font-bold mt-1">{{ $contracts->where('status', 'draft')->count() }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center">
                <p class="text-xs text-gray-400">Submitted</p>
                <p class="text-lg font-bold mt-1">{{ $contracts->where('status', 'submitted')->count() }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center">
                <p class="text-xs text-gray-400">Review</p>
                <p class="text-lg font-bold mt-1">{{ $contracts->where('status', 'under_review')->count() }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center">
                <p class="text-xs text-gray-400">Approved</p>
                <p class="text-lg font-bold mt-1">{{ $contracts->where('status', 'final_approved')->count() }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center">
                <p class="text-xs text-gray-400">Released</p>
                <p class="text-lg font-bold mt-1">{{ $contracts->where('status', 'released')->count() }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center">
                <p class="text-xs text-gray-400">Declined</p>
                <p class="text-lg font-bold mt-1">{{ $contracts->where('status', 'declined')->count() }}</p>
            </div>
        </div>

        <!-- Enhanced Filters -->
        <div class="relative mb-6">
            <!-- Glass Background Effect -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-purple-500/5 rounded-2xl blur-xl"></div>
            
            <!-- Main Filter Card -->
            <div class="relative glass-card-luxury rounded-2xl p-6">
                <!-- Filter Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Advanced Filters
                        </h3>
                        <p class="text-sm text-gray-400 mt-1">Refine your contract search with precision</p>
                    </div>
                    
                    <!-- Filter Toggle Button -->
                    <button id="filterToggle" class="glass-btn rounded-lg p-2 hover:bg-white/5 transition-all">
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>

                <!-- Collapsible Filter Form -->
                <form method="GET" action="{{ route('contracts.index') }}" id="filterForm" class="animate-fade-in">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        
                        <!-- Status Filter -->
                        <div class="space-y-2">
                            <label for="status" class="block text-sm font-medium text-gray-300 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Status
                            </label>
                            <div class="relative">
                                <select name="status" id="status" 
                                    class="w-full px-4 py-3 rounded-xl text-sm appearance-none cursor-pointer
                                        focus:ring-2 focus:ring-blue-500/50 focus:outline-none"
                                    style="background-color: #0f172a !important; color: #f8fafc !important; border: 1px solid #334155;">
                                    <option value="" style="background-color: #0f172a; color: #94a3b8;">All Status</option>
                                    <option value="draft" style="background-color: #0f172a; color: white;" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" style="background-color: #0f172a; color: white;" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="under_review" style="background-color: #0f172a; color: white;" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="final_approved" style="background-color: #0f172a; color: white;" {{ request('status') == 'final_approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="revision_needed" style="background-color: #0f172a; color: white;" {{ request('status') == 'revision_needed' ? 'selected' : '' }}>Revision Needed</option>
                                    <option value="number_issued" style="background-color: #0f172a; color: white;" {{ request('status') == 'number_issued' ? 'selected' : '' }}>Number Issued</option>
                                    <option value="released" style="background-color: #0f172a; color: white;" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
                                    <option value="declined" style="background-color: #0f172a; color: white;" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                                    <option value="cancelled" style="background-color: #0f172a; color: white;" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Contract Type Filter -->
                        <div class="space-y-2">
                            <label for="contract_type" class="block text-sm font-medium text-gray-300 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Document Type
                            </label>
                            <div class="relative">
                                <select name="contract_type" id="contract_type" 
                                    class="w-full px-4 py-3 rounded-xl text-sm appearance-none cursor-pointer
                                        focus:ring-2 focus:ring-purple-500/50 focus:outline-none"
                                    style="background-color: #0f172a !important; color: #f8fafc !important; border: 1px solid #334155;">
                                    <option value="" style="background-color: #0f172a; color: #94a3b8;">All Types</option>
                                    @foreach(['Surat', 'Kontrak'] as $type)
                                        <option value="{{ $type }}" style="background-color: #0f172a; color: white;" {{ request('contract_type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range Filter -->
                        <div class="space-y-2">
                            <label for="date_range" class="block text-sm font-medium text-gray-300 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Date Range
                            </label>
                            <div class="relative">
                                <select name="date_range" id="date_range" 
                                    class="w-full px-4 py-3 rounded-xl text-sm appearance-none cursor-pointer
                                        focus:ring-2 focus:ring-emerald-500/50 focus:outline-none"
                                    style="background-color: #0f172a !important; color: #f8fafc !important; border: 1px solid #334155;">
                                    <option value="" style="background-color: #0f172a; color: #94a3b8;">All Time</option>
                                    <option value="today" style="background-color: #0f172a; color: white;" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="week" style="background-color: #0f172a; color: white;" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" style="background-color: #0f172a; color: white;" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
                                    <option value="quarter" style="background-color: #0f172a; color: white;" {{ request('date_range') == 'quarter' ? 'selected' : '' }}>Last 3 Months</option>
                                    <option value="year" style="background-color: #0f172a; color: white;" {{ request('date_range') == 'year' ? 'selected' : '' }}>This Year</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Search Field -->
                        <div class="space-y-2">
                            <label for="search" class="block text-sm font-medium text-gray-300 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Search Contracts
                            </label>
                            <div class="relative group">
                                <input type="text" 
                                    name="search" 
                                    id="search" 
                                    value="{{ request('search') }}"
                                    placeholder="Search by title, number, or counterparty..."
                                    class="w-full px-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/50 focus:outline-none placeholder-gray-500"
                                    style="background-color: #0f172a !important; color: white !important; border: 1px solid #334155;">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <div class="search-pulse-animation hidden">
                                        <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center justify-between gap-4 mt-8 pt-6 border-t border-gray-800/50">
                        <div class="flex items-center gap-3">
                            <button type="submit" 
                                class="glass-btn-primary px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 
                                    hover:from-blue-700 hover:to-cyan-700 rounded-xl font-medium 
                                    flex items-center gap-2 transition-all duration-300 
                                    hover:scale-[1.02] active:scale-[0.98] group">
                                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Apply Filters
                            </button>
                            
                            @if(request()->hasAny(['status', 'search', 'contract_type', 'date_range']))
                            <a href="{{ route('contracts.index', ['tab' => $activeTab]) }}" 
                               class="glass-btn-secondary px-6 py-3 bg-gray-800/50 hover:bg-gray-700/50 
                                      text-gray-300 rounded-xl font-medium flex items-center gap-2 
                                      transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] clear-filters-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Clear All
                            </a>
                            @endif
                        </div>

                        <!-- Active Filters Badges -->
                        @if(request()->hasAny(['status', 'search', 'contract_type', 'date_range']))
                        <div class="flex flex-wrap gap-2 active-filters-badge">
                            @if(request('status'))
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium 
                                            bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    Status: 
                                    @php
                                        $statuses = is_array(request('status')) 
                                            ? request('status') 
                                            : [request('status')];
                                        echo collect($statuses)
                                            ->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))
                                            ->join(', ');
                                    @endphp
                                    <a href="{{ route('contracts.index', array_merge(request()->except(['status', 'page']), ['tab' => $activeTab])) }}" 
                                       class="ml-1.5 hover:text-blue-200 remove-filter" data-filter="status">
                                        ×
                                    </a>
                                </span>
                            @endif
                            
                            @if(request('contract_type'))
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium 
                                        bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                Type: {{ request('contract_type') }}
                                <a href="{{ route('contracts.index', array_merge(request()->except(['contract_type', 'page']), ['tab' => $activeTab])) }}" 
                                   class="ml-1.5 hover:text-purple-200 remove-filter" data-filter="contract_type">
                                    ×
                                </a>
                            </span>
                            @endif
                            
                            @if(request('date_range'))
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium 
                                        bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                {{ ucfirst(request('date_range')) }}
                                <a href="{{ route('contracts.index', array_merge(request()->except(['date_range', 'page']), ['tab' => $activeTab])) }}" 
                                   class="ml-1.5 hover:text-emerald-200 remove-filter" data-filter="date_range">
                                    ×
                                </a>
                            </span>
                            @endif

                            @if(request('search'))
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium 
                                        bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                Search: "{{ request('search') }}"
                                <a href="{{ route('contracts.index', array_merge(request()->except(['search', 'page']), ['tab' => $activeTab])) }}" 
                                   class="ml-1.5 hover:text-amber-200 remove-filter" data-filter="search">
                                    ×
                                </a>
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Clean Navigation Tabs - Integrated with Table -->
        <div class="mb-4">
            <!-- Tabs Card tanpa glow -->
            <div class="glass-card-luxury rounded-t-2xl border-b-0 overflow-hidden" style="background: rgba(30, 41, 59, 0.4);">
                <!-- Simple top border instead of gradient -->
                <div class="absolute top-0 left-0 right-0 h-px bg-gray-700/50"></div>
                
                <div class="px-6 pt-4">
                    <!-- Tab header with icon - lebih subtle -->
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1 h-4 bg-gray-600 rounded-full"></div>
                        <span class="text-xs font-medium tracking-wider text-gray-500 uppercase">VIEW MODE</span>
                    </div>
                    
                    <!-- Modern Tab Navigation tanpa glow -->
                    <ul class="flex items-center gap-1 pb-0">

                        {{-- ================= USER ================= --}}
                        @role('user')
                            <li class="flex-1 sm:flex-initial">
                                <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                    {{ $activeTab === 'requests' ? 'text-blue-400 bg-blue-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                   href="{{ route('contracts.index', ['tab' => 'requests']) }}" data-tab="requests">
                                    <span>My Requests</span>
                                    @if($activeTab === 'requests')
                                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-500"></span>
                                    @endif
                                </a>
                            </li>
                        @endrole

                        {{-- ================= ADMIN ================= --}}
                        @role('admin')
                            <li class="flex-1 sm:flex-initial">
                                <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                    {{ $activeTab === 'all' ? 'text-emerald-400 bg-emerald-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                   href="{{ route('contracts.index', ['tab' => 'all']) }}" data-tab="all">
                                    <span>All Documents</span>
                                    @if($activeTab === 'all')
                                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500"></span>
                                    @endif
                                </a>
                            </li>
                        @endrole

                        {{-- ================= OTHER ROLES ================= --}}
                        @hasanyrole('legal|admin_fin|admin_tax|admin_acc|staff_fin|staff_tax|staff_acc')

                            {{-- My Requests --}}
                            <li class="flex-1 sm:flex-initial">
                                <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                    {{ $activeTab === 'requests' ? 'text-blue-400 bg-blue-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                   href="{{ route('contracts.index', ['tab' => 'requests']) }}" data-tab="requests">
                                    <span>My Requests</span>
                                    @if($activeTab === 'requests')
                                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-500"></span>
                                    @endif
                                </a>
                            </li>

                            {{-- My Reviews --}}
                            <li class="flex-1 sm:flex-initial">
                                <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                    {{ $activeTab === 'reviews' ? 'text-purple-400 bg-purple-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                   href="{{ route('contracts.index', ['tab' => 'reviews']) }}" data-tab="reviews">
                                    <span>My Reviews</span>
                                    @if($activeTab === 'reviews')
                                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500"></span>
                                    @endif
                                </a>
                            </li>

                            {{-- All Documents --}}
                            @can('contract_view_all')
                                <li class="flex-1 sm:flex-initial">
                                    <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                        {{ $activeTab === 'all' ? 'text-emerald-400 bg-emerald-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                       href="{{ route('contracts.index', ['tab' => 'all']) }}" data-tab="all">
                                        <span>All Documents</span>
                                        @if($activeTab === 'all')
                                            <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500"></span>
                                        @endif
                                    </a>
                                </li>
                            @endcan

                        @endhasanyrole

                    </ul>
                </div>
            </div>
            
            <!-- Subtle separator line that connects to table -->
            <div class="h-px bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>
        </div>

        <!-- Results Summary -->
        <div class="flex items-center justify-between mb-6 px-2">
            <div class="text-xs text-gray-400">
                <span class="font-medium text-gray-300" id="total-count">{{ $contracts->total() }}</span> contracts found
                @if($contracts->count() > 0)
                <span class="mx-1">•</span>
                <span class="font-medium text-gray-300" id="first-item">{{ $contracts->firstItem() }}</span>-<span class="font-medium text-gray-300" id="last-item">{{ $contracts->lastItem() }}</span>
                @endif
            </div>
            
            @if($contracts->total() > 0)
            <div class="flex items-center gap-2">
                <label for="perPage" class="text-xs text-gray-400">Show:</label>
                <select id="perPage" name="per_page" class="text-xs bg-gray-800/50 border border-gray-700 rounded px-2 py-1 text-white">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            @endif
        </div>

        <!-- Contracts Table Container with AJAX -->
        <div class="glass-card-luxury rounded-2xl overflow-hidden animate-slide-up">
            <div id="contractsContainer">
                @include('contracts.partials.table')
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* NEW DOCUMENT BUTTON STYLES */
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
        }

        /* Liquid Glass Effect Styles */
        .glass-card-luxury {
            background: rgba(30, 41, 59, 0.3);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.36);
        }

        .glass-btn-primary {
            background: rgba(37, 99, 235, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .glass-btn-primary:hover {
            background: rgba(37, 99, 235, 0.3);
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
        }

        .glass-btn-secondary {
            background: rgba(55, 65, 81, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(75, 85, 99, 0.3);
        }

        .glass-action-btn {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-action-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        /* Search Animation */
        @keyframes searchPulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .search-pulse-animation .animate-pulse {
            animation: searchPulse 1.5s ease-in-out infinite;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(30, 41, 59, 0.3);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.7);
        }

        /* Hover effects */
        tr:hover td {
            background: linear-gradient(90deg, 
                rgba(59, 130, 246, 0.05) 0%, 
                rgba(168, 85, 247, 0.05) 100%);
        }

        /* Force select options to be dark */
        select option {
            background-color: #0f172a !important;
            color: white !important;
        }

        select:hover, select:focus {
            border-color: #3b82f6 !important;
        }

        .hidden {
            display: none;
        }

        .rotate-180 {
            transform: rotate(180deg);
        }
        
        /* Untuk truncate text */
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .max-w-\[180px\] {
            max-width: 180px;
        }
        
        .max-w-\[150px\] {
            max-width: 150px;
        }
        
        .max-w-\[120px\] {
            max-width: 120px;
        }

        /* Clean Navigation Tabs Styles */
        .tab-link {
            position: relative;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #94a3b8;
            transition: all 0.2s ease;
            border-radius: 0.5rem;
        }

        .tab-link:hover {
            color: #e2e8f0;
            background: rgba(255, 255, 255, 0.03);
        }

        .tab-link.active {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
        }

        .tab-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: #3b82f6;
        }

        .tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.5rem;
            height: 1.25rem;
            padding: 0 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.03);
            color: #94a3b8;
            transition: all 0.2s ease;
        }

        .tab-link.active .tab-badge {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .tab-link {
                padding: 0.5rem 1rem;
                font-size: 0.813rem;
            }
            
            .tab-link svg {
                width: 1rem;
                height: 1rem;
            }
        }

        /* Loading state untuk AJAX */
        #contractsContainer {
            transition: opacity 0.2s ease;
            min-height: 200px;
        }

        /* AJAX Notification Styles */
        .ajax-notification {
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            z-index: 9999;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Loading spinner */
        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Rotate animation for filter toggle */
        .rotate-180 {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }

        /* Disable pointer events during loading */
        #contractsContainer[style*="pointer-events: none"] {
            cursor: wait;
        }

        /* Table row hover effect */
        #contractsContainer tr {
            transition: background-color 0.15s ease;
        }

        /* Responsive adjustments for notifications */
        @media (max-width: 768px) {
            .ajax-notification {
                max-width: 90%;
                right: 5%;
                top: 10px;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📋 Contracts Index page loaded with AJAX');
        
        // DOM Elements
        const filterForm = document.getElementById('filterForm');
        const filterToggle = document.getElementById('filterToggle');
        const searchInput = document.getElementById('search');
        const container = document.getElementById('contractsContainer');
        const searchPulse = document.querySelector('.search-pulse-animation');
        const perPageSelect = document.getElementById('perPage');
        
        // State
        let searchTimeout;
        
        // ─── Helper: Show search animation ─────────────────────────────────────
        function showSearchPulse() {
            if (searchPulse) {
                searchPulse.classList.remove('hidden');
                setTimeout(() => searchPulse.classList.add('hidden'), 1000);
            }
        }
        
        // ─── Core AJAX loader ──────────────────────────────────────────────────
        function loadContracts(url) {
            // Show loading state
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';
            
            // Add loading spinner
            const loadingHtml = `
                <div class="flex justify-center items-center py-12">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-full border-4 border-gray-700 border-t-blue-500 animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                    <span class="ml-3 text-gray-400">Loading contracts...</span>
                </div>
            `;
            container.innerHTML = loadingHtml;
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                container.innerHTML = html;
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
                
                // Update URL without page reload
                const newUrl = new URL(url);
                window.history.pushState({}, '', newUrl.toString());
                
                // Update results summary
                updateResultsSummary();
                
                // Re-attach event listeners for new content
                attachTableEventListeners();
                
                // Show success toast
                showNotification('Data updated successfully', 'success');
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
                showNotification('Failed to load data. Please try again.', 'error');
            });
        }
        
        // ─── Update results summary ───────────────────────────────────────────
        function updateResultsSummary() {
            // This will be populated from the new HTML content
            // For now, we'll just let the table content handle it
        }
        
        // ─── Build URL with current filters ────────────────────────────────────
        function buildFilterUrl() {
            const url = new URL(window.location.href);
            const params = new FormData(filterForm);
            
            // Clear existing params
            url.search = '';
            
            // Add tab parameter
            const tabParam = new URLSearchParams(window.location.search).get('tab');
            if (tabParam) {
                url.searchParams.set('tab', tabParam);
            }
            
            // Add form parameters
            params.forEach((value, key) => {
                if (value) {
                    url.searchParams.set(key, value);
                }
            });
            
            return url.toString();
        }
        
        // ─── Show notification ─────────────────────────────────────────────────
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            document.querySelectorAll('.ajax-notification').forEach(n => n.remove());
            
            const colors = {
                success: 'border-green-500/30 bg-green-900/10 text-green-300',
                error: 'border-red-500/30 bg-red-900/10 text-red-300',
                info: 'border-blue-500/30 bg-blue-900/10 text-blue-300'
            };
            
            const icons = {
                success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                error: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            };
            
            const notification = document.createElement('div');
            notification.className = `ajax-notification fixed top-4 right-4 z-50 glass-card border ${colors[type]} rounded-xl p-4 animate-slide-in`;
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[type]}" />
                    </svg>
                    <p class="text-sm font-medium">${message}</p>
                    <button onclick="this.closest('.ajax-notification').remove()" class="ml-4 hover:opacity-70">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            `;
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // ─── Attach event listeners to table elements ──────────────────────────
        function attachTableEventListeners() {
            // Sort dropdown (if exists in table)
            const sortSelect = document.getElementById('sort');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('sort', this.value);
                    url.searchParams.set('page', '1');
                    loadContracts(url.toString());
                });
            }
            
            // Delete buttons
            document.querySelectorAll('.delete-contract-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    const title = this.dataset.title || 'this contract';
                    const isAdmin = this.dataset.isAdmin === 'true';
                    
                    if (confirmDeleteSimple(title, isAdmin)) {
                        // Show loading on button
                        const originalHtml = this.innerHTML;
                        this.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                        
                        fetch(href, {
                            method: 'DELETE',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Delete failed');
                            // Reload current page
                            loadContracts(window.location.href);
                            showNotification('Contract deleted successfully', 'success');
                        })
                        .catch(error => {
                            console.error('Delete error:', error);
                            showNotification('Failed to delete contract', 'error');
                            this.innerHTML = originalHtml;
                        });
                    }
                });
            });
        }
        
        // ─── Filter toggle ─────────────────────────────────────────────────────
        if (filterToggle) {
            filterToggle.addEventListener('click', function() {
                filterForm.classList.toggle('hidden');
                const svg = this.querySelector('svg');
                if (svg) {
                    svg.classList.toggle('rotate-180');
                }
            });
        }
        
        // ─── Form submit handler ───────────────────────────────────────────────
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                showSearchPulse();
                loadContracts(buildFilterUrl());
            });
            
            // Auto-submit on dropdown changes
            ['status', 'contract_type', 'date_range'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('change', function() {
                        filterForm.dispatchEvent(new Event('submit'));
                    });
                }
            });
        }
        
        // ─── Search with debounce ──────────────────────────────────────────────
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterForm.dispatchEvent(new Event('submit'));
                }, 600); // 600ms debounce
            });
        }
        
        // ─── Per page change ───────────────────────────────────────────────────
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', this.value);
                url.searchParams.set('page', '1');
                loadContracts(url.toString());
            });
        }
        
        // ─── Tab navigation ────────────────────────────────────────────────────
        document.querySelectorAll('.tab-link').forEach(tabLink => {
            tabLink.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                
                // Update active tab styling
                document.querySelectorAll('.tab-link').forEach(link => {
                    link.classList.remove('text-blue-400', 'bg-blue-500/5', 'text-purple-400', 'bg-purple-500/5', 'text-emerald-400', 'bg-emerald-500/5');
                    link.classList.add('text-gray-400');
                    
                    // Remove active indicator
                    const indicator = link.querySelector('.absolute.bottom-0');
                    if (indicator) indicator.remove();
                });
                
                // Add active class based on tab
                if (url.includes('tab=requests')) {
                    this.classList.add('text-blue-400', 'bg-blue-500/5');
                    this.innerHTML += '<span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-500"></span>';
                } else if (url.includes('tab=reviews')) {
                    this.classList.add('text-purple-400', 'bg-purple-500/5');
                    this.innerHTML += '<span class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500"></span>';
                } else if (url.includes('tab=all')) {
                    this.classList.add('text-emerald-400', 'bg-emerald-500/5');
                    this.innerHTML += '<span class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500"></span>';
                }
                
                loadContracts(url);
            });
        });
        
        // ─── Pagination (event delegation) ─────────────────────────────────────
        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('#contractsContainer .pagination a');
            if (paginationLink) {
                e.preventDefault();
                loadContracts(paginationLink.href);
            }
        });
        
        // ─── Clear filters button ──────────────────────────────────────────────
        const clearFiltersBtn = document.querySelector('.clear-filters-btn');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                loadContracts(url);
            });
        }
        
        // ─── Remove filter badges ──────────────────────────────────────────────
        document.querySelectorAll('.remove-filter').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                loadContracts(href);
            });
        });
        
        // ─── Browser back/forward ──────────────────────────────────────────────
        window.addEventListener('popstate', function() {
            loadContracts(window.location.href);
        });
        
        // ─── Initial load with current URL (optional, already loaded by server) ─
        // Attach initial event listeners
        attachTableEventListeners();
        
        // ─── Confirm delete function (global) ──────────────────────────────────
        window.confirmDeleteSimple = function(title, isAdmin) {
            isAdmin = isAdmin === 'true';
            if (isAdmin) {
                return confirm(`⚠️ ADMIN ACTION\n\nHapus kontrak: ${title}?`);
            } else {
                return confirm(`Hapus draft: ${title}?`);
            }
        };
        
        console.log('✅ AJAX handlers initialized');
    });
    </script>
    @endpush
</x-app-layout-dark>