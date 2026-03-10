@php
    $activeStage = $contract->activeStage();
    $isActiveStage = $activeStage && $activeStage->id === $stage->id;

    $canAct = auth()->check() && (
        auth()->user()->hasRole('admin')
        || $stage->assigned_user_id === auth()->user()->id
    );

    $isContractCompleted = 
        $contract->reviewStages->count() > 0
        && $contract->reviewStages->every(fn ($s) => $s->status === 'completed');

    $isLastStage = $stage->isLastStage();
    
    // Department colors untuk stage yang berbeda - lebih subtle
    $stageColors = [
        'legal' => [
            'bg' => 'bg-blue-50/5',
            'bg-gradient' => 'bg-gradient-to-br from-blue-50/5 via-blue-100/5 to-blue-50/5',
            'text' => 'text-blue-300',
            'text-gradient' => 'text-blue-300',
            'border' => 'border-blue-500/20',
            'gradient' => 'from-blue-500/40 to-cyan-500/40',
            'solid' => 'bg-blue-500/80',
            'ring' => 'ring-blue-500/20',
            'light' => 'bg-blue-500/10',
            'shadow' => 'shadow-blue-500/10',
        ],
        'fat' => [
            'bg' => 'bg-purple-50/5',
            'bg-gradient' => 'bg-gradient-to-br from-purple-50/5 via-purple-100/5 to-purple-50/5',
            'text' => 'text-purple-300',
            'text-gradient' => 'text-purple-300',
            'border' => 'border-purple-500/20',
            'gradient' => 'from-purple-500/40 to-pink-500/40',
            'solid' => 'bg-purple-500/80',
            'ring' => 'ring-purple-500/20',
            'light' => 'bg-purple-500/10',
            'shadow' => 'shadow-purple-500/10',
        ],
        'admin_legal' => [
            'bg' => 'bg-indigo-50/5',
            'bg-gradient' => 'bg-gradient-to-br from-indigo-50/5 via-indigo-100/5 to-indigo-50/5',
            'text' => 'text-indigo-300',
            'text-gradient' => 'text-indigo-300',
            'border' => 'border-indigo-500/20',
            'gradient' => 'from-indigo-500/40 to-violet-500/40',
            'solid' => 'bg-indigo-500/80',
            'ring' => 'ring-indigo-500/20',
            'light' => 'bg-indigo-500/10',
            'shadow' => 'shadow-indigo-500/10',
        ],
        'user' => [
            'bg' => 'bg-gray-50/5',
            'bg-gradient' => 'bg-gradient-to-br from-gray-50/5 via-gray-100/5 to-gray-50/5',
            'text' => 'text-gray-300',
            'text-gradient' => 'text-gray-300',
            'border' => 'border-gray-500/20',
            'gradient' => 'from-gray-500/40 to-gray-400/40',
            'solid' => 'bg-gray-500/80',
            'ring' => 'ring-gray-500/20',
            'light' => 'bg-gray-500/10',
            'shadow' => 'shadow-gray-500/10',
        ],
    ];
    
    $stageColor = $stageColors[$stage->stage_type] ?? $stageColors['user'];
    $stageIcon = match($stage->stage_type) {
        'legal' => '⚖️',
        'fat' => '💰',
        'admin_legal' => '👑',
        default => '👤'
    };
@endphp

