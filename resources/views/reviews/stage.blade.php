@php
    $activeStage     = $contract->activeStage();
    $isParallelStage = !is_null($stage->parallel_group);
    $isActiveStage = $isParallelStage
        ? in_array($stage->status, ['in_progress', 'assigned', 'revision_requested'])
        : (($activeStage && $activeStage->id === $stage->id) || ($stage->needs_revision && $stage->status === 'revision_requested'));
    $canAct = auth()->check() && (auth()->user()->hasRole('admin') || $stage->assigned_user_id === auth()->user()->id_user);
    $canExecute = $stage->stage_type === 'executing' && auth()->check() && ((int) auth()->user()->id_user === (int) $contract->user_id || auth()->user()->hasRole('admin'));
    $canArchive = $stage->stage_type === 'archiving' && auth()->check() && ((int) $stage->assigned_user_id === (int) auth()->user()->id_user || auth()->user()->hasRole('admin'));
    $isSpecialStage = in_array($stage->stage_type, ['executing', 'archiving']);
    $isContractCompleted = $contract->reviewStages->count() > 0 && $contract->reviewStages->every(fn ($s) => $s->status === 'completed');
    $isLastStage = !\App\Models\ContractReviewStage::where('contract_id', $contract->id)
        ->whereNull('parallel_group')->where('sequence', '>', $stage->sequence)
        ->whereNotIn('stage_type', ['executing', 'archiving'])
        ->whereNotIn('status', ['completed', 'rejected', 'skipped'])->exists();
    $isRevisionTarget = $stage->needs_revision === true && $stage->status === 'revision_requested';
    $isRevisionSender = $stage->status === 'revision_requested' && !$stage->needs_revision;
    $canShowGenerateBtn = $stage->stage_type === 'legal' && $stage->status === 'in_progress'
        && !$contract->contract_number && $canAct && auth()->user()->hasAnyRole(['legal', 'admin']);
    $revisionRequester = null; $revisionRequesterStage = null;
    if ($isRevisionTarget) {
        $revisionJump = \App\Models\ContractReviewJump::where('contract_id', $contract->id)
            ->where('to_stage_id', $stage->id)->whereRaw("reason LIKE 'Revision requested%'")->latest()->first();
        if ($revisionJump) {
            $revisionRequesterStage = \App\Models\ContractReviewStage::with('assignedUser')->find($revisionJump->from_stage_id);
            $revisionRequester = $revisionRequesterStage?->assignedUser;
        }
        if (!$revisionRequesterStage) {
            $revisionRequesterStage = $contract->reviewStages->where('status','revision_requested')->where('needs_revision',false)->first();
            $revisionRequester = $revisionRequesterStage?->assignedUser;
        }
    }
    $stageAccent = match($stage->stage_type) {
        'legal'      => ['color' => '#60a5fa', 'bg' => 'rgba(96,165,250,0.08)', 'border' => 'rgba(96,165,250,0.2)', 'dot' => 'bg-blue-400'],
        'finance'    => ['color' => '#34d399', 'bg' => 'rgba(52,211,153,0.08)', 'border' => 'rgba(52,211,153,0.2)', 'dot' => 'bg-emerald-400'],
        'accounting' => ['color' => '#38bdf8', 'bg' => 'rgba(56,189,248,0.08)', 'border' => 'rgba(56,189,248,0.2)', 'dot' => 'bg-sky-400'],
        'tax'        => ['color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.08)', 'border' => 'rgba(251,191,36,0.2)', 'dot' => 'bg-amber-400'],
        'executing'  => ['color' => '#818cf8', 'bg' => 'rgba(129,140,248,0.08)', 'border' => 'rgba(129,140,248,0.2)', 'dot' => 'bg-indigo-400'],
        'archiving'  => ['color' => '#fb923c', 'bg' => 'rgba(251,146,60,0.08)', 'border' => 'rgba(251,146,60,0.2)', 'dot' => 'bg-orange-400'],
        default      => ['color' => '#94a3b8', 'bg' => 'rgba(148,163,184,0.08)', 'border' => 'rgba(148,163,184,0.2)', 'dot' => 'bg-slate-400'],
    };
    $stageIcon = match($stage->stage_type) { 'legal'=>'⚖️','finance'=>'💹','accounting'=>'📊','tax'=>'🏛️','executing'=>'✍️','archiving'=>'📦',default=>'👤' };

    // Status label
    $statusLabel = match(true) {
        $stage->status === 'completed' && $stage->stage_type === 'executing' => 'Executed',
        $stage->status === 'completed' && $stage->stage_type === 'archiving' => 'Archived',
        $isRevisionSender => 'Revision Sent',
        $isRevisionTarget => 'Revision Received',
        $stage->status === 'in_progress' => 'In Progress',
        $stage->status === 'completed' => 'Completed',
        $stage->status === 'assigned' => 'Ready to Start',
        $stage->status === 'pending' => 'Pending',
        default => ucfirst(str_replace('_', ' ', $stage->status)),
    };
    $statusDot = match(true) {
        $stage->status === 'completed' => 'bg-emerald-400',
        $stage->status === 'in_progress' => 'bg-blue-400',
        $stage->status === 'assigned' => 'bg-amber-400',
        $isRevisionSender => 'bg-orange-400',
        $isRevisionTarget => 'bg-red-400',
        default => 'bg-slate-500',
    };

    if (!isset($jumpOptions)) {
        $jumpOptions = collect(); $seenPG = [];
        foreach ($contract->reviewStages->sortBy('sequence') as $s) {
            if ($s->id===$stage->id||$s->is_user_stage||$s->stage_type==='user'||in_array($s->stage_type,['executing','archiving'])||$s->sequence<=$stage->sequence) continue;
            if (!is_null($s->parallel_group)) {
                if (in_array($s->parallel_group,$seenPG)) continue;
                $seenPG[]=$s->parallel_group;
                $gc=$contract->reviewStages->where('parallel_group',$s->parallel_group)->count();
                $jumpOptions->push(['id'=>$s->id,'label'=>'Stage '.$s->sequence.' — Substantial Review ('.$gc.' reviewers)','is_parallel'=>true]);
            } else {
                $jumpOptions->push(['id'=>$s->id,'label'=>'Stage '.$s->sequence.' — '.($s->assignedUser->nama_user??'Unassigned').' — '.$s->stage_name,'is_parallel'=>false]);
            }
        }
    }
    $previewNum = null;
    if ($canShowGenerateBtn) { try { $previewNum = app(\App\Services\ContractNumberService::class)->previewNumber($contract); } catch (\Exception $e) {} }
