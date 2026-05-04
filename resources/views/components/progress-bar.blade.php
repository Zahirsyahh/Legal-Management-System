{{-- resources/views/components/progress-bar.blade.php --}}
@props(['contract'])

@if($contract->isInReviewStageSystem())
@php
    $stages = $contract->reviewStages->sortBy('sequence');
    $totalStages = $stages->count();

    $completedStages = $stages->where('status', 'completed')->count();
    $progressPercent = $totalStages > 0 ? round(($completedStages / $totalStages) * 100, 1) : 0;

    $stageIds = $stages->pluck('id')->toArray();

    $sentTaskCounts = \App\Models\ContractRevisionTask::whereIn('from_stage_id', $stageIds)
        ->whereIn('status', ['pending', 'in_progress', 're_requested', 'submitted'])
        ->selectRaw('from_stage_id, status, COUNT(*) as total')
        ->groupBy('from_stage_id', 'status')
        ->get()
        ->groupBy('from_stage_id');

    $receivedTaskCounts = \App\Models\ContractRevisionTask::whereIn('to_stage_id', $stageIds)
        ->whereIn('status', ['pending', 'in_progress', 're_requested'])
        ->selectRaw('to_stage_id, COUNT(*) as total')
        ->groupBy('to_stage_id')
        ->pluck('total', 'to_stage_id');

    $submittedTaskCounts = \App\Models\ContractRevisionTask::whereIn('from_stage_id', $stageIds)
        ->where('status', 'submitted')
        ->selectRaw('from_stage_id, COUNT(*) as total')
        ->groupBy('from_stage_id')
        ->pluck('total', 'from_stage_id');

    $parallelGroups = [];
    $sequentialStages = collect();

    foreach ($stages as $stage) {
        if ($stage->parallel_group !== null) {
            if (!isset($parallelGroups[$stage->parallel_group])) {
                $parallelGroups[$stage->parallel_group] = [
                    'stages'       => collect(),
                    'sequence'     => $stage->sequence,
                    'is_completed' => true,
                ];
            }
            $parallelGroups[$stage->parallel_group]['stages']->push($stage);
            if ($stage->status !== 'completed') {
                $parallelGroups[$stage->parallel_group]['is_completed'] = false;
            }
        } else {
            $sequentialStages->push($stage);
        }
    }

    ksort($parallelGroups);

    $displayItems = [];
    $lastSeq = 0;

    foreach ($sequentialStages as $seqStage) {
        foreach ($parallelGroups as $groupId => $group) {
            if ($group['sequence'] > $lastSeq && $group['sequence'] <= $seqStage->sequence) {
                $displayItems[] = ['type' => 'parallel', 'data' => $group, 'groupId' => $groupId];
                $lastSeq = $group['sequence'];
            }
        }
        $displayItems[] = ['type' => 'sequential', 'data' => $seqStage];
        $lastSeq = $seqStage->sequence;
    }

    foreach ($parallelGroups as $groupId => $group) {
        if ($group['sequence'] > $lastSeq) {
            $displayItems[] = ['type' => 'parallel', 'data' => $group, 'groupId' => $groupId];
            $lastSeq = $group['sequence'];
        }
    }

    $totalItems = count($displayItems);

    $reviewerStagesCount = $sequentialStages->filter(fn($s) => !$s->is_user_stage)->count();
    foreach ($parallelGroups as $group) {
        $reviewerStagesCount += $group['stages']->count();
    }
@endphp

