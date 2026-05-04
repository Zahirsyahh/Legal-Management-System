{{-- resources/views/departments/pending-reviews.blade.php --}}
{{-- Shared by: finance-admin, accounting-admin, tax-admin --}}
@php
    $user       = Auth::user();
    $department = $user->department ?? null;

    // Fallback: derive department from role if relationship not loaded
    if (!$department) {
        if ($user->hasRole('admin_fin')) {
            $department = \App\Models\Department::where('code', 'FIN')->first();
        } elseif ($user->hasRole('admin_acc')) {
            $department = \App\Models\Department::where('code', 'ACC')->first();
        } elseif ($user->hasRole('admin_tax')) {
            $department = \App\Models\Department::where('code', 'TAX')->first();
        }
    }

    $deptCode = $department->code ?? 'FIN';

    $routePrefix = match($deptCode) {
        'FIN' => 'finance',
        'ACC' => 'accounting',
        'TAX' => 'tax',
        default => 'finance',
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

    $color     = $deptColors[$deptCode] ?? $deptColors['FIN'];
    $pageTitle = $color['name'] . ' Review Invitations';

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
        /* TOOLBAR INPUTS                                                       */
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

        /* ------------------------------------------------------------------ */
        /* ACTION BUTTONS — ICON-ONLY STYLE                                    */
        /* ------------------------------------------------------------------ */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 0.5rem;
            border: 1px solid;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            text-decoration: none;
        }
        /* Tooltip on hover */
        .btn-action::after {
            content: attr(title);
            position: absolute;
            bottom: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: #e2e8f0;
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.4rem;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s;
            border: 1px solid #334155;
            z-index: 10;
        }
        .btn-action:hover::after {
            opacity: 1;
        }
        .btn-view {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.15);
            color: #94a3b8;
        }
        .btn-view:hover {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255,255,255,0.25);
            color: #e2e8f0;
        }
        .btn-accept {
            background: rgba(34,197,94,0.12);
            border-color: rgba(34,197,94,0.3);
            color: #86efac;
        }
        .btn-accept:hover {
            background: rgba(34,197,94,0.22);
            border-color: rgba(34,197,94,0.5);
            color: #4ade80;
        }
        .btn-decline {
            background: rgba(239,68,68,0.1);
            border-color: rgba(239,68,68,0.25);
            color: #fca5a5;
        }
        .btn-decline:hover {
            background: rgba(239,68,68,0.2);
            border-color: rgba(239,68,68,0.45);
            color: #f87171;
        }

        /* ------------------------------------------------------------------ */
        /* DECLINE MODAL                                                        */
        /* ------------------------------------------------------------------ */
        #decline-modal {
            position: fixed; inset: 0; z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            background: rgba(2,6,23,0.7);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease;
        }
        #decline-modal.open {
            opacity: 1; pointer-events: all;
        }
        #decline-modal-inner {
            transform: scale(0.95) translateY(8px);
            transition: transform 0.2s ease;
        }
        #decline-modal.open #decline-modal-inner {
            transform: scale(1) translateY(0);
        }
        #decline-reason-input {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            color: #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            resize: none;
            width: 100%;
            outline: none;
            transition: border-color 0.2s;
        }
        #decline-reason-input:focus {
            border-color: rgba(239,68,68,0.5);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
        }
        #decline-reason-input::placeholder { color: #475569; }

        /* ------------------------------------------------------------------ */
        /* LOADING OVERLAY                                                      */
        /* ------------------------------------------------------------------ */
        #pr-loading {
            display: none; position: fixed; inset: 0;
            background: rgba(2,6,23,0.55); backdrop-filter: blur(3px);
            z-index: 9998; align-items: center; justify-content: center;
        }
        #pr-loading.active { display: flex; }

        /* ------------------------------------------------------------------ */
        /* GLASS                                                                */
        /* ------------------------------------------------------------------ */
        .glass-card {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.2);
        }
        .glass-stat {
            background: rgba(255,255,255,0.02);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        .hover-row { transition: background 0.15s; }
        .hover-row:hover { background: rgba(255,255,255,0.025); }

        /* badges */
        .badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.25rem 0.65rem; border-radius: 9999px;
            font-size: 0.7rem; font-weight: 600; border: 1px solid;
        }
        .badge-overdue  { background:rgba(239,68,68,0.15);  border-color:rgba(239,68,68,0.35);  color:#fca5a5; }
        .badge-due-soon { background:rgba(234,179,8,0.15);  border-color:rgba(234,179,8,0.35);  color:#fde047; }
        .badge-pending  { background:rgba(234,179,8,0.08);  border-color:rgba(234,179,8,0.2);   color:#fde047; }
    </style>

    {{-- Loading overlay --}}
    <div id="pr-loading">
        <div class="flex flex-col items-center gap-3">
            <svg class="w-9 h-9 animate-spin" style="color:{{ $color['hex'] }}" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span class="text-sm text-gray-400">Loading…</span>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- DECLINE MODAL                                                     --}}
    {{-- ================================================================ --}}
    <div id="decline-modal" role="dialog" aria-modal="true" aria-labelledby="decline-modal-title">
        <div id="decline-modal-inner"
             class="glass-card rounded-2xl p-6 w-full max-w-md mx-4">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 id="decline-modal-title" class="text-white font-semibold text-lg">Decline Invitation</h3>
                    <p class="text-gray-400 text-sm mt-1">Legal will be notified of your decision.</p>
                </div>
                <button onclick="closeDeclineModal()"
                        class="text-gray-500 hover:text-gray-300 transition-colors p-1 rounded-lg hover:bg-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Contract info display --}}
            <div id="decline-contract-info"
                 class="mb-4 px-4 py-3 rounded-xl border border-white/8"
                 style="background:rgba(255,255,255,0.04)">
                <p class="text-xs text-gray-500 mb-0.5">Contract</p>
                <p id="decline-contract-title" class="text-sm text-gray-200 font-medium">—</p>
            </div>

            <textarea id="decline-reason-input"
                      rows="3"
                      placeholder="Reason for declining (optional)…"></textarea>

            <div class="flex justify-end gap-3 mt-4">
                <button onclick="closeDeclineModal()"
                        class="px-4 py-2 text-sm text-gray-300 rounded-xl border border-white/10
                               bg-white/5 hover:bg-white/10 transition-all">
                    Cancel
                </button>
                <button onclick="submitDecline()"
                        class="px-4 py-2 text-sm text-red-300 rounded-xl border border-red-500/30
                               bg-red-500/15 hover:bg-red-500/25 transition-all font-medium">
                    Confirm Decline
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden decline forms — one per assignment --}}
    @foreach($pendingAssignments as $assignment)
    <form id="decline-form-{{ $assignment->id }}"
          method="POST"
          action="{{ route($routePrefix . '-admin.invitation.decline', $assignment) }}"
          class="hidden">
        @csrf
        <input type="hidden" name="decline_reason" id="decline-reason-{{ $assignment->id }}">
    </form>
    @endforeach

    <div class="relative pb-8 px-4 sm:px-6 lg:px-8">

        {{-- Background Orbs --}}
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-{{ $color['orb1'] }}-600/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-{{ $color['orb2'] }}-600/20 rounded-full blur-3xl animate-pulse" style="animation-delay:2s"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-{{ $color['orb3'] }}-600/10 rounded-full blur-3xl"></div>
        </div>

        {{-- ================================================================ --}}
        {{-- HEADER                                                            --}}
        {{-- ================================================================ --}}
        <div class="relative mb-8">
            <a href="{{ route($routePrefix . '-admin.dashboard') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white mb-6
                      transition-all group glass-stat px-4 py-2 rounded-full">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Dashboard
            </a>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold">
                        <span style="background: linear-gradient(135deg, {{ $color['hex'] }}, {{ $color['hex2'] }});
                                     -webkit-background-clip: text; background-clip: text; color: transparent;">
                            {{ $pageTitle }}
                        </span>
                    </h1>
                    <p class="text-gray-300 mt-2 backdrop-blur-sm inline-block px-3 py-1 rounded-full bg-white/5">
                        Accept or decline contracts sent to your department
                    </p>
                </div>
                <div class="text-right glass-stat px-5 py-3 rounded-2xl">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse shadow-lg shadow-yellow-400/50"></span>
                        <span class="text-sm text-gray-300">{{ $totalCount }} Pending</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- STATS CARDS                                                       --}}
        {{-- ================================================================ --}}
        <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            {{-- Total --}}
            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Total Invitations</p>
                        <p class="text-4xl font-bold text-white">{{ $totalCount }}</p>
                        <p class="text-gray-400 text-xs mt-2">Awaiting response</p>
                    </div>
                    <div class="w-12 h-12 bg-{{ $color['orb1'] }}-500/10 rounded-xl flex items-center justify-center
                                backdrop-blur-sm border border-{{ $color['orb1'] }}-500/20">
                        <svg class="w-6 h-6 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Due this week --}}
            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Due This Week</p>
                        <p class="text-4xl font-bold {{ $dueThisWeekCount > 0 ? 'text-yellow-400' : 'text-white' }}">
                            {{ $dueThisWeekCount }}
                        </p>
                        <p class="text-gray-400 text-xs mt-2">Upcoming deadlines</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500/10 rounded-xl flex items-center justify-center
                                backdrop-blur-sm border border-yellow-500/20">
                        <svg class="w-6 h-6 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Overdue --}}
            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Overdue</p>
                        <p class="text-4xl font-bold {{ $overdueCount > 0 ? 'text-red-400' : 'text-white' }}">
                            {{ $overdueCount }}
                        </p>
                        <p class="text-gray-400 text-xs mt-2">Past deadline</p>
                    </div>
                    <div class="w-12 h-12 bg-red-500/10 rounded-xl flex items-center justify-center
                                backdrop-blur-sm border border-red-500/20">
                        <svg class="w-6 h-6 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Available staff --}}
            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Available Staff</p>
                        <p class="text-4xl font-bold {{ $color['text'] }}">{{ $availableStaff ?? 0 }}</p>
                        <p class="text-gray-400 text-xs mt-2">Ready to assign</p>
                    </div>
                    <div class="w-12 h-12 bg-{{ $color['orb2'] }}-500/10 rounded-xl flex items-center justify-center
                                backdrop-blur-sm border border-{{ $color['orb2'] }}-500/20">
                        <svg class="w-6 h-6 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- TOOLBAR                                                           --}}
        {{-- ================================================================ --}}
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
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
                    {{ method_exists($pendingAssignments,'total') ? $pendingAssignments->total() : $pendingAssignments->count() }}
                    result{{ (method_exists($pendingAssignments,'total') ? $pendingAssignments->total() : $pendingAssignments->count()) !== 1 ? 's' : '' }}
                </span>

                {{-- Clear filters --}}
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

        {{-- ================================================================ --}}
        {{-- MAIN TABLE                                                        --}}
        {{-- ================================================================ --}}
        <div class="relative glass-card rounded-2xl overflow-hidden">

            {{-- Table header --}}
            <div class="px-6 py-4 border-b border-white/10 bg-white/5">
                <div class="grid grid-cols-12 gap-3 text-[0.7rem] font-semibold text-gray-400 uppercase tracking-wider">
                    <div class="col-span-4">Document</div>
                    <div class="col-span-2">Status</div>
                    <div class="col-span-2">Due Date</div>
                    <div class="col-span-2">Requested By</div>
                    <div class="col-span-2 text-right">Actions</div>
                </div>
            </div>

            {{-- Table rows --}}
            <div id="assignmentsList" class="divide-y divide-white/5">
                @forelse($pendingAssignments as $assignment)
                @php
                    $rawDue  = $assignment->contract?->drafting_deadline ?? ($assignment->due_date ?? null);
                    $dueDate = $rawDue ? \Carbon\Carbon::parse($rawDue) : null;
                    $isOverdue  = $dueDate && $dueDate->isPast();
                    $isDueSoon  = $dueDate && !$isOverdue && $dueDate->diffInDays(now()) <= 7;
                    $statusTag  = $isOverdue ? 'overdue' : ($isDueSoon ? 'due_soon' : 'pending');

                    // Who triggered this assignment (legal reviewer from review stages)
                    $legalStage    = $assignment->contract?->reviewStages
                        ?->where('stage_type', 'legal')
                        ->whereNotNull('assigned_user_id')
                        ->sortBy('sequence')
                        ->first();
                    $requestedBy = $legalStage?->assignedUser;

                    $contractTitle = $assignment->contract?->title ?? 'Unknown';
                @endphp

                <div class="hover-row"
                     data-status="{{ $statusTag }}"
                     data-contract-number="{{ $assignment->contract?->contract_number ?? '' }}"
                     data-title="{{ $contractTitle }}">

                    <div class="grid grid-cols-12 gap-3 px-6 py-4 items-center">

                        {{-- Document info --}}
                        <div class="col-span-4">
                            @if($assignment->contract)
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $color['bg'] }} flex items-center justify-center
                                                border {{ $color['border'] }} flex-shrink-0">
                                        <svg class="w-5 h-5 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-white text-sm truncate"
                                           title="{{ $contractTitle }}">
                                            {{ Str::limit($contractTitle, 40) }}
                                        </p>
                                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                            @if($assignment->contract->contract_number)
                                                <span class="font-mono">#{{ $assignment->contract->contract_number }}</span>
                                            @else
                                                <span class="font-mono text-gray-600">No number yet</span>
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

                        {{-- Status badge --}}
                        <div class="col-span-2">
                            @if($isOverdue)
                                <span class="badge badge-overdue">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Overdue
                                </span>
                            @elseif($isDueSoon)
                                <span class="badge badge-due-soon">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Due Soon
                                </span>
                            @else
                                <span class="badge badge-pending">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 inline-block"></span>
                                    Pending
                                </span>
                            @endif
                        </div>

                        {{-- Due date --}}
                        <div class="col-span-2">
                            @if($dueDate)
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1.5 text-sm
                                        {{ $isOverdue ? 'text-red-400' : ($isDueSoon ? 'text-yellow-400' : 'text-gray-400') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $dueDate->format('d M Y') }}
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

                        {{-- Requested by --}}
                        <div class="col-span-2">
                            @if($requestedBy)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br {{ $color['bg'] }}
                                                border {{ $color['border'] }} flex items-center justify-center
                                                text-xs font-bold {{ $color['text'] }} flex-shrink-0">
                                        {{ strtoupper(substr($requestedBy->nama_user ?? 'L', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm text-gray-300 truncate">
                                            {{ Str::limit($requestedBy->nama_user ?? '—', 18) }}
                                        </p>
                                        <span class="text-xs text-gray-500">Legal</span>
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-600 text-sm">—</span>
                            @endif
                        </div>

                        {{-- Actions: View / Accept / Decline (ICON ONLY) --}}
                        <div class="col-span-2 flex items-center justify-end gap-1.5">

                            {{-- View --}}
                            @if($assignment->contract)
                            <a href="{{ route('contracts.show', $assignment->contract) }}"
                               class="btn-action btn-view"
                               title="View contract details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @endif

                            {{-- Accept --}}
                            <form method="POST"
                                  action="{{ route($routePrefix . '-admin.invitation.accept', $assignment) }}"
                                  onsubmit="return confirm('Accept this review invitation for \'{{ addslashes(Str::limit($contractTitle,40)) }}\'?')">
                                @csrf
                                <button type="submit" class="btn-action btn-accept" title="Accept invitation">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>

                            {{-- Decline --}}
                            <button type="button"
                                    class="btn-action btn-decline"
                                    onclick="openDeclineModal('{{ $assignment->id }}', '{{ addslashes(Str::limit($contractTitle, 60)) }}')"
                                    title="Decline invitation">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                    </div>
                </div>
                @empty
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl
                                bg-gradient-to-br {{ $color['bg'] }} border {{ $color['border'] }} mb-4">
                        <svg class="w-8 h-8 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-300 mb-1">All caught up!</h3>
                    <p class="text-gray-500 text-sm">
                        No pending invitations for {{ strtolower($color['name']) }} department.
                    </p>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if(method_exists($pendingAssignments, 'hasPages') && $pendingAssignments->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-white/5 flex justify-end">
                {{ $pendingAssignments->links() }}
            </div>
            @endif

        </div>{{-- end glass-card --}}

    </div>{{-- end container --}}

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ── Toolbar ─────────────────────────────────────────── */
        const form        = document.getElementById('pr-filterForm');
        const searchInput = document.getElementById('pr-search');
        const clearBtn    = document.getElementById('pr-clearSearch');
        const statusSel   = document.getElementById('pr-statusFilter');
        const sortSel     = document.getElementById('pr-sortBy');
        const loading     = document.getElementById('pr-loading');

        let debounceTimer;
        function debounce(fn, ms = 450) { clearTimeout(debounceTimer); debounceTimer = setTimeout(fn, ms); }

        function submitForm() {
            if (loading) loading.classList.add('active');
            form.submit();
        }

        searchInput?.addEventListener('input', function () {
            clearBtn?.classList.toggle('visible', this.value.length > 0);
            debounce(submitForm);
        });

        clearBtn?.addEventListener('click', function () {
            searchInput.value = '';
            this.classList.remove('visible');
            submitForm();
        });

        statusSel?.addEventListener('change', submitForm);
        sortSel?.addEventListener('change',   submitForm);

        document.getElementById('pr-refreshBtn')?.addEventListener('click', function () {
            this.querySelector('svg').classList.add('animate-spin');
            setTimeout(() => location.reload(), 400);
        });

    });

    /* ── Decline modal ───────────────────────────────────────── */
    let activeDeclineId = null;

    function openDeclineModal(assignmentId, contractTitle) {
        activeDeclineId = assignmentId;
        document.getElementById('decline-reason-input').value = '';
        const titleEl = document.getElementById('decline-contract-title');
        if (titleEl) titleEl.textContent = contractTitle || '—';
        document.getElementById('decline-modal').classList.add('open');
        // Focus textarea for accessibility
        setTimeout(() => document.getElementById('decline-reason-input')?.focus(), 150);
    }

    function closeDeclineModal() {
        activeDeclineId = null;
        document.getElementById('decline-modal').classList.remove('open');
    }

    function submitDecline() {
        if (!activeDeclineId) return;
        const reason = document.getElementById('decline-reason-input').value.trim();
        const reasonInput = document.getElementById('decline-reason-' + activeDeclineId);
        if (reasonInput) reasonInput.value = reason;
        const form = document.getElementById('decline-form-' + activeDeclineId);
        if (form) {
            const loading = document.getElementById('pr-loading');
            if (loading) loading.classList.add('active');
            form.submit();
        }
    }

    // Close on backdrop click
    document.getElementById('decline-modal')?.addEventListener('click', function (e) {
        if (e.target === this) closeDeclineModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDeclineModal();
    });
    </script>
</x-app-layout-dark>