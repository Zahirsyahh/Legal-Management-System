<x-app-layout-dark title="Legal Archives">

    <x-slot name="styles">
    <style>
        /* =============================================
         * BADGES
         * ============================================= */
        .validity-badge {
            display: inline-flex; align-items: center; gap: 0.375rem;
            padding: 0.2rem 0.625rem; border-radius: 2rem;
            font-size: 0.7rem; font-weight: 600; border: 1px solid; white-space: nowrap;
        }
        .validity-badge .validity-dot {
            display: inline-block; width: 6px; height: 6px;
            border-radius: 50%; flex-shrink: 0;
        }
        .validity-badge.valid      { background: rgba(14,165,233,0.15);  color: #38bdf8; border-color: rgba(14,165,233,0.35); }
        .validity-badge.valid .validity-dot { background: #38bdf8; }
        .validity-badge.expired    { background: rgba(107,114,128,0.15); color: #9ca3af; border-color: rgba(107,114,128,0.35); }
        .validity-badge.expired .validity-dot { background: #9ca3af; }
        .validity-badge.terminated { background: rgba(239,68,68,0.15);   color: #f87171; border-color: rgba(239,68,68,0.35); }
        .validity-badge.terminated .validity-dot { background: #f87171; }

        .version-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; border: 1px solid; white-space: nowrap; }
        .version-badge.latest     { background: rgba(16,185,129,0.15); color: #34d399; border-color: rgba(16,185,129,0.35); }
        .version-badge.obsolete   { background: rgba(239,68,68,0.15);  color: #f87171; border-color: rgba(239,68,68,0.35); }
        .version-badge.superseded { background: rgba(245,158,11,0.15); color: #fbbf24; border-color: rgba(245,158,11,0.35); }

        /* =============================================
         * LAYOUT
         * ============================================= */
        :root {
            --primary-gradient: linear-gradient(135deg, #0ea5e9, #3b82f6);
            --secondary-gradient: linear-gradient(135deg, #8b5cf6, #6366f1);
        }

        .stat-card {
            background: linear-gradient(145deg, rgba(30,41,59,0.6), rgba(15,23,42,0.8));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(14,165,233,0.15);
            border-radius: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        .stat-card:hover { transform: translateY(-4px); border-color: rgba(14,165,233,0.3); box-shadow: 0 20px 40px -12px rgba(0,0,0,0.3); }

        .stat-icon {
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(14,165,233,0.1), rgba(59,130,246,0.05));
            border: 1px solid rgba(14,165,233,0.2);
        }

        /* =============================================
         * TABLE
         * ============================================= */
        .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .modern-table th {
            background: rgba(15,23,42,0.6); padding: 1rem 1.5rem;
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.05em; color: #9ca3af;
            border-bottom: 1px solid rgba(14,165,233,0.2);
        }
        .modern-table td { padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); color: #e2e8f0; }
        .modern-table tbody tr { transition: all 0.2s ease; }
        .modern-table tbody tr:hover { background: rgba(14,165,233,0.05); }

        /* =============================================
         * CARDS
         * ============================================= */
        .document-card {
            background: linear-gradient(145deg, rgba(30,41,59,0.5), rgba(15,23,42,0.7));
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 1rem;
            transition: all 0.3s ease;
            position: relative; overflow: hidden;
        }
        .document-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: var(--primary-gradient); transform: scaleX(0); transition: transform 0.3s ease;
        }
        .document-card:hover::before { transform: scaleX(1); }
        .document-card:hover { transform: translateY(-4px); border-color: rgba(14,165,233,0.3); box-shadow: 0 20px 40px -12px rgba(0,0,0,0.4); }

        /* =============================================
         * ACTION ICONS
         * ============================================= */
        .action-icon { padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s ease; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); }
        .action-icon:hover { transform: translateY(-2px); }
        .action-icon.view:hover   { background: rgba(59,130,246,0.2);  border-color: rgba(59,130,246,0.3); }
        .action-icon.edit:hover   { background: rgba(245,158,11,0.2);  border-color: rgba(245,158,11,0.3); }
        .action-icon.delete:hover { background: rgba(239,68,68,0.2);   border-color: rgba(239,68,68,0.3); }

        /* =============================================
         * SEARCH BAR
         * ============================================= */
        .search-bar {
            background: rgba(15,23,42,0.6); backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.05); border-radius: 1rem; transition: all 0.3s ease;
        }
        .search-bar:focus-within { border-color: rgba(14,165,233,0.5); box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }

        /* =============================================
         * SEARCH SPINNER
         * ============================================= */
        .search-spinner {
            display: none; position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
            width: 1rem; height: 1rem; border: 2px solid rgba(14,165,233,0.3);
            border-top-color: #0ea5e9; border-radius: 50%; animation: spin 0.6s linear infinite;
        }
        .search-spinner.visible { display: block; }

        /* =============================================
         * CONTENT LOADING OVERLAY
         * ============================================= */
        #contentArea {
            position: relative;
            transition: opacity 0.2s ease;
        }
        #contentArea.loading {
            opacity: 0.5;
            pointer-events: none;
        }
        #contentArea.loading::after {
            content: '';
            position: absolute; inset: 0;
            background: transparent;
            z-index: 10;
            cursor: wait;
        }

        /* =============================================
         * VIEW TOGGLE
         * ============================================= */
        .view-toggle { background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 0.75rem; padding: 0.25rem; }
        .view-toggle-btn {
            padding: 0.5rem 1rem; border-radius: 0.5rem; transition: all 0.2s ease;
            background: transparent; color: #9ca3af; border: none; cursor: pointer; font-family: inherit;
        }
        .view-toggle-btn.active { background: var(--primary-gradient); color: white; box-shadow: 0 4px 12px rgba(14,165,233,0.3); }

        /* =============================================
         * PAGINATION
         * ============================================= */
        .pagination-modern { display: flex; gap: 0.5rem; align-items: center; }
        .page-link {
            padding: 0.5rem 0.875rem; border-radius: 0.5rem;
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.2s ease; font-size: 0.875rem; color: #d1d5db; cursor: pointer;
        }
        .page-link:hover:not(.active):not(.disabled) { background: rgba(14,165,233,0.1); border-color: rgba(14,165,233,0.3); transform: translateY(-1px); }
        .page-link.active   { background: var(--primary-gradient); border-color: transparent; color: white; box-shadow: 0 4px 12px rgba(14,165,233,0.3); }
        .page-link.disabled { opacity: 0.5; cursor: not-allowed; }

        /* =============================================
         * FILTER PANEL
         * ============================================= */
        .filter-dropdown {
            background: rgba(15,23,42,0.95); backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 1rem;
            box-shadow: 0 20px 40px -12px rgba(0,0,0,0.5);
        }

        /* =============================================
         * EMPTY STATE
         * ============================================= */
        .empty-state {
            text-align: center; padding: 4rem 2rem;
            background: linear-gradient(145deg, rgba(30,41,59,0.3), rgba(15,23,42,0.5));
            border-radius: 1.5rem; border: 1px dashed rgba(14,165,233,0.3);
        }

        /* =============================================
         * ANIMATIONS
         * ============================================= */
        @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn    { from { opacity: 0; } to { opacity: 1; } }

        .animate-slide-up { animation: slideInUp 0.5s ease-out; }
        .animate-fade-in  { animation: fadeIn 0.3s ease-out; }
    </style>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-slide-up">

        {{-- ============================================================
             HERO HEADER
             ============================================================ --}}
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

                    <div class="mt-8">
                        <div class="stat-card p-4 inline-flex items-center gap-6 min-w-[220px]">
                            <div>
                                <p class="text-gray-400 text-sm">Total Archives</p>
                                {{-- Diupdate via JS setelah AJAX --}}
                                <p class="text-2xl font-bold text-white mt-1" id="totalCount">{{ $archives->total() }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

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
                        <span id="activeFilterBadge"
                              class="hidden bg-cyan-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================
             SEARCH & VIEW TOGGLE
             ============================================================ --}}
        <div class="search-bar p-3 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative flex-1 w-full">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       id="searchInput"
                       placeholder="Search by Record ID, Doc Number, Doc Name, Counterparty..."
                       class="w-full pl-12 pr-10 py-3 bg-transparent border border-gray-700 rounded-xl focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all outline-none text-white"
                       value="{{ request('search', '') }}"
                       autocomplete="off">
                <span id="searchSpinner" class="search-spinner"></span>
            </div>

            <div class="flex items-center gap-4">
                <select id="sortSelect"
                        class="px-4 py-2.5 bg-gray-800/50 border border-gray-700 rounded-xl text-gray-300 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all outline-none">
                    <option value="date_desc" {{ request('sort', 'date_desc') == 'date_desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="date_asc"  {{ request('sort') == 'date_asc'  ? 'selected' : '' }}>Oldest First</option>
                    <option value="name_asc"  {{ request('sort') == 'name_asc'  ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                </select>

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

        {{-- ============================================================
             FILTER PANEL
             ============================================================ --}}
        <div id="filterPanel" class="filter-dropdown hidden mb-6 p-5 rounded-xl animate-fade-in">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Filter Archives</h3>
                <button id="closeFilterPanel" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Department --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Department</label>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @php
                            $departments = [
                                'LG' => 'Legal',   'HR' => 'HRD',        'OP' => 'Operation',
                                'AC' => 'Accounting','FN' => 'Finance',   'TX' => 'Tax',
                                'EX' => 'Exim',    'CC' => 'CorCom',     'NP' => 'Nickel Ore',
                                'HE' => 'HSE',     'CP' => 'Coal',       'SL' => 'Sales',
                                'PC' => 'Purchasing','IT' => 'IT',        'GA' => 'GA',
                                'DK' => 'Direksi & Komisaris',
                            ];
                        @endphp
                        @foreach($departments as $code => $name)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="filter-dept rounded border-gray-600 bg-gray-700 text-cyan-500" value="{{ $code }}">
                            <span class="text-sm text-gray-300">{{ $code }} – {{ $name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Document Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Document Type</label>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach(\App\Models\Archive::DOC_TYPES as $code => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="filter-type rounded border-gray-600 bg-gray-700 text-cyan-500" value="{{ $code }}">
                            <span class="text-sm text-gray-300">{{ $code }} – {{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Company --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Company</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="filter-company rounded border-gray-600 bg-gray-700 text-cyan-500" value="GNI">
                            <span class="text-sm text-gray-300">GNI – Gunbuster Nickel Industry</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="filter-company rounded border-gray-600 bg-gray-700 text-cyan-500" value="AMI">
                            <span class="text-sm text-gray-300">AMI – Alchemist Metal Industry</span>
                        </label>
                    </div>
                </div>

                {{-- Version Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Version Status</label>
                    <div class="space-y-2">
                        @foreach(\App\Models\Archive::VERSION_STATUS_LABEL as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="filter-version rounded border-gray-600 bg-gray-700 text-cyan-500" value="{{ $value }}">
                            <span class="text-sm text-gray-300">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-700">
                <button id="applyFilters"
                        class="px-5 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 rounded-lg text-white hover:shadow-lg transition-all">
                    Apply Filters
                </button>
                <button id="clearFilters"
                        class="px-5 py-2 bg-gray-800 rounded-lg text-gray-300 hover:bg-gray-700 transition-all">
                    Clear All
                </button>
            </div>
        </div>

        {{-- ============================================================
             ACTIVE FILTER CHIPS
             ============================================================ --}}
        <div id="filterChips" class="flex flex-wrap gap-2 mb-6"></div>

        {{-- ============================================================
             CONTENT AREA — diupdate seluruhnya via AJAX
             ============================================================ --}}
        <div id="contentArea">

            {{-- TABLE VIEW --}}
            <div id="tableView" class="animate-fade-in">
                <div class="bg-gray-900/30 backdrop-blur-sm rounded-2xl border border-gray-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Record ID</th>
                                    <th>Document Name</th>
                                    <th>Version</th>
                                    <th>End Date</th>
                                    <th>Doc. Number</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @include('archives._table_partial', ['archives' => $archives])
                            </tbody>
                        </table>
                    </div>

                    {{-- Table Pagination --}}
                    <div id="tablePagination" class="px-6 py-4 border-t border-gray-800">
                        @include('archives._pagination_partial', ['archives' => $archives])
                    </div>
                </div>
            </div>

            {{-- CARD VIEW --}}
            <div id="cardView" class="hidden animate-fade-in">
                <div id="cardsBody" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @include('archives._cards_partial', ['archives' => $archives])
                </div>

                {{-- Card Pagination --}}
                <div id="cardPagination" class="mt-8 flex justify-center">
                    @include('archives._pagination_partial', ['archives' => $archives])
                </div>
            </div>

        </div>{{-- #contentArea --}}

    </div>{{-- max-w --}}

    <x-slot name="scripts">
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // ================================================================
        // STATE — semua param query disimpan di sini
        // ================================================================
        const state = {
            search  : '{{ request('search', '') }}',
            sort    : '{{ request('sort', 'date_desc') }}',
            page    : 1,
            dept    : (new URLSearchParams(location.search).get('department')     || '').split(',').filter(Boolean),
            type    : (new URLSearchParams(location.search).get('doc_type')       || '').split(',').filter(Boolean),
            version : (new URLSearchParams(location.search).get('version_status') || '').split(',').filter(Boolean),
            company : (new URLSearchParams(location.search).get('company')        || '').split(',').filter(Boolean),
        };

        // ================================================================
        // CSRF TOKEN (untuk keamanan header AJAX)
        // ================================================================
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        // ================================================================
        // DOM REFS
        // ================================================================
        const searchInput    = document.getElementById('searchInput');
        const searchSpinner  = document.getElementById('searchSpinner');
        const sortSelect     = document.getElementById('sortSelect');
        const tableBody      = document.getElementById('tableBody');
        const cardsBody      = document.getElementById('cardsBody');
        const tablePagination= document.getElementById('tablePagination');
        const cardPagination = document.getElementById('cardPagination');
        const totalCount     = document.getElementById('totalCount');
        const contentArea    = document.getElementById('contentArea');
        const filterChips    = document.getElementById('filterChips');
        const badge          = document.getElementById('activeFilterBadge');
        const filterPanel    = document.getElementById('filterPanel');

        // ================================================================
        // VIEW TOGGLE
        // ================================================================
        const tableView    = document.getElementById('tableView');
        const cardView     = document.getElementById('cardView');
        const tableViewBtn = document.getElementById('tableViewBtn');
        const cardViewBtn  = document.getElementById('cardViewBtn');

        function setView(mode) {
            if (mode === 'card') {
                tableView.classList.add('hidden');
                cardView.classList.remove('hidden');
                tableViewBtn.classList.remove('active');
                cardViewBtn.classList.add('active');
            } else {
                cardView.classList.add('hidden');
                tableView.classList.remove('hidden');
                cardViewBtn.classList.remove('active');
                tableViewBtn.classList.add('active');
            }
            localStorage.setItem('archiveView', mode);
        }

        setView(localStorage.getItem('archiveView') || 'table');
        tableViewBtn.addEventListener('click', () => setView('table'));
        cardViewBtn.addEventListener('click',  () => setView('card'));

        // ================================================================
        // CORE AJAX FUNCTION
        // ================================================================
        let currentRequest = null; // track active fetch untuk di-abort jika ada request baru

        function fetchArchives() {
            // Abort request sebelumnya jika ada
            if (currentRequest) currentRequest.abort();

            const controller = new AbortController();
            currentRequest   = controller;

            // Bangun URLSearchParams dari state
            const params = new URLSearchParams();
            if (state.search)           params.set('search',         state.search);
            if (state.sort)             params.set('sort',           state.sort);
            if (state.page > 1)         params.set('page',           state.page);
            if (state.dept.length)      params.set('department',     state.dept.join(','));
            if (state.type.length)      params.set('doc_type',       state.type.join(','));
            if (state.version.length)   params.set('version_status', state.version.join(','));
            if (state.company.length)   params.set('company',        state.company.join(','));

            // Update URL browser tanpa reload
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            history.pushState(state, '', newUrl);

            // Loading state
            contentArea.classList.add('loading');
            searchSpinner.classList.add('visible');

            fetch(`${window.location.pathname}?${params.toString()}`, {
                signal : controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN'    : csrfToken,
                    'Accept'          : 'application/json',
                },
            })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                // Inject HTML ke DOM
                tableBody.innerHTML       = data.table;
                cardsBody.innerHTML       = data.cards;
                tablePagination.innerHTML = data.pagination;
                cardPagination.innerHTML  = data.pagination;

                // Update total count
                if (totalCount) totalCount.textContent = data.total;

                // Remove loading state
                contentArea.classList.remove('loading');
                searchSpinner.classList.remove('visible');

                currentRequest = null;
            })
            .catch(err => {
                if (err.name === 'AbortError') return; // request di-abort — abaikan
                console.error('Archive fetch error:', err);
                contentArea.classList.remove('loading');
                searchSpinner.classList.remove('visible');
                currentRequest = null;
            });
        }

        // ================================================================
        // SEARCH — debounce 400ms
        // ================================================================
        let searchTimer = null;

        searchInput.addEventListener('input', function () {
            searchSpinner.classList.add('visible');
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                state.search = this.value.trim();
                state.page   = 1;
                fetchArchives();
            }, 400);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                this.value   = '';
                state.search = '';
                state.page   = 1;
                fetchArchives();
            }
        });

        // ================================================================
        // SORT
        // ================================================================
        sortSelect.addEventListener('change', function () {
            state.sort = this.value;
            state.page = 1;
            fetchArchives();
        });

        // ================================================================
        // PAGINATION — event delegation pada #contentArea
        // ================================================================
        contentArea.addEventListener('click', function (e) {
            const btn = e.target.closest('.ajax-page');
            if (!btn || btn.disabled || btn.classList.contains('disabled')) return;

            const page = parseInt(btn.dataset.page);
            if (!page || page < 1) return;

            state.page = page;
            fetchArchives();

            // Scroll ke atas konten
            contentArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // ================================================================
        // FILTER PANEL — toggle
        // ================================================================
        document.getElementById('filterButton').addEventListener('click', () => {
            filterPanel.classList.toggle('hidden');
        });
        document.getElementById('closeFilterPanel').addEventListener('click', () => {
            filterPanel.classList.add('hidden');
        });

        // ================================================================
        // APPLY FILTERS
        // ================================================================
        document.getElementById('applyFilters').addEventListener('click', function () {
            state.type    = Array.from(document.querySelectorAll('.filter-type:checked')).map(c => c.value);
            state.dept    = Array.from(document.querySelectorAll('.filter-dept:checked')).map(c => c.value);
            state.version = Array.from(document.querySelectorAll('.filter-version:checked')).map(c => c.value);
            state.company = Array.from(document.querySelectorAll('.filter-company:checked')).map(c => c.value);
            state.page    = 1;

            filterPanel.classList.add('hidden');
            renderFilterChips();
            fetchArchives();
        });

        // ================================================================
        // CLEAR FILTERS
        // ================================================================
        document.getElementById('clearFilters').addEventListener('click', function () {
            state.type    = [];
            state.dept    = [];
            state.version = [];
            state.company = [];
            state.page    = 1;

            // Reset semua checkbox
            document.querySelectorAll('.filter-type, .filter-dept, .filter-version, .filter-company')
                    .forEach(cb => cb.checked = false);

            filterPanel.classList.add('hidden');
            renderFilterChips();
            fetchArchives();
        });

        // ================================================================
        // RESTORE CHECKBOX STATE DARI URL (on page load)
        // ================================================================
        document.querySelectorAll('.filter-type').forEach(cb    => { cb.checked = state.type.includes(cb.value); });
        document.querySelectorAll('.filter-dept').forEach(cb    => { cb.checked = state.dept.includes(cb.value); });
        document.querySelectorAll('.filter-version').forEach(cb => { cb.checked = state.version.includes(cb.value); });
        document.querySelectorAll('.filter-company').forEach(cb => { cb.checked = state.company.includes(cb.value); });

        // ================================================================
        // FILTER CHIPS — render & interaction
        // ================================================================
        function renderFilterChips() {
            const xIcon = `<svg style="width:10px;height:10px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                           </svg>`;

            let html = '';
            state.type.forEach(t    => { html += `<button class="filter-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-cyan-500/20 text-cyan-300 text-xs" data-group="type" data-value="${t}">${t} ${xIcon}</button>`; });
            state.dept.forEach(d    => { html += `<button class="filter-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-purple-500/20 text-purple-300 text-xs" data-group="dept" data-value="${d}">${d} ${xIcon}</button>`; });
            state.version.forEach(v => { html += `<button class="filter-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs" data-group="version" data-value="${v}">${v} ${xIcon}</button>`; });
            state.company.forEach(c => { html += `<button class="filter-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-500/20 text-amber-300 text-xs" data-group="company" data-value="${c}">${c} ${xIcon}</button>`; });

            filterChips.innerHTML = html;

            // Update badge
            const total = state.type.length + state.dept.length + state.version.length + state.company.length;
            badge.textContent = total;
            total > 0 ? badge.classList.remove('hidden') : badge.classList.add('hidden');

            // Chip click → remove individual filter
            document.querySelectorAll('.filter-chip').forEach(chip => {
                chip.addEventListener('click', function () {
                    const g = this.dataset.group;
                    const v = this.dataset.value;
                    state[g] = state[g].filter(x => x !== v);

                    // Uncheck matching checkbox
                    const selector = { type: '.filter-type', dept: '.filter-dept', version: '.filter-version', company: '.filter-company' };
                    const cb = document.querySelector(`${selector[g]}[value="${v}"]`);
                    if (cb) cb.checked = false;

                    state.page = 1;
                    renderFilterChips();
                    fetchArchives();
                });
            });
        }

        // Initial render chips dari URL
        renderFilterChips();

        // ================================================================
        // GLOBAL confirmDelete (dipakai dari partial)
        // ================================================================
        window.confirmDelete = function (docName) {
            return confirm('Are you sure you want to delete archive "' + docName + '"?\nThis action cannot be undone.');
        };

    }); // end DOMContentLoaded
    </script>
    </x-slot>

</x-app-layout-dark>