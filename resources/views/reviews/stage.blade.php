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
    $stageColors = [
        'legal'      => ['bg'=>'bg-blue-50/5','text'=>'text-blue-300','border'=>'border-blue-500/20','gradient'=>'from-blue-500/40 to-cyan-500/40'],
        'finance'    => ['bg'=>'bg-emerald-50/5','text'=>'text-emerald-300','border'=>'border-emerald-500/20','gradient'=>'from-emerald-500/40 to-teal-500/40'],
        'accounting' => ['bg'=>'bg-sky-50/5','text'=>'text-sky-300','border'=>'border-sky-500/20','gradient'=>'from-sky-500/40 to-blue-500/40'],
        'tax'        => ['bg'=>'bg-amber-50/5','text'=>'text-amber-300','border'=>'border-amber-500/20','gradient'=>'from-amber-500/40 to-orange-500/40'],
        'user'       => ['bg'=>'bg-gray-50/5','text'=>'text-gray-300','border'=>'border-gray-500/20','gradient'=>'from-gray-500/40 to-gray-400/40'],
        'executing'  => ['bg'=>'bg-indigo-50/5','text'=>'text-indigo-300','border'=>'border-indigo-500/20','gradient'=>'from-indigo-500/40 to-blue-500/40'],
        'archiving'  => ['bg'=>'bg-amber-50/5','text'=>'text-amber-300','border'=>'border-amber-500/20','gradient'=>'from-amber-500/40 to-orange-500/40'],
    ];
    $stageColor = $stageColors[$stage->stage_type] ?? $stageColors['user'];
    $stageIcon  = match($stage->stage_type) { 'legal'=>'⚖️','finance'=>'💹','accounting'=>'📊','tax'=>'🏛️','executing'=>'✍️','archiving'=>'📦',default=>'👤' };
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

