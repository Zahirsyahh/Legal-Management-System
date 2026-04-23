{{-- resources/views/departments/pending-reviews.blade.php --}}
@php
    $department = Auth::user()->department;
    $routePrefix = match($department->code) {
        'FIN' => 'finance',
        'ACC' => 'accounting',
        'TAX' => 'tax',
        default => 'finance'
    };

    $deptColors = [
        'FIN' => [
            'bg'        => 'from-emerald-500/20 to-green-600/20',
            'text'      => 'text-emerald-300',
            'border'    => 'border-emerald-500/30',
            'name'      => 'Finance',
            'hex'       => '#10b981',
            'hex2'      => '#059669',
            'rgb_glow'  => 'rgba(16,185,129,0.15)',
            'rgb_focus' => 'rgba(16,185,129,0.25)',
            'orb1'      => 'emerald',
            'orb2'      => 'green',
            'orb3'      => 'teal',
        ],
        'ACC' => [
            'bg'        => 'from-cyan-500/20 to-blue-600/20',
            'text'      => 'text-cyan-300',
            'border'    => 'border-cyan-500/30',
            'name'      => 'Accounting',
            'hex'       => '#06b6d4',
            'hex2'      => '#0284c7',
            'rgb_glow'  => 'rgba(6,182,212,0.15)',
            'rgb_focus' => 'rgba(6,182,212,0.25)',
            'orb1'      => 'cyan',
            'orb2'      => 'blue',
            'orb3'      => 'indigo',
        ],
        'TAX' => [
            'bg'        => 'from-purple-500/20 to-pink-600/20',
            'text'      => 'text-purple-300',
            'border'    => 'border-purple-500/30',
            'name'      => 'Tax',
            'hex'       => '#a855f7',
            'hex2'      => '#d946ef',
            'rgb_glow'  => 'rgba(168,85,247,0.15)',
            'rgb_focus' => 'rgba(168,85,247,0.25)',
            'orb1'      => 'purple',
            'orb2'      => 'pink',
            'orb3'      => 'fuchsia',
        ],
    ];

    $color     = $deptColors[$department->code] ?? $deptColors['FIN'];
    $pageTitle = $color['name'] . ' Pending Reviews';

    $now = \Carbon\Carbon::now();
    $statsSource = $allPendingAssignments ?? $pendingAssignments;

    $overdueCount = $statsSource->filter(function ($a) use ($now) {
        $d = $a->contract?->drafting_deadline ?? ($a->due_date ?? null);
        return $d && \Carbon\Carbon::parse($d)->isPast();
    })->count();

    $dueThisWeekCount = $statsSource->filter(function ($a) use ($now) {
        $d = $a->contract?->drafting_deadline ?? ($a->due_date ?? null);
        if (!$d) return false;
        $p = \Carbon\Carbon::parse($d);
        return $p->isFuture() && $p->diffInDays($now) <= 7;
    })->count();

    $totalCount = $statsSource->count();

    $activeSearch = request('search', '');
    $activeStatus = request('status', 'all');
    $activeSort   = request('sort', 'due_date');
@endphp

