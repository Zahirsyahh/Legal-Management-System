@php
    use Illuminate\Support\Facades\Auth;
    use Spatie\Permission\Models\Role;
    use App\Models\Contract;

    $user = Auth::user();
    $allRoles = Role::all();
    $userRoles = $user?->roles->pluck('name')->toArray() ?? [];
    
    $totalStats = [
        'total'        => Contract::count(),
        'draft'        => Contract::where('status', 'draft')->count(),
        'submitted'    => Contract::where('status', 'submitted')->count(),
        'under_review' => Contract::where('status', 'under_review')->count(),
        'executed'     => Contract::where('status', 'executed')->count(),
        'archived'     => Contract::where('status', 'archived')->count(),
        'released'     => Contract::where('status', 'released')->count(),
        'declined'     => Contract::where('status', 'declined')->count(),
    ];
@endphp

<x-app-layout-dark title="My Contracts">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        <!-- Success Notification -->
        @if(session('success'))
        <div id="success-notification" class="mb-6 animate-fade-in">
            <div class="glass-card border border-green-500/30 bg-green-900/10 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="h-6 w-6 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-green-300">{{ session('success') }}</p>
                    </div>
                    <button type="button" onclick="this.closest('#success-notification').remove()"
                            class="ml-4 text-green-400 hover:text-green-300 transition-colors">
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
                        <svg class="h-6 w-6 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-red-300">{{ session('error') }}</p>
                    </div>
                    <button type="button" onclick="this.closest('#error-notification').remove()"
                            class="ml-4 text-red-400 hover:text-red-300 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Info Notification -->
        @if(session('info'))
        <div id="info-notification" class="mb-6 animate-fade-in">
            <div class="glass-card border border-blue-500/30 bg-blue-900/10 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="h-6 w-6 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-blue-300">{{ session('info') }}</p>
                    </div>
                    <button type="button" onclick="this.closest('#info-notification').remove()"
                            class="ml-4 text-blue-400 hover:text-blue-300 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                    <div class="absolute right-0 mt-2 w-80 glass-card rounded-lg shadow-2xl border border-gray-800/50 
                                opacity-0 invisible transition-all duration-200 transform origin-top-right scale-95 z-50 
                                group-hover:opacity-100 group-hover:visible group-hover:scale-100">
                        <div class="p-2">
                            <a href="{{ route('contracts.create', ['type' => 'contract']) }}"
                               class="dropdown-option w-full text-left px-3 py-3 rounded-md flex items-center gap-3 
                                      hover:bg-blue-500/10 hover:text-blue-400 transition-all duration-200 group">
                                <div class="p-1.5 rounded-md bg-gradient-to-br from-blue-500/10 to-cyan-600/10 
                                            group-hover:from-blue-500/20 group-hover:to-cyan-600/20">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-sm">Document Review</div>
                                    <div class="text-xs text-gray-500 group-hover:text-blue-300/70">Submit contract or letter for review</div>
                                </div>
                                <svg class="w-4 h-4 text-blue-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>

                            <div class="my-2 border-t border-gray-800/50"></div>

                            <a href="{{ route('surat.create') }}"
                               class="dropdown-option w-full text-left px-3 py-3 rounded-md flex items-center gap-3 
                                      hover:bg-purple-500/10 hover:text-purple-400 transition-all duration-200 group">
                                <div class="p-1.5 rounded-md bg-gradient-to-br from-purple-500/10 to-pink-600/10 
                                            group-hover:from-purple-500/20 group-hover:to-pink-600/20">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-sm">Request Letter Numbering</div>
                                    <div class="text-xs text-gray-500 group-hover:text-purple-300/70">Generate official letter number</div>
                                </div>
                                <svg class="w-4 h-4 text-purple-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>

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
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-3 mb-6">
            <div class="glass-card rounded-xl p-3 text-center hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 group">
                <p class="text-xs text-gray-400 group-hover:text-gray-300">Total</p>
                <p class="text-lg font-bold mt-1 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">{{ $totalStats['total'] }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center hover:shadow-lg hover:shadow-gray-500/10 transition-all duration-300 group">
                <p class="text-xs text-gray-400 group-hover:text-gray-300">Draft</p>
                <p class="text-lg font-bold mt-1 text-gray-400">{{ $totalStats['draft'] }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 group">
                <p class="text-xs text-gray-400 group-hover:text-gray-300">Submitted</p>
                <p class="text-lg font-bold mt-1 text-blue-400">{{ $totalStats['submitted'] }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center hover:shadow-lg hover:shadow-yellow-500/10 transition-all duration-300 group">
                <p class="text-xs text-gray-400 group-hover:text-gray-300">Under Review</p>
                <p class="text-lg font-bold mt-1 text-yellow-400">{{ $totalStats['under_review'] }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center hover:shadow-lg hover:shadow-green-500/10 transition-all duration-300 group">
                <p class="text-xs text-gray-400 group-hover:text-gray-300">Executed</p>
                <p class="text-lg font-bold mt-1 text-green-400">{{ $totalStats['executed'] }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center hover:shadow-lg hover:shadow-purple-500/10 transition-all duration-300 group">
                <p class="text-xs text-gray-400 group-hover:text-gray-300">Archived</p>
                <p class="text-lg font-bold mt-1 text-purple-400">{{ $totalStats['archived'] }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 group">
                <p class="text-xs text-gray-400 group-hover:text-gray-300">Released</p>
                <p class="text-lg font-bold mt-1 text-emerald-400">{{ $totalStats['released'] }}</p>
            </div>
            <div class="glass-card rounded-xl p-3 text-center hover:shadow-lg hover:shadow-red-500/10 transition-all duration-300 group">
                <p class="text-xs text-gray-400 group-hover:text-gray-300">Declined</p>
                <p class="text-lg font-bold mt-1 text-red-400">{{ $totalStats['declined'] }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="relative mb-6">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-purple-500/5 rounded-2xl blur-xl"></div>
            <div class="relative glass-card-luxury rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Advanced Filters
                        </h3>
                        <p class="text-sm text-gray-400 mt-1">Refine your document search</p>
                    </div>
                </div>

                <form id="filterForm">
                    <input type="hidden" name="tab" id="tabInput" value="{{ $activeTab }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">

                        <!-- Status -->
                        <div class="space-y-2">
                            <label for="status" class="block text-sm font-medium text-gray-300">Status</label>
                            <select name="status" id="status"
                                class="w-full px-4 py-3 rounded-xl text-sm bg-[#0f172a] text-white border border-[#334155]">
                                <option value="">All Status</option>
                                <option value="draft"            {{ request('status') == 'draft'            ? 'selected' : '' }}>Draft</option>
                                <option value="submitted"        {{ request('status') == 'submitted'        ? 'selected' : '' }}>Submitted</option>
                                <option value="under_review"     {{ request('status') == 'under_review'     ? 'selected' : '' }}>Under Review</option>
                                <option value="revision_needed"  {{ request('status') == 'revision_needed'  ? 'selected' : '' }}>Revision Needed</option>
                                <option value="number_issued"    {{ request('status') == 'number_issued'    ? 'selected' : '' }}>Number Issued</option>
                                <option value="executed"         {{ request('status') == 'executed'         ? 'selected' : '' }}>Executed</option>
                                <option value="archived"         {{ request('status') == 'archived'         ? 'selected' : '' }}>Archived</option>
                                <option value="final_approved"   {{ request('status') == 'final_approved'   ? 'selected' : '' }}>Approved (Surat)</option>
                                <option value="released"         {{ request('status') == 'released'         ? 'selected' : '' }}>Completed (Surat)</option>
                                <option value="declined"         {{ request('status') == 'declined'         ? 'selected' : '' }}>Declined / Rejected</option>
                                <option value="cancelled"        {{ request('status') == 'cancelled'        ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <!-- Document Type -->
                        <div class="space-y-2">
                            <label for="contract_type" class="block text-sm font-medium text-gray-300">Document Type</label>
                            <select name="contract_type" id="contract_type"
                                class="w-full px-4 py-3 rounded-xl text-sm bg-[#0f172a] text-white border border-[#334155]">
                                <option value="">All Types</option>
                                <option value="Kontrak" {{ request('contract_type') == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                                <option value="Surat"   {{ request('contract_type') == 'Surat'   ? 'selected' : '' }}>Surat</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="space-y-2">
                            <label for="date_range" class="block text-sm font-medium text-gray-300">Date Range</label>
                            <select name="date_range" id="date_range"
                                class="w-full px-4 py-3 rounded-xl text-sm bg-[#0f172a] text-white border border-[#334155]">
                                <option value="">All Time</option>
                                <option value="today"   {{ request('date_range') == 'today'   ? 'selected' : '' }}>Today</option>
                                <option value="week"    {{ request('date_range') == 'week'    ? 'selected' : '' }}>This Week</option>
                                <option value="month"   {{ request('date_range') == 'month'   ? 'selected' : '' }}>This Month</option>
                                <option value="quarter" {{ request('date_range') == 'quarter' ? 'selected' : '' }}>Last 3 Months</option>
                                <option value="year"    {{ request('date_range') == 'year'    ? 'selected' : '' }}>This Year</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="space-y-2">
                            <label for="search" class="block text-sm font-medium text-gray-300">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                placeholder="Title, number, counterparty..."
                                class="w-full px-4 py-3 rounded-xl text-sm bg-[#0f172a] text-white border border-[#334155] placeholder-gray-500">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-gray-800/50">
                        <button type="button" id="applyFiltersBtn"
                            class="glass-btn-primary px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 
                                   hover:from-blue-700 hover:to-cyan-700 rounded-xl font-medium 
                                   flex items-center gap-2 transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Apply Filters
                        </button>

                        <button type="button" id="clearFiltersBtn"
                            class="glass-btn-secondary px-6 py-3 bg-gray-800/50 hover:bg-gray-700/50 
                                   text-gray-300 rounded-xl font-medium flex items-center gap-2 
                                   transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear All
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="mb-4">
            <div class="glass-card-luxury rounded-t-2xl border-b-0 overflow-hidden" style="background: rgba(30, 41, 59, 0.4);">
                <div class="px-6 pt-4">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1 h-4 bg-gray-600 rounded-full"></div>
                        <span class="text-xs font-medium tracking-wider text-gray-500 uppercase">VIEW MODE</span>
                    </div>
                    
                    <ul class="flex items-center gap-1 pb-0">
                        @role('user')
                            <li class="flex-1 sm:flex-initial">
                                <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                    {{ $activeTab === 'requests' ? 'text-blue-400 bg-blue-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                   href="#" data-tab="requests">
                                    <span>My Requests</span>
                                    @if($activeTab === 'requests')
                                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-500"></span>
                                    @endif
                                </a>
                            </li>
                        @endrole

                        @role('admin')
                            <li class="flex-1 sm:flex-initial">
                                <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                    {{ $activeTab === 'all' ? 'text-emerald-400 bg-emerald-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                   href="#" data-tab="all">
                                    <span>All Documents</span>
                                    @if($activeTab === 'all')
                                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500"></span>
                                    @endif
                                </a>
                            </li>
                        @endrole

                        @hasanyrole('legal|admin_fin|admin_tax|admin_acc|staff_fin|staff_tax|staff_acc')
                            <li class="flex-1 sm:flex-initial">
                                <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                    {{ $activeTab === 'requests' ? 'text-blue-400 bg-blue-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                   href="#" data-tab="requests">
                                    <span>My Requests</span>
                                    @if($activeTab === 'requests')
                                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-500"></span>
                                    @endif
                                </a>
                            </li>

                            <li class="flex-1 sm:flex-initial">
                                <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                    {{ $activeTab === 'reviews' ? 'text-purple-400 bg-purple-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                   href="#" data-tab="reviews">
                                    <span>My Reviews</span>
                                    @if($activeTab === 'reviews')
                                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500"></span>
                                    @endif
                                </a>
                            </li>

                            @can('contract_view_all')
                                <li class="flex-1 sm:flex-initial">
                                    <a class="group relative flex items-center justify-center sm:justify-start gap-2 px-5 py-3 rounded-t-xl text-sm font-medium tab-link
                                        {{ $activeTab === 'all' ? 'text-emerald-400 bg-emerald-500/5' : 'text-gray-400 hover:text-gray-300 hover:bg-white/5' }}"
                                       href="#" data-tab="all">
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
            <div class="h-px bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>
        </div>

        <!-- Results Summary (outside table, updated by JS after each AJAX call) -->
        <div class="flex items-center justify-between mb-6 px-2">
            <div class="text-xs text-gray-400" id="resultsSummary">
                <span class="font-medium text-gray-300">{{ $contracts->total() }}</span> documents found
                @if($contracts->count() > 0)
                    <span class="mx-1">•</span>
                    <span class="font-medium text-gray-300">{{ $contracts->firstItem() }}</span>–<span class="font-medium text-gray-300">{{ $contracts->lastItem() }}</span>
                @endif
            </div>

            @if($contracts->total() > 0)
            <div class="flex items-center gap-2">
                <label for="perPage" class="text-xs text-gray-400">Show:</label>
                <select id="perPage" class="text-xs bg-gray-800/50 border border-gray-700 rounded px-2 py-1 text-white">
                    <option value="10"  {{ request('per_page', 10) == 10  ? 'selected' : '' }}>10</option>
                    <option value="25"  {{ request('per_page') == 25  ? 'selected' : '' }}>25</option>
                    <option value="50"  {{ request('per_page') == 50  ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            @endif
        </div>

        <!-- ✅ AJAX table target - everything inside here gets replaced on each request -->
        <div id="contractsTable">
            @include('contracts.partials.table')
        </div>

    </div>

    @push('styles')
    <style>
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
        .tab-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #94a3b8;
            transition: all 0.2s ease;
            border-radius: 0.5rem 0.5rem 0 0;
            text-decoration: none;
        }
        .tab-link:hover { color: #e2e8f0; background: rgba(255,255,255,0.03); }
        .tab-link.active { border-bottom: 2px solid currentColor; }
        select option { background-color: #0f172a !important; color: white !important; }

        /* Table AJAX transition */
        #contractsTable {
            transition: opacity 0.15s ease;
            min-height: 200px;
        }
        #contractsTable.loading {
            opacity: 0.4;
            pointer-events: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    </style>
    @endpush

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // ─────────────────────────────────────────────
        // ELEMENTS
        // ─────────────────────────────────────────────
        const tableContainer  = document.getElementById('contractsTable');
        const resultsSummary  = document.getElementById('resultsSummary');
        const searchInput     = document.getElementById('search');
        const applyBtn        = document.getElementById('applyFiltersBtn');
        const clearBtn        = document.getElementById('clearFiltersBtn');
        const perPageSelect   = document.getElementById('perPage');

        // ─────────────────────────────────────────────
        // BUILD URL  (preserves current tab + filters)
        // ─────────────────────────────────────────────
        function buildUrl(overrides = {}) {
            // Start from current URL so existing params (tab, filters) are kept
            const url = new URL(window.location.href);

            // Pick live filter values
            const live = {
                status:        document.getElementById('status')?.value        || '',
                contract_type: document.getElementById('contract_type')?.value || '',
                date_range:    document.getElementById('date_range')?.value    || '',
                search:        document.getElementById('search')?.value        || '',
            };

            // Apply live filters
            Object.entries(live).forEach(([k, v]) => {
                if (v) url.searchParams.set(k, v);
                else   url.searchParams.delete(k);
            });

            // Apply caller overrides (tab, page, per_page, etc.)
            Object.entries(overrides).forEach(([k, v]) => {
                if (v !== null && v !== undefined && v !== '') url.searchParams.set(k, v);
                else url.searchParams.delete(k);
            });

            return url;
        }

        // ─────────────────────────────────────────────
        // LOAD TABLE via AJAX
        // ─────────────────────────────────────────────
        function loadTable(url) {
            // Accept both URL object and string
            const finalUrl = (url instanceof URL) ? url.toString() : url;

            tableContainer.classList.add('loading');

            fetch(finalUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.text();
            })
            .then(html => {
                // Replace table content
                tableContainer.innerHTML = html;
                tableContainer.classList.remove('loading');

                // Update results summary from the data-summary element inside the new HTML
                const tmp = document.createElement('div');
                tmp.innerHTML = html;
                const newSummary = tmp.querySelector('[data-summary]');
                if (newSummary && resultsSummary) {
                    resultsSummary.innerHTML = newSummary.innerHTML;
                }

                // Push state so browser URL reflects current view
                window.history.pushState({ url: finalUrl }, '', finalUrl);

                // Re-sync tab highlight (tab may have changed)
                syncTabStyles();
            })
            .catch(err => {
                console.error('AJAX load failed:', err);
                tableContainer.classList.remove('loading');
            });
        }

        // ─────────────────────────────────────────────
        // SYNC TAB HIGHLIGHT
        // ─────────────────────────────────────────────
        function syncTabStyles() {
            const currentTab = new URL(window.location.href).searchParams.get('tab') || '{{ $activeTab }}';
            document.querySelectorAll('.tab-link').forEach(el => {
                const tab = el.dataset.tab;
                // Strip all state classes first
                el.classList.remove(
                    'active',
                    'text-blue-400',    'bg-blue-500/5',
                    'text-emerald-400', 'bg-emerald-500/5',
                    'text-purple-400',  'bg-purple-500/5',
                    'text-gray-400',    'hover:text-gray-300', 'hover:bg-white/5'
                );
                if (tab === currentTab) {
                    el.classList.add('active');
                    if (tab === 'requests') el.classList.add('text-blue-400',    'bg-blue-500/5');
                    if (tab === 'all')      el.classList.add('text-emerald-400', 'bg-emerald-500/5');
                    if (tab === 'reviews')  el.classList.add('text-purple-400',  'bg-purple-500/5');
                } else {
                    el.classList.add('text-gray-400', 'hover:text-gray-300', 'hover:bg-white/5');
                }
            });
        }

        // ─────────────────────────────────────────────
        // APPLY FILTERS button
        // ─────────────────────────────────────────────
        if (applyBtn) {
            applyBtn.addEventListener('click', () => loadTable(buildUrl({ page: 1 })));
        }

        // ─────────────────────────────────────────────
        // CLEAR FILTERS button
        // ─────────────────────────────────────────────
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                ['status', 'contract_type', 'date_range', 'search'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
                loadTable(buildUrl({ page: null }));
            });
        }

        // ─────────────────────────────────────────────
        // SEARCH with debounce
        // ─────────────────────────────────────────────
        if (searchInput) {
            let timer;
            searchInput.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => loadTable(buildUrl({ page: 1 })), 450);
            });
        }

        // ─────────────────────────────────────────────
        // PREVENT form submit (Enter key in search)
        // ─────────────────────────────────────────────
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', e => {
                e.preventDefault();
                loadTable(buildUrl({ page: 1 }));
            });
        }

        // ─────────────────────────────────────────────
        // PER PAGE select
        // ─────────────────────────────────────────────
        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => {
                loadTable(buildUrl({ per_page: perPageSelect.value, page: 1 }));
            });
        }

        // ─────────────────────────────────────────────
        // TABS
        // ─────────────────────────────────────────────
        document.querySelectorAll('.tab-link').forEach(tab => {
            tab.addEventListener('click', e => {
                e.preventDefault();
                loadTable(buildUrl({ tab: tab.dataset.tab, page: 1 }));
            });
        });

        // ─────────────────────────────────────────────
        // EVENT DELEGATION — handles:
        //   • Pagination links  (rendered inside #contractsTable)
        //   • Action buttons    (submit / return-to-draft / delete)
        // ─────────────────────────────────────────────
        document.addEventListener('click', function (e) {

            // ── PAGINATION ──────────────────────────
            // Laravel renders links as <a> inside a nav/div with class "pagination"
            // We intercept any <a> that lives inside .pagination-container
            const paginationLink = e.target.closest('.pagination-container a');
            if (paginationLink) {
                e.preventDefault();
                console.log('Pagination clicked:', paginationLink.href); // debug
                loadTable(paginationLink.href);
                return;
            }

            // ── ACTION BUTTONS ───────────────────────
            const actionBtn = e.target.closest('[data-action]');
            if (!actionBtn) return;

            e.preventDefault();
            const action     = actionBtn.dataset.action;
            const url        = actionBtn.dataset.url;
            const confirmMsg = actionBtn.dataset.confirm;

            if (confirmMsg && !confirm(confirmMsg)) return;

            // submit / return-to-draft  → POST
            if (action === 'submit' || action === 'return-to-draft') {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN':      document.querySelector('meta[name="csrf-token"]')?.content,
                        'X-Requested-With':  'XMLHttpRequest',
                    }
                })
                .then(r => {
                    if (!r.ok) throw new Error('Action failed');
                    loadTable(window.location.href);
                })
                .catch(() => alert('Action failed. Please try again.'));
                return;
            }

            // delete → DELETE
            if (action === 'delete') {
                const title   = actionBtn.dataset.title   || 'this document';
                const isAdmin = actionBtn.dataset.isAdmin === 'true';
                const msg     = isAdmin
                    ? `⚠️ ADMIN DELETE\n\nPermanently delete: "${title}"?`
                    : `Delete: "${title}"?`;

                if (!confirm(msg)) return;

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN':      document.querySelector('meta[name="csrf-token"]')?.content,
                        'X-Requested-With':  'XMLHttpRequest',
                    }
                })
                .then(r => {
                    if (!r.ok) throw new Error('Delete failed');
                    loadTable(window.location.href);
                })
                .catch(() => alert('Delete failed. Please try again.'));
            }
        });

        // ─────────────────────────────────────────────
        // BROWSER BACK / FORWARD
        // ─────────────────────────────────────────────
        window.addEventListener('popstate', () => {
            loadTable(window.location.href);
        });

        // ─────────────────────────────────────────────
        // INITIAL sync on page load
        // ─────────────────────────────────────────────
        syncTabStyles();
    });
    </script>
    @endpush
</x-app-layout-dark>