<x-app-layout-dark title="Stage Review - {{ $contract->title }}">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- HEADER --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('contracts.show', $contract) }}"
                   class="group p-2.5 rounded-xl border border-gray-700/50 bg-gray-800/50 hover:bg-gray-800/70 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-300">Back to Contract</span>
                </a>
            </div>
            <div class="flex items-start gap-4 mb-4">
                <div class="p-4 rounded-2xl {{ $stageColor['bg'] }} border {{ $stageColor['border'] }} shadow-lg">
                    <div class="text-2xl">{{ $stageIcon }}</div>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold {{ $stageColor['text'] }} mb-2">{{ ucfirst(str_replace('_', ' ', $stage->stage_name)) }}</h1>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-800/70 border border-gray-700/50">
                            <div class="w-2 h-2 rounded-full @if($stage->status==='in_progress') bg-blue-500 @elseif($stage->status==='completed') bg-green-500 @elseif($stage->status==='assigned') bg-yellow-500 @elseif($isRevisionSender) bg-orange-500 @elseif($isRevisionTarget) bg-red-400 @else bg-gray-500 @endif"></div>
                            <span class="text-sm font-medium text-gray-300">
                                @if($stage->status==='completed'&&$stage->stage_type==='executing') Executed
                                @elseif($stage->status==='completed'&&$stage->stage_type==='archiving') Archived
                                @elseif($isRevisionSender) Revision Sent
                                @elseif($isRevisionTarget) Revision Received
                                @elseif($stage->status==='in_progress') In Progress
                                @elseif($stage->status==='completed') Completed
                                @elseif($stage->status==='assigned') Ready to Start
                                @elseif($stage->status==='pending') Pending
                                @else {{ ucfirst(str_replace('_',' ',$stage->status)) }}
                                @endif
                            </span>
                        </div>
                        <div class="px-3 py-1.5 rounded-full {{ $stageColor['bg'] }} border {{ $stageColor['border'] }}">
                            <span class="text-sm font-medium {{ $stageColor['text'] }}">Stage {{ $stage->sequence }} of {{ $contract->reviewStages->count() }}</span>
                        </div>
                        @if($isParallelStage)
                            <div class="px-3 py-1.5 rounded-full bg-purple-500/10 border border-purple-500/20">
                                <span class="text-sm font-medium text-purple-300">&#9889; Substantial Review</span>
                            </div>
                        @endif
                        @if($contract->contract_number)
                            <div class="px-3 py-1.5 rounded-full bg-green-500/10 border border-green-500/20">
                                <span class="text-xs font-mono text-green-300">{{ $contract->contract_number }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right">
            <div class="text-sm text-gray-400">Document #</div>
            <div class="font-mono text-gray-300 text-lg">{{ $contract->contract_number ?? '&mdash;' }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $contract->created_at->format('M d, Y') }}</div>
        </div>
    </div>

    {{-- PROGRESS BAR --}}
    <div class="glass-card rounded-2xl p-4 mb-6">
        <div class="flex justify-between text-sm text-gray-400 mb-2">
            <span>Review Progress</span>
            <span>{{ $contract->review_progress ?? 0 }}%</span>
        </div>
        <div class="h-1.5 bg-gray-800/50 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r {{ $stageColor['gradient'] }} transition-all duration-500" style="width: {{ $contract->review_progress ?? 0 }}%"></div>
        </div>
    </div>

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Actions --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="gradient-border rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-300">
                            @if($stage->stage_type==='executing') &#9997;&#65039; Executing Stage
                            @elseif($stage->stage_type==='archiving') &#128230; Archiving Stage
                            @else Review Actions
                            @endif
                        </h2>
                        <p class="text-gray-400 text-sm">
                            @if($stage->stage_type==='executing') Signing &amp; confirmation of documents
                            @elseif($stage->stage_type==='archiving') Storage of final documents
                            @else Select an action for this stage
                            @endif
                        </p>
                    </div>
                </div>

                {{-- EXECUTING --}}
                @if($stage->stage_type === 'executing')
                    <div class="mb-5 p-4 rounded-xl border border-indigo-500/30 bg-indigo-500/5">
                        <p class="text-xs text-gray-400 mb-1">Official Document Number</p>
                        <p class="text-2xl font-mono font-bold text-indigo-300">{{ $contract->contract_number }}</p>
                    </div>
                    @if($stage->status === 'completed')
                        <div class="p-5 rounded-xl border border-green-500/30 bg-green-500/5 text-center">
                            <div class="text-4xl mb-3">&#9989;</div>
                            <p class="text-green-400 font-semibold text-lg">Document Successfully Executed</p>
                            <p class="text-gray-400 text-sm mt-2">{{ $contract->executed_at?->format('d M Y, H:i') ?? $stage->completed_at?->format('d M Y, H:i') }}</p>
                            @if($stage->notes)<div class="mt-4 p-3 bg-gray-800/30 rounded-lg text-left"><p class="text-xs text-gray-500 mb-1">Notes:</p><p class="text-sm text-gray-300">{{ $stage->notes }}</p></div>@endif
                        </div>
                    @elseif($stage->status === 'pending')
                        <div class="p-5 rounded-xl border border-gray-700/30 bg-gray-800/20 text-center">
                            <div class="text-4xl mb-3">&#9203;</div>
                            <p class="text-gray-400 font-medium">Waiting for Legal to Complete Review</p>
                            <p class="text-gray-500 text-sm mt-2">This stage will be active after the legal reviewer approves their last stage.</p>
                        </div>
                    @elseif(in_array($stage->status, ['assigned', 'in_progress']))
                        @if($canExecute)
                            @if($stage->status === 'assigned')
                                <form action="{{ route('review-stages.start', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-500/80 to-blue-500/80 hover:from-indigo-500 hover:to-blue-500 rounded-xl font-semibold text-white transition-all mb-4">&#9654; Start the Executing Process</button>
                                </form>
                            @else
                                <form action="{{ route('review-stages.execute', [$contract, $stage]) }}" method="POST" onsubmit="return confirm('Mark document as executed?')">
                                    @csrf
                                    <div class="space-y-4 mb-5">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Execution Date</label>
                                            <input type="date" name="execution_date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-2.5 text-gray-300 focus:ring-1 focus:ring-indigo-500/30 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Execution Notes <span class="text-red-400">*</span> <span class="text-gray-500 text-xs">(min 5 characters)</span></label>
                                            <textarea name="execution_notes" rows="4" required minlength="5" maxlength="2000" placeholder="Example: Document signed by both parties on ... Soft copy in Synology/..." class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-indigo-500/30 focus:outline-none resize-none"></textarea>
                                        </div>
                                    </div>
                                    @error('execution_notes')<p class="text-sm text-red-400 mb-3">{{ $message }}</p>@enderror
                                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-500/90 to-blue-500/90 hover:from-indigo-500 hover:to-blue-500 rounded-xl font-semibold text-white transition-all">&#9989; Execute Document</button>
                                </form>
                            @endif
                        @else
                            <div class="p-4 rounded-xl border border-gray-700/30 bg-gray-800/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-500/10 flex items-center justify-center text-lg font-bold text-indigo-400">{{ substr($stage->assignedUser?->nama_user ?? 'U', 0, 1) }}</div>
                                    <div><p class="text-sm font-medium text-gray-300">{{ $stage->assignedUser?->nama_user ?? 'Document Owner' }}</p><p class="text-xs text-gray-500">&#9203; Waiting for the document owner to complete the signature</p></div>
                                </div>
                            </div>
                        @endif
                    @endif

                {{-- ARCHIVING --}}
                @elseif($stage->stage_type === 'archiving')
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <div class="p-3 bg-gray-800/30 rounded-xl border border-gray-700/30"><p class="text-xs text-gray-500 mb-1">Document Number</p><p class="font-mono text-indigo-300 text-sm font-semibold">{{ $contract->contract_number }}</p></div>
                        <div class="p-3 bg-gray-800/30 rounded-xl border border-gray-700/30"><p class="text-xs text-gray-500 mb-1">Execution Date</p><p class="text-gray-300 text-sm">{{ $contract->executed_at?->format('d M Y') ?? '&mdash;' }}</p></div>
                    </div>
                    @php $executingNotes = $contract->reviewStages->where('stage_type','executing')->where('status','completed')->first()?->notes; @endphp
                    @if($executingNotes)
                        <div class="mb-5 p-3 bg-indigo-500/5 border border-indigo-500/20 rounded-xl"><p class="text-xs text-indigo-400 mb-1">Notes from execution stage:</p><p class="text-sm text-gray-300">{{ $executingNotes }}</p></div>
                    @endif
                    @if($stage->status === 'completed')
                        <div class="p-5 rounded-xl border border-green-500/30 bg-green-500/5 text-center">
                            <div class="text-4xl mb-3">&#128230;</div>
                            <p class="text-green-400 font-semibold text-lg">Document Successfully Archived</p>
                            <p class="text-gray-400 text-sm mt-2">{{ $contract->archived_at?->format('d M Y, H:i') ?? $stage->completed_at?->format('d M Y, H:i') }}</p>
                            @if($stage->notes)<div class="mt-4 p-3 bg-gray-800/30 rounded-lg text-left"><p class="text-xs text-gray-500 mb-1">Archiving Notes:</p><p class="text-sm text-gray-300">{{ $stage->notes }}</p></div>@endif
                        </div>
                    @elseif($stage->status === 'pending')
                        <div class="p-5 rounded-xl border border-gray-700/30 bg-gray-800/20 text-center">
                            <div class="text-4xl mb-3">&#9203;</div>
                            <p class="text-amber-400 font-medium">Waiting for the Execution Process to Complete</p>
                            <p class="text-gray-500 text-sm mt-2">Archiving will activate automatically after execution is complete.</p>
                            @php $executingStatus = $contract->reviewStages->where('stage_type','executing')->first(); @endphp
                            @if($executingStatus)
                                <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-800 border border-gray-700 text-xs text-gray-400">
                                    <span>Executing:</span>
                                    <span class="@if($executingStatus->status==='in_progress') text-blue-400 @elseif($executingStatus->status==='assigned') text-yellow-400 @else text-gray-500 @endif font-medium">{{ ucfirst($executingStatus->status) }}</span>
                                </div>
                            @endif
                        </div>
                    @elseif(in_array($stage->status, ['assigned', 'in_progress']))
                        @if($canArchive)
                            @if($stage->status === 'assigned')
                                <form action="{{ route('review-stages.start', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-amber-500/80 to-orange-500/80 hover:from-amber-500 hover:to-orange-500 rounded-xl font-semibold text-white transition-all mb-4">&#9654; Start Archiving Process</button>
                                </form>
                            @else
                                <form action="{{ route('review-stages.archive', [$contract, $stage]) }}" method="POST" onsubmit="return confirm('Complete archiving? This will finalize the entire contract workflow.')">
                                    @csrf
                                    <div class="space-y-4 mb-5">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Archiving Notes <span class="text-red-400">*</span> <span class="text-gray-500 text-xs">(min 5 characters)</span></label>
                                            <textarea name="archive_notes" rows="4" required minlength="5" maxlength="2000" placeholder="Example: Original in Shelf A-3. Soft copy in Synology/Legal/2024/..." class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-amber-500/30 focus:outline-none resize-none"></textarea>
                                        </div>
                                    </div>
                                    @error('archive_notes')<p class="text-sm text-red-400 mb-3">{{ $message }}</p>@enderror
                                    <div class="p-3 mb-4 rounded-lg bg-amber-500/5 border border-amber-500/20"><p class="text-xs text-amber-400">&#9888;&#65039; After archiving, status changes to <strong>Archived</strong> and workflow is complete.</p></div>
                                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-amber-500/90 to-orange-500/90 hover:from-amber-500 hover:to-orange-500 rounded-xl font-semibold text-white transition-all">&#128230; Complete Archiving</button>
                                </form>
                            @endif
                        @else
                            <div class="p-4 rounded-xl border border-gray-700/30 bg-gray-800/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center text-lg font-bold text-amber-400">{{ substr($stage->assignedUser?->nama_user ?? 'L', 0, 1) }}</div>
                                    <div><p class="text-sm font-medium text-gray-300">{{ $stage->assignedUser?->nama_user ?? 'Legal Team' }}</p><p class="text-xs text-gray-500">&#9203; Waiting for the legal team to complete archiving</p></div>
                                </div>
                            </div>
                        @endif
                    @endif

                {{-- CONTRACT COMPLETED --}}
                @elseif($isContractCompleted)
                    <div class="p-6 rounded-2xl border border-green-500/30 bg-green-500/5 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-500/10 flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-green-400 mb-2">Contract Review Completed</h2>
                        <p class="text-green-300/80 text-sm">All stages have been completed.</p>
                    </div>

                {{-- REVISION TARGET belum start --}}
                @elseif($isActiveStage && $isRevisionTarget && $stage->status === 'revision_requested' && $canAct)
                    <div class="mb-4 p-4 rounded-xl border border-red-500/30 bg-red-500/5">
                        <div class="flex items-start gap-3">
                            <div class="text-2xl">&#128276;</div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-red-400 mb-1">You've received a revision request</h3>
                                @if($revisionRequester)<p class="text-sm text-gray-400">From <strong class="text-amber-300">{{ $revisionRequester->nama_user }}</strong></p>@endif
                                @if($stage->revision_feedback)<div class="mt-2 p-3 bg-red-500/5 border border-red-500/20 rounded-lg"><p class="text-xs font-medium text-red-400 mb-1">Revision Notes:</p><p class="text-sm text-gray-300">{{ $stage->revision_feedback }}</p></div>@endif
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('review-stages.start', [$contract, $stage]) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-red-500/80 to-orange-500/80 hover:from-red-500 hover:to-orange-500 rounded-xl font-semibold text-white transition-all">&#9654; Start Handling Revision</button>
                    </form>

                {{-- NORMAL START assigned --}}
                @elseif($isActiveStage && $stage->status === 'assigned' && $canAct)
                    <form action="{{ route('review-stages.start', [$contract, $stage]) }}" method="POST" class="mb-6">
                        @csrf
                        <button type="submit" class="w-full py-4 bg-gradient-to-r {{ $stageColor['gradient'] }} hover:opacity-95 rounded-xl font-semibold text-white transition-all">&#9654; Start Reviewing This Stage</button>
                    </form>

                {{-- IN PROGRESS --}}
                @elseif($isActiveStage && $stage->status === 'in_progress' && $canAct)
                <div class="space-y-6">

                    {{-- GENERATE NUMBER --}}
                    @if($canShowGenerateBtn)
                    <div class="glass-card rounded-xl p-5 border border-purple-500/30 bg-purple-500/5" id="generateNumberBlock">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-lg bg-purple-500/10">
                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-purple-400">Generate Document Number</h3>
                                <p class="text-xs text-gray-400">This stage will remain active after the number is generated.</p>
                            </div>
                        </div>
                        @if($previewNum)
                            <div class="mb-3 px-3 py-2 rounded-lg bg-gray-800/50 border border-gray-700/30">
                                <p class="text-xs text-gray-500 mb-0.5">Preview:</p>
                                <p class="font-mono text-purple-300 font-semibold">{{ $previewNum }}</p>
                            </div>
                        @endif
                        {{-- Success state (hidden, shown by JS after AJAX) --}}
                        <div id="generateSuccessState" class="hidden mb-3 px-4 py-3 rounded-xl border border-green-500/40 bg-green-500/5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-green-400 font-medium">Document number generated!</p>
                                    <p class="font-mono text-green-300 font-bold text-sm mt-0.5" id="generatedNumberDisplay">&mdash;</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="generateNumberBtn"
                                onclick="doGenerateNumber('{{ route('contracts.generate-number', $contract) }}', '{{ csrf_token() }}', '{{ addslashes($previewNum ?? '') }}')"
                                class="w-full py-2.5 bg-purple-600/30 hover:bg-purple-600/50 border border-purple-500/30 text-purple-300 font-semibold rounded-lg transition-all text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                            # Generate Official Document Number
                        </button>
                    </div>
                    @endif

                    {{-- NOMOR SUDAH ADA --}}
                    @if($contract->contract_number && $stage->stage_type === 'legal')
                        <div class="p-4 rounded-xl border border-green-500/30 bg-green-500/5" id="numberAlreadyBlock">
                            <p class="text-xs text-green-400 mb-1">&#9989; Document number generated</p>
                            <p class="font-mono text-green-300 font-semibold text-lg" data-number-text>{{ $contract->contract_number }}</p>
                            <p class="text-xs text-gray-500 mt-1">Approve this stage to activate the executing process.</p>
                        </div>
                    @else
                        <div id="numberAlreadyBlock" class="hidden p-4 rounded-xl border border-green-500/30 bg-green-500/5">
                            <p class="text-xs text-green-400 mb-1">&#9989; Document number generated</p>
                            <p class="font-mono text-green-300 font-semibold text-lg" data-number-text>&mdash;</p>
                            <p class="text-xs text-gray-500 mt-1">Approve this stage to activate the executing process.</p>
                        </div>
                    @endif

                    @if($contract->contract_number && $isLastStage && !$isRevisionTarget && $stage->stage_type === 'legal')
                        <div class="p-4 rounded-xl border border-blue-500/30 bg-blue-500/5">
                            <div class="flex items-start gap-3">
                                <div class="text-xl">&#8505;&#65039;</div>
                                <div>
                                    <p class="text-sm font-medium text-blue-300">Document number already generated</p>
                                    <p class="text-xs text-gray-400 mt-1">Approve this stage to activate the <strong class="text-indigo-300">Executing</strong> process.</p>
                                    <p class="font-mono text-green-300 text-sm mt-1">{{ $contract->contract_number }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- REVISION TARGET in_progress --}}
                    @if($isRevisionTarget)
                        @if($stage->revision_feedback)
                            <div class="p-3 bg-red-500/5 border border-red-500/20 rounded-lg"><p class="text-xs font-medium text-red-400 mb-1">Revision notes:</p><p class="text-sm text-gray-300">{{ $stage->revision_feedback }}</p></div>
                        @endif
                        <div class="glass-card rounded-xl p-5 border-l-4 border-amber-500">
                            <h3 class="font-semibold text-amber-400 mb-4">&#8617; Return Revision</h3>
                            @if($revisionRequesterStage)
                                <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="jump_to_stage_id" value="{{ $revisionRequesterStage->id }}">
                                    <textarea name="notes" rows="3" class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 mb-3" placeholder="Explain what has been revised..."></textarea>
                                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-amber-500/90 to-orange-600/90 rounded-lg font-semibold text-white" onclick="return confirm('Return to {{ addslashes($revisionRequester?->nama_user ?? 'reviewer') }}?')">
                                        &#8617; Return to {{ $revisionRequester?->nama_user ?? 'Reviewer' }}
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <textarea name="notes" rows="3" class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 mb-3"></textarea>
                                    <select name="jump_to_stage_id" required class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 mb-3">
                                        <option value="">-- Select reviewer --</option>
                                        @foreach($contract->reviewStages->where('status','revision_requested')->where('needs_revision',false) as $rs)
                                            <option value="{{ $rs->id }}">{{ $rs->assignedUser->nama_user ?? '?' }} &mdash; {{ $rs->stage_name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-amber-500/90 to-orange-600/90 rounded-lg font-semibold text-white">&#8617; Return Revision</button>
                                </form>
                            @endif
                        </div>

                    {{-- NORMAL ACTIONS --}}
                    @else

                        {{-- APPROVE --}}
                        <div class="glass-card rounded-xl p-5 border-l-4 border-green-500">

                            @if($isLastStage)
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 rounded-lg bg-green-500/10"><svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                                    <div>
                                        <h3 class="font-semibold text-green-400">Approve Stage</h3>
                                        <p class="text-sm text-gray-400">@if($contract->contract_number) Number already exists &mdash; approve to activate executing @else <span class="text-amber-400">Generate document number first before approving</span> @endif</p>
                                    </div>
                                </div>
                                @if(!$contract->contract_number)
                                    <div class="p-4 rounded-xl border border-amber-500/40 bg-amber-500/5 mb-4">
                                        <div class="flex items-start gap-3">
                                            <div class="text-xl flex-shrink-0">&#128274;</div>
                                            <div>
                                                <p class="text-sm font-semibold text-amber-400 mb-1">Generate Number Required</p>
                                                <p class="text-xs text-gray-400 leading-relaxed">This is the <strong class="text-white">final review stage</strong>. You must generate the official document number before approving. Once approved, the workflow moves to the <strong class="text-indigo-300">Executing</strong> stage.</p>
                                                <p class="text-xs text-amber-400/70 mt-2">&#8593; Use the "Generate Official Document Number" button above.</p>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Tombol locked -- JS hide ini setelah AJAX sukses --}}
                                    <div id="approveLockedBlock">
                                        <button type="button" disabled class="w-full py-3 bg-gray-700/50 border border-gray-600/30 text-gray-500 font-semibold rounded-lg cursor-not-allowed flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            Approve Stage (Locked &mdash; Generate Number First)
                                        </button>
                                    </div>
                                    {{-- Form approve tersembunyi -- JS show ini setelah AJAX sukses --}}
                                    <div id="approveUnlockedBlock" class="hidden">
                                        <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="is_last_stage" value="1">
                                            <textarea name="notes" rows="3" maxlength="1000" class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 mb-4" placeholder="Final notes (optional)..."></textarea>
                                            <button type="submit" class="w-full py-3 bg-gradient-to-r from-green-500/90 to-emerald-600/90 hover:from-green-500 hover:to-emerald-600 rounded-lg font-semibold text-white transition-all">
                                                &#10003; Approve &rarr; Enable Executing Stage
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="is_last_stage" value="1">
                                        <textarea name="notes" rows="3" maxlength="1000" class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 mb-4" placeholder="Final notes (optional)..."></textarea>
                                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-green-500/90 to-emerald-600/90 hover:from-green-500 hover:to-emerald-600 rounded-lg font-semibold text-white transition-all">
                                            &#10003; Approve &rarr; Enable Executing Stage
                                        </button>
                                    </form>
                                @endif

                            @elseif($isParallelStage)
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 rounded-lg bg-green-500/10"><svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><h3 class="font-semibold text-green-400">Approve Review</h3><p class="text-sm text-gray-400">&#9889; Substantial Review &mdash; does not block other reviewers</p></div>
                                </div>
                                @php $otherPendingInGroup = $contract->reviewStages->where('parallel_group',$stage->parallel_group)->where('id','!=',$stage->id)->whereNotIn('status',['completed','rejected'])->count(); @endphp
                                @if($otherPendingInGroup > 0)
                                    <div class="mb-4 p-3 bg-blue-500/5 border border-blue-500/20 rounded-lg"><p class="text-xs text-blue-400">&#8505;&#65039; {{ $otherPendingInGroup }} other reviewer(s) still in progress.</p></div>
                                @else
                                    <div class="mb-4 p-3 bg-green-500/5 border border-green-500/20 rounded-lg"><p class="text-xs text-green-400">&#127881; You are the last reviewer in this substantial review group.</p></div>
                                @endif
                                <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <textarea name="notes" rows="3" maxlength="1000" class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 mb-4" placeholder="Notes (optional)..."></textarea>
                                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-green-500/90 to-emerald-600/90 rounded-lg font-semibold text-white transition-all" onclick="return confirm('Approve this review?')">&#10003; Approve</button>
                                </form>

                            @else
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 rounded-lg bg-green-500/10"><svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><h3 class="font-semibold text-green-400">Approve &amp; Jump</h3><p class="text-sm text-gray-400">Approve and move to the next stage</p></div>
                                </div>
                                <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <div class="space-y-4">
                                        @if($jumpOptions->isEmpty())
                                            <div class="p-3 bg-gray-800/30 rounded-lg border border-gray-700/30"><p class="text-xs text-gray-500">No next stage available.</p></div>
                                        @else
                                            <div>
                                                <label class="block text-xs font-medium text-gray-400 mb-2">Next Stage</label>
                                                <select name="jump_to_stage_id" required class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-green-500/30 focus:outline-none">
                                                    <option value="">-- Select next stage --</option>
                                                    @foreach($jumpOptions as $opt)
                                                        <option value="{{ $opt['id'] }}">@if($opt['is_parallel']) &#9889; @endif{{ $opt['label'] }}</option>
                                                    @endforeach
                                                </select>
                                                @if($jumpOptions->where('is_parallel', true)->isNotEmpty())
                                                    <p class="text-xs text-purple-400/70 mt-1.5">&#9889; Substantial Review will activate all reviewers simultaneously.</p>
                                                @endif
                                            </div>
                                        @endif
                                        <textarea name="notes" rows="3" maxlength="1000" class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-green-500/30 focus:outline-none" placeholder="Notes for the next reviewer (optional)..."></textarea>
                                        <button type="submit" @if($jumpOptions->isEmpty()) disabled @endif class="w-full py-3 bg-gradient-to-r from-green-500/90 to-emerald-600/90 hover:from-green-500 hover:to-emerald-600 rounded-lg font-medium text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                                            &rarr; Move to Next Stage
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        {{-- REJECT --}}
                        @if(auth()->user()->hasAnyRole(['legal', 'admin']))
                        <div class="glass-card rounded-xl p-5 border-l-4 border-red-500">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 rounded-lg bg-red-500/10"><svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
                                <div><h3 class="font-semibold text-red-400">Reject Contract</h3><p class="text-sm text-gray-400">If you reject this contract, it cannot be undone.</p></div>
                            </div>
                            <form action="{{ route('review-stages.reject', [$contract, $stage]) }}" method="POST" id="rejectForm">
                                @csrf
                                <textarea name="rejection_reason" rows="3" required minlength="10" maxlength="2000" class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 mb-4" placeholder="Justification for rejection..."></textarea>
                                <button type="submit" class="w-full py-3 bg-red-600/20 hover:bg-red-600/40 border border-red-500/30 text-red-300 font-semibold rounded-lg transition-all">&#10005; Reject Contract</button>
                            </form>
                        </div>
                        @endif

                    @endif
                </div>

                @elseif($stage->status === 'completed')
                    <div class="p-5 rounded-xl border border-green-500/30 bg-green-500/5 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-green-500/10 flex items-center justify-center mb-3"><svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                        <p class="text-green-400 font-semibold">Stage Completed</p>
                        @if($stage->notes)<div class="mt-3 p-3 bg-gray-800/30 rounded-lg text-left"><p class="text-xs text-gray-500 mb-1">Notes:</p><p class="text-sm text-gray-300">{{ $stage->notes }}</p></div>@endif
                        <p class="text-xs text-gray-500 mt-2">{{ $stage->completed_at?->format('d M Y, H:i') ?? '&mdash;' }}</p>
                    </div>
                @elseif(!$canAct)
                    <div class="p-5 rounded-xl border border-gray-700/30 bg-gray-800/20 text-center">
                        <p class="text-gray-500 text-sm">You do not have any actions for this stage.</p>
                    </div>
                @endif

            </div>

            @if(!$isSpecialStage)
                @include('reviews._revision-task-panel', [
                    'contract'=>$contract,'stage'=>$stage,
                    'sentRevisionTasks'=>$sentRevisionTasks??collect(),
                    'receivedRevisionTasks'=>$receivedRevisionTasks??collect(),
                    'revisionTargetStages'=>$revisionTargetStages??collect(),
                    'canAct'=>$canAct,'stageColor'=>$stageColor,'isRevisionTarget'=>$isRevisionTarget,
                ])
            @endif
        </div>

        {{-- RIGHT: Sidebar --}}
        <div class="space-y-6">
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-300 mb-4">Workflow Stages</h3>
                <div class="space-y-2">
                    @php
                        $sidebarItems=[]; $seenPS=[];
                        foreach ($contract->reviewStages->sortBy('sequence') as $si) {
                            if (!is_null($si->parallel_group)) {
                                if (in_array($si->parallel_group,$seenPS)) continue;
                                $seenPS[]=$si->parallel_group;
                                $gm=$contract->reviewStages->where('parallel_group',$si->parallel_group);
                                $sidebarItems[]=['type'=>'parallel','stage'=>$si,'group_members'=>$gm,
                                    'all_done'=>$gm->every(fn($m)=>$m->status==='completed'),
                                    'any_active'=>$gm->contains(fn($m)=>in_array($m->status,['in_progress','assigned'])),
                                    'current_in_group'=>$gm->contains(fn($m)=>$m->id===$stage->id),
                                    'done_count'=>$gm->where('status','completed')->count(),'total_count'=>$gm->count()];
                            } else { $sidebarItems[]=['type'=>'sequential','stage'=>$si]; }
                        }
                    @endphp
                    @foreach($sidebarItems as $item)
                        @if($item['type']==='sequential')
                            @php
                                $si=$item['stage'];
                                $siRevT=$si->needs_revision&&$si->status==='revision_requested';
                                $siRevS=!$si->needs_revision&&$si->status==='revision_requested';
                                $siIcon=match($si->stage_type){'legal'=>'⚖️','finance'=>'💹','accounting'=>'📊','tax'=>'🏛️','executing'=>'✍️','archiving'=>'📦',default=>'👤'};
                                $siSP=in_array($si->stage_type,['executing','archiving'])&&$si->status==='pending';
                            @endphp
                            <div class="flex items-start gap-3 p-3 rounded-xl border transition-all @if($si->id==$stage->id) border-blue-500/40 bg-blue-500/10 @elseif($si->status==='completed') border-green-500/20 bg-green-500/5 @elseif($siSP) border-gray-700/20 bg-gray-800/10 opacity-40 @else border-gray-700/30 bg-gray-800/20 hover:bg-gray-800/40 @endif">
                                <div class="text-sm mt-0.5 flex-shrink-0">{{ $siIcon }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-300 truncate text-sm">{{ $si->is_user_stage?'User Submission':ucfirst(str_replace('_',' ',$si->stage_name)) }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ $si->assignedUser->nama_user??'Unassigned' }}</div>
                                    <div class="text-xs mt-0.5">
                                        @if($si->status==='completed'&&$si->stage_type==='executing') <span class="text-green-400">&#9997;&#65039; Executed</span>
                                        @elseif($si->status==='completed'&&$si->stage_type==='archiving') <span class="text-green-400">&#128230; Archived</span>
                                        @elseif($si->status==='completed') <span class="text-green-400">&#10003; Completed</span>
                                        @elseif($siRevT) <span class="text-red-400">&#128276; Revision</span>
                                        @elseif($siRevS) <span class="text-orange-400">&#128228; Sent Revision</span>
                                        @elseif($si->id==$stage->id) <span class="text-blue-400">&#9679; Current</span>
                                        @elseif($si->status==='in_progress') <span class="text-blue-400">In Progress</span>
                                        @elseif($si->status==='assigned') <span class="text-yellow-400">Assigned</span>
                                        @else <span class="text-gray-500">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            @php $allD=$item['all_done'];$anyA=$item['any_active'];$isCur=$item['current_in_group'];$dc=$item['done_count'];$tc=$item['total_count']; @endphp
                            <div class="rounded-xl border transition-all @if($isCur) border-purple-500/40 bg-purple-500/10 @elseif($allD) border-green-500/20 bg-green-500/5 @elseif($anyA) border-purple-500/20 bg-purple-500/5 @else border-gray-700/30 bg-gray-800/20 @endif">
                                <div class="flex items-start gap-3 p-3">
                                    <div class="text-sm mt-0.5 flex-shrink-0">&#9889;</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-purple-300 truncate text-sm">Substantial Review</div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            @if($allD) <span class="text-green-400">&#10003; All completed ({{ $tc }}/{{ $tc }})</span>
                                            @elseif($anyA) <span class="text-purple-400">{{ $dc }}/{{ $tc }} completed</span>
                                            @else <span class="text-gray-500">Pending ({{ $tc }} reviewers)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="border-t border-gray-700/30 px-3 pb-2 pt-2 space-y-1.5">
                                    @foreach($item['group_members']->sortBy('sequence') as $gm)
                                        <div class="flex items-center gap-2 pl-2">
                                            <div class="w-1.5 h-1.5 rounded-full flex-shrink-0 @if($gm->status==='completed') bg-green-400 @elseif(in_array($gm->status,['in_progress','assigned'])) bg-blue-400 @else bg-gray-600 @endif"></div>
                                            <span class="text-xs text-gray-400 truncate">{{ $gm->assignedUser->nama_user??'Unassigned' }}</span>
                                            @if($gm->id===$stage->id) <span class="text-xs text-blue-400 ml-auto flex-shrink-0">&#9679; You</span>
                                            @elseif($gm->status==='completed') <span class="text-xs text-green-400 ml-auto flex-shrink-0">&#10003;</span>
                                            @elseif($gm->status==='in_progress') <span class="text-xs text-blue-400 ml-auto flex-shrink-0">Active</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-gray-300 mb-4">Review History</h3>
                @if($reviewLogs->isEmpty())
                    <p class="text-center text-gray-500 text-sm py-4">No history yet.</p>
                @else
                    <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                        @foreach($reviewLogs as $log)
                            <div class="border border-gray-700/30 rounded-xl p-4 bg-gray-900/30">
                                <div class="text-sm text-gray-300 mb-1">
                                    <strong class="text-gray-200">{{ $log->user->nama_user??'System' }}</strong>
                                    <span class="text-gray-400"> &mdash; {{ ucfirst(str_replace('_',' ',$log->action)) }}</span>
                                </div>
                                @if($log->notes)<div class="mt-2 text-gray-400 text-sm bg-gray-800/30 p-2 rounded-lg">{{ $log->notes }}</div>@endif
                                <div class="text-xs text-gray-500 mt-2">{{ $log->created_at->format('d M Y H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.gradient-border{background:linear-gradient(145deg,rgba(30,41,59,.9),rgba(15,23,42,.95));border:1px solid rgba(255,255,255,.08);border-radius:1rem}
.glass-card{background:rgba(255,255,255,.03);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.08)}
textarea:focus,select:focus,input[type="date"]:focus{outline:none}
input[type="date"]{color-scheme:dark}
::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:rgba(255,255,255,.05);border-radius:3px}::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const r = document.querySelector('textarea[name="rejection_reason"]');
            if (!r?.value.trim() || r.value.trim().length < 10) { e.preventDefault(); alert('Rejection reason must be at least 10 characters.'); return; }
            if (!confirm('Reject this contract? This cannot be undone.')) e.preventDefault();
        });
    }
    const revNotes = document.querySelector('textarea[name="revision_notes"]');
    const revCount = document.getElementById('revisionCharCount');
    if (revNotes && revCount) revNotes.addEventListener('input', () => { revCount.textContent = revNotes.value.length + '/2000'; });
    const revForm = document.getElementById('revisionForm');
    if (revForm) {
        revForm.addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('input[name="jump_to_stage_ids[]"]:checked');
            if (checked.length === 0) { e.preventDefault(); alert('Select at least 1 reviewer!'); return; }
            if (!revNotes?.value.trim() || revNotes.value.trim().length < 10) { e.preventDefault(); alert('Revision notes must be at least 10 characters.'); return; }
            const btn = document.getElementById('submitRevisionBtn');
            if (btn) { btn.disabled = true; btn.textContent = 'Sending...'; }
        });
    }
    document.querySelectorAll('textarea').forEach(ta => {
        ta.addEventListener('input', function() { this.style.height='auto'; this.style.height=Math.min(this.scrollHeight+2,250)+'px'; });
        setTimeout(() => ta.dispatchEvent(new Event('input')), 100);
    });
});

