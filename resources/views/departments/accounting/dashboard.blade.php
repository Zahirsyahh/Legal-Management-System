<x-app-layout-dark title="Accounting Department Admin Dashboard">
    @push('header')
        <div class="flex items-center space-x-4">
            @include('components.notification-bell')
        </div>
        <style>
            /* Custom Glassmorphism Effects */
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

            .glass-table {
                background: rgba(0, 0, 0, 0.2);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.05);
            }

            /* Gradient borders */
            .gradient-border {
                position: relative;
                background: rgba(255, 255, 255, 0.02);
                backdrop-filter: blur(20px);
                border-radius: 1rem;
            }

            .gradient-border::before {
                content: '';
                position: absolute;
                inset: 0;
                border-radius: 1rem;
                padding: 1px;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.02));
                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                -webkit-mask-composite: xor;
                mask-composite: exclude;
                pointer-events: none;
            }

            /* Animated gradient text */
            .gradient-text-animated {
                background: linear-gradient(135deg, #a5b4fc, #818cf8, #c084fc);
                background-size: 200% 200%;
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
                animation: gradient 3s ease infinite;
            }

            @keyframes gradient {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }

            /* Glossy button effect */
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
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                transition: left 0.5s ease;
            }

            .glossy-btn:hover::after {
                left: 100%;
            }

            /* Blur overlay for depth */
            .depth-blur {
                background: rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(8px);
            }
        </style>
    @endpush

    <div class="relative pb-8 px-4 sm:px-6 lg:px-8">
        <!-- Animated Background Orbs for iOS-like feel -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-indigo-600/20 rounded-full blur-3xl animate-pulse">
            </div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-600/20 rounded-full blur-3xl animate-pulse"
                style="animation-delay: 2s;"></div>
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl">
            </div>
        </div>

        <!-- Header Welcome Section -->
        <div class="relative mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold">
                        <span class="gradient-text-animated">Accounting Dashboard</span>
                    </h1>
                    <p class="text-gray-300 mt-2 backdrop-blur-sm inline-block px-3 py-1 rounded-full bg-white/5">
                        Welcome back, <span class="text-indigo-300 font-medium">{{ Auth::user()->name }}</span>
                    </p>
                </div>
                <div class="text-right glass-stat px-5 py-3 rounded-2xl">
                    <p class="text-sm text-gray-300">{{ now()->format('l, F j, Y') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ now()->format('g:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards Row - Glassmorphism Style -->
        <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Pending Assignments -->
            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Pending Assignments</p>
                        <p class="text-4xl font-bold text-white">{{ $pendingCount }}</p>
                        <p class="text-gray-400 text-xs mt-2">Waiting for staff</p>
                    </div>
                    <div
                        class="w-12 h-12 bg-yellow-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-yellow-500/20">
                        <svg class="w-6 h-6 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('accounting-admin.pending') }}"
                    class="text-xs text-yellow-300/80 hover:text-yellow-300 mt-4 inline-flex items-center gap-1 transition-all">
                    View details
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Active Reviews -->
            <div class="glass-stat rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-300 text-sm mb-1 font-medium">Active Reviews</p>
                        <p class="text-4xl font-bold text-white">{{ $activeCount }}</p>
                        <p class="text-gray-400 text-xs mt-2">In progress</p>
                    </div>
                    <div
                        class="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-indigo-500/20">
                        <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('accounting-admin.active') }}"
                    class="text-xs text-indigo-300/80 hover:text-indigo-300 mt-4 inline-flex items-center gap-1 transition-all">
                    View details
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Main Content - Two Tables Row with Glass Effect -->
        <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Review Invitation Table -->
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Review Invitations</h2>
                        <p class="text-xs text-gray-400 mt-1">Contracts waiting for your department to join</p>
                    </div>
                    <a href="{{ route('accounting-admin.pending') }}"
                        class="text-sm text-yellow-300/80 hover:text-yellow-300 transition-all">
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
                                <th class="px-6 py-3 font-medium text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($pendingAssignments->take(5) as $assignment)
                                <tr class="hover:bg-white/5 transition-colors duration-200">
                                    <td class="px-6 py-4 text-sm font-mono text-gray-300">
                                        {{ $assignment->contract->contract_number ?? 'DOC-' . str_pad($assignment->contract->id, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300 max-w-[160px] truncate">
                                        {{ $assignment->contract->title }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-400">
                                        {{ $assignment->contract->user->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">

                                            {{-- View --}}
                                            <a href="{{ route('contracts.show', $assignment->contract) }}"
                                                class="btn-action btn-view" title="View contract details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>

                                            {{-- Accept --}}
                                            <form method="POST"
                                                action="{{ route('accounting-admin.invitation.accept', $assignment) }}"
                                                onsubmit="return confirm('Accept this review invitation?')">
                                                @csrf
                                                <button type="submit" class="btn-action btn-accept"
                                                    title="Accept invitation">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>

                                            {{-- Decline (opens decline modal) --}}
                                            <button type="button" class="btn-action btn-decline" title="Decline invitation"
                                                onclick="openDeclineModal('{{ $assignment->id }}', '{{ addslashes(Str::limit($assignment->contract->title, 60)) }}')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>

                                            {{-- Hidden decline form --}}
                                            <form id="decline-form-{{ $assignment->id }}" method="POST"
                                                action="{{ route('accounting-admin.invitation.decline', $assignment) }}"
                                                class="hidden">
                                                @csrf
                                                <input type="hidden" name="decline_reason"
                                                    id="decline-reason-{{ $assignment->id }}">
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-500 mb-3 opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p>No pending invitations</p>
                                            <p class="text-xs mt-1 opacity-60">Your department has no open invitations</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pendingAssignments->count() > 5)
                    <div class="px-6 py-3 border-t border-white/10 text-center">
                        <a href="{{ route('accounting-admin.pending') }}"
                            class="text-xs text-yellow-300/70 hover:text-yellow-300 transition-all">
                            + {{ $pendingAssignments->count() - 5 }} more invitations
                        </a>
                    </div>
                @endif
            </div>

            <style>
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
                    background: rgba(255, 255, 255, 0.06);
                    border-color: rgba(255, 255, 255, 0.15);
                    color: #94a3b8;
                }

                .btn-view:hover {
                    background: rgba(255, 255, 255, 0.12);
                    border-color: rgba(255, 255, 255, 0.25);
                    color: #e2e8f0;
                }

                .btn-accept {
                    background: rgba(34, 197, 94, 0.12);
                    border-color: rgba(34, 197, 94, 0.3);
                    color: #86efac;
                }

                .btn-accept:hover {
                    background: rgba(34, 197, 94, 0.22);
                    border-color: rgba(34, 197, 94, 0.5);
                    color: #4ade80;
                }

                .btn-decline {
                    background: rgba(239, 68, 68, 0.1);
                    border-color: rgba(239, 68, 68, 0.25);
                    color: #fca5a5;
                }

                .btn-decline:hover {
                    background: rgba(239, 68, 68, 0.2);
                    border-color: rgba(239, 68, 68, 0.45);
                    color: #f87171;
                }
            </style>

            <!-- Active Reviews Table -->
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Active Reviews</h2>
                        <p class="text-xs text-gray-400 mt-1">Currently being reviewed by accounting staff</p>
                    </div>
                    <a href="{{ route('accounting-admin.active') }}"
                        class="text-sm text-indigo-300/80 hover:text-indigo-300 transition-all">
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
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium backdrop-blur-sm {{ $review->status == 'assigned' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' }}">
                                            {{ ucfirst($review->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('contracts.show', $review->contract) }}"
                                            class="glossy-btn px-3 py-1.5 text-xs bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 rounded-lg transition-all inline-flex items-center gap-1 border border-indigo-500/30">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-500 mb-3 opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                        <a href="{{ route('accounting-admin.active') }}"
                            class="text-xs text-indigo-300/70 hover:text-indigo-300 transition-all">
                            + {{ $activeReviews->count() - 5 }} more active reviews
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Bottom Row - Tools & Status with Glass Effect -->
        <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Accounting Admin Tools -->
            <div class="glass-card rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Accounting Admin Tools</h2>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('accounting-admin.pending') }}"
                        class="flex items-center justify-between p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-all group backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-yellow-500/10 rounded-xl flex items-center justify-center border border-yellow-500/20">
                                <svg class="w-5 h-5 text-yellow-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-white text-sm">Review Invitations</h3>
                                <p class="text-xs text-gray-400">See pending review invitations</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="{{ route('accounting-admin.active') }}"
                        class="flex items-center justify-between p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-all group backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-indigo-500/10 rounded-xl flex items-center justify-center border border-indigo-500/20">
                                <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-white text-sm">Monitor Progress</h3>
                                <p class="text-xs text-gray-400">Track review progress</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="{{ route('accounting-admin.completed') }}"
                        class="flex items-center justify-between p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-all group backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-green-500/10 rounded-xl flex items-center justify-center border border-green-500/20">
                                <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-white text-sm">Completed Reviews</h3>
                                <p class="text-xs text-gray-400">View finished reviews</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Department Status -->
<div class="glass-card rounded-2xl p-6">
    <h2 class="text-lg font-semibold text-white mb-4">Department Status</h2>
    <div class="space-y-4">
        <!-- Stats -->
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white/5 rounded-xl p-3 backdrop-blur-sm border border-white/5 text-center">
                <div class="flex items-center justify-center gap-1 mb-1">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-xs text-gray-400">Pending</p>
                </div>
                <p class="text-2xl font-bold text-yellow-300">{{ $pendingCount }}</p>
                <p class="text-xs text-gray-500">need assignment</p>
            </div>

            <div class="bg-white/5 rounded-xl p-3 backdrop-blur-sm border border-white/5 text-center">
                <div class="flex items-center justify-center gap-1 mb-1">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-gray-400">In Progress</p>
                </div>
                <p class="text-2xl font-bold text-indigo-300">{{ $activeCount }}</p>
                <p class="text-xs text-gray-500">active reviews</p>
            </div>

            <div class="bg-white/5 rounded-xl p-3 backdrop-blur-sm border border-white/5 text-center">
                <div class="flex items-center justify-center gap-1 mb-1">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-xs text-gray-400">Completed</p>
                </div>
                <p class="text-2xl font-bold text-green-300">{{ $completedCount }}</p>
                <p class="text-xs text-gray-500">this month</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div>
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-300">Overall Completion Rate</span>
                <span class="text-white font-medium">
                    @php
                        $total = $pendingCount + $activeCount + $completedCount;
                        $rate = $total > 0 ? round(($completedCount / $total) * 100) : 0;
                    @endphp
                    {{ $rate }}%
                </span>
            </div>
            <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-400 to-purple-400 h-2 rounded-full transition-all duration-700"
                    style="width: {{ $rate }}%"></div>
            </div>
        </div>

        <!-- Priority Tasks -->
        <div class="pt-4 border-t border-white/10">
            <h3 class="text-sm font-medium text-gray-300 mb-3">Priority Tasks</h3>
            <div class="space-y-2">
                @if($pendingCount > 0)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                            <span class="text-gray-300">Documents need assignment</span>
                        </div>
                        <span class="text-yellow-300 font-medium">{{ $pendingCount }}</span>
                    </div>
                @endif
                @if($activeCount > 0)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-indigo-400 rounded-full"></div>
                            <span class="text-gray-300">Reviews in progress</span>
                        </div>
                        <span class="text-indigo-300 font-medium">{{ $activeCount }}</span>
                    </div>
                @endif
                @if($completedCount > 0)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                            <span class="text-gray-300">Completed this month</span>
                        </div>
                        <span class="text-green-300 font-medium">{{ $completedCount }}</span>
                    </div>
                @endif
                @if($pendingCount == 0 && $activeCount == 0 && $completedCount == 0)
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                        <span class="text-gray-500">No tasks yet</span>
                    </div>
                @elseif($pendingCount == 0 && $activeCount == 0 && $completedCount > 0)
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-gray-300">All caught up! 🎉</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
        </div>
    </div>

    {{-- ===== FLASH: INVITATION DECLINED ===== --}}
    @if(session('invitation_declined'))
    <div id="inv-declined-toast"
        class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] w-full max-w-lg px-4"
        style="animation: slideDownToast 0.4s ease;">
        <div class="flex items-start gap-4 px-5 py-4 rounded-2xl border border-red-500/40 shadow-2xl"
            style="background: linear-gradient(135deg, rgba(185,28,28,0.3), rgba(239,68,68,0.15));
                    backdrop-filter: blur(20px);">
            <div class="w-10 h-10 rounded-full bg-red-500/20 border border-red-500/40
                        flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-red-300 font-semibold text-sm">Invitation Declined</p>
                <p class="text-red-200/80 text-xs mt-0.5 break-words">
                    {!! session('invitation_declined') !!}
                </p>
            </div>
            <button onclick="document.getElementById('inv-declined-toast').remove()"
                    class="text-red-400/50 hover:text-red-300 transition-colors flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <style>
    @keyframes slideDownToast {
        from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
        to   { opacity: 1; transform: translateX(-50%) translateY(0); }
    }
    </style>
    <script>
    setTimeout(() => document.getElementById('inv-declined-toast')?.remove(), 6000);
    </script>
    @endif
    
    {{-- ===== FLASH: INVITATION ACCEPTED ===== --}}
    @if(session('invitation_accepted'))
    <div id="inv-accepted-toast"
        class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] w-full max-w-lg px-4"
        style="animation: slideDownToast 0.4s ease;">
        <div class="flex items-start gap-4 px-5 py-4 rounded-2xl border border-green-500/40 shadow-2xl"
            style="background: linear-gradient(135deg, rgba(5,150,105,0.3), rgba(16,185,129,0.15));
                    backdrop-filter: blur(20px);">
            <div class="w-10 h-10 rounded-full bg-green-500/20 border border-green-500/40
                        flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-green-300 font-semibold text-sm">Joined Workflow!</p>
                <p class="text-green-200/80 text-xs mt-0.5 break-words">
                    {!! session('invitation_accepted') !!}
                </p>
            </div>
            <button onclick="document.getElementById('inv-accepted-toast').remove()"
                    class="text-green-400/50 hover:text-green-300 transition-colors flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <script>
    setTimeout(() => document.getElementById('inv-accepted-toast')?.remove(), 6000);
    </script>
    @endif

    {{-- ================================================================ --}}
    {{-- DECLINE REASON MODAL                                              --}}
    {{-- ================================================================ --}}
    <div id="decline-modal"
        class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="glass-card rounded-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-white font-semibold text-lg mb-2">Decline Invitation</h3>
            <p class="text-gray-400 text-sm mb-4">Optionally provide a reason. Legal will be notified.</p>
            <textarea id="decline-reason-input" rows="3" placeholder="Reason (optional)..."
                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200
                       placeholder-gray-500 focus:outline-none focus:border-white/30 resize-none mb-4"></textarea>
            <div class="flex justify-end gap-3">
                <button onclick="closeDeclineModal()"
                    class="px-4 py-2 text-sm text-gray-300 bg-white/5 hover:bg-white/10 rounded-lg
                           border border-white/10 transition-all">
                    Cancel
                </button>
                <button onclick="submitDecline()"
                    class="px-4 py-2 text-sm text-red-300 bg-red-500/20 hover:bg-red-500/30 rounded-lg
                           border border-red-500/30 transition-all">
                    Confirm Decline
                </button>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- JS FUNCTIONS                                                      --}}
    {{-- ================================================================ --}}
    <script>
        let activeDeclineId = null;

        function openDeclineModal(assignmentId, contractTitle) {
            activeDeclineId = assignmentId;
            document.getElementById('decline-reason-input').value = '';
            document.getElementById('decline-modal').classList.remove('hidden');
            setTimeout(() => document.getElementById('decline-reason-input')?.focus(), 150);
        }

        function closeDeclineModal() {
            activeDeclineId = null;
            document.getElementById('decline-modal').classList.add('hidden');
        }

        function submitDecline() {
            if (!activeDeclineId) return;
            const reason = document.getElementById('decline-reason-input').value;
            document.getElementById('decline-reason-' + activeDeclineId).value = reason;
            document.getElementById('decline-form-' + activeDeclineId).submit();
        }

        // Tutup modal kalau klik backdrop
        document.getElementById('decline-modal').addEventListener('click', function(e) {
            if (e.target === this) closeDeclineModal();
        });

        // Tutup modal kalau tekan Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDeclineModal();
        });
    </script>
</x-app-layout-dark>