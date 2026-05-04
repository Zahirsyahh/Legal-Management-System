{{-- resources/views/contracts/show-surat.blade.php --}}
@php
    $activeStage = $contract->activeStage();
    $isInProgress = $activeStage && $activeStage->status === 'in_progress';
    $isAssignedToMe = $activeStage && $activeStage->assigned_user_id === auth()->id();

    $isLegal = auth()->user()->hasAnyRole(['admin', 'legal']);
    
    $canApprove = $isLegal && 
                  $contract->status === \App\Models\Contract::STATUS_SUBMITTED;
    
    $canGenerateNumber = $isLegal && 
                         $contract->status === \App\Models\Contract::STATUS_FINAL_APPROVED &&
                         empty($contract->contract_number);

    $isNumberIssued = $contract->status === 'number_issued' && $contract->contract_number;
    
    $canUploadFinal = $contract->user_id === auth()->id() && 
                      $isNumberIssued && 
                      !$contract->surat_file_path;
@endphp

<x-app-layout-dark :title="'Letter Detail - ' . $contract->title">

    {{-- ── Scoped styles ── --}}
    @push('styles')
    <style>
        /* ── Glass card ── */
        .glass-card {
            background: rgba(17, 25, 40, 0.75);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: background .2s, border-color .2s;
        }
        .glass-card:hover {
            background: rgba(17, 25, 40, 0.85);
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* ── Animations ── */
        @keyframes pulse-slow {
            0%, 100% { opacity: .3; }
            50%       { opacity: .6; }
        }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }

        /* ── File preview entry ── */
        @keyframes fileSlideIn {
            from { opacity: 0; transform: translateY(-10px) scale(.97); }
            to   { opacity: 1; transform: translateY(0)     scale(1); }
        }
        /* ── File preview exit ── */
        @keyframes fileSlideOut {
            from { opacity: 1; transform: translateY(0)    scale(1);   max-height: 200px; }
            to   { opacity: 0; transform: translateY(-8px) scale(.96); max-height: 0;     }
        }
        /* ── Drop-zone pulse when dragging ── */
        @keyframes dropPulse {
            0%, 100% { border-color: rgba(168,85,247,.5); }
            50%       { border-color: rgba(168,85,247,.9); }
        }

        /* ── File preview card states ── */
        #selected-file-container {
            overflow: hidden;
            display: none;
            opacity: 0;
        }
        #selected-file-container.file-entering {
            display: block;
            animation: fileSlideIn .35s cubic-bezier(.22,.68,0,1.15) forwards;
        }
        #selected-file-container.file-visible {
            display: block;
            opacity: 1;
        }
        #selected-file-container.file-leaving {
            display: block;
            animation: fileSlideOut .3s ease forwards;
        }

        /* ── Drop-zone drag state ── */
        #file-dropzone.drag-over {
            animation: dropPulse .8s ease-in-out infinite;
            background: rgba(168,85,247,.07);
        }

        /* ── Upload button shimmer ── */
        @keyframes shimmer {
            from { background-position: -200% center; }
            to   { background-position:  200% center; }
        }
        .upload-shimmer {
            background: linear-gradient(90deg,
                rgba(147,51,234,.2) 0%,
                rgba(168,85,247,.35) 40%,
                rgba(147,51,234,.2) 100%);
            background-size: 200% auto;
            animation: shimmer 2.5s linear infinite;
        }

        /* ── Delete loading spinner ── */
        @keyframes spinFade {
            0%   { opacity: 0; transform: rotate(0deg)   scale(.7); }
            20%  { opacity: 1; transform: rotate(60deg)  scale(1); }
            100% { opacity: 1; transform: rotate(360deg) scale(1); }
        }
        .spin-fade { animation: spinFade .5s ease forwards; }

        /* ── Progress bar fill ── */
        @keyframes progressFill {
            from { width: 0%; }
            to   { width: 100%; }
        }
        .progress-fill { animation: progressFill .55s ease .1s both; }

        /* ── Check bounce ── */
        @keyframes checkBounce {
            0%   { transform: scale(0) rotate(-15deg); opacity: 0; }
            60%  { transform: scale(1.2) rotate(5deg);  opacity: 1; }
            100% { transform: scale(1)   rotate(0deg);  opacity: 1; }
        }
        .check-bounce { animation: checkBounce .45s cubic-bezier(.22,.68,0,1.2) .15s both; }

        /* ── Scrollbar ── */
        .scrollbar-thin::-webkit-scrollbar       { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
    </style>
    @endpush

    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        {{-- ══════════════════════════════════════════
             HEADER
        ══════════════════════════════════════════ --}}
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3 flex-wrap">

                        {{-- Status Badge --}}
                        <span class="px-3 py-1.5 text-xs rounded-full inline-flex items-center gap-1.5
                            @if($contract->status == 'draft')          bg-gray-700 text-gray-300
                            @elseif($contract->status == 'submitted')   bg-yellow-600/20  text-yellow-300  border border-yellow-600/30
                            @elseif($contract->status == 'final_approved') bg-blue-600/20 text-blue-300    border border-blue-600/30
                            @elseif($contract->status == 'number_issued')  bg-emerald-600/20 text-emerald-300 border border-emerald-600/30
                            @elseif($contract->status == 'released')    bg-green-600/20   text-green-300   border border-green-600/30
                            @else bg-gray-700/50 text-gray-300 @endif">

                            @if($contract->status == 'draft')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            @elseif($contract->status == 'submitted')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($contract->status == 'final_approved')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            @elseif($contract->status == 'number_issued')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h-1.5a2 2 0 00-2 2v0a2 2 0 002 2H15m3-4h-1.5a2 2 0 00-2 2v0a2 2 0 002 2H18m-9 4h1m-1-4h1m-1-4h1m9 0v8a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2z"/></svg>
                            @elseif($contract->status == 'released')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif

                            {{ strtoupper(str_replace('_', ' ', $contract->status_label)) }}
                        </span>

                        {{-- Contract Number Badge --}}
                        @if($contract->contract_number)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600/20 text-purple-300 border border-purple-500/30 rounded-full text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h-1.5a2 2 0 00-2 2v0a2 2 0 002 2H15m3-4h-1.5a2 2 0 00-2 2v0a2 2 0 002 2H18m-9 4h1m-1-4h1m-1-4h1m9 0v8a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2z"/>
                            </svg>
                            NUMBER: {{ $contract->contract_number }}
                        </span>
                        @endif

                        @if($isInProgress)
                        <span class="flex items-center gap-1">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </span>
                            <span class="text-blue-400 text-xs font-medium">In Progress</span>
                        </span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                        {{ $contract->title }}
                    </h1>

                    <div class="flex items-center gap-4 text-gray-400 flex-wrap">
                        <span class="flex items-center gap-1.5 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $contract->user->nama_user ?? $contract->user->name ?? '-' }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1.5 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $contract->created_at->format('d M Y, H:i') }}
                        </span>
                        <span>•</span>
                        <span class="text-sm">Department: <span class="text-gray-300 font-medium">{{ $contract->department_code ?? '-' }}</span></span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <a href="{{ route('contracts.index') }}"
                       class="flex items-center justify-center px-5 py-3 bg-gray-800/80 hover:bg-gray-700 rounded-xl text-gray-300 hover:text-white transition-all duration-300 border border-gray-700/50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl">
                <p class="text-green-400">{!! session('success') !!}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                <p class="text-red-400">{{ session('error') }}</p>
            </div>
        @endif

        {{-- ══════════════════════════════════════════
             OFFICIAL NUMBER BANNER
        ══════════════════════════════════════════ --}}
        @if($contract->contract_number)
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-900/30 to-cyan-900/20 border border-blue-500/30 rounded-xl shadow-lg shadow-blue-500/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-600/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Official Letter Number</p>
                        <p class="text-lg font-bold text-blue-300 font-mono tracking-wide">{{ $contract->contract_number }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Generated on: {{ $contract->number_issued_at ? $contract->number_issued_at->format('d M Y, H:i') : $contract->updated_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════
             PROGRESS BAR
        ══════════════════════════════════════════ --}}
        @if($contract->isInReviewStageSystem())
            <div class="mb-6 relative">
                @if($isInProgress)
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl blur opacity-20 animate-pulse-slow"></div>
                @endif
                <div class="relative">
                    @include('components.progress-bar', ['contract' => $contract])
                </div>
            </div>
        @else
            @php
                $steps = [
                    ['status' => 'draft',          'label' => 'Draft',         'icon' => 'document'],
                    ['status' => 'submitted',       'label' => 'Submitted',     'icon' => 'clock'],
                    ['status' => 'final_approved',  'label' => 'Legal Approved','icon' => 'check'],
                    ['status' => 'number_issued',   'label' => 'Number Issued', 'icon' => 'number'],
                    ['status' => 'released',        'label' => 'Completed',     'icon' => 'check-circle'],
                ];
                $currentIndex = array_search($contract->status, array_column($steps, 'status'));
                if ($currentIndex === false) $currentIndex = 0;
            @endphp

            <div class="mb-6 bg-gradient-to-br from-gray-900/50 via-gray-900/30 to-gray-950/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-800/50">
                <div class="flex items-center justify-between relative">
                    {{-- Progress Line --}}
                    <div class="absolute left-0 top-1/2 w-full h-1 bg-gray-800 -translate-y-1/2 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 transition-all duration-1000"
                             style="width: {{ ($currentIndex / (count($steps) - 1)) * 100 }}%"></div>
                    </div>

                    @foreach($steps as $index => $step)
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-500
                                @if($index < $currentIndex)
                                    bg-gradient-to-r from-green-500 to-emerald-500 shadow-lg shadow-green-500/30
                                @elseif($index == $currentIndex)
                                    bg-gradient-to-r from-blue-500 to-purple-500 shadow-lg shadow-blue-500/30 scale-110 ring-4 ring-blue-500/20
                                @else
                                    bg-gray-800 border-2 border-gray-700
                                @endif">
                                @if($index < $currentIndex)
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif($index == $currentIndex)
                                    @if($step['icon'] == 'document')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @elseif($step['icon'] == 'clock')
                                        <svg class="w-6 h-6 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif($step['icon'] == 'check')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    @elseif($step['icon'] == 'number')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                    @else
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                @else
                                    <span class="text-gray-500 font-medium">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <div class="mt-3 text-center">
                                <p class="text-sm font-medium {{ $index <= $currentIndex ? 'text-white' : 'text-gray-500' }}">
                                    {{ $step['label'] }}
                                </p>
                                @if($index == $currentIndex)
                                    <p class="text-xs text-blue-400 mt-1">Current</p>
                                @endif
                                @if($index == 3 && $contract->contract_number && $index <= $currentIndex)
                                    <p class="text-xs text-green-400 mt-1 font-mono truncate max-w-[120px]">
                                        {{ Str::limit($contract->contract_number, 10) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 pt-4 border-t border-gray-700/50 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-gray-400">Status</p>
                        <p class="text-lg font-semibold {{ $currentIndex === count($steps)-1 ? 'text-green-400' : 'text-blue-400' }}">
                            {{ $steps[$currentIndex]['label'] }}
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-400">Progress</p>
                        <p class="text-lg font-semibold text-gray-300">
                            {{ round(($currentIndex / (count($steps) - 1)) * 100) }}%
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════
             MAIN CONTENT GRID
        ══════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── LEFT COLUMN ── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Letter Information --}}
                <div class="glass-card rounded-xl p-6 @if($isInProgress) border border-blue-500/30 shadow-lg shadow-blue-500/10 @endif">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-300">Letter Information</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Letter Title</p>
                                <p class="text-gray-300 font-medium">{{ $contract->title }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Letter Date</p>
                                <p class="text-gray-300">
                                    {{ $contract->effective_date ? \Carbon\Carbon::parse($contract->effective_date)->format('d F Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Department</p>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1.5 bg-blue-500/10 text-blue-400 rounded-lg text-sm font-mono border border-blue-500/20">
                                        {{ $contract->department_code ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Letter Number</p>
                                @if($contract->contract_number)
                                    <div class="mt-1 p-3 bg-gradient-to-r from-purple-900/30 to-pink-900/30 border border-purple-500/30 rounded-lg">
                                        <p class="text-lg font-bold font-mono text-purple-400">{{ $contract->contract_number }}</p>
                                    </div>
                                @else
                                    <p class="text-gray-400 flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        Not yet generated
                                    </p>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Description</p>
                                <p class="text-gray-300 leading-relaxed">
                                    {{ $contract->description ?? 'No description provided' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Review History --}}
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-300 mb-4 flex items-center justify-between">
                        <span>Review History</span>
                        <span class="text-xs text-gray-500 font-normal">
                            {{ $reviewLogs->count() ?? 0 }} {{ Str::plural('entry', $reviewLogs->count() ?? 0) }}
                        </span>
                    </h3>

                    @if(empty($reviewLogs) || $reviewLogs->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-12 h-12 mx-auto mb-3 text-gray-500">
                                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-gray-500 text-sm">No review history yet.</p>
                            <p class="text-gray-400 text-xs mt-1">Review actions will appear here</p>
                        </div>
                    @else
                        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2 scrollbar-thin">
                            @foreach ($reviewLogs as $log)
                                @php
                                    $metadata = is_array($log->metadata ?? null) ? $log->metadata : [];
                                    $displayNotes =
                                        !empty(trim($log->notes ?? ''))                        ? $log->notes
                                        : (!empty(trim($metadata['notes'] ?? ''))              ? $metadata['notes']
                                        : (!empty(trim($metadata['revision_notes'] ?? ''))     ? $metadata['revision_notes']
                                        : (!empty(trim($metadata['rejection_reason'] ?? ''))   ? $metadata['rejection_reason']
                                        : (!empty(trim($metadata['response'] ?? ''))           ? $metadata['response']
                                        : (!empty(trim($metadata['feedback'] ?? ''))           ? $metadata['feedback']
                                        : null)))));

                                    $actionColors = [
                                        'surat_created'          => 'bg-gray-500/10  text-gray-400   border-gray-500/30',
                                        'surat_submitted'        => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                        'surat_approved'         => 'bg-blue-500/10   text-blue-400   border-blue-500/30',
                                        'surat_number_generated' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                        'file_uploaded'          => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                                        'file_deleted'           => 'bg-red-500/10    text-red-400    border-red-500/30',
                                        'surat_deleted'          => 'bg-red-500/10    text-red-400    border-red-500/30',
                                        'surat_note_sent'        => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                    ];
                                    $actionColor = $actionColors[$log->action] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/30';

                                    $actionLabels = [
                                        'surat_created'          => 'Draft Created',
                                        'surat_submitted'        => 'Submitted to Legal',
                                        'surat_approved'         => 'Approved by Legal',
                                        'surat_number_generated' => 'Number Generated',
                                        'file_uploaded'          => 'File Uploaded',
                                        'file_deleted'           => 'File Deleted',
                                        'surat_deleted'          => 'Letter Deleted',
                                        'surat_note_sent'        => 'Note from Legal',
                                    ];
                                    $actionLabel = $actionLabels[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action));
                                @endphp

                                <div class="border border-gray-700/30 rounded-lg p-4 bg-gray-900/30 hover:bg-gray-900/50 transition-colors">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500/30 to-indigo-500/30 flex items-center justify-center flex-shrink-0 border border-blue-500/30">
                                                <span class="text-sm font-semibold text-blue-400">
                                                    {{ substr($log->user->nama_user ?? 'S', 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-sm font-semibold text-gray-200">
                                                        {{ $log->user->nama_user ?? 'System' }}
                                                    </span>
                                                    @if($log->user && $log->user->jabatan)
                                                        @php
                                                            $roleDisplay = match($log->user->jabatan) {
                                                                'ADMIN' => 'Admin',
                                                                'LEGAL' => 'Legal',
                                                                'USER'  => 'User',
                                                                default => $log->user->jabatan
                                                            };
                                                        @endphp
                                                        <span class="px-1.5 py-0.5 rounded bg-gray-800 text-xs text-gray-300">{{ $roleDisplay }}</span>
                                                    @endif
                                                    <span class="text-xs text-gray-500 ml-1">• {{ $log->created_at->format('M d, H:i') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-xs px-2.5 py-1.5 rounded-full border font-medium {{ $actionColor }}">
                                            {{ $actionLabel }}
                                        </span>
                                    </div>

                                    @if($displayNotes)
                                        <div class="mt-2 p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                            <div class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                                </svg>
                                                <span class="text-sm font-medium text-gray-300">Notes:</span>
                                            </div>
                                            <div class="ml-6 mt-1">
                                                <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">{{ $displayNotes }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-3 pt-3 border-t border-gray-700/30 flex justify-between items-center">
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                                        </div>
                                        @if($log->created_at->isToday())
                                            <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 rounded-full text-xs">Today</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>{{-- END LEFT COLUMN --}}

            {{-- ── RIGHT COLUMN ── --}}
            <div class="space-y-6">

                {{-- ════════════════════════════════════
                     FILE MANAGEMENT CARD
                ════════════════════════════════════ --}}
                <div class="glass-card rounded-xl p-6 @if($isInProgress) border border-blue-500/20 shadow-md shadow-blue-500/10 @endif">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-orange-500 flex items-center justify-center shadow-lg shadow-red-500/20">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-md font-semibold text-gray-300">Letter File</h3>

                        @if($contract->surat_file_path)
                            @php $fileExists = Storage::disk('public')->exists($contract->surat_file_path); @endphp
                            @if($fileExists)
                                <span class="ml-auto px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs border border-green-500/30">✓ Uploaded</span>
                            @else
                                <span class="ml-auto px-2 py-0.5 bg-red-500/20 text-red-400 rounded-full text-xs border border-red-500/30">⚠ File Missing</span>
                            @endif
                        @endif
                    </div>

                    {{-- ── Existing file info ── --}}
                    @if($contract->surat_file_path)
                        @php $fileExists = Storage::disk('public')->exists($contract->surat_file_path); @endphp

                        @if($fileExists)
                            {{-- File info card --}}
                            <div id="existing-file-card"
                                 class="bg-gray-900/50 rounded-lg p-3 border border-gray-700/50 mb-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-600/20 to-orange-600/20 flex items-center justify-center border border-red-500/30 flex-shrink-0">
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-300 truncate" title="{{ basename($contract->surat_file_path) }}">
                                            {{ basename($contract->surat_file_path) }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                            <span>{{ number_format($contract->surat_file_size / 1024, 0) }} KB</span>
                                            <span>•</span>
                                            <span>{{ $contract->updated_at->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <a href="{{ route('surat.preview', $contract) }}" target="_blank"
                                           class="p-1.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded-lg transition-all duration-300 border border-blue-500/30 hover:border-blue-500/50"
                                           title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('surat.download', $contract) }}"
                                           class="p-1.5 bg-green-600/20 hover:bg-green-600/30 text-green-400 rounded-lg transition-all duration-300 border border-green-500/30 hover:border-green-500/50"
                                           title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                        @if($contract->status !== \App\Models\Contract::STATUS_RELEASED)
                                            <form id="delete-file-form"
                                                  action="{{ route('surat.delete-file', $contract->id) }}"
                                                  method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                        id="delete-file-btn"
                                                        onclick="confirmDeleteFile()"
                                                        class="p-1.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg transition-all duration-300 border border-red-500/30 hover:border-red-500/50"
                                                        title="Delete">
                                                    <svg class="w-4 h-4" id="delete-file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- File missing --}}
                            <div class="bg-red-500/10 rounded-lg p-4 border border-red-500/30 mb-3">
                                <div class="flex items-center gap-2 text-red-400 mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.959-1.333-2.73 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span class="font-medium">File Not Found</span>
                                </div>
                                <p class="text-sm text-red-300">
                                    File path: <code class="bg-red-900/30 px-2 py-0.5 rounded text-xs">{{ $contract->surat_file_path }}</code>
                                </p>
                                <p class="text-xs text-red-300 mt-1">Please re-upload the file or contact the administrator if the issue persists.</p>
                            </div>
                        @endif
                    @else
                        {{-- No file yet --}}
                        <div class="bg-gray-900/30 rounded-lg p-4 text-center border border-dashed border-gray-700/50 mb-3">
                            <div class="w-10 h-10 mx-auto rounded-full bg-gradient-to-br from-red-600/10 to-orange-600/10 flex items-center justify-center border border-red-500/20">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="mt-2 text-sm text-gray-400">No file uploaded yet</p>
                            <p class="text-xs text-gray-500 mt-0.5">PDF, max 10 MB</p>
                        </div>
                    @endif

                    {{-- ── Upload form ── --}}
                    @if($contract->status !== \App\Models\Contract::STATUS_RELEASED)
                        <form id="upload-form"
                              action="{{ route('surat.upload', $contract->id) }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf

                            {{-- Drop-zone --}}
                            <label id="file-dropzone"
                                   for="file-upload"
                                   class="flex flex-col items-center justify-center w-full px-3 py-4
                                          bg-gray-800/50 hover:bg-gray-800/80 rounded-xl
                                          border border-dashed border-gray-700/60 hover:border-purple-500/50
                                          cursor-pointer transition-all duration-300">
                                <svg id="dropzone-icon" class="w-6 h-6 mb-1.5 text-gray-400 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span class="text-xs text-gray-400 text-center leading-relaxed">
                                    <span class="text-purple-400 font-semibold">Click to select</span> or drag & drop
                                    <br><span class="text-gray-600">PDF only · Max 10 MB</span>
                                </span>
                                <input id="file-upload"
                                       type="file"
                                       name="file"
                                       accept=".pdf,application/pdf"
                                       required
                                       class="hidden">
                            </label>

                            {{-- ── File preview (after selection) ── --}}
                            <div id="selected-file-container" class="mt-2">
                                <div class="rounded-xl border border-purple-500/35 bg-gradient-to-br from-purple-500/8 to-violet-500/5 overflow-hidden">
                                    {{-- Header bar --}}
                                    <div class="flex items-center gap-2 px-3 pt-3 pb-2.5 border-b border-purple-500/15">
                                        <div class="check-bounce w-4 h-4 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0 shadow shadow-purple-500/40">
                                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="text-purple-400 text-xs font-bold tracking-wide uppercase">File Ready</span>
                                    </div>

                                    {{-- Body --}}
                                    <div class="px-3 py-2.5 flex items-center gap-3">
                                        <div class="w-8 h-9 rounded-lg bg-red-500/12 border border-red-500/25 flex flex-col items-center justify-center flex-shrink-0 gap-0.5">
                                            <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5z"/>
                                            </svg>
                                            <span class="text-red-400 text-[8px] font-bold tracking-widest">PDF</span>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p id="selected-file-name" class="text-xs font-semibold text-gray-200 truncate"></p>
                                            <p id="selected-file-size" class="text-xs text-gray-500 mt-0.5 font-mono"></p>
                                            {{-- Progress bar (cosmetic) --}}
                                            <div class="mt-1.5 h-0.5 w-full bg-gray-700/60 rounded-full overflow-hidden">
                                                <div class="progress-fill h-full bg-gradient-to-r from-purple-500 to-violet-400 rounded-full"></div>
                                            </div>
                                        </div>

                                        <button type="button"
                                                onclick="clearFileSelection()"
                                                class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-gray-600 hover:text-red-400 hover:bg-red-500/10 transition-all duration-150">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit button --}}
                            <button type="submit"
                                    id="upload-submit-btn"
                                    disabled
                                    class="mt-3 w-full px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-300 flex items-center justify-center gap-2
                                           bg-gray-700/40 text-gray-500 border border-gray-700/40 cursor-not-allowed"
                                    data-idle-class="bg-gray-700/40 text-gray-500 border border-gray-700/40 cursor-not-allowed"
                                    data-ready-class="upload-shimmer text-purple-300 border border-purple-500/40 hover:border-purple-400/60 cursor-pointer">
                                <svg id="upload-btn-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/>
                                </svg>
                                <span id="upload-btn-text">
                                    {{ $contract->surat_file_path ? 'Select a file to replace' : 'Select a file first' }}
                                </span>
                            </button>
                        </form>
                    @endif
                </div>

                {{-- ════════════════════════════════════
                     ACTIONS CARD
                ════════════════════════════════════ --}}
                <div class="glass-card rounded-xl p-6 border border-gray-700/50">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <h3 class="text-md font-semibold text-gray-300">Actions</h3>
                    </div>

                    @php
                        $canDelete   = false;
                        $deleteReason = '';
                        if (auth()->user()->hasAnyRole(['admin'])) {
                            $canDelete    = true;
                            $deleteReason = 'Admin can delete letters at any time';
                        } elseif (auth()->user()->hasRole('legal') && in_array($contract->status, ['draft','submitted','final_approved'])) {
                            $canDelete    = true;
                            $deleteReason = 'Legal can delete letters before the number is generated';
                        } elseif ($contract->user_id === auth()->id() && $contract->status === 'draft') {
                            $canDelete    = true;
                            $deleteReason = 'Owner can delete their own draft';
                        }
                    @endphp

                    <div class="space-y-3">

                        {{-- Approve (submitted → final_approved) --}}
                        @if($canApprove)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-400">Step 1: Approve letter request</span>
                                </div>
                                <form action="{{ route('surat.approve', $contract) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Approve this letter request?\n\nStatus will change to Final Approved.')"
                                            class="w-full px-4 py-2.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 text-sm font-medium rounded-lg transition-all duration-300 border border-blue-500/30 hover:border-blue-500/50">
                                        <span class="flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            Approve Letter Request
                                        </span>
                                    </button>
                                </form>
                            </div>
                        @endif

                        {{-- Send Notes (legal/admin, status = submitted) --}}
                        @if($isLegal && $contract->status === \App\Models\Contract::STATUS_SUBMITTED)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-400">Send notes to the requestor</span>
                                </div>
                                <button type="button"
                                        onclick="document.getElementById('modal-send-note').classList.remove('hidden')"
                                        class="w-full px-4 py-2.5 bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400 text-sm font-medium rounded-lg transition-all duration-300 border border-yellow-500/30 hover:border-yellow-500/50">
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        Send Notes to User
                                    </span>
                                </button>
                            </div>
                        @endif

                        {{-- Generate Number (final_approved → number_issued) --}}
                        @if($canGenerateNumber)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-400">Step 2: Generate official number</span>
                                </div>
                                <form action="{{ route('surat.generate-number', $contract) }}" method="POST" id="generateForm">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Generate official letter number?\n\nStatus will change to Number Issued.')"
                                            class="w-full px-4 py-2.5 bg-green-600/20 hover:bg-green-600/30 text-green-400 text-sm font-medium rounded-lg transition-all duration-300 border border-green-500/30 hover:border-green-500/50">
                                        <span class="flex items-center justify-center gap-2" id="buttonText">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                            Generate Letter Number
                                        </span>
                                        <span class="hidden items-center justify-center gap-2" id="loadingSpinner">
                                            <svg class="animate-spin h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Processing...
                                        </span>
                                    </button>
                                </form>
                            </div>
                        @endif

                        {{-- Submit to Legal (draft → submitted, owner only) --}}
                        @if($contract->status === 'draft' && $contract->user_id === auth()->id())
                            <form action="{{ route('surat.submit', $contract) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Submit this letter for Legal review?')"
                                        class="w-full px-4 py-2.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 text-sm font-medium rounded-lg transition-all duration-300 border border-blue-500/30 hover:border-blue-500/50">
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                                        Submit to Legal Department
                                    </span>
                                </button>
                            </form>
                        @endif

                        {{-- Upload Final reminder (number_issued, no final file yet) --}}
                        @if($canUploadFinal)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <p class="text-xs font-medium text-gray-400">Upload the signed final letter</p>
                                <p class="text-center text-xs text-gray-500 mt-1">Use the upload form in the "Letter File" section above</p>
                            </div>
                        @endif

                        {{-- Delete / Reject --}}
                        @if($canDelete)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.959-1.333-2.73 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-400">{{ $deleteReason }}</span>
                                </div>
                                <button type="button"
                                        onclick="document.getElementById('modal-delete-surat').classList.remove('hidden')"
                                        class="w-full px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm font-medium rounded-lg transition-all duration-300 border border-red-500/30 hover:border-red-500/50">
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Reject / Delete Letter
                                    </span>
                                </button>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Quick Info --}}
                <div class="glass-card rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-md font-semibold text-gray-300">Quick Information</h3>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-800/50">
                            <span class="text-xs text-gray-500">Created</span>
                            <span class="text-xs text-gray-300">{{ $contract->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($contract->submitted_at)
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-800/50">
                            <span class="text-xs text-gray-500">Submitted</span>
                            <span class="text-xs text-gray-300">{{ $contract->submitted_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($contract->final_approved_at)
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-800/50">
                            <span class="text-xs text-gray-500">Approved</span>
                            <span class="text-xs text-gray-300">{{ $contract->final_approved_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($contract->number_issued_at)
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-800/50">
                            <span class="text-xs text-gray-500">Number Issued</span>
                            <span class="text-xs text-emerald-400">{{ $contract->number_issued_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($contract->released_at)
                        <div class="flex justify-between items-center py-1.5">
                            <span class="text-xs text-gray-500">Executed</span>
                            <span class="text-xs text-green-400">{{ $contract->released_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Review Progress --}}
                @if($contract->isInReviewStageSystem())
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-md font-semibold text-gray-300 mb-3">Review Progress</h3>
                    <div class="space-y-3">
                        @foreach($contract->reviewStages->sortBy('sequence') as $reviewStage)
                            <div class="flex items-start gap-2">
                                <div class="flex-shrink-0 mt-0.5">
                                    @if($reviewStage->status === 'completed')
                                        <div class="w-5 h-5 rounded-full bg-green-500/20 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @elseif($reviewStage->status === 'in_progress')
                                        <div class="relative">
                                            <div class="absolute inset-0 w-5 h-5 rounded-full bg-blue-500 animate-ping opacity-30"></div>
                                            <div class="w-5 h-5 rounded-full bg-blue-500/30 flex items-center justify-center">
                                                <div class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></div>
                                            </div>
                                        </div>
                                    @elseif($reviewStage->status === 'assigned')
                                        <div class="w-5 h-5 rounded-full bg-yellow-500/20 flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 rounded-full bg-yellow-400"></div>
                                        </div>
                                    @elseif($reviewStage->status === 'revision_requested')
                                        <div class="w-5 h-5 rounded-full bg-orange-500/20 flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                                        </div>
                                    @else
                                        <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                            <span class="text-[10px] text-gray-400">{{ $reviewStage->sequence }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-medium text-gray-300">
                                            @if($reviewStage->is_user_stage) User Review
                                            @else {{ Str::limit($reviewStage->stage_name, 15) }}
                                            @endif
                                        </div>
                                        <div class="text-[10px]">
                                            @if($reviewStage->status === 'completed')     <span class="text-green-400">Completed</span>
                                            @elseif($reviewStage->status === 'in_progress') <span class="text-blue-400">In Progress</span>
                                            @elseif($reviewStage->status === 'assigned')    <span class="text-yellow-400">Assigned</span>
                                            @elseif($reviewStage->status === 'revision_requested') <span class="text-orange-400">Revision</span>
                                            @else <span class="text-gray-500">Pending</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-400 truncate mt-0.5">
                                        {{ $reviewStage->assignedUser->name ?? 'Unassigned' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>{{-- END RIGHT COLUMN --}}
        </div>{{-- END MAIN GRID --}}

    </div>

    {{-- ══════════════════════════════════════════
         MODAL: Send Notes to User
    ══════════════════════════════════════════ --}}
    <div id="modal-send-note"
         class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
        <div class="relative bg-gray-900 border border-yellow-500/30 rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-5 border-b border-gray-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-white">Send Notes to User</h3>
                        <p class="text-xs text-gray-400">Letter status <strong class="text-yellow-400">will not change</strong></p>
                    </div>
                </div>
                <button onclick="document.getElementById('modal-send-note').classList.add('hidden')"
                        class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('surat.send-note', $contract) }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                    <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center text-sm font-bold text-blue-400">
                        {{ substr($contract->user->nama_user ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-200">{{ $contract->user->nama_user ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $contract->user->email ?? '-' }}</p>
                    </div>
                    <span class="ml-auto text-xs px-2 py-1 bg-blue-500/20 text-blue-400 rounded-full border border-blue-500/30">Recipient</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">
                        Notes / Required Documents <span class="text-red-400">*</span>
                    </label>
                    <textarea name="notes" rows="5" required minlength="10" maxlength="2000"
                              placeholder="Example: Please complete the following documents:&#10;1. Valid national ID&#10;2. Signed power of attorney&#10;3. Copy of taxpayer ID"
                              class="w-full px-4 py-3 bg-gray-800/60 border border-gray-700/50 focus:border-yellow-500/50 focus:ring-1 focus:ring-yellow-500/30 rounded-xl text-gray-200 text-sm placeholder-gray-500 resize-none transition-all duration-200 outline-none"
                              oninput="document.getElementById('note-char-count').textContent = this.value.length"></textarea>
                    <div class="flex justify-between mt-1">
                        <p class="text-xs text-gray-500">Minimum 10 characters</p>
                        <p class="text-xs text-gray-500"><span id="note-char-count">0</span>/2000</p>
                    </div>
                </div>

                <div class="flex items-start gap-2 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                    <svg class="w-4 h-4 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs text-yellow-300">
                        This note will be sent via <strong>email</strong> and <strong>system notification</strong> to the requestor.
                        The letter status remains <strong>SUBMITTED</strong>.
                    </p>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button"
                            onclick="document.getElementById('modal-send-note').classList.add('hidden')"
                            class="flex-1 px-4 py-2.5 bg-gray-700/60 hover:bg-gray-700 text-gray-300 text-sm rounded-xl transition-colors border border-gray-600/50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400 text-sm font-medium rounded-xl transition-all duration-200 border border-yellow-500/30 hover:border-yellow-500/50 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Send Notes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         MODAL: Delete / Reject Letter
    ══════════════════════════════════════════ --}}
    <div id="modal-delete-surat"
         class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
        <div class="relative bg-gray-900 border border-red-500/30 rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-5 border-b border-gray-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-white">Reject / Delete Letter</h3>
                        <p class="text-xs text-gray-400">This action <strong class="text-red-400">cannot be undone</strong></p>
                    </div>
                </div>
                <button onclick="document.getElementById('modal-delete-surat').classList.add('hidden')"
                        class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('surat.destroy', $contract) }}" method="POST" class="p-5 space-y-4">
                @csrf
                @method('DELETE')

                <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                    <p class="text-xs text-gray-400 mb-1">Letter to be deleted:</p>
                    <p class="text-sm font-semibold text-gray-200">{{ $contract->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Status: {{ strtoupper(str_replace('_', ' ', $contract->status)) }}</p>
                </div>

                @php $isOwnerDraft = ($contract->user_id === auth()->id() && $contract->status === 'draft'); @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">
                        Rejection / Deletion Reason
                        @if(!$isOwnerDraft) <span class="text-red-400">*</span>
                        @else <span class="text-gray-500">(optional)</span>
                        @endif
                    </label>
                    <textarea name="delete_reason" rows="4"
                              {{ !$isOwnerDraft ? 'required' : '' }}
                              minlength="10" maxlength="1000"
                              placeholder="Example: Incomplete documents, incorrect letter format, etc."
                              class="w-full px-4 py-3 bg-gray-800/60 border border-gray-700/50 focus:border-red-500/50 focus:ring-1 focus:ring-red-500/30 rounded-xl text-gray-200 text-sm placeholder-gray-500 resize-none transition-all duration-200 outline-none"
                              oninput="document.getElementById('delete-char-count').textContent = this.value.length"></textarea>
                    <div class="flex justify-between mt-1">
                        <p class="text-xs text-gray-500">{{ !$isOwnerDraft ? 'Required, minimum 10 characters' : 'Optional' }}</p>
                        <p class="text-xs text-gray-500"><span id="delete-char-count">0</span>/1000</p>
                    </div>
                </div>

                @if(!$isOwnerDraft)
                <div class="flex items-start gap-2 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                    <svg class="w-4 h-4 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs text-red-300">
                        This reason will be sent to <strong>{{ $contract->user->nama_user ?? 'the user' }}</strong> via email and system notification.
                    </p>
                </div>
                @endif

                <div class="flex gap-3 pt-1">
                    <button type="button"
                            onclick="document.getElementById('modal-delete-surat').classList.add('hidden')"
                            class="flex-1 px-4 py-2.5 bg-gray-700/60 hover:bg-gray-700 text-gray-300 text-sm rounded-xl transition-colors border border-gray-600/50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm font-medium rounded-xl transition-all duration-200 border border-red-500/30 hover:border-red-500/50 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Yes, Delete Letter
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ──────────────────────────────────────────
           FILE UPLOAD INTERACTIONS
        ────────────────────────────────────────── */
        const fileInput     = document.getElementById('file-upload');
        const dropzone      = document.getElementById('file-dropzone');
        const previewCard   = document.getElementById('selected-file-container');
        const nameDisplay   = document.getElementById('selected-file-name');
        const sizeDisplay   = document.getElementById('selected-file-size');
        const submitBtn     = document.getElementById('upload-submit-btn');
        const submitText    = document.getElementById('upload-btn-text');

        const LABEL_REPLACE = '{{ $contract->surat_file_path ? "Replace File" : "Upload File" }}';

        // ── Byte formatter ──────────────────────
        function fmtBytes(b) {
            if (b < 1024)    return b + ' B';
            if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
            return (b / 1048576).toFixed(2) + ' MB';
        }

        // ── Show preview with slide-in animation ─
        function showPreview(file) {
            if (nameDisplay) nameDisplay.textContent = file.name;
            if (sizeDisplay) sizeDisplay.textContent = fmtBytes(file.size);

            // Reset animation state
            previewCard.classList.remove('file-visible', 'file-leaving', 'file-entering');
            void previewCard.offsetWidth; // reflow
            previewCard.classList.add('file-entering');

            // After animation ends, lock to visible
            previewCard.addEventListener('animationend', function onEnd() {
                previewCard.classList.remove('file-entering');
                previewCard.classList.add('file-visible');
                previewCard.removeEventListener('animationend', onEnd);
            });

            // Activate submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.className = submitBtn.className
                    .replace(submitBtn.dataset.idleClass, '')
                    .trim();
                submitBtn.classList.add(...submitBtn.dataset.readyClass.split(' '));
            }
            if (submitText) submitText.textContent = LABEL_REPLACE;
        }

        // ── Hide preview with slide-out animation ─
        function hidePreview() {
            previewCard.classList.remove('file-entering', 'file-visible');
            previewCard.classList.add('file-leaving');

            previewCard.addEventListener('animationend', function onEnd() {
                previewCard.classList.remove('file-leaving');
                previewCard.style.display = 'none'; // ensure hidden after animation
                previewCard.removeEventListener('animationend', onEnd);
            });

            // Reset submit button
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.remove(...submitBtn.dataset.readyClass.split(' '));
                submitBtn.classList.add(...submitBtn.dataset.idleClass.split(' '));
            }
            if (submitText) submitText.textContent = '{{ $contract->surat_file_path ? "Select a file to replace" : "Select a file first" }}';
        }

        // ── File input change ───────────────────
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const f = this.files[0];
                if (!f) return hidePreview();
                if (f.type !== 'application/pdf') {
                    alert('Only PDF files are allowed!');
                    this.value = '';
                    return hidePreview();
                }
                if (f.size > 10 * 1024 * 1024) {
                    alert('Maximum file size is 10 MB!');
                    this.value = '';
                    return hidePreview();
                }
                showPreview(f);
            });
        }

        // ── Drag & drop ─────────────────────────
        if (dropzone) {
            dropzone.addEventListener('dragover', function (e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
            ['dragleave', 'dragend'].forEach(evt => dropzone.addEventListener(evt, function () {
                this.classList.remove('drag-over');
            }));
            dropzone.addEventListener('drop', function (e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                const f = e.dataTransfer.files[0];
                if (!f) return;
                const dt = new DataTransfer();
                dt.items.add(f);
                fileInput.files = dt.files;
                fileInput.dispatchEvent(new Event('change'));
            });
        }

        /* ──────────────────────────────────────────
           DELETE FILE — animated spinner
        ────────────────────────────────────────── */
        window.confirmDeleteFile = function () {
            if (!confirm('Delete this file?\n\nThis action cannot be undone.')) return;

            const btn  = document.getElementById('delete-file-btn');
            const icon = document.getElementById('delete-file-icon');

            if (btn && icon) {
                btn.disabled = true;
                // Swap to spinner SVG with fade-in spin animation
                icon.outerHTML = `
                    <svg id="delete-file-icon"
                         class="w-4 h-4 spin-fade"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>`;
            }

            // Fade out the entire file card, then submit
            const card = document.getElementById('existing-file-card');
            if (card) {
                card.style.transition = 'opacity .4s ease, transform .4s ease';
                card.style.opacity    = '0';
                card.style.transform  = 'scale(.97)';
                setTimeout(() => document.getElementById('delete-file-form').submit(), 420);
            } else {
                document.getElementById('delete-file-form').submit();
            }
        };

        /* ──────────────────────────────────────────
           GENERATE NUMBER — loading spinner
        ────────────────────────────────────────── */
        const generateForm = document.getElementById('generateForm');
        if (generateForm) {
            generateForm.addEventListener('submit', function () {
                const btn    = this.querySelector('button[type="submit"]');
                const text   = document.getElementById('buttonText');
                const spinner = document.getElementById('loadingSpinner');
                if (btn && text && spinner) {
                    btn.disabled = true;
                    text.classList.add('hidden');
                    spinner.classList.remove('hidden');
                    spinner.classList.add('flex');
                }
            });
        }
    });

    // ── Clear file selection (global) ───────────
    window.clearFileSelection = function () {
        const fi = document.getElementById('file-upload');
        if (fi) fi.value = '';
        // Trigger the hide animation via the change event path
        fi && fi.dispatchEvent(new Event('change'));
    };
    </script>
    @endpush

</x-app-layout-dark>