<div class="progress-bar-container" style="position: sticky; top: 0; z-index: 5; background: inherit; padding: 1rem 0; margin-bottom: 1rem;">
    <div class="glass-card rounded-xl p-6 overflow-visible" style="position: relative; z-index: 5;">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-300">Review Progress</h3>
            <span class="text-sm text-gray-400">{{ $progressPercent }}% Complete</span>
        </div>

        <div class="h-2 bg-gray-700 rounded-full overflow-hidden mb-6">
            <div class="h-full bg-gradient-to-r from-blue-500 to-green-500 transition-all duration-500"
                 style="width: {{ $progressPercent }}%"></div>
        </div>

        <div class="relative overflow-visible">
            <div class="absolute h-0.5 bg-gray-700 -z-10" style="top: 32px; left: 0; right: 0;"></div>

            @if($totalItems < 8)
            <div class="grid relative z-10 overflow-visible" style="grid-template-columns: repeat({{ $totalItems }}, minmax(0, 1fr)); padding-top: 12px;">
                @foreach($displayItems as $index => $item)
                    @if($item['type'] === 'sequential')
                        @php
                            $stage = $item['data'];
                            $sentOpen      = isset($sentTaskCounts[$stage->id]) ? $sentTaskCounts[$stage->id]->sum('total') : 0;
                            $sentSubmitted = $submittedTaskCounts[$stage->id] ?? 0;
                            $receivedOpen  = $receivedTaskCounts[$stage->id] ?? 0;
                            $hasIncomingRevision = $receivedOpen > 0;
                            $hasOutgoingRevision = $sentOpen > 0 || $sentSubmitted > 0;
                        @endphp
                        <div class="flex flex-col items-center relative overflow-visible">
                            <div class="relative mb-2 overflow-visible">
                                @if($stage->isActive())
                                <div class="absolute z-20" style="right: -6px; top: -6px;">
                                    <div class="w-3.5 h-3.5 bg-blue-400 rounded-full animate-ping"></div>
                                </div>
                                @endif
                                @if($hasIncomingRevision)
                                <div class="absolute z-30" style="left: -4px; top: -4px;">
                                    <div class="w-4 h-4 bg-orange-500 rounded-full flex items-center justify-center border border-gray-900">
                                        <span class="text-[9px] font-bold text-white leading-none">{{ $receivedOpen }}</span>
                                    </div>
                                </div>
                                @endif
                                @if($sentSubmitted > 0)
                                <div class="absolute z-30" style="right: -8px; top: -8px;">
                                    <div class="w-4 h-4 bg-purple-500 rounded-full flex items-center justify-center border border-gray-900 animate-pulse">
                                        <span class="text-[9px] font-bold text-white leading-none">{{ $sentSubmitted }}</span>
                                    </div>
                                </div>
                                @endif

                                <div class="w-10 h-10 rounded-full flex items-center justify-center relative z-10
                                    @if($stage->status === 'completed') bg-green-500 text-white shadow-lg shadow-green-500/30
                                    @elseif($stage->status === 'declined') bg-red-500 text-white shadow-lg shadow-red-500/30
                                    @elseif($stage->isActive()) bg-blue-500 text-white border-2 border-blue-300 shadow-lg shadow-blue-500/30
                                    @elseif($stage->status === 'pending') bg-gray-700 text-gray-400
                                    @elseif($stage->status === 'revision_requested') bg-orange-500 text-white
                                    @else bg-gray-800 text-gray-300 border border-gray-600
                                    @endif">
                                    @if($stage->status === 'completed')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif($stage->status === 'declined')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    @elseif($stage->status === 'revision_requested')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    @elseif($stage->is_user_stage)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    @elseif($stage->stage_type === 'executing')
                                        <span class="text-base">✍️</span>
                                    @elseif($stage->stage_type === 'archiving')
                                        <span class="text-base">📦</span>
                                    @else
                                        <span class="text-sm font-semibold">{{ $stage->sequence }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-center max-w-[120px]">
                                <p class="text-xs font-medium text-gray-300 truncate" title="{{ $stage->stage_name }}">
                                    {{ Str::limit($stage->stage_name, 20) }}
                                </p>
                                <p class="text-xs text-gray-400 truncate mt-1" title="{{ $stage->assignedUser->nama_user ?? 'Unassigned' }}">
                                    {{ $stage->assignedUser ? Str::limit($stage->assignedUser->nama_user, 15) : 'Unassigned' }}
                                </p>
                                <p class="text-xs mt-1
                                    @if($stage->status === 'completed') text-green-400
                                    @elseif($stage->status === 'declined') text-red-400
                                    @elseif($stage->isActive()) text-blue-400
                                    @elseif($stage->status === 'revision_requested') text-orange-400
                                    @else text-gray-500
                                    @endif">
                                    @if($stage->stage_type === 'executing' && $stage->status === 'completed') Executed
                                    @elseif($stage->stage_type === 'archiving' && $stage->status === 'completed') Archived
                                    @elseif($stage->status === 'revision_requested') Revision
                                    @elseif($stage->status === 'declined') Declined
                                    @elseif($stage->status === 'in_progress') In Progress
                                    @elseif($stage->status === 'assigned') Assigned
                                    @elseif($stage->status === 'pending') Pending
                                    @elseif($stage->status === 'completed') Completed
                                    @else {{ ucfirst($stage->status) }}
                                    @endif
                                </p>
                                @if($hasIncomingRevision)
                                    <p class="text-[10px] text-orange-400 mt-0.5">{{ $receivedOpen }} revisi masuk</p>
                                @endif
                                @if($sentSubmitted > 0)
                                    <p class="text-[10px] text-purple-400 mt-0.5">{{ $sentSubmitted }} hasil ditinjau</p>
                                @elseif($sentOpen > 0 && $sentSubmitted === 0)
                                    @php $openOnly = $sentOpen - $sentSubmitted; @endphp
                                    @if($openOnly > 0)
                                        <p class="text-[10px] text-amber-400 mt-0.5">{{ $openOnly }} sedang dikerjakan</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @else
                        @php
                            $group   = $item['data'];
                            $groupId = $item['groupId'];

                            // ✅ FIX: Hitung state parallel group dengan benar termasuk 'declined'
                            $hasActive   = $group['stages']->contains(fn($s) => $s->isActive());
                            $hasDeclined = $group['stages']->contains(fn($s) => $s->status === 'declined');
                            // Group dianggap "semua declined" jika tidak ada yang pending/assigned/in_progress/completed
                            $allTerminated = $group['stages']->every(fn($s) => in_array($s->status, ['declined', 'skipped']));
                            $doneCount = $group['stages']->where('status', 'completed')->count();
                            $declinedCount = $group['stages']->where('status', 'declined')->count();
                        @endphp
                        <div class="parallel-group-wrapper flex flex-col items-center relative overflow-visible">
                            <div class="relative mb-2 overflow-visible">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center relative z-10 transition-all duration-200
                                    {{-- ✅ FIX: Urutan prioritas warna yang benar --}}
                                    @if($group['is_completed']) bg-purple-500 text-white shadow-lg shadow-purple-500/30
                                    @elseif($hasActive) bg-purple-500 text-white border-2 border-purple-300 shadow-lg shadow-purple-500/30
                                    @elseif($allTerminated && $hasDeclined) bg-red-500/70 text-white shadow-lg shadow-red-500/20
                                    @elseif($hasDeclined) bg-orange-500/70 text-white shadow-lg shadow-orange-500/20
                                    @else bg-gray-700 text-gray-400
                                    @endif">
                                    {{-- ✅ FIX: Icon sesuai state --}}
                                    @if($allTerminated && $hasDeclined)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    @endif
                                </div>
                                <button class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-gray-800 border border-gray-600 flex items-center justify-center hover:bg-gray-700 transition-all z-20 parallel-dropdown-btn"
                                        data-group="{{ $groupId }}">
                                    <svg class="w-3 h-3 text-gray-300 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <div class="text-center max-w-[140px]">
                                <p class="text-xs font-medium text-purple-300 truncate">Substantial Review</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $doneCount }}/{{ $group['stages']->count() }} Done
                                    @if($declinedCount > 0)
                                        · <span class="text-red-400">{{ $declinedCount }} declined</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @else
            {{-- Banyak stages: scrollable horizontal --}}
            <div class="stage-scroll-wrapper">
                <div class="stage-scroll" style="padding-top: 14px; padding-bottom: 4px;">
                    <div class="flex gap-8 pb-4" style="min-width: max-content;">
                        @foreach($displayItems as $index => $item)
                            @if($item['type'] === 'sequential')
                                @php
                                    $stage = $item['data'];
                                    $sentOpen      = isset($sentTaskCounts[$stage->id]) ? $sentTaskCounts[$stage->id]->sum('total') : 0;
                                    $sentSubmitted = $submittedTaskCounts[$stage->id] ?? 0;
                                    $receivedOpen  = $receivedTaskCounts[$stage->id] ?? 0;
                                    $hasIncomingRevision = $receivedOpen > 0;
                                @endphp
                                <div class="flex flex-col items-center" style="min-width: 140px;">
                                    <div class="relative mb-2" style="overflow: visible;">
                                        @if($stage->isActive())
                                        <div class="absolute z-20" style="right: -6px; top: -6px;">
                                            <div class="w-3.5 h-3.5 bg-blue-400 rounded-full animate-ping"></div>
                                        </div>
                                        @endif
                                        @if($hasIncomingRevision)
                                        <div class="absolute z-30" style="left: -4px; top: -4px;">
                                            <div class="w-4 h-4 bg-orange-500 rounded-full flex items-center justify-center border border-gray-900">
                                                <span class="text-[9px] font-bold text-white leading-none">{{ $receivedOpen }}</span>
                                            </div>
                                        </div>
                                        @endif
                                        @if($sentSubmitted > 0)
                                        <div class="absolute z-30" style="right: -8px; top: -8px;">
                                            <div class="w-4 h-4 bg-purple-500 rounded-full flex items-center justify-center border border-gray-900 animate-pulse">
                                                <span class="text-[9px] font-bold text-white leading-none">{{ $sentSubmitted }}</span>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="w-10 h-10 rounded-full flex items-center justify-center relative z-10
                                            @if($stage->status === 'completed') bg-green-500 text-white shadow-lg shadow-green-500/30
                                            @elseif($stage->status === 'declined') bg-red-500 text-white shadow-lg shadow-red-500/30
                                            @elseif($stage->isActive()) bg-blue-500 text-white border-2 border-blue-300 shadow-lg shadow-blue-500/30
                                            @elseif($stage->status === 'pending') bg-gray-700 text-gray-400
                                            @elseif($stage->status === 'revision_requested') bg-orange-500 text-white
                                            @else bg-gray-800 text-gray-300 border border-gray-600
                                            @endif">
                                            @if($stage->status === 'completed')
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            @elseif($stage->status === 'declined')
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            @elseif($stage->status === 'revision_requested')
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                            @elseif($stage->is_user_stage)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            @elseif($stage->stage_type === 'executing')
                                                <span class="text-base">✍️</span>
                                            @elseif($stage->stage_type === 'archiving')
                                                <span class="text-base">📦</span>
                                            @else
                                                <span class="text-sm font-semibold">{{ $stage->sequence }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-center w-[140px]">
                                        <p class="text-xs font-medium text-gray-300 truncate">{{ Str::limit($stage->stage_name, 20) }}</p>
                                        <p class="text-xs text-gray-400 truncate mt-1">{{ $stage->assignedUser ? Str::limit($stage->assignedUser->nama_user, 15) : 'Unassigned' }}</p>
                                        <p class="text-xs mt-1
                                            @if($stage->status === 'completed') text-green-400
                                            @elseif($stage->status === 'declined') text-red-400
                                            @elseif($stage->isActive()) text-blue-400
                                            @else text-gray-500 @endif">
                                            @if($stage->stage_type === 'executing' && $stage->status === 'completed') Executed
                                            @elseif($stage->stage_type === 'archiving' && $stage->status === 'completed') Archived
                                            @elseif($stage->status === 'declined') Declined
                                            @elseif($stage->status === 'in_progress') In Progress
                                            @elseif($stage->status === 'assigned') Assigned
                                            @elseif($stage->status === 'pending') Pending
                                            @elseif($stage->status === 'completed') Completed
                                            @else {{ ucfirst($stage->status) }}
                                            @endif
                                        </p>
                                        @if($hasIncomingRevision)
                                            <p class="text-[10px] text-orange-400 mt-0.5">{{ $receivedOpen }} revisi masuk</p>
                                        @endif
                                        @if($sentSubmitted > 0)
                                            <p class="text-[10px] text-purple-400 mt-0.5">{{ $sentSubmitted }} hasil ditinjau</p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                @php
                                    $group = $item['data'];
                                    $groupId = $item['groupId'];
                                    $hasActive = $group['stages']->contains(fn($s) => $s->isActive());
                                    $hasDeclined = $group['stages']->contains(fn($s) => $s->status === 'declined');
                                    $allTerminated = $group['stages']->every(fn($s) => in_array($s->status, ['declined', 'skipped']));
                                    $doneCount = $group['stages']->where('status', 'completed')->count();
                                    $declinedCount = $group['stages']->where('status', 'declined')->count();
                                @endphp
                                <div class="parallel-group-wrapper flex flex-col items-center relative overflow-visible" style="min-width: 160px;">
                                    <div class="relative mb-2 overflow-visible">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center relative z-10
                                            @if($group['is_completed']) bg-purple-500 text-white shadow-lg shadow-purple-500/30
                                            @elseif($hasActive) bg-purple-500 text-white border-2 border-purple-300 shadow-lg shadow-purple-500/30
                                            @elseif($allTerminated && $hasDeclined) bg-red-500/70 text-white shadow-lg shadow-red-500/20
                                            @elseif($hasDeclined) bg-orange-500/70 text-white shadow-lg shadow-orange-500/20
                                            @else bg-gray-700 text-gray-400
                                            @endif">
                                            @if($allTerminated && $hasDeclined)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                            @endif
                                        </div>
                                        <button class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-gray-800 border border-gray-600 flex items-center justify-center hover:bg-gray-700 transition-all z-20 parallel-dropdown-btn"
                                                data-group="{{ $groupId }}">
                                            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-center w-[160px]">
                                        <p class="text-xs font-medium text-purple-300 truncate">Parallel Review</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ $doneCount }}/{{ $group['stages']->count() }} Done
                                            @if($declinedCount > 0)
                                                · <span class="text-red-400">{{ $declinedCount }} declined</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ── LEGENDA BADGE ── --}}
        @php
            $totalIncoming = $receivedTaskCounts->sum();
            $totalSubmitted = $submittedTaskCounts->sum();
            $totalOpenSent  = $sentTaskCounts->flatten()->sum('total');
            // ✅ Hitung total declined di semua parallel groups
            $totalDeclined = 0;
            foreach ($parallelGroups as $group) {
                $totalDeclined += $group['stages']->where('status', 'declined')->count();
            }
        @endphp
        @if($totalIncoming > 0 || $totalSubmitted > 0 || $totalOpenSent > 0 || $totalDeclined > 0)
        <div class="mt-4 pt-3 border-t border-gray-700/50 flex flex-wrap gap-4 text-xs">
            @if($totalIncoming > 0)
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                <span class="text-orange-400">{{ $totalIncoming }} revisi masuk (perlu dikerjakan)</span>
            </div>
            @endif
            @if($totalSubmitted > 0)
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-purple-500 animate-pulse"></div>
                <span class="text-purple-400">{{ $totalSubmitted }} hasil menunggu keputusan</span>
            </div>
            @endif
            @if(($totalOpenSent - $totalSubmitted) > 0)
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                <span class="text-amber-400">{{ $totalOpenSent - $totalSubmitted }} revisi sedang dikerjakan</span>
            </div>
            @endif
            {{-- ✅ TAMBAH: legenda untuk declined --}}
            @if($totalDeclined > 0)
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <span class="text-red-400">{{ $totalDeclined }} dept declined.</span>
            </div>
            @endif
        </div>
        @endif

        {{-- Stats Footer --}}
        <div class="mt-3 pt-3 border-t border-gray-700/50">
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-baseline gap-2">
                    <span class="text-gray-400 text-xs">Completed:</span>
                    <span class="text-base font-semibold text-green-400">{{ $completedStages }}/{{ $totalStages }}</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-gray-400 text-xs">Progress:</span>
                    <span class="text-base font-semibold text-gray-300">{{ rtrim(rtrim(number_format($progressPercent, 1), '0'), '.') }}%</span>
                </div>
                @if($reviewerStagesCount > 0)
                <div class="flex items-baseline gap-2">
                    <span class="text-gray-400 text-xs">Reviewers:</span>
                    <span class="text-base font-semibold text-purple-400">{{ $reviewerStagesCount }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Dropdown Container --}}
