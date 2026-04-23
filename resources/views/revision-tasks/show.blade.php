{{-- resources/views/revision-tasks/show.blade.php --}}
{{-- Halaman detail revision task untuk penerima (Bunga/Sari) dan pemberi (Bimo) --}}

@php
    $isAssignee  = $currentUser->id_user === $task->assigned_to;
    $isRequester = $currentUser->id_user === $task->requested_by;

    $statusColors = [
        'pending'      => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
        'in_progress'  => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
        'submitted'    => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
        'approved'     => 'bg-green-500/10 text-green-400 border-green-500/30',
        're_requested' => 'bg-orange-500/10 text-orange-400 border-orange-500/30',
        'cancelled'    => 'bg-gray-500/10 text-gray-400 border-gray-500/30',
    ];

    $statusColor = $statusColors[$task->status] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/30';
    
    // Status icon mapping
    $statusIcon = match($task->status) {
        'pending' => '🕐', 'in_progress' => '🔄', 'submitted' => '📨',
        'approved' => '✅', 're_requested' => '↩️', 'cancelled' => '❌',
        default => '📌'
    };
@endphp

<x-app-layout-dark title="Revision Task #{{ $task->id }}">
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header with gradient accent --}}
    <div class="relative mb-8">
        <div class="absolute top-0 left-0 w-24 h-1 bg-gradient-to-r from-amber-500 to-orange-500 rounded-full"></div>
        <div class="flex items-center gap-3 mb-4 pt-2">
            <a href="{{ route('contracts.show', $task->contract_id) }}"
               class="group inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-700/50 bg-gray-800/40 hover:bg-gray-800/60 hover:border-gray-600/50 transition-all duration-200">
                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="text-sm text-gray-400 group-hover:text-gray-300">Back to Contract</span>
            </a>
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-100 to-gray-300 bg-clip-text text-transparent">
                        Revision Task #{{ $task->id }}
                    </h1>
                    @if($task->loop_count > 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-500/15 text-orange-400 border border-orange-500/25">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Loop {{ $task->loop_count }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <p class="text-gray-400 text-sm">
                        Contract: <span class="text-gray-300 font-medium">{{ $task->contract->title }}</span>
                    </p>
                    <span class="w-1 h-1 rounded-full bg-gray-600"></span>
                    <p class="text-gray-400 text-sm">
                        Stage: <span class="text-gray-300">{{ $task->fromStage->stage_name ?? '-' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-sm font-semibold {{ $statusColor }}">
                    <span class="text-base">{{ $statusIcon }}</span>
                    {{ $task->status_label }}
                </span>
            </div>
        </div>
    </div>

    {{-- Flash Messages with animation --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-green-500/10 to-emerald-500/10 border-l-4 border-green-500 shadow-lg animate-fade-in">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-green-400 text-sm font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-red-500/10 to-rose-500/10 border-l-4 border-red-500 shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-400 text-sm font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif
    @if(session('warning'))
        <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-amber-500/10 to-orange-500/10 border-l-4 border-amber-500 shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-amber-400 text-sm font-medium">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Task Content & Actions --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Revision Request Box --}}
            <div class="glass-card rounded-2xl overflow-hidden border-l-4 border-amber-500 transition-all hover:shadow-xl hover:shadow-amber-500/5">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-amber-400 text-lg">Revision Request</h3>
                            <div class="flex items-center gap-2 text-sm text-gray-400 mt-0.5">
                                <span>From <strong class="text-gray-200">{{ $task->requester->nama_user ?? 'Unknown' }}</strong></span>
                                <span class="w-1 h-1 rounded-full bg-gray-600"></span>
                                <span>{{ $task->fromStage->stage_name ?? '-' }}</span>
                                <span class="w-1 h-1 rounded-full bg-gray-600"></span>
                                <span>{{ $task->sent_at?->format('d M Y, H:i') ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></div>
                        <div class="pl-5 py-2">
                            <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">{{ $task->revision_notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Response Box (jika sudah ada) --}}
            @if($task->response_notes)
                <div class="glass-card rounded-2xl overflow-hidden border-l-4 border-purple-500 transition-all hover:shadow-xl hover:shadow-purple-500/5">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/20">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-purple-400 text-lg">Revision Result</h3>
                                <div class="flex items-center gap-2 text-sm text-gray-400 mt-0.5">
                                    <span>From <strong class="text-gray-200">{{ $task->assignee->nama_user ?? 'Unknown' }}</strong></span>
                                    <span class="w-1 h-1 rounded-full bg-gray-600"></span>
                                    <span>{{ $task->submitted_at?->format('d M Y, H:i') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-purple-500 to-indigo-500 rounded-full"></div>
                            <div class="pl-5 py-2">
                                <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">{{ $task->response_notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══════════════════════════════════════════════
                 ACTION AREA: Berdasarkan role & status task
            ══════════════════════════════════════════════ --}}

            {{-- ASSIGNEE (Bunga/Sari): Mulai kerjakan --}}
            @if($isAssignee && in_array($task->status, ['pending', 're_requested']))
                <div class="glass-card rounded-2xl p-6 border border-blue-500/30 hover:border-blue-500/50 transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/15 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-blue-400">Start Working</h3>
                            <p class="text-xs text-gray-500">Mark this revision as in progress</p>
                        </div>
                    </div>
                    <form action="{{ route('revision-tasks.start', $task) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 rounded-xl font-semibold text-white transition-all duration-200 shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2">
                            <span>▶</span> Start Revision
                        </button>
                    </form>
                </div>
            @endif

            {{-- ASSIGNEE (Bunga/Sari): Submit hasil --}}
            @if($isAssignee && in_array($task->status, ['pending', 'in_progress', 're_requested']))
                <div class="glass-card rounded-2xl p-6 border border-purple-500/30 hover:border-purple-500/50 transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/15 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-purple-400">Submit Result</h3>
                            <p class="text-xs text-gray-500">Send your revision result to reviewer</p>
                        </div>
                    </div>
                    <form action="{{ route('revision-tasks.submit', $task) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Response Notes <span class="text-red-400">*</span>
                                <span class="text-gray-500 text-xs ml-1">(min 5 characters)</span>
                            </label>
                            <textarea name="response_notes" rows="4" required minlength="5" maxlength="2000"
                                      class="w-full bg-gray-800/50 border border-gray-700/50 rounded-xl px-4 py-3 text-gray-300 text-sm focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500/40 outline-none transition-all resize-none"
                                      placeholder="Explain what has been revised, what changes were made, or any questions you have..."></textarea>
                        </div>
                        <button type="submit"
                                class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 rounded-xl font-semibold text-white transition-all duration-200 shadow-lg shadow-purple-500/20 flex items-center justify-center gap-2"
                                onclick="return confirm('Submit the revised results to {{ addslashes($task->requester->nama_user ?? 'reviewer') }}?')">
                            <span>📤</span> Submit Revision
                        </button>
                    </form>
                </div>
            @endif

            {{-- REQUESTER (Bimo): Approve atau Re-request --}}
            @if($isRequester && $task->status === 'submitted')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Approve --}}
                    <div class="glass-card rounded-2xl p-5 border border-green-500/30 hover:border-green-500/50 transition-all group">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-green-500/15 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-green-400">Approve</h3>
                        </div>
                        <p class="text-xs text-gray-400 mb-4">Revision result is satisfactory</p>
                        <form action="{{ route('revision-tasks.approve', $task) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="approval_notes" rows="2" maxlength="1000"
                                          class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-3 py-2 text-gray-300 text-sm focus:ring-1 focus:ring-green-500/30 outline-none transition-all"
                                          placeholder="Notes (optional)..."></textarea>
                            </div>
                            <button type="submit"
                                    class="w-full py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 rounded-lg font-semibold text-white text-sm transition-all duration-200 flex items-center justify-center gap-2"
                                    onclick="return confirm('Approve revision from {{ addslashes($task->assignee->nama_user ?? 'reviewer') }}?')">
                                <span>✓</span> Approve
                            </button>
                        </form>
                    </div>

                    {{-- Re-request --}}
                    <div class="glass-card rounded-2xl p-5 border border-orange-500/30 hover:border-orange-500/50 transition-all group">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-500/15 flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-orange-400">Re-request</h3>
                        </div>
                        <p class="text-xs text-gray-400 mb-4">Need more improvements</p>
                        <form action="{{ route('revision-tasks.re-request', $task) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="revision_notes" rows="2" required minlength="10" maxlength="2000"
                                          class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-3 py-2 text-gray-300 text-sm focus:ring-1 focus:ring-orange-500/30 outline-none transition-all"
                                          placeholder="What still needs to be revised? (min 10 chars)"></textarea>
                            </div>
                            <button type="submit"
                                    class="w-full py-2.5 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 rounded-lg font-semibold text-white text-sm transition-all duration-200 flex items-center justify-center gap-2"
                                    onclick="return confirm('Send revision again to {{ addslashes($task->assignee->nama_user ?? 'reviewer') }}?')">
                                <span>↩</span> Re-request
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Task sudah selesai / cancelled --}}
            @if(in_array($task->status, ['approved', 'cancelled']))
                <div class="glass-card rounded-2xl p-8 text-center border border-{{ $task->status === 'approved' ? 'green' : 'gray' }}-500/30">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl {{ $task->status === 'approved' ? 'bg-green-500/15' : 'bg-gray-500/15' }} flex items-center justify-center">
                        @if($task->status === 'approved')
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    <p class="text-xl font-bold {{ $task->status === 'approved' ? 'text-green-400' : 'text-gray-400' }}">
                        {{ $task->status === 'approved' ? 'Revision Approved' : 'Task Cancelled' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ $task->status === 'approved' ? ($task->approved_at?->format('d M Y, H:i') ?? '-') : ($task->cancelled_at?->format('d M Y, H:i') ?? '-') }}
                    </p>
                </div>
            @endif

        </div>

        {{-- RIGHT: Info & History --}}
        <div class="space-y-6">

            {{-- Task Info --}}
            <div class="glass-card rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-5">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="font-semibold text-gray-300">Task Information</h3>
                </div>
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-gray-800/50">
                        <span class="text-gray-500">Requester</span>
                        <div class="text-right">
                            <p class="text-gray-200 font-medium">{{ $task->requester->nama_user ?? '-' }}</p>
                            <p class="text-gray-500 text-xs">{{ $task->fromStage->stage_name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-800/50">
                        <span class="text-gray-500">Assignee</span>
                        <div class="text-right">
                            <p class="text-gray-200 font-medium">{{ $task->assignee->nama_user ?? '-' }}</p>
                            <p class="text-gray-500 text-xs">{{ $task->toStage->stage_name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-800/50">
                        <span class="text-gray-500">Sent</span>
                        <p class="text-gray-300">{{ $task->sent_at?->format('d M Y, H:i') ?? '-' }}</p>
                    </div>
                    @if($task->started_at)
                    <div class="flex justify-between items-center py-2 border-b border-gray-800/50">
                        <span class="text-gray-500">Started</span>
                        <p class="text-gray-300">{{ $task->started_at->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                    @if($task->submitted_at)
                    <div class="flex justify-between items-center py-2 border-b border-gray-800/50">
                        <span class="text-gray-500">Submitted</span>
                        <p class="text-gray-300">{{ $task->submitted_at->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                    @if($task->approved_at)
                    <div class="flex justify-between items-center py-2 border-b border-gray-800/50">
                        <span class="text-gray-500">Approved</span>
                        <p class="text-green-400 font-medium">{{ $task->approved_at->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-500">Loop</span>
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-800 text-gray-300 text-sm font-semibold">{{ $task->loop_count }}</span>
                    </div>
                </div>

                {{-- Cancel button (requester only, active tasks) --}}
                @if($isRequester && !in_array($task->status, ['approved', 'cancelled']))
                    <div class="mt-5 pt-4 border-t border-gray-700/50">
                        <form action="{{ route('revision-tasks.cancel', $task) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full py-2.5 text-sm font-medium text-red-400 border border-red-500/30 rounded-xl hover:bg-red-500/10 hover:border-red-500/50 transition-all duration-200 flex items-center justify-center gap-2"
                                    onclick="return confirm('Cancel this revision task?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancel Task
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Revision Loop History --}}
            @if(count($taskHistory) > 1)
                <div class="glass-card rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-5">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <h3 class="font-semibold text-gray-300">Revision History</h3>
                    </div>
                    <div class="space-y-2">
                        @foreach($taskHistory as $histTask)
                            <div class="flex items-start gap-3 p-3 rounded-xl transition-all {{ $histTask->id === $task->id ? 'bg-gray-700/30 border border-gray-600/50' : 'hover:bg-gray-800/30' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5
                                    {{ $histTask->status === 'approved' ? 'bg-green-500/15 text-green-400' :
                                       ($histTask->status === 'submitted' ? 'bg-purple-500/15 text-purple-400' :
                                       ($histTask->status === 're_requested' ? 'bg-orange-500/15 text-orange-400' : 'bg-gray-600/30 text-gray-400')) }}">
                                    <span class="text-xs font-bold">{{ $histTask->loop_count }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-medium
                                            {{ $histTask->status === 'approved' ? 'text-green-400' :
                                               ($histTask->status === 'submitted' ? 'text-purple-400' :
                                               ($histTask->status === 're_requested' ? 'text-orange-400' : 'text-gray-400')) }}">
                                            {{ $histTask->status_label }}
                                        </span>
                                        @if($histTask->id === $task->id)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-500/15 text-blue-400">current</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-gray-500 mt-1">
                                        {{ $histTask->sent_at?->format('d M, H:i') ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

<style>
.glass-card {
    background: rgba(17, 25, 40, 0.75);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.06);
    transition: all 0.2s ease;
}
.glass-card:hover {
    border-color: rgba(255, 255, 255, 0.1);
}

textarea:focus, select:focus { outline: none; }

@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

/* Custom scrollbar */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: rgba(255,255,255,.03); border-radius: 10px; }
::-webkit-scrollbar-thumb { background: rgba(245,158,11,.3); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(245,158,11,.5); }
</style>
</x-app-layout-dark>