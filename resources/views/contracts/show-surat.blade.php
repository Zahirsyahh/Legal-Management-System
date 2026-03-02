{{-- resources/views/contracts/show-surat.blade.php --}}
@php
    $activeStage = $contract->activeStage();
    $isInProgress = $activeStage && $activeStage->status === 'in_progress';
    $isAssignedToMe = $activeStage && $activeStage->assigned_user_id === auth()->id();

    // Cek apakah user adalah legal/admin
    $isLegal = auth()->user()->hasAnyRole(['admin', 'legal']);
    
    // Cek status untuk approve
    $canApprove = $isLegal && 
                  $contract->status === \App\Models\Contract::STATUS_SUBMITTED;
    
    // Cek status untuk generate number (SETELAH di-approve)
    $canGenerateNumber = $isLegal && 
                         $contract->status === \App\Models\Contract::STATUS_FINAL_APPROVED &&
                         empty($contract->contract_number);

    // ✅ Cek apakah sudah number issued (BUKAN final_approved)
    $isNumberIssued = $contract->status === 'number_issued' && $contract->contract_number;
    
    // ✅ User bisa upload final setelah number issued
    $canUploadFinal = $contract->user_id === auth()->id() && 
                      $isNumberIssued && 
                      !$contract->surat_file_path;
@endphp

