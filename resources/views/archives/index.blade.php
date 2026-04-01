<x-app-layout-dark title="Legal Archives">
    
    <style>
        /* Modern Dashboard Layout Styles */
        :root {
            --primary-gradient: linear-gradient(135deg, #0ea5e9, #3b82f6);
            --secondary-gradient: linear-gradient(135deg, #8b5cf6, #6366f1);
            --success-gradient: linear-gradient(135deg, #10b981, #059669);
            --warning-gradient: linear-gradient(135deg, #f59e0b, #d97706);
            --danger-gradient: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        /* Card Styles */
        .stat-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.8));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(14, 165, 233, 0.15);
            border-radius: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(14, 165, 233, 0.3);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.3);
        }
        
        /* Modern Table Design */
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .modern-table th {
            background: rgba(15, 23, 42, 0.6);
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            border-bottom: 1px solid rgba(14, 165, 233, 0.2);
        }
        
        .modern-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.2s ease;
        }
        
        .modern-table tr {
            transition: all 0.2s ease;
        }
        
        .modern-table tbody tr:hover {
            background: rgba(14, 165, 233, 0.05);
            transform: scale(1.01);
        }
        
        /* Document Card View (Alternative Layout) */
        .document-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.5), rgba(15, 23, 42, 0.7));
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .document-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .document-card:hover::before {
            transform: scaleX(1);
        }
        
        .document-card:hover {
            transform: translateY(-4px);
            border-color: rgba(14, 165, 233, 0.3);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
        }
        
        /* Badge Styles */
        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .type-badge.contract { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(59, 130, 246, 0.3); }
        .type-badge.invoice  { background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        .type-badge.report   { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); }
        .type-badge.legal    { background: rgba(139, 92, 246, 0.15); color: #c084fc; border-color: rgba(139, 92, 246, 0.3); }
        
        /* Version Badge */
        .version-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            border: 1px solid;
        }

        .version-badge.latest     { background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        .version-badge.obsolete   { background: rgba(239, 68, 68, 0.15);  color: #f87171; border-color: rgba(239, 68, 68, 0.3); }
        .version-badge.superseded { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); }

        /* Validity Badge */
        .validity-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.2rem 0.6rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
            border: 1px solid;
        }

        .validity-badge.ongoing { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border-color: rgba(14, 165, 233, 0.3); }
        .validity-badge.ended   { background: rgba(107, 114, 128, 0.15); color: #9ca3af; border-color: rgba(107, 114, 128, 0.3); }

        /* Action Buttons */
        .action-icon {
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .action-icon:hover {
            transform: translateY(-2px);
        }
        
        .action-icon.view:hover   { background: rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.3); }
        .action-icon.edit:hover   { background: rgba(245, 158, 11, 0.2); border-color: rgba(245, 158, 11, 0.3); }
        .action-icon.delete:hover { background: rgba(239, 68, 68, 0.2);  border-color: rgba(239, 68, 68, 0.3); }
        
        /* Search & Filter Bar */
        .search-bar {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-bar:focus-within {
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        
        /* View Toggle */
        .view-toggle {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 0.25rem;
        }
        
        .view-toggle-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            background: transparent;
            color: #9ca3af;
        }
        
        .view-toggle-btn.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }
        
        /* Pagination Modern */
        .pagination-modern {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .page-link {
            padding: 0.5rem 0.875rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }
        
        .page-link:hover:not(.active):not(.disabled) {
            background: rgba(14, 165, 233, 0.1);
            border-color: rgba(14, 165, 233, 0.3);
            transform: translateY(-1px);
        }
        
        .page-link.active {
            background: var(--primary-gradient);
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }
        
        .page-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Animations */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        
        .animate-slide-up { animation: slideInUp 0.5s ease-out; }
        .animate-fade-in  { animation: fadeIn 0.3s ease-out; }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.3), rgba(15, 23, 42, 0.5));
            border-radius: 1.5rem;
            border: 1px dashed rgba(14, 165, 233, 0.3);
        }
        
        /* Quick Stats */
        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(59, 130, 246, 0.05));
            border: 1px solid rgba(14, 165, 233, 0.2);
        }
        
        /* Filter Dropdown */
        .filter-dropdown {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.5);
        }
        
        /* Loading Shimmer */
        .shimmer {
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0) 100%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-slide-up">
        
        <!-- Hero Header Section -->
        <div class="relative mb-12">
            <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/10 via-blue-500/5 to-transparent rounded-3xl blur-3xl"></div>
            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="stat-icon">
                            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold bg-gradient-to-r from-cyan-400 via-blue-400 to-cyan-400 bg-clip-text text-transparent">
                                Legal Archives
                            </h1>
                            <p class="text-gray-400 mt-1">Centralized document management system</p>
                        </div>
                    </div>
                    
                    <!-- Quick Stats Row — hanya Total Archives -->
                    <div class="mt-8">
                        <div class="stat-card p-4 inline-flex items-center gap-6 min-w-[220px]">
                            <div>
                                <p class="text-gray-400 text-sm">Total Archives</p>
                                <p class="text-2xl font-bold text-white mt-1">{{ $archives->total() }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('archives.create') }}" 
                       class="group relative px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-medium overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-cyan-500/25 hover:-translate-y-0.5">
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create New Entry
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500 to-blue-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                    
                    <button id="filterButton" 
                            class="px-5 py-3 rounded-xl bg-gray-800/50 border border-gray-700 text-gray-300 font-medium flex items-center gap-2 hover:bg-gray-700/50 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filters
                        <span id="activeFilterBadge" class="hidden bg-cyan-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Search & View Toggle Bar -->
        <div class="search-bar p-3 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative flex-1 w-full">
                <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" 
                       id="searchInput"
                       placeholder="Search by Record ID, Document Name, or Company..."
                       class="w-full pl-12 pr-4 py-3 bg-transparent border border-gray-700 rounded-xl focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all outline-none text-white"
                       value="{{ request('search', '') }}">
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Sort Dropdown -->
                <select id="sortSelect" class="px-4 py-2.5 bg-gray-800/50 border border-gray-700 rounded-xl text-gray-300 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all outline-none">
                    <option value="record_id_asc">Sort by ID (A-Z)</option>
                    <option value="record_id_desc">Sort by ID (Z-A)</option>
                    <option value="name_asc" selected>Sort by Name (A-Z)</option>
                    <option value="name_desc">Sort by Name (Z-A)</option>
                    <option value="date_desc">Newest First</option>
                    <option value="date_asc">Oldest First</option>
                </select>
                
                <!-- View Toggle -->
                <div class="view-toggle flex">
                    <button id="tableViewBtn" class="view-toggle-btn active flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        Table
                    </button>
                    <button id="cardViewBtn" class="view-toggle-btn flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/>
                        </svg>
                        Cards
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Filter Dropdown Panel -->
        <div id="filterPanel" class="filter-dropdown hidden mb-6 p-5 rounded-xl animate-fade-in">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Filter Archives</h3>
                <button id="closeFilterPanel" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Document Type</label>
                    <div class="space-y-2">
                        @foreach($archives->pluck('doc_type')->unique()->filter() as $type)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="filter-type rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500" value="{{ $type }}">
                            <span class="text-sm text-gray-300">{{ $type }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Department</label>
                    <div class="space-y-2">
                        @foreach($archives->pluck('department_code')->unique()->filter() as $dept)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="filter-dept rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500" value="{{ $dept }}">
                            <span class="text-sm text-gray-300">{{ $dept }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Version Status</label>
                    <div class="space-y-2">
                        @foreach(\App\Models\Archive::VERSION_STATUS_LABEL as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="filter-version rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500" value="{{ $value }}">
                            <span class="text-sm text-gray-300">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-700">
                <button id="applyFilters" class="px-5 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 rounded-lg text-white hover:shadow-lg transition-all">Apply Filters</button>
                <button id="clearFilters" class="px-5 py-2 bg-gray-800 rounded-lg text-gray-300 hover:bg-gray-700 transition-all">Clear All</button>
            </div>
        </div>
        
        <!-- Active Filter Chips -->
        <div id="filterChips" class="flex flex-wrap gap-2 mb-6"></div>
        
        <!-- TABLE VIEW -->
        <div id="tableView" class="animate-fade-in">
            <div class="bg-gray-900/30 backdrop-blur-sm rounded-2xl border border-gray-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Record ID</th>
                                <th>Document Name</th>
                                <th>Version</th>
                                <th>Validity Date</th>
                                <th>Department</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($archives as $archive)
                            <tr class="group">
                                <td class="font-mono text-cyan-300 font-medium">{{ $archive->record_id }}</td>

                                <td>
                                    <div class="font-medium text-white">{{ $archive->doc_name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $archive->created_at ? $archive->created_at->format('d M Y') : 'N/A' }}</div>
                                </td>

                                {{-- Version --}}
                                <td>
                                    @php
                                        $versionClass = match($archive->version_status) {
                                            'latest'     => 'latest',
                                            'obsolete'   => 'obsolete',
                                            'superseded' => 'superseded',
                                            default      => '',
                                        };
                                        $versionLabel = \App\Models\Archive::VERSION_STATUS_LABEL[$archive->version_status] ?? $archive->version_status;
                                    @endphp
                                    <span class="version-badge {{ $versionClass }}">
                                        {{ $versionLabel }}
                                    </span>
                                </td>

                                {{-- Validity Date --}}
                                <td>
                                    @php
                                        $isOngoing = $archive->validity_status === 'ongoing';
                                    @endphp
                                    <div class="flex flex-col gap-1">
                                        <div class="text-sm text-gray-300">
                                            {{ $archive->start_date ? $archive->start_date->format('d M Y') : '—' }}
                                            @if($archive->end_date)
                                                <span class="text-gray-500 mx-1">→</span>
                                                {{ $archive->end_date->format('d M Y') }}
                                            @else
                                                <span class="text-gray-500 mx-1">→</span>
                                                <span class="text-gray-500 italic">No end date</span>
                                            @endif
                                        </div>
                                        <span class="validity-badge {{ $isOngoing ? 'ongoing' : 'ended' }} self-start">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $isOngoing ? 'bg-cyan-400' : 'bg-gray-400' }}"></span>
                                            {{ $isOngoing ? 'Ongoing' : 'Ended' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="inline-flex items-center gap-1.5 text-sm text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                                        </svg>
                                        {{ $archive->department_code }}
                                    </span>
                                </td>

                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('archives.show', $archive->id) }}" class="action-icon view inline-flex" title="View">
                                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('archives.edit', $archive->id) }}" class="action-icon edit inline-flex" title="Edit">
                                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('archives.destroy', $archive->id) }}" method="POST" class="inline" onsubmit="return confirmDelete('{{ addslashes($archive->doc_name) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon delete inline-flex" title="Delete">
                                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-16">
                                    <div class="empty-state">
                                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-800/50 flex items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold text-white mb-2">No Archives Found</h3>
                                        <p class="text-gray-400">No archives match your search or filter criteria</p>
                                        <a href="{{ route('archives.create') }}" class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 rounded-lg text-white hover:shadow-lg transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Create Your First Archive
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($archives->hasPages())
                <div class="px-6 py-4 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-400">
                        Showing {{ $archives->firstItem() ?? 0 }} to {{ $archives->lastItem() ?? 0 }} of {{ $archives->total() }} records
                    </div>
                    <div class="pagination-modern">
                        <button onclick="changePage({{ $archives->currentPage() - 1 }})" 
                                class="page-link {{ $archives->onFirstPage() ? 'disabled' : '' }}"
                                {{ $archives->onFirstPage() ? 'disabled' : '' }}>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        
                        @php
                            $current = $archives->currentPage();
                            $last    = $archives->lastPage();
                            $start   = max(1, $current - 2);
                            $end     = min($last, $current + 2);
                            
                            if ($start > 1) {
                                echo '<button onclick="changePage(1)" class="page-link">1</button>';
                                if ($start > 2) echo '<span class="text-gray-500 px-1">...</span>';
                            }
                            
                            for ($i = $start; $i <= $end; $i++) {
                                $activeClass = $i == $current ? 'active' : '';
                                echo '<button onclick="changePage(' . $i . ')" class="page-link ' . $activeClass . '">' . $i . '</button>';
                            }
                            
                            if ($end < $last) {
                                if ($end < $last - 1) echo '<span class="text-gray-500 px-1">...</span>';
                                echo '<button onclick="changePage(' . $last . ')" class="page-link">' . $last . '</button>';
                            }
                        @endphp
                        
                        <button onclick="changePage({{ $archives->currentPage() + 1 }})" 
                                class="page-link {{ !$archives->hasMorePages() ? 'disabled' : '' }}"
                                {{ !$archives->hasMorePages() ? 'disabled' : '' }}>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- CARD VIEW -->
        <div id="cardView" class="hidden animate-fade-in">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($archives as $archive)
                <div class="document-card p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Record ID</p>
                                <p class="font-mono text-cyan-300 font-medium">{{ $archive->record_id }}</p>
                            </div>
                        </div>
                        @php
                            $versionClass = match($archive->version_status) {
                                'latest'     => 'latest',
                                'obsolete'   => 'obsolete',
                                'superseded' => 'superseded',
                                default      => '',
                            };
                            $versionLabel = \App\Models\Archive::VERSION_STATUS_LABEL[$archive->version_status] ?? $archive->version_status;
                        @endphp
                        <span class="version-badge {{ $versionClass }}">
                            {{ $versionLabel }}
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-white mb-3 line-clamp-2">{{ $archive->doc_name }}</h3>
                    
                    {{-- Validity Date --}}
                    <div class="mb-4">
                        @php $isOngoing = $archive->validity_status === 'ongoing'; @endphp
                        <p class="text-xs text-gray-500 mb-1">Validity Date</p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm text-gray-300">
                                {{ $archive->start_date ? $archive->start_date->format('d M Y') : '—' }}
                                @if($archive->end_date)
                                    <span class="text-gray-500 mx-1">→</span>{{ $archive->end_date->format('d M Y') }}
                                @else
                                    <span class="text-gray-500 mx-1">→</span><span class="italic text-gray-500">No end date</span>
                                @endif
                            </span>
                            <span class="validity-badge {{ $isOngoing ? 'ongoing' : 'ended' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isOngoing ? 'bg-cyan-400' : 'bg-gray-400' }}"></span>
                                {{ $isOngoing ? 'Ongoing' : 'Ended' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mb-4">
                        <span class="inline-flex items-center gap-1.5 text-sm text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                            </svg>
                            {{ $archive->department_code }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between pt-3 border-t border-gray-800">
                        <span class="text-xs text-gray-500">
                            {{ $archive->created_at ? $archive->created_at->format('d M Y') : 'N/A' }}
                        </span>
                        <div class="flex gap-2">
                            <a href="{{ route('archives.show', $archive->id) }}" class="p-2 rounded-lg bg-gray-800/50 hover:bg-blue-500/20 transition-all" title="View">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('archives.edit', $archive->id) }}" class="p-2 rounded-lg bg-gray-800/50 hover:bg-amber-500/20 transition-all" title="Edit">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('archives.destroy', $archive->id) }}" method="POST" class="inline" onsubmit="return confirmDelete('{{ addslashes($archive->doc_name) }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg bg-gray-800/50 hover:bg-red-500/20 transition-all" title="Delete">
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full">
                    <div class="empty-state">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-800/50 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">No Archives Found</h3>
                        <p class="text-gray-400">No archives match your search or filter criteria</p>
                    </div>
                </div>
                @endforelse
            </div>
            
            <!-- Card View Pagination -->
            @if($archives->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="pagination-modern">
                    <button onclick="changePage({{ $archives->currentPage() - 1 }})" 
                            class="page-link {{ $archives->onFirstPage() ? 'disabled' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    
                    @for($i = max(1, $archives->currentPage() - 2); $i <= min($archives->lastPage(), $archives->currentPage() + 2); $i++)
                        <button onclick="changePage({{ $i }})" class="page-link {{ $i == $archives->currentPage() ? 'active' : '' }}">{{ $i }}</button>
                    @endfor
                    
                    <button onclick="changePage({{ $archives->currentPage() + 1 }})" 
                            class="page-link {{ !$archives->hasMorePages() ? 'disabled' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Delete confirmation
            window.confirmDelete = function(docName) {
                return confirm(`Are you sure you want to delete archive "${docName}"?\nThis action cannot be undone.`);
            };
            
            // Pagination
            window.changePage = function(page) {
                let url = new URL(window.location.href);
                url.searchParams.set('page', page);
                window.location.href = url.toString();
            };
            
            // View Toggle
            const tableViewBtn = document.getElementById('tableViewBtn');
            const cardViewBtn  = document.getElementById('cardViewBtn');
            const tableView    = document.getElementById('tableView');
            const cardView     = document.getElementById('cardView');
            
            const savedView = localStorage.getItem('archiveView') || 'table';
            if (savedView === 'card') {
                tableView.classList.add('hidden');
                cardView.classList.remove('hidden');
                tableViewBtn.classList.remove('active');
                cardViewBtn.classList.add('active');
            }
            
            tableViewBtn?.addEventListener('click', function() {
                tableView.classList.remove('hidden');
                cardView.classList.add('hidden');
                tableViewBtn.classList.add('active');
                cardViewBtn.classList.remove('active');
                localStorage.setItem('archiveView', 'table');
            });
            
            cardViewBtn?.addEventListener('click', function() {
                cardView.classList.remove('hidden');
                tableView.classList.add('hidden');
                cardViewBtn.classList.add('active');
                tableViewBtn.classList.remove('active');
                localStorage.setItem('archiveView', 'card');
            });
            
            // Filter Panel
            const filterButton    = document.getElementById('filterButton');
            const filterPanel     = document.getElementById('filterPanel');
            const closeFilterPanel = document.getElementById('closeFilterPanel');
            
            filterButton?.addEventListener('click', function() {
                filterPanel.classList.toggle('hidden');
            });
            
            closeFilterPanel?.addEventListener('click', function() {
                filterPanel.classList.add('hidden');
            });
            
            // Apply Filters
            document.getElementById('applyFilters')?.addEventListener('click', function() {
                const selectedTypes    = Array.from(document.querySelectorAll('.filter-type:checked')).map(cb => cb.value);
                const selectedDepts    = Array.from(document.querySelectorAll('.filter-dept:checked')).map(cb => cb.value);
                const selectedVersions = Array.from(document.querySelectorAll('.filter-version:checked')).map(cb => cb.value);
                
                const url = new URL(window.location.href);
                if (selectedTypes.length)    url.searchParams.set('doc_types', selectedTypes.join(','));
                else                         url.searchParams.delete('doc_types');
                if (selectedDepts.length)    url.searchParams.set('departments', selectedDepts.join(','));
                else                         url.searchParams.delete('departments');
                if (selectedVersions.length) url.searchParams.set('versions', selectedVersions.join(','));
                else                         url.searchParams.delete('versions');
                url.searchParams.set('page', '1');
                
                window.location.href = url.toString();
            });
            
            document.getElementById('clearFilters')?.addEventListener('click', function() {
                const url = new URL(window.location.href);
                url.searchParams.delete('doc_types');
                url.searchParams.delete('departments');
                url.searchParams.delete('versions');
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            });
            
            // Search Input
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;
            searchInput?.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const url = new URL(window.location.href);
                    if (this.value.trim()) url.searchParams.set('search', this.value);
                    else                   url.searchParams.delete('search');
                    url.searchParams.set('page', '1');
                    window.location.href = url.toString();
                }, 500);
            });
            
            // Sort Select
            document.getElementById('sortSelect')?.addEventListener('change', function() {
                const url = new URL(window.location.href);
                switch(this.value) {
                    case 'record_id_asc':  url.searchParams.set('sort', 'record_id'); url.searchParams.set('order', 'asc');  break;
                    case 'record_id_desc': url.searchParams.set('sort', 'record_id'); url.searchParams.set('order', 'desc'); break;
                    case 'name_asc':       url.searchParams.set('sort', 'doc_name');  url.searchParams.set('order', 'asc');  break;
                    case 'name_desc':      url.searchParams.set('sort', 'doc_name');  url.searchParams.set('order', 'desc'); break;
                    case 'date_asc':       url.searchParams.set('sort', 'created_at'); url.searchParams.set('order', 'asc'); break;
                    case 'date_desc':      url.searchParams.set('sort', 'created_at'); url.searchParams.set('order', 'desc'); break;
                }
                window.location.href = url.toString();
            });
            
            // Load filters from URL → restore checkboxes
            const urlParams = new URLSearchParams(window.location.search);
            const docTypes  = urlParams.get('doc_types')?.split(',')   || [];
            const depts     = urlParams.get('departments')?.split(',')  || [];
            const versions  = urlParams.get('versions')?.split(',')     || [];
            
            document.querySelectorAll('.filter-type').forEach(cb    => { cb.checked = docTypes.includes(cb.value); });
            document.querySelectorAll('.filter-dept').forEach(cb    => { cb.checked = depts.includes(cb.value); });
            document.querySelectorAll('.filter-version').forEach(cb => { cb.checked = versions.includes(cb.value); });
            
            // Filter Chips
            const filterChips = document.getElementById('filterChips');

            function updateFilterChips() {
                let chipsHtml = '';
                const x = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
                docTypes.forEach(t  => { chipsHtml += `<button class="filter-chip px-3 py-1.5 rounded-full bg-cyan-500/20 text-cyan-300 text-sm flex items-center gap-2" data-filter="type" data-value="${t}">${t} ${x}</button>`; });
                depts.forEach(d     => { chipsHtml += `<button class="filter-chip px-3 py-1.5 rounded-full bg-purple-500/20 text-purple-300 text-sm flex items-center gap-2" data-filter="dept" data-value="${d}">${d} ${x}</button>`; });
                versions.forEach(v  => { chipsHtml += `<button class="filter-chip px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 text-sm flex items-center gap-2" data-filter="version" data-value="${v}">${v} ${x}</button>`; });
                
                filterChips.innerHTML = chipsHtml;
                
                const activeCount = docTypes.length + depts.length + versions.length;
                const badge = document.getElementById('activeFilterBadge');
                if (activeCount > 0) { badge.textContent = activeCount; badge.classList.remove('hidden'); }
                else                 { badge.classList.add('hidden'); }
                
                document.querySelectorAll('.filter-chip').forEach(chip => {
                    chip.addEventListener('click', function() {
                        removeFilter(this.dataset.filter, this.dataset.value);
                    });
                });
            }
            
            function removeFilter(filterType, filterValue) {
                const url = new URL(window.location.href);
                const paramMap = { type: 'doc_types', dept: 'departments', version: 'versions' };
                const param = paramMap[filterType];
                let values = url.searchParams.get(param)?.split(',') || [];
                values = values.filter(v => v !== filterValue);
                if (values.length) url.searchParams.set(param, values.join(','));
                else               url.searchParams.delete(param);
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }
            
            updateFilterChips();

            // Highlight matched rows
            const searchTerm = "{{ request('search', '') }}";
            if (searchTerm) {
                document.querySelectorAll('#tableView tbody tr, .document-card').forEach(el => {
                    if (el.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                        el.classList.add('ring-1', 'ring-cyan-500/30');
                    }
                });
            }
        });
    </script>

</x-app-layout-dark>