// AJAX Generate Number
// FIX UTAMA: Header 'Accept: application/json' WAJIB ada.
// Tanpanya Laravel tidak menganggap request sebagai JSON request
// -> controller return redirect (HTML 302) bukan JSON -> JSON.parse error.
async function doGenerateNumber(url, token, previewNum) {
    const previewText = previewNum || 'this number';
    if (!confirm('Generate number ' + previewText + '?\nThis number cannot be changed after creation.')) return;
    const btn          = document.getElementById('generateNumberBtn');
    const successState = document.getElementById('generateSuccessState');
    const genBlock     = document.getElementById('generateNumberBlock');
    if (btn) { btn.disabled=true; btn.innerHTML='<svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Generating...'; }
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept':       'application/json',  // KRITIS: tanpa ini dapat HTML bukan JSON
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
        });
        let data;
        try { data = await res.json(); }
        catch(pe) { throw new Error('Server returned non-JSON (HTTP '+res.status+'). Try refreshing the page (CSRF may have expired).'); }
        if (data.success) {
            const disp = document.getElementById('generatedNumberDisplay');
            if (disp) disp.textContent = data.contract_number;
            if (successState) successState.classList.remove('hidden');
            if (btn) btn.classList.add('hidden');
            const nab = document.getElementById('numberAlreadyBlock');
            if (nab) { const nt=nab.querySelector('[data-number-text]'); if(nt) nt.textContent=data.contract_number; nab.classList.remove('hidden'); }
            const alb = document.getElementById('approveLockedBlock');
            const aub = document.getElementById('approveUnlockedBlock');
            if (alb) alb.classList.add('hidden');
            if (aub) aub.classList.remove('hidden');
            if (genBlock) { genBlock.style.transition='box-shadow 0.3s'; genBlock.style.boxShadow='0 0 0 1px rgba(34,197,94,0.5)'; setTimeout(()=>{genBlock.style.boxShadow='';},2000); }
        } else {
            resetGenerateBtn(btn);
            alert(data.message || 'Failed to generate number. Please try again.');
        }
    } catch(err) {
        console.error('doGenerateNumber error:', err);
        resetGenerateBtn(btn);
        alert('Error: ' + err.message);
    }
}
function resetGenerateBtn(btn) {
    if (!btn) return;
    btn.disabled=false;
    btn.innerHTML='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg> # Generate Official Document Number';
}
</script>
</x-app-layout-dark>"""