<x-app-layout-dark title="Stage Review - {{ $contract->title }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-6">
            <div class="flex-1">
                <!-- Back Button -->
                <div class="flex items-center gap-3 mb-6">
                    <a href="{{ route('contracts.show', $contract) }}" 
                       class="group p-2.5 rounded-xl border border-gray-700/50 bg-gray-800/50 hover:bg-gray-800/70 hover:border-gray-600 transition-all duration-300 flex items-center gap-2 backdrop-blur-sm">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="text-sm font-medium text-gray-300">Back to Contract</span>
                    </a>
                </div>
                
                <!-- Main Title Section -->
                <div class="flex items-start gap-4 mb-6">
                    <div class="p-4 rounded-2xl {{ $stageColor['bg'] }} border {{ $stageColor['border'] }} shadow-lg backdrop-blur-sm">
                        <div class="text-2xl">{{ $stageIcon }}</div>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold {{ $stageColor['text'] }} mb-2">
                            {{ ucfirst(str_replace('_', ' ', $stage->stage_name)) }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-800/70 backdrop-blur-sm border border-gray-700/50">
                                <div class="w-2 h-2 rounded-full {{ $stage->status === 'in_progress' ? 'bg-blue-500' : ($stage->status === 'completed' ? 'bg-green-500' : 'bg-yellow-500') }}"></div>
                                <span class="text-sm font-medium text-gray-300">
                                    @switch($stage->status)
                                        @case('in_progress')
                                            In Progress
                                            @break
                                        @case('completed')
                                            Completed
                                            @break
                                        @case('revision_requested')
                                            Revision Requested
                                            @break
                                        @case('assigned')
                                            Ready to Start
                                            @break
                                        @default
                                            {{ ucfirst($stage->status) }}
                                    @endswitch
                                </span>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full {{ $stageColor['bg'] }} border {{ $stageColor['border'] }} backdrop-blur-sm">
                                <svg class="w-4 h-4 {{ $stageColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="text-sm font-medium {{ $stageColor['text'] }}">
                                    Stage {{ $stage->sequence }} of {{ $contract->reviewStages->count() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col items-end gap-2">
                <div class="text-right">
                    <div class="text-sm text-gray-400">Contract #</div>
                    <div class="font-mono text-gray-300 text-lg">{{ $contract->contract_number ?? 'N/A' }}</div>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-800/70 border border-gray-700/50 backdrop-blur-sm">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm text-gray-300">
                        Created: {{ $contract->created_at->format('M d, Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="glass-card rounded-2xl p-6 mb-6">
            <div class="flex justify-between text-sm text-gray-400 mb-3">
                <span>Review Progress</span>
                <span>{{ $contract->review_progress ?? 0 }}% Complete</span>
            </div>
            <div class="h-2 bg-gray-800/50 rounded-full overflow-hidden backdrop-blur-sm">
                <div class="h-full bg-gradient-to-r {{ $stageColor['gradient'] }} transition-all duration-500" 
                     style="width: {{ $contract->review_progress ?? 0 }}%"></div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Review Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Review Actions Card -->
                <div class="gradient-border rounded-2xl p-6">
                    <div class="relative">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-bold text-gray-300">Review Actions</h2>
                                <p class="text-gray-400">Choose your action for this stage</p>
                            </div>
                            
                            <div class="px-4 py-2 rounded-full {{ $stageColor['bg'] }} border {{ $stageColor['border'] }} backdrop-blur-sm">
                                <span class="text-sm text-gray-300">
                                    {{ ucfirst(str_replace('_', ' ', $stage->status)) }}
                                </span>
                            </div>
                        </div>

                        @if($isContractCompleted)
                            <div class="mb-6 p-6 rounded-2xl border border-green-500/30 bg-green-500/5 text-center backdrop-blur-sm">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-500/10 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-green-400 mb-2">
                                    Contract Review Completed
                                </h2>
                                <p class="text-green-300/80 text-sm">
                                    All review stages have been completed. This contract is now finalized.
                                </p>
                            </div>
                        @endif

                        <!-- START REVIEW BUTTON -->
                        @if(
                            $isActiveStage
                            && in_array($stage->status, ['assigned', 'revision_requested'])
                            && $canAct
                        )
                        <form action="{{ route('review-stages.start', [$contract, $stage]) }}" method="POST" class="mb-6">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-4 bg-gradient-to-r {{ $stageColor['gradient'] }} hover:opacity-95 rounded-xl font-semibold text-white transition-all duration-300 hover:shadow-lg {{ str_replace('from-', 'shadow-', $stageColor['gradient']) }}/20">
                                <div class="flex items-center justify-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @if($stage->status === 'revision_requested')
                                        Continue Review
                                    @else
                                        Start Reviewing This Stage
                                    @endif
                                </div>
                            </button>
                            <p class="text-sm text-gray-400 text-center mt-2">
                                @if($stage->status === 'revision_requested')
                                    Click to continue reviewing after revision
                                @else
                                    Click to begin reviewing this contract stage
                                @endif
                            </p>
                        </form>
                        @endif

                        <!-- REVIEW ACTIONS -->
                        @if(!$isContractCompleted && $isActiveStage && $stage->status === 'in_progress' && $canAct)
                        <div class="space-y-6">
                            <!-- APPROVE & JUMP -->
                            <div class="glass-card rounded-xl p-5 border-l-4 border-green-500">
                                @if($isLastStage)
                                    <!-- FINAL STAGE: APPROVE & FINISH -->
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-green-400">Approve & Finish Contract</h3>
                                            <p class="text-sm text-gray-400">Approve this final stage and complete the contract review</p>
                                        </div>
                                    </div>
                                    
                                    <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST" id="approveFinalForm">
                                        @csrf
                                        <input type="hidden" name="is_last_stage" value="1">
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                                    Final Notes
                                                    <span class="text-gray-500 text-xs">(Optional - will be recorded)</span>
                                                </label>
                                                <textarea name="notes" 
                                                        rows="3"
                                                        maxlength="1000"
                                                        class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-green-500/30 focus:border-green-500/50 transition-all placeholder:text-gray-500/70"
                                                        placeholder="Add final notes, comments, or recommendations about the contract approval..."
                                                        id="finalNotes">{{ old('notes') }}</textarea>
                                                <div class="text-right mt-1">
                                                    <div class="text-xs text-gray-500" id="finalCharCount">0/1000 characters</div>
                                                </div>
                                            </div>
                                            
                                            <div class="pt-2">
                                                <button type="submit" 
                                                        class="w-full px-4 py-3 bg-gradient-to-r from-green-500/90 to-emerald-600/90 hover:from-green-500 hover:to-emerald-600 rounded-lg font-semibold text-white transition-all duration-300 shadow-md shadow-green-500/10 hover:shadow-lg hover:shadow-green-500/20"
                                                        id="approveFinalBtn">
                                                    <span class="flex items-center justify-center gap-2">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Approve & Finish Contract Review
                                                    </span>
                                                </button>
                                                
                                                <p class="text-xs text-gray-500 mt-2 text-center">
                                                    This will <strong>complete the entire contract review</strong> and mark it as Approved
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                @else
                                    <!-- NON-FINAL STAGE: APPROVE & JUMP -->
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/10 to-emerald-600/10">
                                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-green-400">Approve & Jump to Another Stage</h3>
                                            <p class="text-sm text-gray-400">Approve this stage and jump to another stage in the workflow</p>
                                        </div>
                                    </div>
                                    
                                    <form action="{{ route('review-stages.approve-jump', [$contract, $stage]) }}" method="POST" id="approveJumpForm">
                                        @csrf
                                        
                                        @if ($errors->any())
                                            <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-lg backdrop-blur-sm">
                                                @foreach ($errors->all() as $error)
                                                    <p class="text-sm text-red-400">{{ $error }}</p>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        <div class="space-y-4">
                                            <!-- Stage Selection -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                                    Jump to Stage *
                                                    <span class="text-gray-500 text-xs">(Select the next stage to continue review)</span>
                                                </label>
                                                <select name="jump_to_stage_id" 
                                                        required
                                                        class="w-full bg-gray-800/50 border {{ $errors->has('jump_to_stage_id') ? 'border-red-500/50' : 'border-gray-700/50' }} rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-green-500/30 focus:border-green-500/50 transition-all backdrop-blur-sm"
                                                        id="jumpStageSelect">
                                                    <option value="">-- Select stage to jump to --</option>
                                                    
                                                    <!-- Group: Next Sequential Stages -->
                                                    @php
                                                        $nextStages = [];
                                                        $otherStages = [];
                                                        
                                                        foreach($availableStages as $availableStage) {
                                                            $stageData = is_array($availableStage) ? $availableStage : (array) $availableStage;
                                                            $stageId = $stageData['id'] ?? '';
                                                            $isUserStage = $stageData['is_user_stage'] ?? false;
                                                            $sequence = $stageData['sequence'] ?? 0;
                                                            
                                                            if(!$isUserStage && $stageId != $stage->id) {
                                                                if($sequence > $stage->sequence) {
                                                                    $nextStages[] = $stageData;
                                                                } else {
                                                                    $otherStages[] = $stageData;
                                                                }
                                                            }
                                                        }
                                                        
                                                        usort($nextStages, function($a, $b) {
                                                            return ($a['sequence'] ?? 0) <=> ($b['sequence'] ?? 0);
                                                        });
                                                        
                                                        usort($otherStages, function($a, $b) {
                                                            return ($a['sequence'] ?? 0) <=> ($b['sequence'] ?? 0);
                                                        });
                                                    @endphp
                                                    
                                                    @if(!empty($nextStages))
                                                        <optgroup label="📈 Next Stages in Sequence" class="bg-gray-800">
                                                            @foreach($nextStages as $nextStage)
                                                                @php
                                                                    $stageName = $nextStage['stage_name'] ?? '';
                                                                    $stageId = $nextStage['id'] ?? '';
                                                                    $stageType = $nextStage['stage_type'] ?? '';
                                                                    $assignedUserName = $nextStage['assigned_user_name'] ?? 'Unassigned';
                                                                    $sequence = $nextStage['sequence'] ?? 0;
                                                                @endphp
                                                                <option value="{{ $stageId }}" 
                                                                        {{ old('jump_to_stage_id') == $stageId ? 'selected' : '' }}>
                                                                    @switch($stageType)
                                                                        @case('legal')
                                                                            ⚖️ 
                                                                            @break
                                                                        @case('admin_legal')
                                                                            👑 
                                                                            @break
                                                                        @case('fat')
                                                                            💼 
                                                                            @break
                                                                        @default
                                                                            📋 
                                                                    @endswitch
                                                                    
                                                                    {{ ucfirst(str_replace('_', ' ', $stageName)) }}
                                                                    <span class="text-gray-500 text-xs">(Stage {{ $sequence }})</span>
                                                                    - {{ $assignedUserName }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif
                                                    
                                                    @if(!empty($otherStages))
                                                        <optgroup label="↩️ Other Available Stages" class="bg-gray-800">
                                                            @foreach($otherStages as $otherStage)
                                                                @php
                                                                    $stageName = $otherStage['stage_name'] ?? '';
                                                                    $stageId = $otherStage['id'] ?? '';
                                                                    $stageType = $otherStage['stage_type'] ?? '';
                                                                    $assignedUserName = $otherStage['assigned_user_name'] ?? 'Unassigned';
                                                                    $sequence = $otherStage['sequence'] ?? 0;
                                                                    $isCompleted = $otherStage['is_completed'] ?? false;
                                                                @endphp
                                                                <option value="{{ $stageId }}" 
                                                                        {{ old('jump_to_stage_id') == $stageId ? 'selected' : '' }}>
                                                                    @switch($stageType)
                                                                        @case('legal')
                                                                            ⚖️ 
                                                                            @break
                                                                        @case('admin_legal')
                                                                            👑 
                                                                            @break
                                                                        @case('fat')
                                                                            💼 
                                                                            @break
                                                                        @default
                                                                            📋 
                                                                    @endswitch
                                                                    
                                                                    {{ ucfirst(str_replace('_', ' ', $stageName)) }}
                                                                    <span class="text-gray-500 text-xs">(Stage {{ $sequence }})</span>
                                                                    
                                                                    @if($isCompleted)
                                                                        <span class="text-green-400">✓</span>
                                                                    @endif
                                                                    
                                                                    - {{ $assignedUserName }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif
                                                </select>
                                                
                                                @if($errors->has('jump_to_stage_id'))
                                                    <p class="mt-1 text-sm text-red-400">{{ $errors->first('jump_to_stage_id') }}</p>
                                                @endif
                                            </div>
                                            
                                            <!-- Notes -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                                    Approval Notes
                                                    <span class="text-gray-500 text-xs">(Optional - will be recorded with approval)</span>
                                                </label>
                                                <textarea name="notes" 
                                                        rows="3"
                                                        maxlength="1000"
                                                        class="w-full bg-gray-800/50 border {{ $errors->has('notes') ? 'border-red-500/50' : 'border-gray-700/50' }} rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-green-500/30 focus:border-green-500/50 transition-all placeholder:text-gray-500/70 backdrop-blur-sm"
                                                        placeholder="Add notes, comments, or recommendations for the next reviewer..."
                                                        id="approvalNotes">{{ old('notes') }}</textarea>
                                                <div class="flex justify-between items-center mt-1">
                                                    <div>
                                                        @if($errors->has('notes'))
                                                            <p class="text-sm text-red-400">{{ $errors->first('notes') }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-gray-500" id="approvalCharCount">0/1000 characters</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Submit Button -->
                                            <div class="pt-2">
                                                <button type="submit" 
                                                        class="w-full py-3 bg-gradient-to-r from-green-500/90 to-emerald-600/90 hover:from-green-500 hover:to-emerald-600 rounded-lg font-medium text-white transition-all duration-300 shadow-md shadow-green-500/10 hover:shadow-lg hover:shadow-green-500/20"
                                                        id="approveJumpBtn">
                                                    <span class="flex items-center justify-center gap-2">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                        </svg>
                                                        Continue to Next Stage
                                                    </span>
                                                </button>
                                                
                                                <p class="text-xs text-gray-500 mt-2 text-center">
                                                    This will complete your review and pass the contract to the next selected reviewer
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>

                            <!-- REQUEST REVISION -->
                            <div class="glass-card rounded-xl p-5 border-l-4 border-amber-500">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-amber-400">Request Revision</h3>
                                        <p class="text-sm text-gray-400">
                                            @if($stage->isLastStage())
                                                Request revision and jump to any stage (final stage)
                                            @else
                                                Request revision and jump to any other stage
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                <form action="{{ route('review-stages.request-revision', [$contract, $stage]) }}" method="POST" id="revisionForm">
                                    @csrf
                                    
                                    <input type="hidden" name="is_last_stage" value="{{ $stage->isLastStage() ? 1 : 0 }}">
                                    
                                    @if ($errors->any())
                                        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-lg backdrop-blur-sm">
                                            @foreach ($errors->all() as $error)
                                                <p class="text-sm text-red-400">{{ $error }}</p>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="space-y-4">
                                        <!-- Stage Selection -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                                Jump to Stage
                                                <span class="text-gray-500 text-xs">(Optional - leave empty to jump back to USER stage)</span>
                                            </label>
                                            <select name="jump_to_stage_id" 
                                                    class="w-full bg-gray-800/50 border {{ $errors->has('jump_to_stage_id') ? 'border-red-500/50' : 'border-gray-700/50' }} rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-amber-500/30 focus:border-amber-500/50 transition-all backdrop-blur-sm"
                                                    id="jumpStageSelect">
                                                <option value="">-- Select stage to jump to --</option>
                                                
                                                @php
                                                    $userStage = $contract->reviewStages->where('is_user_stage', true)->first();
                                                @endphp
                                                @if($userStage)
                                                    <option value="{{ $userStage->id }}" {{ old('jump_to_stage_id') == $userStage->id ? 'selected' : '' }}>
                                                        🏠 USER Stage (Back to Contract Owner: {{ $userStage->assignedUser->name ?? 'Unassigned' }})
                                                    </option>
                                                @endif
                                                
                                                <option disabled class="bg-gray-800 text-gray-500">────────── Other Stages ──────────</option>
                                                
                                                @foreach($availableStages as $availableStage)
                                                    @php
                                                        $stageData = is_array($availableStage) ? $availableStage : (array) $availableStage;
                                                        $stageName = $stageData['stage_name'] ?? '';
                                                        $stageId = $stageData['id'] ?? '';
                                                        $isUserStage = $stageData['is_user_stage'] ?? false;
                                                        $assignedUserName = $stageData['assigned_user_name'] ?? 'Unassigned';
                                                        $stageType = $stageData['stage_type'] ?? '';
                                                        $isCompleted = $stageData['is_completed'] ?? false;
                                                    @endphp
                                                    
                                                    @if(!$isUserStage && $stageId != $stage->id)
                                                        <option value="{{ $stageId }}" 
                                                                {{ old('jump_to_stage_id') == $stageId ? 'selected' : '' }}>
                                                            @switch($stageType)
                                                                @case('legal')
                                                                    ⚖️ 
                                                                    @break
                                                                @case('admin_legal')
                                                                    👑 
                                                                    @break
                                                                @case('fat')
                                                                    💼 
                                                                    @break
                                                                @default
                                                                    📋 
                                                            @endswitch
                                                            
                                                            {{ ucfirst(str_replace('_', ' ', $stageName)) }}
                                                            
                                                            @if($isCompleted)
                                                                <span class="text-green-400">✓</span>
                                                            @endif
                                                            
                                                            ({{ $assignedUserName }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            
                                            @if($errors->has('jump_to_stage_id'))
                                                <p class="mt-1 text-sm text-red-400">{{ $errors->first('jump_to_stage_id') }}</p>
                                            @endif
                                        </div>
                                        
                                        <!-- Revision Notes -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                                Revision Notes *
                                                <span class="text-gray-500 text-xs">(Minimum 10 characters)</span>
                                            </label>
                                            <textarea name="revision_notes" 
                                                    rows="4" 
                                                    required
                                                    minlength="10"
                                                    maxlength="2000"
                                                    class="w-full bg-gray-800/50 border {{ $errors->has('revision_notes') ? 'border-red-500/50' : 'border-gray-700/50' }} rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-amber-500/30 focus:border-amber-500/50 transition-all placeholder:text-gray-500/70 backdrop-blur-sm"
                                                    placeholder="Please explain in detail what needs to be revised, why, and any specific requirements..."
                                                    id="revisionNotes">{{ old('revision_notes') }}</textarea>
                                            
                                            <div class="flex justify-between items-center mt-1">
                                                <div>
                                                    @if($errors->has('revision_notes'))
                                                        <p class="text-sm text-red-400">{{ $errors->first('revision_notes') }}</p>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500" id="charCount">0/2000 characters</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Submit Button -->
                                        <div class="pt-2">
                                            <button type="submit" 
                                                    class="w-full py-3 bg-gradient-to-r from-amber-500/90 to-orange-600/90 hover:from-amber-500 hover:to-orange-600 rounded-lg font-medium text-white transition-all duration-300 shadow-md shadow-amber-500/10 hover:shadow-lg hover:shadow-amber-500/20"
                                                    id="submitBtn">
                                                <span class="flex items-center justify-center gap-2">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Request Revision
                                                </span>
                                            </button>
                                            
                                            <p class="text-xs text-gray-500 mt-2 text-center">
                                                This will move the contract to the selected stage and notify the assigned reviewer
                                            </p>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- REJECT CONTRACT -->
                            <div class="glass-card rounded-xl p-5 border-l-4 border-red-500">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 rounded-lg bg-gradient-to-br from-red-500/10 to-pink-600/10">
                                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-red-400">Reject Contract</h3>
                                        <p class="text-sm text-gray-400">Reject this contract entirely (cannot be undone)</p>
                                    </div>
                                </div>
                                
                                <form action="{{ route('review-stages.reject', [$contract, $stage]) }}" method="POST" id="rejectForm">
                                    @csrf
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                                Rejection Reason *
                                            </label>
                                            <textarea name="rejection_reason" rows="3" required
                                                      class="w-full bg-gray-800/50 border border-gray-700/50 rounded-lg px-4 py-3 text-gray-300 focus:ring-1 focus:ring-red-500/30 focus:border-red-500/50 transition-all placeholder:text-gray-500/70 backdrop-blur-sm"
                                                      placeholder="Explain why you're rejecting this contract...">{{ old('rejection_reason') }}</textarea>
                                        </div>
                                        
                                        <button type="submit" 
                                                class="w-full py-3 bg-gradient-to-r from-red-500/90 to-pink-600/90 hover:from-red-500 hover:to-pink-600 rounded-lg font-medium text-white transition-all duration-300 shadow-md shadow-red-500/10 hover:shadow-lg hover:shadow-red-500/20">
                                            <span>Reject Contract</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif

                        <!-- COMPLETED STAGE VIEW -->
                        @if($stage->status === 'completed')
                        <div class="p-6 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-xl border border-green-500/20 text-center backdrop-blur-sm">
                            <div class="w-16 h-16 mx-auto rounded-full bg-green-500/10 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-green-400 mb-2">Stage Completed</h3>
                            <p class="text-gray-300">This review stage has been completed.</p>
                            @if($stage->notes)
                            <div class="mt-3 p-3 bg-gray-800/50 rounded-lg backdrop-blur-sm">
                                <p class="text-sm text-gray-300">{{ $stage->notes }}</p>
                            </div>
                            @endif
                            <p class="text-sm text-gray-400 mt-2">
                                Completed on: {{ $stage->completed_at ? $stage->completed_at->format('d M Y, H:i') : 'N/A' }}
                                @if($stage->jump_to_stage_id)
                                <br>
                                Jumped to: {{ $stage->jumpToStage->stage_name ?? 'Next stage' }}
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Stage Info & Timeline -->
            <div class="space-y-6">
                <!-- STAGE INFO CARD -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-lg {{ $stageColor['light'] }}">
                            <svg class="w-5 h-5 {{ $stageColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-300">Stage Information</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-400 mb-1">Stage Type</div>
                            <div class="font-medium text-gray-300">
                                @if($stage->stage_type === 'legal')
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 text-blue-300 border border-blue-500/20 backdrop-blur-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Legal Review
                                </span>
                                @elseif($stage->stage_type === 'fat')
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20 backdrop-blur-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    FAT Review
                                </span>
                                @elseif($stage->stage_type === 'admin_legal')
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 backdrop-blur-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Admin Legal
                                </span>
                                @elseif($stage->stage_type === 'user')
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-500/10 text-gray-300 border border-gray-500/20 backdrop-blur-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    User
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <div class="text-sm font-medium text-gray-400 mb-1">Assigned To</div>
                            <div class="font-medium text-gray-300">{{ $stage->assignedUser->name ?? 'Unassigned' }}</div>
                        </div>
                        
                        <div>
                            <div class="text-sm font-medium text-gray-400 mb-1">Sequence</div>
                            <div class="font-medium text-gray-300">Stage {{ $stage->sequence }}</div>
                        </div>
                        
                        @if($stage->assigned_at)
                        <div>
                            <div class="text-sm font-medium text-gray-400 mb-1">Assigned At</div>
                            <div class="font-medium text-gray-300">{{ $stage->assigned_at->format('d M Y, H:i') }}</div>
                        </div>
                        @endif
                        
                        @if($stage->started_at)
                        <div>
                            <div class="text-sm font-medium text-gray-400 mb-1">Started At</div>
                            <div class="font-medium text-gray-300">{{ $stage->started_at->format('d M Y, H:i') }}</div>
                        </div>
                        @endif
                        
                        @if($stage->completed_at)
                        <div>
                            <div class="text-sm font-medium text-gray-400 mb-1">Completed At</div>
                            <div class="font-medium text-gray-300">{{ $stage->completed_at->format('d M Y, H:i') }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- REVIEW TIMELINE -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-300">Review Timeline</h3>
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($contract->reviewStages->sortBy('sequence') as $stageItem)
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                @if($stageItem->status === 'completed')
                                    <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center border border-green-500/20">
                                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @elseif($stageItem->id === $stage->id && $stage->status === 'in_progress')
                                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center border border-blue-500/30 shadow-md shadow-blue-500/20">
                                        <div class="w-2.5 h-2.5 rounded-full bg-blue-400"></div>
                                    </div>
                                @elseif($stageItem->id === $stage->id)
                                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center border border-blue-500/20">
                                        <div class="w-2.5 h-2.5 rounded-full bg-blue-400"></div>
                                    </div>
                                @elseif($stageItem->status === 'assigned')
                                    <div class="w-10 h-10 rounded-xl bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20">
                                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                                    </div>
                                @elseif($stageItem->status === 'revision_requested')
                                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 flex items-center justify-center border border-orange-500/20">
                                        <div class="w-2.5 h-2.5 rounded-full bg-orange-400"></div>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-gray-800/50 flex items-center justify-center border border-gray-700/50 backdrop-blur-sm">
                                        <span class="text-sm text-gray-400">{{ $stageItem->sequence }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-300 truncate">
                                    @if($stageItem->is_user_stage)
                                        USER
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $stageItem->stage_name)) }}
                                    @endif
                                </div>
                                <div class="text-sm text-gray-400 truncate">
                                    {{ $stageItem->assignedUser->name ?? 'Unassigned' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    @if($stageItem->status === 'completed')
                                    <span class="text-green-400">✓ Completed</span>
                                    @elseif($stageItem->id == $stage->id)
                                    <span class="text-blue-400">Current</span>
                                    @elseif($stageItem->status === 'in_progress')
                                    <span class="text-blue-400">In Progress</span>
                                    @elseif($stageItem->status === 'assigned')
                                    <span class="text-yellow-400">Assigned</span>
                                    @elseif($stageItem->status === 'revision_requested')
                                    <span class="text-orange-400">Revision</span>
                                    @else
                                    <span class="text-gray-500">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- REVIEW HISTORY -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-300">Review History</h3>
                    </div>

                    @if($reviewLogs->isEmpty())
                        <div class="text-center py-4">
                            <div class="p-3 rounded-full bg-gray-800/50 inline-flex mb-3 backdrop-blur-sm">
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <p class="text-gray-500 text-sm">No review history yet.</p>
                        </div>
                    @else
                        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                            @foreach ($reviewLogs as $log)
                                <div class="border border-gray-700/30 rounded-xl p-4 bg-gray-900/30 hover:bg-gray-900/50 transition-colors backdrop-blur-sm">
                                    <div class="text-sm text-gray-300 mb-2">
                                        <strong class="font-semibold text-gray-200">
                                            {{ $log->user->name ?? 'System' }}
                                        </strong>
                                        
                                        <span class="text-gray-400">
                                            — {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                        </span>
                                        
                                        @php
                                            $stageName = $log->stage ? $log->stage->stage_name : 'Unknown Stage';
                                        @endphp
                                        
                                        <span class="text-gray-500 text-xs ml-1">
                                            ({{ $stageName }})
                                        </span>
                                        
                                        @switch($log->action)
                                            @case('approve')
                                            @case('approve_jump')
                                                <span class="ml-2 text-green-400 text-xs">✓</span>
                                                @break
                                            @case('revision')
                                                <span class="ml-2 text-amber-400 text-xs">↻</span>
                                                @break
                                            @case('reject')
                                                <span class="ml-2 text-red-400 text-xs">✗</span>
                                                @break
                                        @endswitch
                                    </div>

                                    @if ($log->notes)
                                        <div class="mt-2 text-gray-400 text-sm bg-gray-800/30 p-3 rounded-lg backdrop-blur-sm">
                                            {{ $log->notes }}
                                        </div>
                                    @endif

                                    <div class="text-xs text-gray-500 mt-2 flex justify-between">
                                        <span>
                                            @if($log->stage && $log->stage->stage_type !== 'unknown')
                                                <span class="px-2 py-1 rounded bg-gray-800/50 text-gray-300 backdrop-blur-sm">
                                                    {{ strtoupper($log->stage->stage_type) }}
                                                </span>
                                            @endif
                                        </span>
                                        <span>{{ $log->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- CONTRACT QUICK INFO -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-300">Contract Info</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-400 mb-1">Title</div>
                            <div class="font-medium text-gray-300 truncate">{{ $contract->title }}</div>
                        </div>
                        
                        <div>
                            <div class="text-sm font-medium text-gray-400 mb-1">Counterparty</div>
                            <div class="font-medium text-gray-300">{{ $contract->counterparty_name }}</div>
                        </div>
                        
                        <div>
                            <div class="text-sm font-medium text-gray-400 mb-1">Contract Value</div>
                            <div class="font-medium text-gray-300">
                                @if($contract->contract_value)
                                {{ number_format($contract->contract_value, 2) }} {{ $contract->currency ?? 'IDR' }}
                                @else
                                <span class="text-gray-500 italic">Not specified</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .gradient-border {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95));
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            backdrop-filter: blur(10px);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        textarea:focus, select:focus {
            outline: none;
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.3);
        }
        
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.15);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Character counters
            const notesTextarea = document.getElementById('finalNotes');
            const charCount = document.getElementById('finalCharCount');
            const approvalNotes = document.getElementById('approvalNotes');
            const approvalCharCount = document.getElementById('approvalCharCount');
            const revisionNotes = document.getElementById('revisionNotes');
            const revisionCharCount = document.getElementById('charCount');
            
            function updateCharCount(textarea, counter, maxLength) {
                if (textarea && counter) {
                    textarea.addEventListener('input', function() {
                        counter.textContent = `${this.value.length}/${maxLength}`;
                        if (this.value.length > maxLength * 0.9) {
                            counter.classList.add('text-amber-400');
                            counter.classList.remove('text-gray-500');
                        } else {
                            counter.classList.remove('text-amber-400');
                            counter.classList.add('text-gray-500');
                        }
                    });
                    // Initialize
                    textarea.dispatchEvent(new Event('input'));
                }
            }
            
            updateCharCount(notesTextarea, charCount, 1000);
            updateCharCount(approvalNotes, approvalCharCount, 1000);
            updateCharCount(revisionNotes, revisionCharCount, 2000);
            
            // Form validations with better UX
            const approveFinalForm = document.getElementById('approveFinalForm');
            if (approveFinalForm) {
                approveFinalForm.addEventListener('submit', function(e) {
                    const btn = document.getElementById('approveFinalBtn');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Processing...</span>';
                    btn.disabled = true;
                    
                    if (!confirm('Are you sure you want to complete the contract review? This action cannot be undone.')) {
                        e.preventDefault();
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                });
            }
            
            const rejectForm = document.getElementById('rejectForm');
            if (rejectForm) {
                rejectForm.addEventListener('submit', function(e) {
                    const rejectionReason = document.querySelector('textarea[name="rejection_reason"]');
                    if (!rejectionReason.value.trim() || rejectionReason.value.trim().length < 10) {
                        e.preventDefault();
                        alert('Please provide a detailed rejection reason (minimum 10 characters).');
                        rejectionReason.focus();
                    } else if (!confirm('Are you absolutely sure you want to reject this contract? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            }
            
            const revisionForm = document.getElementById('revisionForm');
            if (revisionForm) {
                revisionForm.addEventListener('submit', function(e) {
                    const revisionNotes = document.querySelector('textarea[name="revision_notes"]');
                    if (!revisionNotes.value.trim() || revisionNotes.value.trim().length < 10) {
                        e.preventDefault();
                        alert('Please provide detailed revision notes (minimum 10 characters).');
                        revisionNotes.focus();
                    } else if (!confirm('Are you sure you want to request revision? This will notify the relevant reviewers.')) {
                        e.preventDefault();
                    }
                });
            }
            
            // Auto-resize textareas
            const textareas = document.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight + 2) + 'px';
                });
                // Trigger initial resize
                setTimeout(() => textarea.dispatchEvent(new Event('input')), 100);
            });
        });
    </script>
</x-app-layout-dark>