<div id="parallel-dropdown-root" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 5;">
    @foreach($displayItems as $item)
        @if($item['type'] === 'parallel')
            @php $group = $item['data']; $groupId = $item['groupId']; @endphp
            <div class="parallel-dropdown hidden" data-group="{{ $groupId }}"
                 style="position: absolute; pointer-events: auto; background: rgb(17, 25, 40); border: 1px solid rgba(55, 65, 81, 0.8); border-radius: 12px; box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.5); backdrop-filter: blur(12px); min-width: 280px; max-width: 360px;">
                <div class="dropdown-arrow" style="position: absolute; width: 12px; height: 12px; background: rgb(17, 25, 40); border-top: 1px solid rgba(55, 65, 81, 0.8); border-left: 1px solid rgba(55, 65, 81, 0.8); transform: rotate(45deg);"></div>
                <div class="relative p-3">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-700">
                        <h4 class="text-sm font-semibold text-purple-300">Reviewers</h4>
                        <span class="text-xs text-gray-400">{{ $group['stages']->where('status', 'completed')->count() }}/{{ $group['stages']->count() }} Complete</span>
                    </div>
                    <div class="space-y-2 max-h-[300px] overflow-y-auto">
                        @foreach($group['stages'] as $parStage)
                        @php
                            $parReceived  = $receivedTaskCounts[$parStage->id] ?? 0;
                            $parSubmitted = $submittedTaskCounts[$parStage->id] ?? 0;
                            $isDeclined   = $parStage->status === 'declined';
                        @endphp
                        <div class="border-l-2
                            {{ $parStage->status === 'completed' ? 'border-green-500'
                               : ($parStage->isActive()           ? 'border-blue-500'
                               : ($isDeclined                     ? 'border-red-500'   {{-- ✅ FIX --}}
                               : 'border-gray-600')) }} pl-3">
                            <div class="flex items-center justify-between mb-1">
                                <p class="font-medium text-gray-200 text-sm">{{ $parStage->stage_name }}</p>
                                <div class="flex items-center gap-1">
                                    @if($parReceived > 0)
                                        <span class="text-[10px] px-1 py-0.5 rounded bg-orange-500/20 text-orange-400">{{ $parReceived }} revisi</span>
                                    @endif
                                    @if($parSubmitted > 0)
                                        <span class="text-[10px] px-1 py-0.5 rounded bg-purple-500/20 text-purple-400">{{ $parSubmitted }} submit</span>
                                    @endif
                                    {{-- ✅ FIX: Tambahkan kondisi 'declined' --}}
                                    <span class="text-xs px-1.5 py-0.5 rounded-full
                                        {{ $parStage->status === 'completed' ? 'bg-green-500/20 text-green-400'
                                           : ($parStage->isActive()           ? 'bg-blue-500/20 text-blue-400'
                                           : ($parStage->status === 'assigned' ? 'bg-yellow-500/20 text-yellow-400'
                                           : ($isDeclined                      ? 'bg-red-500/20 text-red-400'
                                           : 'bg-gray-600/20 text-gray-400'))) }}">
                                        {{ $parStage->status === 'completed' ? 'Done'
                                           : ($parStage->isActive()           ? 'Active'
                                           : ($parStage->status === 'assigned' ? 'Assigned'
                                           : ($isDeclined                      ? 'Declined'
                                           : 'Pending'))) }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-gray-300 text-sm">
                                <span class="text-gray-500">Reviewer:</span>
                                {{ $parStage->assignedUser->nama_user ?? ($isDeclined ? 'Invitation Declined' : 'Not Assigned') }}
                            </p>
                            @if($parStage->completed_at)
                            <p class="text-green-400 text-xs mt-1">Completed: {{ $parStage->completed_at->format('d M Y, H:i') }}</p>
                            @endif
                            {{-- ✅ TAMBAH: Info untuk yang declined --}}
                            @if($isDeclined)
                            <p class="text-red-400/70 text-xs mt-1 italic">Use Re-Invite in Edit Workflow</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>

<style>
.stage-scroll-wrapper { position: relative; width: 100%; }
.stage-scroll { scroll-behavior: smooth; overflow-x: auto; overflow-y: visible; position: relative; width: 100%; }
.stage-scroll::-webkit-scrollbar { height: 8px; }
.stage-scroll::-webkit-scrollbar-track { background: #1f2937; border-radius: 10px; }
.stage-scroll::-webkit-scrollbar-thumb { background: #4B5563; border-radius: 10px; }
.glass-card, .overflow-visible, .grid, .relative, .flex, .parallel-group-wrapper, .stage-scroll-wrapper, .stage-scroll { overflow: visible !important; }
.glass-card { border: none !important; }
.progress-bar-container { position: sticky; top: 0; z-index: 20; background: inherit; backdrop-filter: blur(10px); }
.animate-ping { animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; }
@keyframes ping { 75%, 100% { transform: scale(2); opacity: 0; } }
*:focus { outline: none !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtns = document.querySelectorAll('.parallel-dropdown-btn');
    let activeDropdown = null;
    let activeBtn = null;
    let rafId = null;

    function closeAllDropdowns() {
        document.querySelectorAll('.parallel-dropdown').forEach(d => d.classList.add('hidden'));
        document.querySelectorAll('.parallel-dropdown-btn svg').forEach(i => i.style.transform = 'rotate(0deg)');
        if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
        activeDropdown = null; activeBtn = null;
    }

    function positionDropdown(btn, dropdown) {
        const btnRect = btn.getBoundingClientRect();
        const dropH = dropdown.offsetHeight, dropW = dropdown.offsetWidth;
        const vH = window.innerHeight, vW = window.innerWidth;
        let top = btnRect.bottom + 8, left = btnRect.left + (btnRect.width / 2) - (dropW / 2), showAbove = false;
        if (top + dropH > vH - 20) { top = btnRect.top - dropH - 8; showAbove = true; }
        if (left < 10) left = 10;
        else if (left + dropW > vW - 10) left = vW - dropW - 10;
        dropdown.style.top = top + 'px'; dropdown.style.left = left + 'px';
        const arrow = dropdown.querySelector('.dropdown-arrow');
        if (arrow) {
            const arrowLeft = Math.min(Math.max(btnRect.left + (btnRect.width / 2) - left - 6, 10), dropW - 20);
            arrow.style.left = arrowLeft + 'px';
            if (showAbove) { arrow.style.top = 'auto'; arrow.style.bottom = '-6px'; arrow.style.transform = 'rotate(225deg)'; }
            else { arrow.style.top = '-6px'; arrow.style.bottom = 'auto'; arrow.style.transform = 'rotate(45deg)'; }
        }
    }

    function updatePos() {
        if (activeDropdown && activeBtn && !activeDropdown.classList.contains('hidden')) {
            positionDropdown(activeBtn, activeDropdown);
            rafId = requestAnimationFrame(updatePos);
        }
    }

    dropdownBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation(); e.preventDefault();
            const dropdown = document.querySelector(`.parallel-dropdown[data-group="${this.dataset.group}"]`);
            if (!dropdown) return;
            if (dropdown.classList.contains('hidden')) {
                closeAllDropdowns();
                dropdown.classList.remove('hidden');
                setTimeout(() => { positionDropdown(this, dropdown); activeDropdown = dropdown; activeBtn = this; rafId = requestAnimationFrame(updatePos); }, 10);
                const icon = this.querySelector('svg'); if (icon) icon.style.transform = 'rotate(180deg)';
            } else { closeAllDropdowns(); }
        });
    });

    window.addEventListener('resize', () => { if (activeDropdown && activeBtn) positionDropdown(activeBtn, activeDropdown); });
    document.addEventListener('click', e => { if (!e.target.closest('.parallel-dropdown-btn') && !e.target.closest('.parallel-dropdown')) closeAllDropdowns(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllDropdowns(); });
});
</script>
@endif