<x-app-layout-dark :title="'Detail Surat - ' . $contract->title">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        {{-- ================================================ --}}
        {{-- HEADER - MODERN & CLEAN --}}
        {{-- ================================================ --}}
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3 flex-wrap">
                        {{-- Status Badge --}}
                        <span class="px-3 py-1.5 text-xs rounded-full inline-flex items-center gap-1.5
                            @if($contract->status == 'draft') bg-gray-700 text-gray-300
                            @elseif($contract->status == 'submitted') bg-yellow-600/20 text-yellow-300 border border-yellow-600/30
                            @elseif($contract->status == 'final_approved') bg-blue-600/20 text-blue-300 border border-blue-600/30
                            @elseif($contract->status == 'number_issued') bg-emerald-600/20 text-emerald-300 border border-emerald-600/30
                            @elseif($contract->status == 'released') bg-green-600/20 text-green-300 border border-green-600/30
                            @else bg-gray-700/50 text-gray-300 @endif">
                            
                            @if($contract->status == 'draft')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            @elseif($contract->status == 'submitted')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($contract->status == 'final_approved')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            @elseif($contract->status == 'number_issued')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h-1.5a2 2 0 00-2 2v0a2 2 0 002 2H15m3-4h-1.5a2 2 0 00-2 2v0a2 2 0 002 2H18m-9 4h1m-1-4h1m-1-4h1m9 0v8a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2z" /></svg>
                            @elseif($contract->status == 'released')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                            
                            {{ strtoupper(str_replace('_', ' ', $contract->status_label)) }}
                        </span>

                        {{-- Contract Number Badge --}}
                        @if($contract->contract_number)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600/20 text-purple-300 border border-purple-500/30 rounded-full text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h-1.5a2 2 0 00-2 2v0a2 2 0 002 2H15m3-4h-1.5a2 2 0 00-2 2v0a2 2 0 002 2H18m-9 4h1m-1-4h1m-1-4h1m9 0v8a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2z" />
                            </svg>
                            NOMOR: {{ $contract->contract_number }}
                        </span>
                        @endif

                        {{-- Status indicator glow for in_progress --}}
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ $contract->user->nama_user ?? $contract->user->name ?? '-' }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1.5 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $contract->created_at->format('d M Y, H:i') }}
                        </span>
                        <span>•</span>
                        <span class="text-sm">Departemen: <span class="text-gray-300 font-medium">{{ $contract->department_code ?? '-' }}</span></span>
                    </div>
                </div>

                {{-- Action Buttons (KEMBALI) --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <a href="{{ route('contracts.index') }}" 
                       class="flex items-center justify-center px-5 py-3 bg-gray-800/80 hover:bg-gray-700 rounded-xl text-gray-300 hover:text-white transition-all duration-300 border border-gray-700/50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
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

        {{-- ================================================ --}}
        {{-- SHOW GENERATED NUMBER --}}
        {{-- ================================================ --}}
        @if($contract->contract_number)
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-900/30 to-cyan-900/20 
                        border border-blue-500/30 rounded-xl shadow-lg shadow-blue-500/10">

                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-600/20 
                                flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400">
                            Official Letter Number
                        </p>

                        <p class="text-lg font-bold text-blue-300 font-mono tracking-wide">
                            {{ $contract->contract_number }}
                        </p>

                        <p class="text-xs text-gray-500 mt-1">
                            Generated on: {{ $contract->number_issued_at ? $contract->number_issued_at->format('d M Y, H:i') : $contract->updated_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ================================================ --}}
        {{-- PROGRESS BAR - DENGAN 2 STEP LEGAL --}}
        {{-- ================================================ --}}
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
                // PROGRESS BAR DENGAN 5 STEP (legal approve + generate number)
                $steps = [
                    ['status' => 'draft', 'label' => 'Draft', 'icon' => 'document'],
                    ['status' => 'submitted', 'label' => 'Submitted', 'icon' => 'clock'],
                    ['status' => 'final_approved', 'label' => 'Legal Approve', 'icon' => 'check'],
                    ['status' => 'number_issued', 'label' => 'Number Issued', 'icon' => 'number'],
                    ['status' => 'released', 'label' => 'Completed', 'icon' => 'check-circle'],
                ];
                
                $currentIndex = array_search($contract->status, array_column($steps, 'status'));
                if ($currentIndex === false) $currentIndex = 0;
            @endphp

            {{-- PROGRESS BAR --}}
            <div class="mb-6 bg-gradient-to-br from-gray-900/50 via-gray-900/30 to-gray-950/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-800/50">
                <div class="flex items-center justify-between relative">
                    {{-- Progress Line --}}
                    <div class="absolute left-0 top-1/2 w-full h-1 bg-gray-800 -translate-y-1/2 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 transition-all duration-1000"
                             style="width: {{ ($currentIndex / (count($steps) - 1)) * 100 }}%"></div>
                    </div>

                    {{-- Steps --}}
                    @foreach($steps as $index => $step)
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-500
                                @if($index < $currentIndex) 
                                    bg-gradient-to-r from-green-500 to-emerald-500 shadow-lg shadow-green-500/30
                                @elseif($index == $currentIndex)
                                    bg-gradient-to-r from-blue-500 to-purple-500 shadow-lg shadow-blue-500/30 scale-110
                                    ring-4 ring-blue-500/20
                                @else
                                    bg-gray-800 border-2 border-gray-700
                                @endif">
                                
                                @if($index < $currentIndex)
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @elseif($index == $currentIndex)
                                    @if($step['icon'] == 'document')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    @elseif($step['icon'] == 'clock')
                                        <svg class="w-6 h-6 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @elseif($step['icon'] == 'check')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    @elseif($step['icon'] == 'number')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
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

                {{-- Stats Footer --}}
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

        {{-- ================================================ --}}
        {{-- MAIN CONTENT GRID --}}
        {{-- ================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT COLUMN - Document Info --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Document Information Card --}}
                <div class="glass-card rounded-xl p-6 @if($isInProgress) border border-blue-500/30 shadow-lg shadow-blue-500/10 @endif">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-300">Informasi Surat</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Left Column Info --}}
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Judul Surat</p>
                                <p class="text-gray-300 font-medium">{{ $contract->title }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Tanggal Surat</p>
                                <p class="text-gray-300">
                                    {{ $contract->effective_date ? \Carbon\Carbon::parse($contract->effective_date)->format('d F Y') : '-' }}
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Departemen</p>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1.5 bg-blue-500/10 text-blue-400 rounded-lg text-sm font-mono border border-blue-500/20">
                                        {{ $contract->department_code ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column Info --}}
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Nomor Surat</p>
                                @if($contract->contract_number)
                                    <div class="mt-1 p-3 bg-gradient-to-r from-purple-900/30 to-pink-900/30 border border-purple-500/30 rounded-lg">
                                        <p class="text-lg font-bold font-mono text-purple-400">{{ $contract->contract_number }}</p>
                                    </div>
                                @else
                                    <p class="text-gray-400 flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        Belum digenerate
                                    </p>
                                @endif
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Description</p>
                                <p class="text-gray-300 leading-relaxed">
                                    {{ $contract->description ?? 'Tidak ada description' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================= --}}
                {{-- REVIEW HISTORY --}}
                {{-- ============================= --}}
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 text-sm">No review history yet.</p>
                            <p class="text-gray-400 text-xs mt-1">Review actions will appear here</p>
                        </div>
                    @else
                        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent">
                            @foreach ($reviewLogs as $log)
                                @php
                                $metadata = is_array($log->metadata ?? null) ? $log->metadata : [];
                                
                                $displayNotes =
                                    !empty(trim($log->notes ?? '')) ? $log->notes
                                    : (!empty(trim($metadata['notes'] ?? '')) ? $metadata['notes']
                                    : (!empty(trim($metadata['revision_notes'] ?? '')) ? $metadata['revision_notes']
                                    : (!empty(trim($metadata['rejection_reason'] ?? '')) ? $metadata['rejection_reason']
                                    : (!empty(trim($metadata['response'] ?? '')) ? $metadata['response']
                                    : (!empty(trim($metadata['feedback'] ?? '')) ? $metadata['feedback']
                                    : null)))));
                                
                                $actionColors = [
                                    'surat_created' => 'bg-gray-500/10 text-gray-400 border-gray-500/30',
                                    'surat_submitted' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                    'surat_approved' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                    'surat_number_generated' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                    'file_uploaded' => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                                    'file_deleted' => 'bg-red-500/10 text-red-400 border-red-500/30',
                                    'surat_deleted' => 'bg-red-500/10 text-red-400 border-red-500/30',
                                    'surat_note_sent' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                ];
                                
                                $actionColor = $actionColors[$log->action] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/30';
                                
                                $actionLabels = [
                                    'surat_created' => 'Draft Created',
                                    'surat_submitted' => 'Submitted to Legal',
                                    'surat_approved' => 'Approved by Legal',
                                    'surat_number_generated' => 'Number Generated',
                                    'file_uploaded' => 'File Uploaded',
                                    'file_deleted' => 'File Deleted',
                                    'surat_deleted' => 'Letter Deleted',
                                    'surat_note_sent' => 'Note from Legal',
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
                                                                'USER' => 'User',
                                                                default => $log->user->jabatan
                                                            };
                                                        @endphp
                                                        <span class="px-1.5 py-0.5 rounded bg-gray-800 text-xs text-gray-300">
                                                            {{ $roleDisplay }}
                                                        </span>
                                                    @endif
                                                    
                                                    <span class="text-xs text-gray-500 ml-1">
                                                        • {{ $log->created_at->format('M d, H:i') }}
                                                    </span>
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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-300">Notes:</span>
                                            </div>
                                            <div class="ml-6 mt-1">
                                                <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">
                                                    {{ $displayNotes }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="mt-3 pt-3 border-t border-gray-700/30 flex justify-between items-center">
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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

            </div> {{-- END LEFT COLUMN --}}

            {{-- ================================================ --}}
            {{-- RIGHT COLUMN - File & Actions --}}
            {{-- ================================================ --}}
            <div class="space-y-6">

                {{-- File Management Card --}}
                <div class="glass-card rounded-xl p-6 @if($isInProgress) border border-blue-500/20 shadow-md shadow-blue-500/10 @endif">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-orange-500 flex items-center justify-center shadow-lg shadow-red-500/20">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-md font-semibold text-gray-300">File Surat</h3>
                        
                        @if($contract->surat_file_path)
                            @php
                                $fileExists = Storage::disk('public')->exists($contract->surat_file_path);
                            @endphp
                            
                            @if($fileExists)
                                <span class="ml-auto px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs border border-green-500/30">
                                    ✓ Uploaded
                                </span>
                            @else
                                <span class="ml-auto px-2 py-0.5 bg-red-500/20 text-red-400 rounded-full text-xs border border-red-500/30">
                                    ⚠ File Missing
                                </span>
                            @endif
                        @endif
                    </div>

                    @if($contract->surat_file_path)
                        @php
                            $fileExists = Storage::disk('public')->exists($contract->surat_file_path);
                        @endphp
                        
                        @if($fileExists)
                            {{-- ✅ FILE EXISTS - SHOW INFO --}}
                            <div class="bg-gray-900/50 rounded-lg p-3 border border-gray-700/50 mb-3">
                                <div class="flex items-start gap-3">
                                    {{-- File Icon --}}
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-600/20 to-orange-600/20 flex items-center justify-center border border-red-500/30 flex-shrink-0">
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>

                                    {{-- File Info --}}
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

                                    {{-- ACTION BUTTONS --}}
                                    <div class="flex gap-1">
                                        {{-- Preview Button --}}
                                        <a href="{{ route('surat.preview', $contract) }}" 
                                        target="_blank"
                                        class="p-1.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded-lg transition-all duration-300 border border-blue-500/30 hover:border-blue-500/50"
                                        title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        
                                        {{-- Download Button --}}
                                        <a href="{{ route('surat.download', $contract) }}"
                                        class="p-1.5 bg-green-600/20 hover:bg-green-600/30 text-green-400 rounded-lg transition-all duration-300 border border-green-500/30 hover:border-green-500/50"
                                        title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </a>
                                        
                                        {{-- Delete Button (only if not released) --}}
                                        @if($contract->status !== \App\Models\Contract::STATUS_RELEASED)
                                            <form action="{{ route('surat.delete-file', $contract->id) }}" 
                                                method="POST" 
                                                class="inline"
                                                onsubmit="return confirm('Hapus file surat ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg transition-all duration-300 border border-red-500/30 hover:border-red-500/50"
                                                        title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- ⚠️ FILE MISSING --}}
                            <div class="bg-red-500/10 rounded-lg p-4 border border-red-500/30 mb-3">
                                <div class="flex items-center gap-2 text-red-400 mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.959-1.333-2.73 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span class="font-medium">File Tidak Ditemukan</span>
                                </div>
                                <p class="text-sm text-red-300">
                                    File path: <code class="bg-red-900/30 px-2 py-0.5 rounded text-xs">{{ $contract->surat_file_path }}</code>
                                </p>
                                <p class="text-xs text-red-300 mt-1">
                                    Silakan upload file kembali atau hubungi admin jika masalah berlanjut.
                                </p>
                            </div>
                        @endif
                    @else
                        {{-- No File - Compact --}}
                        <div class="bg-gray-900/30 rounded-lg p-4 text-center border border-gray-700/50 border-dashed mb-3">
                            <div class="w-10 h-10 mx-auto rounded-full bg-gradient-to-br from-red-600/10 to-orange-600/10 flex items-center justify-center border border-red-500/20">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="mt-2 text-sm text-gray-400">No file uploaded yet</p>
                            <p class="text-xs text-gray-500 mt-0.5">PDF, max 10MB</p>
                        </div>
                    @endif

                    {{-- Upload Form - Compact --}}
                    @if($contract->status !== \App\Models\Contract::STATUS_RELEASED)
                        <div class="bg-gray-900/30 rounded-lg p-3 border border-dashed border-gray-700/50 hover:border-purple-500/50 transition-all duration-300">
                            <form action="{{ route('surat.upload', $contract->id) }}" 
                                method="POST" 
                                enctype="multipart/form-data"
                                class="space-y-3">
                                @csrf
                                
                                <div class="flex flex-col gap-2">
                                    <label for="file-upload" class="flex flex-col items-center justify-center w-full px-3 py-3 bg-gray-800/50 hover:bg-gray-800/80 rounded-lg border border-gray-700/50 hover:border-purple-500/30 cursor-pointer transition-all duration-300">
                                        <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z" />
                                        </svg>
                                        <span class="text-xs text-gray-400">
                                            <span class="text-purple-400 font-medium">Upload your file here!</span>
                                        </span>
                                        <span class="text-xs text-gray-500 mt-0.5">Click to select or drag & drop</span>
                                        <input id="file-upload" 
                                            type="file" 
                                            name="file" 
                                            accept=".pdf,application/pdf" 
                                            required 
                                            class="hidden"
                                            onchange="updateFileName(this)">
                                    </label>
                                    
                                    {{-- File name preview (akan muncul setelah pilih file) --}}
                                    <div id="selected-file-container" class="hidden">
                                        <div class="flex items-center gap-2 p-2 bg-purple-500/10 border border-purple-500/30 rounded-lg">
                                            <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span id="selected-file-name" class="text-xs text-gray-300 truncate flex-1"></span>
                                            <button type="button" onclick="clearFileSelection()" class="text-gray-400 hover:text-white">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <button type="submit"
                                            class="w-full px-4 py-2 bg-purple-600/20 hover:bg-purple-600/30 text-purple-400 text-sm font-medium rounded-lg transition-all duration-300 border border-purple-500/30 hover:border-purple-500/50">
                                        <span class="flex items-center justify-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
                                            </svg>
                                            {{ $contract->surat_file_path ? 'Replace File' : 'Upload File' }}
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

            
                {{-- ================================================ --}}
                {{-- ACTION BUTTONS CARD - SOFT COLORS --}}
                {{-- ================================================ --}}
                <div class="glass-card rounded-xl p-6 border border-gray-700/50">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <h3 class="text-md font-semibold text-gray-300">Actions</h3>
                    </div>

                    @php
                        $canDelete = false;
                        $deleteReason = '';

                        if (auth()->user()->hasAnyRole(['admin'])) {
                            $canDelete = true;
                            $deleteReason = 'Admin dapat menghapus surat kapan saja';
                        } elseif (auth()->user()->hasRole('legal') && in_array($contract->status, ['draft', 'submitted', 'final_approved'])) {
                            $canDelete = true;
                            $deleteReason = 'Legal can delete letters before the number is generated';
                        } elseif ($contract->user_id === auth()->id() && $contract->status === 'draft') {
                            $canDelete = true;
                            $deleteReason = 'Pemilik dapat menghapus draft';
                        }
                    @endphp

                    <div class="space-y-3">

                        {{-- ✅ TOMBOL APPROVE (submitted → final_approved) --}}
                        @if($canApprove)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-400">Step 1: Approve letter request</span>
                                </div>
                                <form action="{{ route('surat.approve', $contract) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Approve permohonan surat ini?\n\nStatus akan berubah menjadi Final Approved.')"
                                            class="w-full px-4 py-2.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 text-sm font-medium rounded-lg transition-all duration-300 border border-blue-500/30 hover:border-blue-500/50">
                                        <span class="flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            Approve Letter Request
                                        </span>
                                    </button>
                                </form>
                            </div>
                        @endif

                        {{-- ✅ TOMBOL SEND NOTES (LEGAL/ADMIN SAJA, status submitted) --}}
                        @if($isLegal && $contract->status === \App\Models\Contract::STATUS_SUBMITTED)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-400">Send Notes to Users</span>
                                </div>
                                <button type="button"
                                        onclick="document.getElementById('modal-send-note').classList.remove('hidden')"
                                        class="w-full px-4 py-2.5 bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400 text-sm font-medium rounded-lg transition-all duration-300 border border-yellow-500/30 hover:border-yellow-500/50">
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Send Notes to User
                                    </span>
                                </button>
                            </div>
                        @endif

                        {{-- ✅ TOMBOL GENERATE NUMBER (final_approved → number_issued) --}}
                        @if($canGenerateNumber)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-400">Step 2: Generate official number</span>
                                </div>
                                <form action="{{ route('surat.generate-number', $contract) }}" method="POST" id="generateForm">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Generate official letter number?\n\nSetelah digenerate, status akan berubah menjadi Number Issued.')"
                                            class="w-full px-4 py-2.5 bg-green-600/20 hover:bg-green-600/30 text-green-400 text-sm font-medium rounded-lg transition-all duration-300 border border-green-500/30 hover:border-green-500/50">
                                        <span class="flex items-center justify-center gap-2" id="buttonText">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                            </svg>
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

                        {{-- ✅ USER SUBMIT BUTTON (DRAFT → SUBMITTED) --}}
                        @if($contract->status === 'draft' && $contract->user_id === auth()->id())
                            <form action="{{ route('surat.submit', $contract) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Submit surat ini untuk legal review?')"
                                        class="w-full px-4 py-2.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 text-sm font-medium rounded-lg transition-all duration-300 border border-blue-500/30 hover:border-blue-500/50">
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                        </svg>
                                        Submit to Legal Department
                                    </span>
                                </button>
                            </form>
                        @endif

                        {{-- ✅ USER UPLOAD FINAL SURAT (SETELAH NUMBER ISSUED) --}}
                        @if($canUploadFinal)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <span class="text-xs font-medium text-gray-400">Upload surat yang sudah ditandatangani</span>
                                <div class="text-center text-xs text-gray-500 mt-1">Gunakan form upload di bagian "File Surat" di atas</div>
                            </div>
                        @endif

                        {{-- ✅ DELETE BUTTON (DENGAN MODAL ALASAN) --}}
                        @if($canDelete)
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.959-1.333-2.73 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-400">{{ $deleteReason }}</span>
                                </div>
                                <button type="button"
                                        onclick="document.getElementById('modal-delete-surat').classList.remove('hidden')"
                                        class="w-full px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm font-medium rounded-lg transition-all duration-300 border border-red-500/30 hover:border-red-500/50">
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Reject / Delete Letter
                                    </span>
                                </button>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- ================================================ --}}
                {{-- MODAL: KIRIM CATATAN KE USER                    --}}
                {{-- Letakkan di LUAR grid, sebelum </x-app-layout-dark> --}}
                {{-- ================================================ --}}
                <div id="modal-send-note"
                    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
                    <div class="relative bg-gray-900 border border-yellow-500/30 rounded-2xl shadow-2xl w-full max-w-lg">

                        {{-- Header --}}
                        <div class="flex items-center justify-between p-5 border-b border-gray-700/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-white">Send Notes to Users</h3>
                                    <p class="text-xs text-gray-400">Letter Status <strong class="text-yellow-400">wont change</strong></p>
                                </div>
                            </div>
                            <button onclick="document.getElementById('modal-send-note').classList.add('hidden')"
                                    class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Body --}}
                        <form action="{{ route('surat.send-note', $contract) }}" method="POST" class="p-5 space-y-4">
                            @csrf

                            {{-- Info penerima --}}
                            <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center text-sm font-bold text-blue-400">
                                    {{ substr($contract->user->nama_user ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-200">{{ $contract->user->nama_user ?? '-' }}</p>
                                    <p class="text-xs text-gray-400">{{ $contract->user->email ?? '-' }}</p>
                                </div>
                                <span class="ml-auto text-xs px-2 py-1 bg-blue-500/20 text-blue-400 rounded-full border border-blue-500/30">
                                    Penerima
                                </span>
                            </div>

                            {{-- Textarea notes --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1.5">
                                    Catatan / Kelengkapan yang Diperlukan
                                    <span class="text-red-400">*</span>
                                </label>
                                <textarea name="notes"
                                        rows="5"
                                        required
                                        minlength="10"
                                        maxlength="2000"
                                        placeholder="Contoh: Mohon melengkapi dokumen berikut:&#10;1. KTP yang masih berlaku&#10;2. Surat kuasa bermaterai&#10;3. Fotokopi NPWP"
                                        class="w-full px-4 py-3 bg-gray-800/60 border border-gray-700/50 focus:border-yellow-500/50 focus:ring-1 focus:ring-yellow-500/30 rounded-xl text-gray-200 text-sm placeholder-gray-500 resize-none transition-all duration-200 outline-none"
                                        oninput="document.getElementById('note-char-count').textContent = this.value.length"></textarea>
                                <div class="flex justify-between mt-1">
                                    <p class="text-xs text-gray-500">10 Characters minimum</p>
                                    <p class="text-xs text-gray-500"><span id="note-char-count">0</span>/2000</p>
                                </div>
                            </div>

                            {{-- Warning info --}}
                            <div class="flex items-start gap-2 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                                <svg class="w-4 h-4 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-xs text-yellow-300">
                                    This note will be sent via <strong>email</strong> and <strong>system notification</strong> to the requestor.
                                    The status of the letter remains <strong>SUBMITTED</strong>.
                                </p>
                            </div>

                            {{-- Action buttons --}}
                            <div class="flex gap-3 pt-1">
                                <button type="button"
                                        onclick="document.getElementById('modal-send-note').classList.add('hidden')"
                                        class="flex-1 px-4 py-2.5 bg-gray-700/60 hover:bg-gray-700 text-gray-300 text-sm rounded-xl transition-colors border border-gray-600/50">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="flex-1 px-4 py-2.5 bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400 text-sm font-medium rounded-xl transition-all duration-200 border border-yellow-500/30 hover:border-yellow-500/50 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    Kirim Catatan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ================================================ --}}
                {{-- MODAL: DELETE / REJECT SURAT DENGAN ALASAN      --}}
                {{-- ================================================ --}}
                <div id="modal-delete-surat"
                    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
                    <div class="relative bg-gray-900 border border-red-500/30 rounded-2xl shadow-2xl w-full max-w-lg">

                        {{-- Header --}}
                        <div class="flex items-center justify-between p-5 border-b border-gray-700/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-red-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-white">Tolak / Hapus Surat</h3>
                                    <p class="text-xs text-gray-400">Tindakan ini <strong class="text-red-400">tidak dapat dibatalkan</strong></p>
                                </div>
                            </div>
                            <button onclick="document.getElementById('modal-delete-surat').classList.add('hidden')"
                                    class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Body --}}
                        <form action="{{ route('surat.destroy', $contract) }}" method="POST" class="p-5 space-y-4">
                            @csrf
                            @method('DELETE')

                            {{-- Info surat --}}
                            <div class="p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                                <p class="text-xs text-gray-400 mb-1">Surat yang akan dihapus:</p>
                                <p class="text-sm font-semibold text-gray-200">{{ $contract->title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Status: {{ strtoupper(str_replace('_', ' ', $contract->status)) }}</p>
                            </div>

                            {{-- Textarea alasan (wajib untuk non-owner atau bukan draft) --}}
                            @php
                                $isOwnerDraft = ($contract->user_id === auth()->id() && $contract->status === 'draft');
                            @endphp

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1.5">
                                    Alasan Penolakan / Penghapusan
                                    @if(!$isOwnerDraft)
                                        <span class="text-red-400">*</span>
                                    @else
                                        <span class="text-gray-500">(opsional)</span>
                                    @endif
                                </label>
                                <textarea name="delete_reason"
                                        rows="4"
                                        {{ !$isOwnerDraft ? 'required' : '' }}
                                        minlength="10"
                                        maxlength="1000"
                                        placeholder="Contoh: Dokumen tidak lengkap, format surat tidak sesuai standar, dll."
                                        class="w-full px-4 py-3 bg-gray-800/60 border border-gray-700/50 focus:border-red-500/50 focus:ring-1 focus:ring-red-500/30 rounded-xl text-gray-200 text-sm placeholder-gray-500 resize-none transition-all duration-200 outline-none"
                                        oninput="document.getElementById('delete-char-count').textContent = this.value.length"></textarea>
                                <div class="flex justify-between mt-1">
                                    <p class="text-xs text-gray-500">{{ !$isOwnerDraft ? 'Wajib diisi, minimal 10 karakter' : 'Opsional' }}</p>
                                    <p class="text-xs text-gray-500"><span id="delete-char-count">0</span>/1000</p>
                                </div>
                            </div>

                            @if(!$isOwnerDraft)
                            {{-- Info notifikasi ke user --}}
                            <div class="flex items-start gap-2 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                                <svg class="w-4 h-4 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-xs text-red-300">
                                    Alasan ini akan dikirim ke <strong>{{ $contract->user->nama_user ?? 'user' }}</strong> via email dan notifikasi sistem.
                                </p>
                            </div>
                            @endif

                            {{-- Action buttons --}}
                            <div class="flex gap-3 pt-1">
                                <button type="button"
                                        onclick="document.getElementById('modal-delete-surat').classList.add('hidden')"
                                        class="flex-1 px-4 py-2.5 bg-gray-700/60 hover:bg-gray-700 text-gray-300 text-sm rounded-xl transition-colors border border-gray-600/50">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="flex-1 px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm font-medium rounded-xl transition-all duration-200 border border-red-500/30 hover:border-red-500/50 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Ya, Hapus Surat
                                </button>
                            </div>
                        </form>
                    </div>
                </div>



                {{-- Quick Info Card --}}
                <div class="glass-card rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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

                {{-- Review Progress Timeline --}}
                @if($contract->isInReviewStageSystem())
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-md font-semibold text-gray-300 mb-3">Review Progress</h3>

                    <div class="space-y-3">
                        @foreach($contract->reviewStages->sortBy('sequence') as $reviewStage)
                            <div class="flex items-start gap-2">
                                <div class="flex-shrink-0 mt-0.5">
                                    @if($reviewStage->status === 'completed')
                                        <div class="w-5 h-5 rounded-full bg-green-500/20 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
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
                                            @if($reviewStage->is_user_stage)
                                                User Review
                                            @else
                                                {{ Str::limit($reviewStage->stage_name, 15) }}
                                            @endif
                                        </div>
                                        <div class="text-[10px]">
                                            @if($reviewStage->status === 'completed')
                                            <span class="text-green-400">Completed</span>
                                            @elseif($reviewStage->status === 'in_progress')
                                            <span class="text-blue-400">In Progress</span>
                                            @elseif($reviewStage->status === 'assigned')
                                            <span class="text-yellow-400">Assigned</span>
                                            @elseif($reviewStage->status === 'revision_requested')
                                            <span class="text-orange-400">Revision</span>
                                            @else
                                            <span class="text-gray-500">Pending</span>
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

            </div> {{-- END RIGHT COLUMN --}}

        </div> {{-- END MAIN CONTENT GRID --}}

    </div>

    {{-- ================================================ --}}
    {{-- SCRIPTS --}}
    {{-- ================================================ --}}
    @push('scripts')
    <script>
        // File name preview
        function updateFileName(input) {
            const fileContainer = document.getElementById('selected-file-container');
            const fileNameSpan = document.getElementById('selected-file-name');
            
            if (input.files && input.files[0]) {
                fileNameSpan.textContent = input.files[0].name;
                fileContainer.classList.remove('hidden');
            }
        }

        // Clear file selection
        function clearFileSelection() {
            const fileInput = document.getElementById('file-upload');
            const fileContainer = document.getElementById('selected-file-container');
            
            fileInput.value = '';
            fileContainer.classList.add('hidden');
        }

        // Delete confirmation
        function confirmDeleteSurat() {
            if (confirm('Hapus surat ini? Tindakan ini tidak dapat dibatalkan.')) {
                document.getElementById('delete-surat-form').submit();
            }
        }

        // Loading spinner untuk generate button
        document.addEventListener('DOMContentLoaded', function() {
            const generateForm = document.getElementById('generateForm');
            if (generateForm) {
                generateForm.addEventListener('submit', function(e) {
                    const btn = this.querySelector('button[type="submit"]');
                    const buttonText = document.getElementById('buttonText');
                    const loadingSpinner = document.getElementById('loadingSpinner');
                    
                    if (btn && buttonText && loadingSpinner) {
                        btn.disabled = true;
                        buttonText.classList.add('hidden');
                        loadingSpinner.classList.remove('hidden');
                    }
                });
            }
        });
    </script>
    @endpush

    {{-- Custom Styles --}}
    @push('styles')
    <style>
        @keyframes gradient-x {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }
        .animate-gradient-x {
            animation: gradient-x 3s ease-in-out infinite;
        }
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .glass-card {
            background: rgba(17, 25, 40, 0.75);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glass-card:hover {
            background: rgba(17, 25, 40, 0.85);
            border-color: rgba(255, 255, 255, 0.1);
        }
    </style>
    @endpush

</x-app-layout-dark>