<x-app-layout-dark title="{{ $pageTitle }}">
    @push('header')
        <div class="flex items-center space-x-4">
            @include('components.notification-bell')
        </div>
    @endpush

    <style>
        /* ------------------------------------------------------------------ */
        /* TOOLBAR                                                             */
        /* ------------------------------------------------------------------ */
        #pr-search,
        #pr-statusFilter,
        #pr-sortBy {
            appearance: none;
            -webkit-appearance: none;
            background-color: #0f172a !important;
            border: 1.5px solid #334155 !important;
            color: #e2e8f0 !important;
            border-radius: 0.75rem !important;
            padding: 0.6rem 1rem !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s !important;
            outline: none !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.4) !important;
        }
        #pr-search {
            padding-left: 2.5rem !important;
            padding-right: 2rem !important;
            width: 18rem !important;
        }
        #pr-search::placeholder { color: #64748b !important; opacity: 1 !important; }

        #pr-statusFilter,
        #pr-sortBy {
            padding-right: 2.25rem !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            cursor: pointer !important;
        }
        #pr-search:hover,
        #pr-statusFilter:hover,
        #pr-sortBy:hover {
            background-color: #1e293b !important;
            border-color: #475569 !important;
        }
        #pr-search:focus,
        #pr-statusFilter:focus,
        #pr-sortBy:focus {
            background-color: #0f172a !important;
            border-color: {{ $color['hex'] }} !important;
            box-shadow: 0 0 0 3px {{ $color['rgb_focus'] }}, 0 1px 3px rgba(0,0,0,0.4) !important;
        }
        #pr-statusFilter option,
        #pr-sortBy option { background-color: #1e293b !important; color: #e2e8f0 !important; }

        .pr-search-wrap { position: relative; display: inline-flex; align-items: center; }
        .pr-search-icon {
            position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
            color: #64748b; pointer-events: none; z-index: 1;
        }

        /* Clear "×" inside search input */
        #pr-clearSearch {
            display: none;
            position: absolute; right: 0.55rem; top: 50%; transform: translateY(-50%);
            background: #334155; border: none; border-radius: 50%;
            width: 1.1rem; height: 1.1rem; cursor: pointer;
            color: #94a3b8; font-size: 0.6rem;
            align-items: center; justify-content: center;
            transition: background 0.15s; z-index: 2;
        }
        #pr-clearSearch:hover { background: #475569; color: white; }
        #pr-clearSearch.visible { display: flex; }

        #pr-refreshBtn {
            background-color: #0f172a !important;
            border: 1.5px solid #334155 !important;
            color: #64748b !important;
            border-radius: 0.75rem !important;
            padding: 0.6rem 0.75rem !important;
            transition: all 0.2s !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.4) !important;
            cursor: pointer !important;
        }
        #pr-refreshBtn:hover { background-color: #1e293b !important; border-color: #475569 !important; color: #e2e8f0 !important; }

        .pr-toolbar-label {
            font-size: 0.75rem; color: #475569; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;
        }

        /* Loading overlay */
        #pr-loading {
            display: none; position: fixed; inset: 0;
            background: rgba(2,6,23,0.55); backdrop-filter: blur(3px);
            z-index: 9999; align-items: center; justify-content: center;
        }
        #pr-loading.active { display: flex; }
    </style>

    {{-- Loading overlay --}}
    <div id="pr-loading">
        <div class="flex flex-col items-center gap-3">
            <svg class="w-9 h-9 animate-spin" style="color:{{ $color['hex'] }}" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span class="text-sm text-gray-400">Searching…</span>
        </div>
    </div>

    <div class="relative pb-8 px-4 sm:px-6 lg:px-8">
        <!-- Background Orbs -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-{{ $color['orb1'] }}-600/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-{{ $color['orb2'] }}-600/20 rounded-full blur-3xl animate-pulse" style="animation-delay:2s"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-{{ $color['orb3'] }}-600/10 rounded-full blur-3xl"></div>
        </div>

        {{-- ===== HEADER ===== --}}
        <div class="relative mb-8">
            <a href="{{ route($routePrefix . '-admin.dashboard') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white mb-6 transition-all group glass-stat px-4 py-2 rounded-full">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Dashboard
            </a>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold">
                        <span class="gradient-text-animated">{{ $pageTitle }}</span>
                    </h1>
                    <p class="text-gray-300 mt-2 backdrop-blur-sm inline-block px-3 py-1 rounded-full bg-white/5">
                        Documents waiting for staff assignment
                    </p>
                </div>
                <div class="text-right glass-stat px-5 py-3 rounded-2xl">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse shadow-lg shadow-yellow-400/50"></span>
                        <span class="text-sm text-gray-300">{{ $totalCount }} Pending Assignments</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== STATS CARDS ===== --}}
        <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Total Pending</p>
                        <p class="text-4xl font-bold text-white">{{ $totalCount }}</p>
                        <p class="text-gray-400 text-xs mt-2">Awaiting assignment</p>
                    </div>
                    <div class="w-12 h-12 bg-{{ $color['orb1'] }}-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-{{ $color['orb1'] }}-500/20">
                        <svg class="w-6 h-6 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Due This Week</p>
                        <p class="text-4xl font-bold {{ $dueThisWeekCount > 0 ? 'text-yellow-400' : 'text-white' }}">{{ $dueThisWeekCount }}</p>
                        <p class="text-gray-400 text-xs mt-2">Upcoming deadlines</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-yellow-500/20">
                        <svg class="w-6 h-6 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Overdue</p>
                        <p class="text-4xl font-bold {{ $overdueCount > 0 ? 'text-red-400' : 'text-white' }}">{{ $overdueCount }}</p>
                        <p class="text-gray-400 text-xs mt-2">Past deadline</p>
                    </div>
                    <div class="w-12 h-12 bg-red-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-red-500/20">
                        <svg class="w-6 h-6 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Available Staff</p>
                        <p class="text-4xl font-bold {{ $color['text'] }}">{{ $availableStaff ?? 0 }}</p>
                        <p class="text-gray-400 text-xs mt-2">Ready to assign</p>
                    </div>
                    <div class="w-12 h-12 bg-{{ $color['orb2'] }}-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-{{ $color['orb2'] }}-500/20">
                        <svg class="w-6 h-6 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TOOLBAR =====
             Toolbar adalah <form method="GET"> — semua filter dikirim ke server.
             Search di-debounce 450ms agar tidak spam request saat mengetik.
             Pagination links di-append dengan query params agar filter tetap aktif saat ganti halaman.
        --}}
        <form id="pr-filterForm"
              method="GET"
              action="{{ request()->url() }}"
              class="relative flex flex-wrap items-center justify-between gap-3 mb-6
                     px-4 py-3 rounded-2xl border border-white/10"
              style="background:rgba(15,23,42,0.7); backdrop-filter:blur(12px);">

            <div class="flex flex-wrap items-center gap-3">

                {{-- Search --}}
                <div class="pr-search-wrap">
                    <svg class="pr-search-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input id="pr-search"
                           name="search"
                           type="text"
                           placeholder="Search contracts…"
                           autocomplete="off"
                           value="{{ $activeSearch }}">
                    <button type="button" id="pr-clearSearch"
                            class="{{ $activeSearch ? 'visible' : '' }}"
                            title="Clear search">✕</button>
                </div>

                <div class="hidden sm:block w-px h-6 bg-white/10"></div>

                {{-- Status --}}
                <div class="flex items-center gap-2">
                    <span class="pr-toolbar-label hidden sm:block">Status</span>
                    <select id="pr-statusFilter" name="status">
                        <option value="all"      {{ $activeStatus === 'all'      ? 'selected' : '' }}>All Status</option>
                        <option value="pending"  {{ $activeStatus === 'pending'  ? 'selected' : '' }}>Pending</option>
                        <option value="due_soon" {{ $activeStatus === 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                        <option value="overdue"  {{ $activeStatus === 'overdue'  ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div class="flex items-center gap-2">
                    <span class="pr-toolbar-label hidden sm:block">Sort</span>
                    <select id="pr-sortBy" name="sort">
                        <option value="due_date"        {{ $activeSort === 'due_date'        ? 'selected' : '' }}>Due Date</option>
                        <option value="title"           {{ $activeSort === 'title'           ? 'selected' : '' }}>Title</option>
                        <option value="contract_number" {{ $activeSort === 'contract_number' ? 'selected' : '' }}>Doc #</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Result count --}}
                <span class="text-xs px-3 py-1.5 rounded-full font-medium"
                      style="background:rgba(255,255,255,0.07); color:#94a3b8; border:1px solid rgba(255,255,255,0.1);">
                    {{ $pendingAssignments->total() }} result{{ $pendingAssignments->total() !== 1 ? 's' : '' }}
                </span>

                {{-- Clear filters badge --}}
                @if($activeSearch || $activeStatus !== 'all')
                    <a href="{{ request()->url() }}"
                       class="text-xs px-2.5 py-1.5 rounded-full font-medium transition-all"
                       style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3);">
                        ✕ Clear filters
                    </a>
                @endif

                {{-- Refresh --}}
                <button type="button" id="pr-refreshBtn" title="Refresh">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
        </form>

        {{-- ===== MAIN TABLE ===== --}}
        <div class="relative glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 bg-white/5">
                <div class="grid grid-cols-12 gap-3 text-[0.7rem] font-semibold text-gray-400 uppercase tracking-wider">
                    <div class="col-span-5">
                        <span>Document Information</span>
                    </div>
                    <div class="col-span-2">Status</div>
                    <div class="col-span-2">Due Date</div>
                    <div class="col-span-2">Assigned By</div>
                    <div class="col-span-1 text-right">Action</div>
                </div>
            </div>

            <div id="assignmentsList" class="divide-y divide-white/5">
                @forelse($pendingAssignments as $assignment)
                @php
                    $rawDue = $assignment->contract?->drafting_deadline ?? ($assignment->due_date ?? null);
                    $dueDate = $rawDue ? \Carbon\Carbon::parse($rawDue) : null;
                    $isOverdue = $dueDate && $dueDate->isPast();
                    $isDueSoon = $dueDate && !$isOverdue && $dueDate->diffInDays(now()) <= 7;

                    $legalStage = $assignment->contract?->reviewStages
                        ?->where('stage_type', 'legal')
                        ->whereNotNull('assigned_user_id')
                        ->sortBy('sequence')
                        ->first();
                    $assignedByUser = $legalStage?->assignedUser;
                    $statusTag = $isOverdue ? 'overdue' : ($isDueSoon ? 'due_soon' : 'pending');
                @endphp

                <div class="hover-row transition-all duration-200"
                    data-status="{{ $statusTag }}"
                    data-duedate="{{ $rawDue ?? '' }}"
                    data-contract-number="{{ $assignment->contract?->contract_number ?? '' }}"
                    data-title="{{ $assignment->contract?->title ?? '' }}">

                    <div class="grid grid-cols-12 gap-3 px-6 py-4 items-center text-sm">
                        <div class="col-span-5 flex items-start gap-3">
                            @if($assignment->contract)
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $color['bg'] }} flex items-center justify-center border {{ $color['border'] }} flex-shrink-0">
                                        <svg class="w-5 h-5 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-white truncate text-sm" title="{{ $assignment->contract->title }}">
                                            {{ Str::limit($assignment->contract->title, 45) }}
                                        </p>
                                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                            @if($assignment->contract->contract_number)
                                                <span class="font-mono">#{{ $assignment->contract->contract_number }}</span>
                                            @endif
                                            @if($assignment->contract->contract_type)
                                                <span>• {{ $assignment->contract->contract_type }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-red-400/60 italic text-sm">Document removed</span>
                            @endif
                        </div>

                        <div class="col-span-2">
                            @if($isOverdue)
                                <span class="badge badge-overdue">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                                    </svg>
                                    Overdue
                                </span>
                            @elseif($isDueSoon)
                                <span class="badge badge-due-soon">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                                    </svg>
                                    Due Soon
                                </span>
                            @else
                                <span class="badge badge-pending">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>
                                    Pending
                                </span>
                            @endif
                        </div>

                        <div class="col-span-2">
                            @if($dueDate)
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1.5 {{ $isOverdue ? 'text-red-400' : ($isDueSoon ? 'text-yellow-400' : 'text-gray-400') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm">{{ $dueDate->format('d M Y') }}</span>
                                    </div>
                                    @if($isOverdue)
                                        <span class="text-xs text-red-500/80 mt-0.5 ml-5">{{ $dueDate->diffForHumans() }}</span>
                                    @elseif($isDueSoon)
                                        <span class="text-xs text-yellow-500/80 mt-0.5 ml-5">in {{ $dueDate->diffInDays(now()) }} days</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-600 text-sm">—</span>
                            @endif
                        </div>

                        <div class="col-span-2">
                            @if($assignedByUser)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br {{ $color['bg'] }} border {{ $color['border'] }}
                                                flex items-center justify-center text-xs font-bold {{ $color['text'] }}">
                                        {{ strtoupper(substr($assignedByUser->nama_user ?? 'L', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-300 truncate">
                                            {{ Str::limit($assignedByUser->nama_user ?? '—', 20) }}
                                        </p>
                                        <span class="text-xs text-gray-500">Legal</span>
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-600 text-sm">—</span>
                            @endif
                        </div>

                        <div class="col-span-1 flex items-center justify-end gap-1">
                            @if($assignment->contract)
                                <a href="{{ route('contracts.show', $assignment->contract) }}"
                                class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200"
                                title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            @endif

                            <a href="{{ route($routePrefix . '-admin.assign', $assignment) }}"
                            class="p-2 {{ $color['text'] }} hover:bg-white/10 rounded-lg transition-all duration-200"
                            title="Assign Staff">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br {{ $color['bg'] }} border {{ $color['border'] }} mb-4">
                        <svg class="w-8 h-8 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-300 mb-1">All caught up!</h3>
                    <p class="text-gray-500 text-sm">No pending assignments for {{ strtolower($color['name']) }} department.</p>
                </div>
                @endforelse
            </div>

            @if($pendingAssignments->count() > 0)
            <div class="px-6 py-4 border-t border-white/10 bg-white/5 flex items-center justify-end text-sm">
                @if(method_exists($pendingAssignments, 'hasPages') && $pendingAssignments->hasPages())
                    <div class="flex items-center gap-2">
                        {{ $pendingAssignments->links() }}
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form        = document.getElementById('pr-filterForm');
            const searchInput = document.getElementById('pr-search');
            const clearBtn    = document.getElementById('pr-clearSearch');
            const statusSel   = document.getElementById('pr-statusFilter');
            const sortSel     = document.getElementById('pr-sortBy');
            const loading     = document.getElementById('pr-loading');

            /* ── Debounce helper ── */
            let debounceTimer;
            function debounce(fn, ms = 450) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fn, ms);
            }

            /* ── Submit (dengan loading overlay) ── */
            function submitForm() {
                if (loading) loading.classList.add('active');
                form.submit();
            }

            /* ── Search: debounce 450ms ── */
            searchInput?.addEventListener('input', function () {
                clearBtn?.classList.toggle('visible', this.value.length > 0);
                debounce(submitForm);
            });

            /* ── Clear search ── */
            clearBtn?.addEventListener('click', function () {
                searchInput.value = '';
                this.classList.remove('visible');
                submitForm();
            });

            /* ── Selects: submit langsung ── */
            statusSel?.addEventListener('change', submitForm);
            sortSel?.addEventListener('change',   submitForm);

            /* ── Refresh ── */
            document.getElementById('pr-refreshBtn')?.addEventListener('click', function () {
                this.querySelector('svg').classList.add('animate-spin');
                setTimeout(() => location.reload(), 400);
            });

            /* ── Checkbox select-all ── */
            const selectAll     = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const selectedCount = document.getElementById('selectedCount');

            function updateCount() {
                if (selectedCount)
                    selectedCount.textContent = document.querySelectorAll('.row-checkbox:checked').length;
            }
            selectAll?.addEventListener('change', function () {
                rowCheckboxes.forEach(cb => cb.checked = this.checked);
                updateCount();
            });
            rowCheckboxes.forEach(cb => cb.addEventListener('change', updateCount));
        });
    </script>
</x-app-layout-dark>