@endphp

<x-app-layout-dark title="Stage Review — {{ $contract->title }}">
<div class="rs-root">

    {{-- ═══════════════════════════════════ HEADER ═══════════════════════════════════ --}}
    <div class="rs-header">
        <div class="rs-header-top">
            <a href="{{ route('contracts.show', $contract) }}" class="rs-back-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Contract
            </a>
            <div class="rs-meta-right">
                <span class="rs-date">{{ $contract->created_at->format('d M Y') }}</span>
            </div>
        </div>

        <div class="rs-header-body">
            <div class="rs-stage-icon" style="background:{{ $stageAccent['bg'] }};border-color:{{ $stageAccent['border'] }}">
                {{ $stageIcon }}
            </div>
            <div class="rs-header-text">
                <h1 class="rs-title" style="color:{{ $stageAccent['color'] }}">
                    {{ ucfirst(str_replace('_', ' ', $stage->stage_name)) }}
                </h1>
                <div class="rs-badges">
                    <span class="rs-badge rs-badge-status">
                        <span class="rs-dot {{ $statusDot }}"></span>
                        {{ $statusLabel }}
                    </span>
                    <span class="rs-badge" style="background:{{ $stageAccent['bg'] }};border-color:{{ $stageAccent['border'] }};color:{{ $stageAccent['color'] }}">
                        Stage {{ $stage->sequence }} of {{ $contract->reviewStages->count() }}
                    </span>
                    @if($isParallelStage)
                        <span class="rs-badge rs-badge-parallel">⚡ Substantial Review</span>
                    @endif
                    @if($contract->contract_number)
                        <span class="rs-badge rs-badge-number">{{ $contract->contract_number }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Progress --}}
        <div class="rs-progress-wrap">
            <div class="rs-progress-labels">
                <span>Review Progress</span>
                <span>{{ $contract->review_progress ?? 0 }}%</span>
            </div>
            <div class="rs-progress-track">
                <div class="rs-progress-bar" style="width:{{ $contract->review_progress ?? 0 }}%;background:{{ $stageAccent['color'] }}"></div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════ MAIN GRID ═══════════════════════════════════ --}}
    <div class="rs-grid">

        {{-- ─── LEFT: Actions Panel ─── --}}
        <div class="rs-main">

            {{-- ——————————— PANEL HEADER ——————————— --}}
            <div class="rs-panel">
                <div class="rs-panel-header" style="border-left-color:{{ $stageAccent['color'] }}">
                    <div>
                        <h2 class="rs-panel-title">
                            @if($stage->stage_type==='executing') ✍️ Executing Stage
                            @elseif($stage->stage_type==='archiving') 📦 Archiving Stage
                            @else Review Actions
                            @endif
                        </h2>
                        <p class="rs-panel-sub">
                            @if($stage->stage_type==='executing') Document signing & confirmation
                            @elseif($stage->stage_type==='archiving') Final document storage
                            @else Select an action for this review stage
                            @endif
                        </p>
                    </div>
                </div>

                {{-- ══════════ EXECUTING ══════════ --}}
                @if($stage->stage_type === 'executing')

                    @if($stage->status === 'completed')
                        <div class="rs-state-box rs-state-success">
                            <div class="rs-state-icon">✅</div>
                            <p class="rs-state-title">Document Successfully Executed</p>
                            <p class="rs-state-sub">{{ $contract->executed_at?->format('d M Y, H:i') ?? $stage->completed_at?->format('d M Y, H:i') }}</p>
                            @if($stage->notes)
                                <div class="rs-note-box rs-mt-3">
                                    <span class="rs-note-label">Notes</span>
                                    <p>{{ $stage->notes }}</p>
                                </div>
                            @endif
                        </div>

                    @elseif($stage->status === 'pending')
                        <div class="rs-state-box rs-state-neutral">
                            <div class="rs-state-icon">⏳</div>
                            <p class="rs-state-title">Waiting for Legal Review to Complete</p>
                            <p class="rs-state-sub">This stage activates after the legal reviewer approves their final stage.</p>
                        </div>

                    @elseif(in_array($stage->status, ['assigned', 'in_progress']))
                        @if($canExecute)
                            @if($stage->status === 'assigned')
                                <form action="{{ route('review-stages.start', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rs-btn rs-btn-primary rs-btn-full" style="--btn-color:{{ $stageAccent['color'] }}">
                                        ▶ Start Executing Process
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('review-stages.execute', [$contract, $stage]) }}" method="POST" onsubmit="return confirm('Mark document as executed?')">
                                    @csrf
                                    <div class="rs-form-group">
                                        <label class="rs-label">Execution Date</label>
                                        <input type="date" name="execution_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="rs-input">
                                    </div>
                                    <div class="rs-form-group">
                                        <div class="rs-label-row">
                                            <label class="rs-label">
                                                Execution Notes <span class="rs-required">*</span>
                                                <span class="rs-hint">min. 5 characters</span>
                                            </label>
                                            <div class="rs-info-wrap">
                                                <span class="rs-info-icon">i</span>
                                                <div class="rs-info-tooltip">
                                                    <strong class="rs-tooltip-title">📝 Notes Guidelines</strong>
                                                    <ul>
                                                        <li>Date and method of signing (wet / digital)</li>
                                                        <li>Any additional execution remarks</li>
                                                        <li>Can a copy be handed over to Legal?</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <textarea name="execution_notes" rows="4" required minlength="5" maxlength="2000"
                                            placeholder="E.g: Document signed by both parties on 2024-01-15. Soft copy at Synology/Legal/2024/Contract_ABC.pdf."
                                            class="rs-textarea"></textarea>
                                    </div>
                                    @error('execution_notes')<p class="rs-error">{{ $message }}</p>@enderror
                                    <button type="submit" class="rs-btn rs-btn-primary rs-btn-full" style="--btn-color:{{ $stageAccent['color'] }}">
                                        ✅ Execute Document
                                    </button>
                                </form>
                            @endif
                        @else
                            <div class="rs-assignee-row">
                                <div class="rs-avatar" style="background:{{ $stageAccent['bg'] }};color:{{ $stageAccent['color'] }}">
                                    {{ substr($stage->assignedUser?->nama_user ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="rs-assignee-name">{{ $stage->assignedUser?->nama_user ?? 'Document Owner' }}</p>
                                    <p class="rs-assignee-sub">⏳ Waiting for the document owner to complete signing</p>
                                </div>
                            </div>
                        @endif
                    @endif

                {{-- ══════════ ARCHIVING ══════════ --}}
                @elseif($stage->stage_type === 'archiving')
                    @php $executingNotes = $contract->reviewStages->where('stage_type','executing')->where('status','completed')->first()?->notes; @endphp
                    @if($executingNotes)
                        <div class="rs-note-box rs-note-indigo rs-mb-4">
                            <span class="rs-note-label">From Executing Stage</span>
                            <p>{{ $executingNotes }}</p>
                        </div>
                    @endif

                    @if($stage->status === 'completed')
                        <div class="rs-state-box rs-state-success">
                            <div class="rs-state-icon">📦</div>
                            <p class="rs-state-title">Document Successfully Archived</p>
                            <p class="rs-state-sub">{{ $contract->archived_at?->format('d M Y, H:i') ?? $stage->completed_at?->format('d M Y, H:i') }}</p>
                            @if($stage->notes)
                                <div class="rs-note-box rs-mt-3">
                                    <span class="rs-note-label">Archiving Notes</span>
                                    <p>{{ $stage->notes }}</p>
                                </div>
                            @endif
                        </div>

                    @elseif($stage->status === 'pending')
                        <div class="rs-state-box rs-state-neutral">
                            <div class="rs-state-icon">⏳</div>
                            <p class="rs-state-title">Waiting for Execution to Complete</p>
                            <p class="rs-state-sub">Archiving activates automatically after execution is finished.</p>
                            @php $executingStatus = $contract->reviewStages->where('stage_type','executing')->first(); @endphp
                            @if($executingStatus)
                                <span class="rs-badge rs-mt-3" style="display:inline-flex">
                                    Executing:
                                    <span class="rs-ml-1 @if($executingStatus->status==='in_progress') rs-text-blue @elseif($executingStatus->status==='assigned') rs-text-amber @else rs-text-muted @endif">
                                        {{ ucfirst($executingStatus->status) }}
                                    </span>
                                </span>
                            @endif
                        </div>

                    @elseif(in_array($stage->status, ['assigned', 'in_progress']))
                        @if($canArchive)
                            @if($stage->status === 'assigned')
                                <form action="{{ route('review-stages.start', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rs-btn rs-btn-primary rs-btn-full" style="--btn-color:{{ $stageAccent['color'] }}">
                                        ▶ Start Archiving Process
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('review-stages.archive', [$contract, $stage]) }}" method="POST" onsubmit="return confirm('Complete archiving? This will finalize the entire contract workflow.')">
                                    @csrf
                                    <div class="rs-form-group">
                                        <label class="rs-label">Archiving Notes <span class="rs-required">*</span> <span class="rs-hint">min. 5 characters</span></label>
                                        <textarea name="archive_notes" rows="4" required minlength="5" maxlength="2000"
                                            placeholder="E.g: Original at Shelf A-3. Soft copy at Synology/Legal/2024/..."
                                            class="rs-textarea"></textarea>
                                    </div>
                                    @error('archive_notes')<p class="rs-error">{{ $message }}</p>@enderror
                                    <div class="rs-alert rs-alert-warning rs-mb-4">
                                        ⚠️ After archiving, status changes to <strong>Archived</strong> and the workflow is complete.
                                    </div>
                                    <button type="submit" class="rs-btn rs-btn-primary rs-btn-full" style="--btn-color:{{ $stageAccent['color'] }}">
                                        📦 Complete Archiving
                                    </button>
                                </form>
                            @endif
                        @else
                            <div class="rs-assignee-row">
                                <div class="rs-avatar" style="background:{{ $stageAccent['bg'] }};color:{{ $stageAccent['color'] }}">
                                    {{ substr($stage->assignedUser?->nama_user ?? 'L', 0, 1) }}
                                </div>
                                <div>
                                    <p class="rs-assignee-name">{{ $stage->assignedUser?->nama_user ?? 'Legal Team' }}</p>
                                    <p class="rs-assignee-sub">⏳ Waiting for the legal team to complete archiving</p>
                                </div>
                            </div>
                        @endif
                    @endif

                {{-- ══════════ CONTRACT COMPLETED ══════════ --}}
                @elseif($isContractCompleted)
                    <div class="rs-state-box rs-state-success">
                        <div class="rs-state-icon">🎉</div>
                        <p class="rs-state-title">Contract Review Completed</p>
                        <p class="rs-state-sub">All stages have been completed successfully.</p>
                    </div>

                {{-- ══════════ REVISION TARGET (belum start) ══════════ --}}
                @elseif($isActiveStage && $isRevisionTarget && $stage->status === 'revision_requested' && $canAct)
                    <div class="rs-alert rs-alert-danger rs-mb-4">
                        <strong>🔔 You've received a revision request</strong>
                        @if($revisionRequester)
                            <br><span class="rs-text-muted">From <strong class="rs-text-amber">{{ $revisionRequester->nama_user }}</strong></span>
                        @endif
                        @if($stage->revision_feedback)
                            <div class="rs-note-box rs-mt-3">
                                <span class="rs-note-label">Revision Notes</span>
                                <p>{{ $stage->revision_feedback }}</p>
                            </div>
                        @endif
                    </div>
                    <form action="{{ route('review-stages.start', [$contract, $stage]) }}" method="POST">
                        @csrf
                        <button type="submit" class="rs-btn rs-btn-danger rs-btn-full">▶ Start Handling Revision</button>
                    </form>

                {{-- ══════════ NORMAL START (assigned) ══════════ --}}
                @elseif($isActiveStage && $stage->status === 'assigned' && $canAct)
                    <form action="{{ route('review-stages.start', [$contract, $stage]) }}" method="POST">
                        @csrf
                        <button type="submit" class="rs-btn rs-btn-primary rs-btn-full" style="--btn-color:{{ $stageAccent['color'] }}">
                            ▶ Start Reviewing This Stage
                        </button>
                    </form>

                {{-- ══════════ IN PROGRESS ══════════ --}}
                @elseif($isActiveStage && $stage->status === 'in_progress' && $canAct)
                <div class="rs-actions-stack">

                    {{-- ── GENERATE NUMBER BLOCK ── --}}
                    @if($canShowGenerateBtn)
                    <div class="rs-section" id="generateNumberBlock">
                        <div class="rs-section-head">
                            <span class="rs-section-tag rs-tag-purple"># Generate Document Number</span>
                        </div>
                        {{-- Preview sebelum generate --}}
                        <div id="generatePreviewRow">
                            @if($previewNum)
                                <p class="rs-preview-label">Preview</p>
                                <p class="rs-mono rs-preview-num">{{ $previewNum }}</p>
                            @endif
                            <button type="button" id="generateNumberBtn"
                                onclick="doGenerateNumber('{{ route('contracts.generate-number', $contract) }}', '{{ csrf_token() }}', '{{ addslashes($previewNum ?? '') }}')"
                                class="rs-btn rs-btn-ghost-purple rs-btn-full rs-mt-2">
                                # Generate Official Document Number
                            </button>
                        </div>
                        {{-- Success state — ditampilkan JS --}}
                        <div id="generateSuccessRow" class="rs-hidden">
                            <div class="rs-number-display">
                                <span class="rs-number-label">Document Number</span>
                                <span class="rs-number-value" id="generatedNumberDisplay">—</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ── REVISION TARGET in_progress ── --}}
                    @if($isRevisionTarget)
                        @if($stage->revision_feedback)
                            <div class="rs-note-box rs-note-danger">
                                <span class="rs-note-label">Revision Notes</span>
                                <p>{{ $stage->revision_feedback }}</p>
                            </div>
                        @endif
                        <div class="rs-section rs-section-warning">
                            <h3 class="rs-section-title">↩ Return Revision</h3>
                            @if($revisionRequesterStage)
                                <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="jump_to_stage_id" value="{{ $revisionRequesterStage->id }}">
                                    <textarea name="notes" rows="3" class="rs-textarea rs-mb-3" placeholder="Explain what has been revised..."></textarea>
                                    <button type="submit" class="rs-btn rs-btn-warning rs-btn-full"
                                        onclick="return confirm('Return to {{ addslashes($revisionRequester?->nama_user ?? 'reviewer') }}?')">
                                        ↩ Return to {{ $revisionRequester?->nama_user ?? 'Reviewer' }}
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <textarea name="notes" rows="3" class="rs-textarea rs-mb-3"></textarea>
                                    <select name="jump_to_stage_id" required class="rs-select rs-mb-3">
                                        <option value="">— Select reviewer —</option>
                                        @foreach($contract->reviewStages->where('status','revision_requested')->where('needs_revision',false) as $rs)
                                            <option value="{{ $rs->id }}">{{ $rs->assignedUser->nama_user ?? '?' }} — {{ $rs->stage_name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rs-btn rs-btn-warning rs-btn-full">↩ Return Revision</button>
                                </form>
                            @endif
                        </div>

                    {{-- ── NORMAL ACTIONS ── --}}
                    @else

                        {{-- APPROVE --}}
                        <div class="rs-section rs-section-approve">
                            @if($isLastStage)
                                <h3 class="rs-section-title rs-text-green">✓ Approve Stage</h3>
                                @if(!$contract->contract_number)
                                    <div class="rs-alert rs-alert-warning rs-mb-4">
                                        🔒 <strong>Generate Document Number First</strong><br>
                                        <span class="rs-text-muted rs-sm">This is the final review stage. Generate the official number before approving — the workflow then moves to <strong>Executing</strong>.</span>
                                    </div>
                                    <div id="approveLockedBlock">
                                        <button type="button" disabled class="rs-btn rs-btn-locked rs-btn-full">
                                            🔒 Approve Stage (Generate Number First)
                                        </button>
                                    </div>
                                    <div id="approveUnlockedBlock" class="rs-hidden">
                                        <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="is_last_stage" value="1">
                                            <textarea name="notes" rows="3" maxlength="1000" class="rs-textarea rs-mb-3" placeholder="Final notes (optional)..."></textarea>
                                            <button type="submit" class="rs-btn rs-btn-success rs-btn-full">
                                                ✓ Approve → Enable Executing Stage
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="is_last_stage" value="1">
                                        <textarea name="notes" rows="3" maxlength="1000" class="rs-textarea rs-mb-3" placeholder="Final notes (optional)..."></textarea>
                                        <button type="submit" class="rs-btn rs-btn-success rs-btn-full">
                                            ✓ Approve → Enable Executing Stage
                                        </button>
                                    </form>
                                @endif

                            @elseif($isParallelStage)
                                <h3 class="rs-section-title rs-text-green">✓ Approve Review</h3>
                                <p class="rs-section-sub">⚡ Substantial Review — does not block other reviewers</p>
                                @php $otherPendingInGroup = $contract->reviewStages->where('parallel_group',$stage->parallel_group)->where('id','!=',$stage->id)->whereNotIn('status',['completed','rejected'])->count(); @endphp
                                @if($otherPendingInGroup > 0)
                                    <p class="rs-text-muted rs-sm rs-mb-3">ℹ️ {{ $otherPendingInGroup }} other reviewer(s) still in progress.</p>
                                @else
                                    <p class="rs-text-green rs-sm rs-mb-3">🎉 You are the last reviewer in this group.</p>
                                @endif
                                <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <textarea name="notes" rows="3" maxlength="1000" class="rs-textarea rs-mb-3" placeholder="Notes (optional)..."></textarea>
                                    <button type="submit" class="rs-btn rs-btn-success rs-btn-full" onclick="return confirm('Approve this review?')">✓ Approve</button>
                                </form>

                            @else
                                <h3 class="rs-section-title rs-text-green">✓ Approve & Move Forward</h3>
                                <p class="rs-section-sub">Approve and advance to the next stage</p>
                                <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    @if($jumpOptions->isEmpty())
                                        <p class="rs-text-muted rs-sm rs-mb-3">No next stage available.</p>
                                    @else
                                        <div class="rs-form-group">
                                            <label class="rs-label">Next Stage</label>
                                            <select name="jump_to_stage_id" required class="rs-select">
                                                <option value="">— Select next stage —</option>
                                                @foreach($jumpOptions as $opt)
                                                    <option value="{{ $opt['id'] }}">@if($opt['is_parallel'])⚡ @endif{{ $opt['label'] }}</option>
                                                @endforeach
                                            </select>
                                            @if($jumpOptions->where('is_parallel', true)->isNotEmpty())
                                                <p class="rs-hint rs-mt-1">⚡ Substantial Review activates all reviewers simultaneously.</p>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="rs-form-group">
                                        <textarea name="notes" rows="3" maxlength="1000" class="rs-textarea" placeholder="Notes for the next reviewer (optional)..."></textarea>
                                    </div>
                                    <button type="submit" @if($jumpOptions->isEmpty()) disabled @endif class="rs-btn rs-btn-success rs-btn-full rs-btn-disabled">
                                        → Move to Next Stage
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- REJECT --}}
                        @if(auth()->user()->hasAnyRole(['legal', 'admin']))
                        <div class="rs-section rs-section-reject">
                            <h3 class="rs-section-title rs-text-red">✕ Reject Contract</h3>
                            <p class="rs-section-sub">If rejected, this cannot be undone.</p>
                            <form action="{{ route('review-stages.reject', [$contract, $stage]) }}" method="POST" id="rejectForm">
                                @csrf
                                <textarea name="rejection_reason" rows="3" required minlength="10" maxlength="2000"
                                    class="rs-textarea rs-mb-3" placeholder="Justification for rejection..."></textarea>
                                <button type="submit" class="rs-btn rs-btn-danger rs-btn-full">✕ Reject Contract</button>
                            </form>
                        </div>
                        @endif

                    @endif
                </div>

                @elseif($stage->status === 'completed')
                    <div class="rs-state-box rs-state-success">
                        <div class="rs-state-icon">✓</div>
                        <p class="rs-state-title">Stage Completed</p>
                        @if($stage->notes)
                            <div class="rs-note-box rs-mt-3">
                                <span class="rs-note-label">Notes</span>
                                <p>{{ $stage->notes }}</p>
                            </div>
                        @endif
                        <p class="rs-state-sub rs-mt-2">{{ $stage->completed_at?->format('d M Y, H:i') ?? '—' }}</p>
                    </div>

                @elseif(!$canAct)
                    <div class="rs-state-box rs-state-neutral">
                        <p class="rs-text-muted rs-sm">You have no actions for this stage.</p>
                    </div>
                @endif

            </div>{{-- /rs-panel --}}

            {{-- Revision Task Panel --}}
            @if(!$isSpecialStage)
                @include('reviews._revision-task-panel', [
                    'contract'=>$contract,'stage'=>$stage,
                    'sentRevisionTasks'=>$sentRevisionTasks??collect(),
                    'receivedRevisionTasks'=>$receivedRevisionTasks??collect(),
                    'revisionTargetStages'=>$revisionTargetStages??collect(),
                    'canAct'=>$canAct,'stageColor'=>$stageAccent,'isRevisionTarget'=>$isRevisionTarget,
                ])
            @endif
        </div>{{-- /rs-main --}}

        {{-- ─── RIGHT: Sidebar ─── --}}
        <div class="rs-sidebar">

            {{-- Workflow Stages --}}
            <div class="rs-panel">
                <h3 class="rs-sidebar-title">Workflow Stages</h3>
                @php
                    $sidebarItems=[]; $seenPS=[];
                    $currentStageId = $stage->id; // capture to avoid linter false-positive inside closure
                    foreach ($contract->reviewStages->sortBy('sequence') as $si) {
                        if (!is_null($si->parallel_group)) {
                            if (in_array($si->parallel_group,$seenPS)) continue;
                            $seenPS[]=$si->parallel_group;
                            $gm=$contract->reviewStages->where('parallel_group',$si->parallel_group);
                            $sidebarItems[]=['type'=>'parallel','stage'=>$si,'group_members'=>$gm,
                                'all_done'=>$gm->every(fn($m)=>$m->status==='completed'),
                                'any_active'=>$gm->contains(fn($m)=>in_array($m->status,['in_progress','assigned'])),
                                'current_in_group'=>$gm->contains(fn($m)=>$m->id===$currentStageId),
                                'done_count'=>$gm->where('status','completed')->count(),'total_count'=>$gm->count()];
                        } else { $sidebarItems[]=['type'=>'sequential','stage'=>$si]; }
                    }
                @endphp
                <div class="rs-stages-list">
                    @foreach($sidebarItems as $item)
                        @if($item['type']==='sequential')
                            @php
                                $si=$item['stage'];
                                $siIcon=match($si->stage_type){'legal'=>'⚖️','finance'=>'💹','accounting'=>'📊','tax'=>'🏛️','executing'=>'✍️','archiving'=>'📦',default=>'👤'};
                                $siRevT=$si->needs_revision&&$si->status==='revision_requested';
                                $siRevS=!$si->needs_revision&&$si->status==='revision_requested';
                                $siSP=in_array($si->stage_type,['executing','archiving'])&&$si->status==='pending';
                                $siCurrent=$si->id==$stage->id;
                            @endphp
                            <div class="rs-stage-item @if($siCurrent) rs-stage-current @elseif($si->status==='completed') rs-stage-done @elseif($siSP) rs-stage-muted @endif">
                                <span class="rs-stage-icon-sm">{{ $siIcon }}</span>
                                <div class="rs-stage-info">
                                    <p class="rs-stage-name">{{ $si->is_user_stage ? 'User Submission' : ucfirst(str_replace('_',' ',$si->stage_name)) }}</p>
                                    <p class="rs-stage-user">{{ $si->assignedUser->nama_user ?? 'Unassigned' }}</p>
                                    <p class="rs-stage-status-text @if($si->status==='completed') rs-text-green @elseif($siRevT) rs-text-red @elseif($siRevS) rs-text-amber @elseif($siCurrent) rs-text-blue @elseif($si->status==='in_progress') rs-text-blue @elseif($si->status==='assigned') rs-text-amber @else rs-text-muted @endif">
                                        @if($si->status==='completed'&&$si->stage_type==='executing') ✍️ Executed
                                        @elseif($si->status==='completed'&&$si->stage_type==='archiving') 📦 Archived
                                        @elseif($si->status==='completed') ✓ Completed
                                        @elseif($siRevT) 🔔 Revision
                                        @elseif($siRevS) 📨 Sent Revision
                                        @elseif($siCurrent) ● Current
                                        @elseif($si->status==='in_progress') In Progress
                                        @elseif($si->status==='assigned') Assigned
                                        @else Pending
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @else
                            @php $allD=$item['all_done'];$anyA=$item['any_active'];$isCur=$item['current_in_group'];$dc=$item['done_count'];$tc=$item['total_count']; @endphp
                            <div class="rs-stage-parallel @if($isCur) rs-stage-current @elseif($allD) rs-stage-done @elseif($anyA) rs-stage-active-parallel @endif">
                                <div class="rs-parallel-head">
                                    <span>⚡</span>
                                    <div>
                                        <p class="rs-stage-name rs-text-purple">Substantial Review</p>
                                        <p class="rs-stage-status-text @if($allD) rs-text-green @elseif($anyA) rs-text-purple @else rs-text-muted @endif">
                                            @if($allD) ✓ All completed ({{ $tc }}/{{ $tc }})
                                            @elseif($anyA) {{ $dc }}/{{ $tc }} completed
                                            @else Pending ({{ $tc }} reviewers)
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="rs-parallel-members">
                                    @foreach($item['group_members']->sortBy('sequence') as $gm)
                                        <div class="rs-parallel-member">
                                            <span class="rs-member-dot @if($gm->status==='completed') bg-emerald-400 @elseif(in_array($gm->status,['in_progress','assigned'])) bg-blue-400 @else bg-slate-600 @endif"></span>
                                            <span class="rs-stage-user">{{ $gm->assignedUser->nama_user ?? 'Unassigned' }}</span>
                                            @if($gm->id===$stage->id) <span class="rs-text-blue rs-sm rs-ml-auto">● You</span>
                                            @elseif($gm->status==='completed') <span class="rs-text-green rs-sm rs-ml-auto">✓</span>
                                            @elseif($gm->status==='in_progress') <span class="rs-text-blue rs-sm rs-ml-auto">Active</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Review History --}}
            <div class="rs-panel">
                <h3 class="rs-sidebar-title">Review History</h3>
                @if($reviewLogs->isEmpty())
                    <p class="rs-text-muted rs-sm rs-text-center">No history yet.</p>
                @else
                    <div class="rs-history-list">
                        @foreach($reviewLogs as $log)
                            <div class="rs-log-item">
                                <p class="rs-log-actor">
                                    {{ $log->user->nama_user ?? 'System' }}
                                    <span class="rs-text-muted rs-fw-normal"> — {{ ucfirst(str_replace('_',' ',$log->action)) }}</span>
                                </p>
                                @if($log->notes)
                                    <p class="rs-log-notes">{{ $log->notes }}</p>
                                @endif
                                <p class="rs-log-date">{{ $log->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>{{-- /rs-sidebar --}}
    </div>{{-- /rs-grid --}}
</div>{{-- /rs-root --}}

<style>
/* ═══════════════════════════════════════════
   ROOT & LAYOUT
═══════════════════════════════════════════ */
.rs-root {
    max-width: 1280px;
    margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
    font-family: 'DM Sans', 'Inter', sans-serif;
    color: #cbd5e1;
}

/* ═══ HEADER ═══ */
.rs-header {
    margin-bottom: 2rem;
}
.rs-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}
.rs-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    color: #94a3b8;
    font-size: 0.8125rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.rs-back-btn:hover { background: rgba(255,255,255,0.06); color: #cbd5e1; }
.rs-date { font-size: 0.75rem; color: #475569; }

.rs-header-body {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.rs-stage-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    border: 1px solid;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.rs-header-text { flex: 1; }
.rs-title {
    font-size: 1.75rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0 0 0.5rem;
    line-height: 1.2;
}

/* ═══ BADGES ═══ */
.rs-badges { display: flex; flex-wrap: wrap; gap: 0.375rem; }
.rs-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.04);
    color: #94a3b8;
}
.rs-badge-status { color: #cbd5e1; }
.rs-badge-parallel { background: rgba(168,85,247,0.08); border-color: rgba(168,85,247,0.2); color: #c084fc; }
.rs-badge-number { background: rgba(52,211,153,0.08); border-color: rgba(52,211,153,0.25); color: #6ee7b7; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 0.7rem; letter-spacing: 0.04em; }
.rs-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ═══ PROGRESS ═══ */
.rs-progress-wrap {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 10px;
    padding: 0.875rem 1.125rem;
}
.rs-progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 0.5rem;
}
.rs-progress-track {
    height: 4px;
    background: rgba(255,255,255,0.06);
    border-radius: 999px;
    overflow: hidden;
}
.rs-progress-bar {
    height: 100%;
    border-radius: 999px;
    transition: width 0.5s ease;
}

/* ═══ MAIN GRID ═══ */
.rs-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1024px) {
    .rs-grid { grid-template-columns: 1fr; }
}

/* ═══ PANEL ═══ */
.rs-panel {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
}
.rs-panel:last-child { margin-bottom: 0; }
.rs-panel-header {
    border-left: 3px solid;
    padding-left: 0.875rem;
    margin-bottom: 1.5rem;
}
.rs-panel-title {
    font-size: 1rem;
    font-weight: 600;
    color: #e2e8f0;
    margin: 0 0 0.25rem;
}
.rs-panel-sub { font-size: 0.8125rem; color: #64748b; margin: 0; }

/* ═══ SIDEBAR ═══ */
.rs-sidebar-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin: 0 0 1rem;
}

/* ═══ STAGE LIST ═══ */
.rs-stages-list { display: flex; flex-direction: column; gap: 0.375rem; }
.rs-stage-item {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    padding: 0.625rem 0.75rem;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.05);
    background: rgba(255,255,255,0.02);
    transition: background 0.15s;
}
.rs-stage-item.rs-stage-current {
    border-color: rgba(96,165,250,0.3);
    background: rgba(96,165,250,0.06);
}
.rs-stage-item.rs-stage-done {
    border-color: rgba(52,211,153,0.15);
    background: rgba(52,211,153,0.03);
}
.rs-stage-item.rs-stage-muted { opacity: 0.35; }
.rs-stage-icon-sm { font-size: 0.875rem; margin-top: 1px; flex-shrink: 0; }
.rs-stage-info { flex: 1; min-width: 0; }
.rs-stage-name { font-size: 0.8125rem; font-weight: 500; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }
.rs-stage-user { font-size: 0.75rem; color: #64748b; margin: 0.125rem 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rs-stage-status-text { font-size: 0.7rem; margin: 0.2rem 0 0; }

.rs-stage-parallel {
    border-radius: 8px;
    border: 1px solid rgba(168,85,247,0.12);
    background: rgba(168,85,247,0.04);
    overflow: hidden;
}
.rs-stage-parallel.rs-stage-current { border-color: rgba(168,85,247,0.3); background: rgba(168,85,247,0.08); }
.rs-stage-parallel.rs-stage-done { border-color: rgba(52,211,153,0.15); background: rgba(52,211,153,0.03); }
.rs-stage-parallel.rs-stage-active-parallel { border-color: rgba(168,85,247,0.2); }
.rs-parallel-head {
    display: flex;
    gap: 0.625rem;
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
}
.rs-parallel-members {
    border-top: 1px solid rgba(255,255,255,0.05);
    padding: 0.5rem 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}
.rs-parallel-member {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.rs-member-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

/* ═══ HISTORY ═══ */
.rs-history-list {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    max-height: 380px;
    overflow-y: auto;
    padding-right: 2px;
}
.rs-log-item {
    padding: 0.75rem;
    border-radius: 8px;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.05);
}
.rs-log-actor { font-size: 0.8125rem; font-weight: 600; color: #e2e8f0; margin: 0 0 0.25rem; }
.rs-log-notes { font-size: 0.8rem; color: #94a3b8; margin: 0.25rem 0; }
.rs-log-date { font-size: 0.7rem; color: #475569; margin: 0.25rem 0 0; }

/* ═══ FORMS ═══ */
.rs-form-group { margin-bottom: 1rem; }
.rs-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 500;
    color: #94a3b8;
    margin-bottom: 0.5rem;
}
.rs-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}
.rs-label-row .rs-label { margin-bottom: 0; }
.rs-input, .rs-textarea, .rs-select {
    width: 100%;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 8px;
    padding: 0.625rem 0.875rem;
    color: #cbd5e1;
    font-size: 0.875rem;
    outline: none;
    transition: border-color 0.15s;
    font-family: inherit;
    box-sizing: border-box;
}
.rs-input:focus, .rs-textarea:focus, .rs-select:focus {
    border-color: rgba(255,255,255,0.2);
}
.rs-textarea { resize: none; }
input[type="date"] { color-scheme: dark; }
.rs-required { color: #f87171; }
.rs-hint { font-size: 0.7rem; color: #475569; font-weight: 400; }
.rs-error { font-size: 0.8rem; color: #f87171; margin: 0.25rem 0 0.5rem; }

/* ═══ BUTTONS ═══ */
.rs-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity 0.15s, background 0.15s;
    text-decoration: none;
}
.rs-btn-full { width: 100%; }
.rs-btn-primary {
    background: color-mix(in srgb, var(--btn-color, #60a5fa) 18%, transparent);
    border-color: color-mix(in srgb, var(--btn-color, #60a5fa) 35%, transparent);
    color: var(--btn-color, #60a5fa);
}
.rs-btn-primary:hover { background: color-mix(in srgb, var(--btn-color, #60a5fa) 26%, transparent); }
.rs-btn-success {
    background: rgba(52,211,153,0.12);
    border-color: rgba(52,211,153,0.25);
    color: #34d399;
}
.rs-btn-success:hover { background: rgba(52,211,153,0.2); }
.rs-btn-danger {
    background: rgba(248,113,113,0.1);
    border-color: rgba(248,113,113,0.2);
    color: #f87171;
}
.rs-btn-danger:hover { background: rgba(248,113,113,0.18); }
.rs-btn-warning {
    background: rgba(251,191,36,0.1);
    border-color: rgba(251,191,36,0.2);
    color: #fbbf24;
}
.rs-btn-warning:hover { background: rgba(251,191,36,0.18); }
.rs-btn-ghost-purple {
    background: rgba(168,85,247,0.08);
    border-color: rgba(168,85,247,0.2);
    color: #c084fc;
}
.rs-btn-ghost-purple:hover { background: rgba(168,85,247,0.15); }
.rs-btn-locked {
    background: rgba(255,255,255,0.03);
    border-color: rgba(255,255,255,0.06);
    color: #475569;
    cursor: not-allowed;
}
.rs-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ═══ SECTIONS (inner groupings) ═══ */
.rs-actions-stack { display: flex; flex-direction: column; gap: 1.25rem; }
.rs-section {
    border-radius: 10px;
    padding: 1.125rem;
    border: 1px solid rgba(255,255,255,0.07);
    background: rgba(255,255,255,0.015);
}
.rs-section-approve { border-left: 2px solid #34d399; }
.rs-section-reject { border-left: 2px solid #f87171; }
.rs-section-warning { border-left: 2px solid #fbbf24; }
.rs-section-title { font-size: 0.9375rem; font-weight: 600; margin: 0 0 0.25rem; }
.rs-section-sub { font-size: 0.8rem; color: #64748b; margin: 0 0 0.875rem; }
.rs-section-head { margin-bottom: 0.875rem; }
.rs-section-tag {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.rs-tag-purple { background: rgba(168,85,247,0.1); color: #c084fc; border: 1px solid rgba(168,85,247,0.2); }

/* ═══ NUMBER DISPLAY ═══ */
.rs-number-display {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0.875rem 1rem;
    background: rgba(52,211,153,0.05);
    border: 1px solid rgba(52,211,153,0.18);
    border-radius: 10px;
}
.rs-number-label { font-size: 0.7rem; font-weight: 600; color: #34d399; text-transform: uppercase; letter-spacing: 0.06em; }
.rs-number-value {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 1rem;
    font-weight: 700;
    color: #6ee7b7;
    letter-spacing: 0.04em;
}
.rs-preview-label { font-size: 0.7rem; color: #64748b; margin: 0 0 0.125rem; }
.rs-preview-num {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 0.875rem;
    color: #a78bfa;
    margin: 0 0 0.5rem;
}

/* ═══ STATE BOXES ═══ */
.rs-state-box {
    border-radius: 10px;
    padding: 2rem 1.5rem;
    text-align: center;
    border: 1px solid;
}
.rs-state-success {
    background: rgba(52,211,153,0.04);
    border-color: rgba(52,211,153,0.2);
}
.rs-state-neutral {
    background: rgba(255,255,255,0.02);
    border-color: rgba(255,255,255,0.07);
}
.rs-state-icon { font-size: 2rem; margin-bottom: 0.75rem; }
.rs-state-title { font-size: 1rem; font-weight: 600; color: #e2e8f0; margin: 0 0 0.25rem; }
.rs-state-sub { font-size: 0.8125rem; color: #64748b; margin: 0; }

/* ═══ NOTE BOXES ═══ */
.rs-note-box {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 0.75rem 0.875rem;
}
.rs-note-box p { font-size: 0.8125rem; color: #94a3b8; margin: 0; line-height: 1.5; }
.rs-note-label { display: block; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.25rem; }
.rs-note-indigo { background: rgba(99,102,241,0.05); border-color: rgba(99,102,241,0.15); }
.rs-note-indigo .rs-note-label { color: #818cf8; }
.rs-note-danger { background: rgba(248,113,113,0.05); border-color: rgba(248,113,113,0.15); }
.rs-note-danger .rs-note-label { color: #f87171; }

/* ═══ ALERTS ═══ */
.rs-alert {
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.8125rem;
    line-height: 1.5;
    border: 1px solid;
}
.rs-alert-warning { background: rgba(251,191,36,0.06); border-color: rgba(251,191,36,0.2); color: #fbbf24; }
.rs-alert-danger { background: rgba(248,113,113,0.06); border-color: rgba(248,113,113,0.2); color: #f87171; }

/* ═══ ASSIGNEE ROW ═══ */
.rs-assignee-row {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1rem;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 10px;
}
.rs-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
    flex-shrink: 0;
}
.rs-assignee-name { font-size: 0.875rem; font-weight: 500; color: #e2e8f0; margin: 0 0 0.125rem; }
.rs-assignee-sub { font-size: 0.75rem; color: #64748b; margin: 0; }

/* ═══ INFO TOOLTIP ═══ */
.rs-info-wrap { position: relative; display: inline-flex; cursor: help; }
.rs-info-icon {
    width: 18px; height: 18px;
    border-radius: 50%;
    background: rgba(96,165,250,0.12);
    border: 1px solid rgba(96,165,250,0.3);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    color: #60a5fa;
    font-style: normal;
    transition: background 0.15s;
}
.rs-info-wrap:hover .rs-info-icon { background: rgba(96,165,250,0.22); }
.rs-info-tooltip {
    visibility: hidden; opacity: 0;
    position: absolute;
    bottom: calc(100% + 8px);
    right: 0;
    width: 260px;
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    line-height: 1.5;
    color: #94a3b8;
    pointer-events: none;
    z-index: 50;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    transition: opacity 0.15s;
}
.rs-info-tooltip .rs-tooltip-title { display: block; color: #60a5fa; font-weight: 600; margin-bottom: 0.5rem; }
.rs-info-tooltip ul { margin: 0; padding-left: 1rem; }
.rs-info-tooltip li { margin-bottom: 0.25rem; }
.rs-info-wrap:hover .rs-info-tooltip { visibility: visible; opacity: 1; }

/* ═══ UTILITIES ═══ */
.rs-hidden { display: none !important; }
.rs-mt-2 { margin-top: 0.5rem; }
.rs-mt-3 { margin-top: 0.75rem; }
.rs-mb-3 { margin-bottom: 0.75rem; }
.rs-mb-4 { margin-bottom: 1rem; }
.rs-ml-1 { margin-left: 0.25rem; }
.rs-ml-auto { margin-left: auto; }
.rs-sm { font-size: 0.8rem; }
.rs-mono { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
.rs-fw-normal { font-weight: 400; }
.rs-text-center { text-align: center; }
.rs-text-green { color: #34d399; }
.rs-text-red { color: #f87171; }
.rs-text-blue { color: #60a5fa; }
.rs-text-amber { color: #fbbf24; }
.rs-text-purple { color: #c084fc; }
.rs-text-muted { color: #64748b; }

/* Scrollbar */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Reject form validation
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function (e) {
            const r = document.querySelector('textarea[name="rejection_reason"]');
            if (!r?.value.trim() || r.value.trim().length < 10) {
                e.preventDefault();
                alert('Rejection reason must be at least 10 characters.');
                return;
            }
            if (!confirm('Reject this contract? This cannot be undone.')) e.preventDefault();
        });
    }

    // Revision form validation
    const revNotes = document.querySelector('textarea[name="revision_notes"]');
    const revForm  = document.getElementById('revisionForm');
    if (revForm) {
        revForm.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('input[name="jump_to_stage_ids[]"]:checked');
            if (checked.length === 0) { e.preventDefault(); alert('Select at least 1 reviewer!'); return; }
            if (!revNotes?.value.trim() || revNotes.value.trim().length < 10) { e.preventDefault(); alert('Revision notes must be at least 10 characters.'); return; }
            const btn = document.getElementById('submitRevisionBtn');
            if (btn) { btn.disabled = true; btn.textContent = 'Sending...'; }
        });
    }

    // Auto-grow textareas
    document.querySelectorAll('textarea').forEach(ta => {
        ta.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight + 2, 250) + 'px';
        });
        setTimeout(() => ta.dispatchEvent(new Event('input')), 100);
    });
});

// AJAX Generate Number
async function doGenerateNumber(url, token, previewNum) {
    const label = previewNum || 'this number';
    if (!confirm('Generate number ' + label + '?\nThis cannot be changed after creation.')) return;

    const btn          = document.getElementById('generateNumberBtn');
    const previewRow   = document.getElementById('generatePreviewRow');
    const successRow   = document.getElementById('generateSuccessRow');
    const numDisplay   = document.getElementById('generatedNumberDisplay');
    const nab          = document.getElementById('numberAlreadyBlock');
    const lockedBlock  = document.getElementById('approveLockedBlock');
    const unlockBlock  = document.getElementById('approveUnlockedBlock');

    if (btn) { btn.disabled = true; btn.textContent = 'Generating…'; }

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept':       'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
        });

        let data;
        try { data = await res.json(); }
        catch (pe) { throw new Error('Server returned non-JSON (HTTP ' + res.status + '). Try refreshing (CSRF may have expired).'); }

        if (data.success) {
            // Hide preview row, show success row with the actual number
            if (previewRow)  previewRow.classList.add('rs-hidden');
            if (successRow)  successRow.classList.remove('rs-hidden');
            if (numDisplay)  numDisplay.textContent = data.contract_number;

            // Show number display block (the single source of truth)
            if (nab) {
                const nt = nab.querySelector('[data-number-text]');
                if (nt) nt.textContent = data.contract_number;
                nab.classList.remove('rs-hidden');
            }

            // Unlock approve button
            if (lockedBlock) lockedBlock.classList.add('rs-hidden');
            if (unlockBlock) unlockBlock.classList.remove('rs-hidden');

        } else {
            resetBtn(btn);
            alert(data.message || 'Failed to generate number. Please try again.');
        }
    } catch (err) {
        console.error(err);
        resetBtn(btn);
        alert('Error: ' + err.message);
    }
}

function resetBtn(btn) {
    if (!btn) return;
    btn.disabled = false;
    btn.textContent = '# Generate Official Document Number';
}
</script>
</x-app-layout-dark>