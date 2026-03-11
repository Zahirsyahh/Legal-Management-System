<x-app-layout-dark title="Edit Workflow - {{ $contract->title }}">

    <style>
        /* Custom styles for Workflow Editor */
        .gradient-border {
            position: relative;
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98));
            border: 1px solid transparent;
            border-radius: 0.75rem;
        }
        
        .gradient-border::before {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            bottom: -1px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(59, 130, 246, 0.1), rgba(14, 165, 233, 0.3));
            border-radius: 0.75rem;
            z-index: -1;
            opacity: 0.5;
        }
        
        .stage-card {
            background: rgba(15, 23, 42, 0.95) !important;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .stage-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            transition: left 0.5s;
        }
        
        .stage-card:hover::before {
            left: 100%;
        }
        
        .stage-card:hover {
            border-color: rgba(14, 165, 233, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.15);
        }
        
        .stage-card.active-stage {
            border-color: rgba(14, 165, 233, 0.6);
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.15)) !important;
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.25);
        }
        
        .stage-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            font-weight: bold;
            border-radius: 50%;
            position: relative;
            flex-shrink: 0;
        }
        
        .stage-number::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.5;
            animation: pulse-glow 2s infinite;
        }
        
        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: white !important;
            border-width: 1px;
            border-style: solid;
            white-space: nowrap;
        }
        
        .status-pending {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(245, 158, 11, 0.15)) !important;
            color: #fbbf24 !important;
            border-color: rgba(245, 158, 11, 0.4) !important;
        }
        
        .status-assigned {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(59, 130, 246, 0.15)) !important;
            color: #60a5fa !important;
            border-color: rgba(59, 130, 246, 0.4) !important;
        }
        
        .status-in_progress {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(14, 165, 233, 0.15)) !important;
            color: #38bdf8 !important;
            border-color: rgba(14, 165, 233, 0.4) !important;
        }
        
        .status-completed {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.25), rgba(34, 197, 94, 0.15)) !important;
            color: #4ade80 !important;
            border-color: rgba(34, 197, 94, 0.4) !important;
        }
        
        .status-skipped {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.25), rgba(148, 163, 184, 0.15)) !important;
            color: #94a3b8 !important;
            border-color: rgba(148, 163, 184, 0.4) !important;
        }
        
        .reviewer-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            font-weight: bold;
            font-size: 0.75rem;
            border-radius: 50%;
            flex-shrink: 0;
        }
        
        .reviewer-section {
            margin-top: 12px;
            padding: 8px 12px;
            background: rgba(30, 41, 59, 0.7) !important;
            border-radius: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }
        
        .compact-reviewer-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.875rem;
        }
        
        .compact-reviewer-text {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .compact-reviewer-name {
            color: white;
            font-weight: 500;
        }
        
        .drag-handle {
            cursor: move;
            transition: color 0.2s;
            flex-shrink: 0;
        }
        
        .drag-handle:hover {
            color: #0ea5e9;
        }
        
        .remove-stage-btn {
            transition: all 0.3s ease;
            opacity: 0.5;
            flex-shrink: 0;
        }
        
        .remove-stage-btn:hover {
            opacity: 1;
            color: #ef4444;
            transform: scale(1.1);
        }
        
        .add-stage-btn {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(59, 130, 246, 0.08));
            border: 2px dashed rgba(14, 165, 233, 0.4);
            transition: all 0.3s ease;
        }
        
        .add-stage-btn:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(59, 130, 246, 0.15));
            border-color: rgba(14, 165, 233, 0.6);
            transform: translateY(-1px);
        }
        
        .form-input {
            background: rgba(30, 41, 59, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .form-input:focus {
            background: rgba(30, 41, 59, 0.9) !important;
            border-color: rgba(14, 165, 233, 0.6);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
            outline: none;
        }
        
        .form-select {
            background: rgba(30, 41, 59, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.5rem;
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            color: white !important;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .form-select:focus {
            background: rgba(30, 41, 59, 0.9) !important;
            border-color: rgba(14, 165, 233, 0.6);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
            outline: none;
        }
        
        .form-select option {
            background: #0f172a !important;
            color: white !important;
            padding: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.25);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.35);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: rgba(30, 41, 59, 0.9);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        
        .dragging {
            opacity: 0.5;
            transform: rotate(3deg);
        }
        
        .drag-over {
            border-color: #0ea5e9 !important;
            background: rgba(14, 165, 233, 0.08) !important;
        }
        
        .active-stage-indicator {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 10px;
            height: 10px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border-radius: 50%;
            animation: pulse 2s infinite;
            z-index: 10;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
        }
        
        @keyframes pulse-glow {
            0%, 100% {
                opacity: 0.5;
            }
            50% {
                opacity: 0.8;
            }
        }
        
        .glass-card {
            background: rgba(30, 41, 59, 0.7) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .progress-line {
            position: absolute;
            left: 50%;
            top: 20px;
            transform: translateX(-50%);
            width: calc(100% - 80px);
            height: 2px;
            background: linear-gradient(90deg, 
                #0ea5e9 0%, 
                #0ea5e9 25%, 
                #3b82f6 25%, 
                #3b82f6 50%,
                #8b5cf6 50%, 
                #8b5cf6 75%,
                #ec4899 75%, 
                #ec4899 100%
            );
            background-size: 400% 100%;
            animation: progress-flow 3s linear infinite;
            z-index: 0;
        }
        
        @keyframes progress-flow {
            0% {
                background-position: 100% 0;
            }
            100% {
                background-position: 0% 0;
            }
        }
        
        select[name*="[status]"] {
            color-scheme: dark !important;
        }
        
        #workflowStages {
            position: relative;
            z-index: 1;
        }
        
        .stage-card * {
            position: relative;
            z-index: 2;
        }
        
        .stage-card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.95);
            z-index: 1;
            border-radius: 0.75rem;
        }
        
        .text-gray-400 {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        
        .text-gray-500 {
            color: rgba(255, 255, 255, 0.4) !important;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-600/20 border border-blue-500/30">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 via-cyan-400 to-blue-400 bg-clip-text text-transparent">
                            Edit Workflow
                        </h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-gray-400">Contract:</span>
                            <span class="font-medium text-white">{{ $contract->title }}</span>
                            <span class="text-gray-500">•</span>
                            <span class="text-sm px-2 py-1 rounded-full bg-gradient-to-r from-cyan-500/30 to-blue-500/30 text-cyan-300">
                                ID: {{ $contract->id }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="flex flex-wrap gap-4 mt-6">
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-blue-500/20 to-cyan-600/20">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Total Stages</p>
                            <p class="text-2xl font-bold text-white">{{ $stages->count() }}</p>
                        </div>
                    </div>
                    
                    @php
                        $activeStage = $stages->whereIn('status', ['assigned', 'in_progress'])->first();
                    @endphp
                    
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/20 to-orange-600/20">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Current Stage</p>
                            <p class="text-2xl font-bold text-amber-300">
                                @if($activeStage)
                                    #{{ $activeStage->sequence }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[200px]">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/20 to-emerald-600/20">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Completed</p>
                            <p class="text-2xl font-bold text-green-300">{{ $stages->where('status', 'completed')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3">
                <a href="{{ route('contracts.show', $contract) }}" 
                   class="glass-card px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-white/5 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                    Back to Contract
                </a>
                
                <button type="button" 
                        onclick="resetToDefault()"
                        class="glass-card px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-amber-500/10 hover:text-amber-300 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Workflow
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="glass-card rounded-xl p-4 mb-6 border border-green-500/40 animate-scale-in">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-gradient-to-br from-green-500/20 to-emerald-600/20">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-green-300">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="glass-card rounded-xl p-4 mb-6 border border-red-500/40 animate-scale-in">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-gradient-to-br from-red-500/20 to-pink-600/20">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-red-300">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Utama -->
        <form method="POST"
              action="{{ route('legal.workflow.update', $contract) }}"
              id="workflowForm"
              class="space-y-8">
            @csrf
            
            <!-- Workflow Stages Container -->
            <div class="gradient-border p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white">
                        Workflow Stages Configuration
                    </h3>
                    <div class="text-sm text-gray-400">
                        Drag to reorder • Click to edit
                    </div>
                </div>

                <div class="relative mb-2">
                    <div class="progress-line"></div>
                </div>

                <!-- Stages Container -->
                <div id="workflowStages" class="space-y-4">
                    @foreach($stages as $stage)
                        @php
                            $isActiveStage = in_array($stage->status, ['assigned', 'in_progress']);
                        @endphp
                        <div class="stage-card p-4 {{ $isActiveStage ? 'active-stage' : '' }}"
                             data-stage-id="{{ $stage->id }}"
                             draggable="true">
                            <div class="stage-card-overlay"></div>
                            
                            @if($isActiveStage)
                                <div class="active-stage-indicator"></div>
                            @endif
                            
                            <div class="relative z-10">
                                <div class="flex items-center justify-between gap-4">
                                    <!-- Left Side: Number and Drag Handle -->
                                    <div class="flex items-center gap-3">
                                        <div class="stage-number" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                            {{ $stage->sequence }}
                                        </div>
                                        <div class="drag-handle text-gray-400 hover:text-blue-400 cursor-move p-1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Middle: Stage Details -->
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <!-- Stage Name -->
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Stage Name</label>
                                            <input type="text"
                                                   name="stages[{{ $loop->index }}][stage_name]"
                                                   class="form-input py-2 text-sm"
                                                   value="{{ $stage->stage_name }}"
                                                   required>
                                            <input type="hidden" 
                                                   name="stages[{{ $loop->index }}][id]" 
                                                   value="{{ $stage->id }}">
                                            <input type="hidden" 
                                                   name="stages[{{ $loop->index }}][sequence]" 
                                                   class="sequence-input"
                                                   value="{{ $stage->sequence }}">
                                        </div>

                                        <!-- Reviewer -->
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Reviewer</label>
                                            <select name="stages[{{ $loop->index }}][assigned_user_id]"
                                                    class="form-select py-2 text-sm"
                                                    required>
                                                <option value="">Select Reviewer</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id_user }}"
                                                        {{ $stage->assigned_user_id == $user->id_user ? 'selected' : '' }}>
                                                        {{ $user->nama_user }}
                                                        @if($user->jabatan)
                                                            ({{ $user->jabatan }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Status -->
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Status</label>
                                            <select name="stages[{{ $loop->index }}][status]"
                                                    class="form-select py-2 text-sm"
                                                    required>
                                                <option value="pending" {{ $stage->status === 'pending' ? 'selected' : '' }}>
                                                    Pending
                                                </option>
                                                <option value="assigned" {{ $stage->status === 'assigned' ? 'selected' : '' }}>
                                                    Assigned
                                                </option>
                                                <option value="in_progress" {{ $stage->status === 'in_progress' ? 'selected' : '' }}>
                                                    In Progress
                                                </option>
                                                <option value="completed" {{ $stage->status === 'completed' ? 'selected' : '' }}>
                                                    Completed
                                                </option>
                                                <option value="skipped" {{ $stage->status === 'skipped' ? 'selected' : '' }}>
                                                    Skipped
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Right Side: Actions and Status Badge -->
                                    <div class="flex items-center gap-3">
                                        <div class="{{ $stage->status }}-badge">
                                            <span class="status-badge status-{{ $stage->status }}" style="padding: 0.3rem 0.8rem; font-size: 0.7rem;">
                                                {{ str_replace('_', ' ', $stage->status) }}
                                            </span>
                                        </div>
                                        
                                        @if($stage->id)
                                            <button type="button"
                                                    class="remove-stage-btn text-gray-400 hover:text-red-400 p-1"
                                                    onclick="deleteStage({{ $stage->id }}, '{{ addslashes($stage->stage_name) }}')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="remove-stage-btn text-gray-400 hover:text-red-400 p-1"
                                                    onclick="removeNewStage(this)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Compact Reviewer Info -->
                                @if($stage->assignedUser)
                                    <div class="reviewer-section mt-3">
                                        <div class="compact-reviewer-info">
                                            <div class="reviewer-avatar compact-reviewer-avatar">
                                                {{ substr($stage->assignedUser->nama_user, 0, 1) }}
                                            </div>
                                            <div class="compact-reviewer-text">
                                                <span class="compact-reviewer-name">{{ $stage->assignedUser->nama_user }}</span>
                                                <span class="text-gray-500 mx-2">•</span>
                                                <span>{{ $stage->assignedUser->email }}</span>
                                                @if($stage->assignedUser->jabatan)
                                                    <span class="text-gray-500 mx-2">•</span>
                                                    <span class="text-cyan-300">{{ $stage->assignedUser->jabatan }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Add Stage Button -->
                <button type="button"
                        id="addStageBtn"
                        class="add-stage-btn w-full mt-6 p-5 rounded-xl flex flex-col items-center justify-center gap-2 relative z-10"
                        onclick="addNewStage()">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="text-blue-300 font-medium">Add New Stage</span>
                    <span class="text-sm text-gray-400">Click to add a new review stage</span>
                </button>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-8 border-t border-gray-800">
                <div class="text-sm text-gray-500">
                    <p>⚠️ Note: Changing active stage reviewer will split the workflow</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('contracts.show', $contract) }}"
                       class="btn-secondary px-6 py-3 rounded-xl flex items-center gap-2">
                        Cancel
                    </a>
                    <button type="submit"
                            id="saveWorkflowBtn"
                            class="btn-primary px-8 py-3 rounded-xl flex items-center gap-2 font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Workflow Changes
                    </button>
                </div>
            </div>
        </form>

        <!-- Form Delete Tersembunyi (diluar form utama) -->
        <form method="POST" id="deleteStageForm" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        <!-- Workflow Preview -->
        <div class="mt-12">
            <h3 class="text-xl font-semibold text-white mb-4">Workflow Preview</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($stages as $stage)
                    <div class="glass-card p-4 rounded-xl">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="stage-number" style="width: 30px; height: 30px; font-size: 0.875rem;">
                                    {{ $stage->sequence }}
                                </div>
                                <span class="font-medium text-white text-sm">{{ $stage->stage_name }}</span>
                            </div>
                            <span class="status-badge status-{{ $stage->status }}" style="padding: 0.2rem 0.6rem; font-size: 0.65rem;">
                                {{ substr(str_replace('_', ' ', $stage->status), 0, 10) }}
                            </span>
                        </div>
                        @if($stage->assignedUser)
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <div class="reviewer-avatar" style="width: 22px; height: 22px; font-size: 0.7rem;">
                                    {{ substr($stage->assignedUser->nama_user, 0, 1) }}
                                </div>
                                <span class="text-xs">{{ $stage->assignedUser->nama_user }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        let draggedItem = null;

        document.addEventListener('DOMContentLoaded', function() {
            // RESET BUTTON IF PAGE REFRESHED
            const submitButton = document.getElementById('saveWorkflowBtn');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Workflow Changes
                `;
            }

            initializeDragAndDrop();
            updateSequenceNumbers();
            
            // Form submission
            document.getElementById('workflowForm').addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to update the workflow? This action may affect current reviews.')) {
                    e.preventDefault();
                    return;
                }
                
                updateSequenceNumbers();
                showLoading();
            });
        });

        // Add pageshow event handler untuk handle browser back/forward cache
        window.addEventListener('pageshow', function() {
            const btn = document.getElementById('saveWorkflowBtn');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Workflow Changes
                `;
            }
        });

        // ========================================
        // DRAG AND DROP FUNCTIONALITY
        // ========================================
        function initializeDragAndDrop() {
            document.querySelectorAll('.stage-card').forEach(card => {
                initializeDragEvents(card);
            });
        }

        function initializeDragEvents(element) {
            element.addEventListener('dragstart', function(e) {
                if (e.target.tagName === 'INPUT' || 
                    e.target.tagName === 'SELECT' || 
                    e.target.tagName === 'BUTTON' ||
                    e.target.tagName === 'OPTION') {
                    e.preventDefault();
                    return;
                }
                
                this.classList.add('dragging');
                draggedItem = this;
                e.dataTransfer.effectAllowed = 'move';
            });
            
            element.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                draggedItem = null;
                document.querySelectorAll('.stage-card').forEach(c => c.classList.remove('drag-over'));
                updateSequenceNumbers();
            });
            
            element.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
            
            element.addEventListener('dragenter', function(e) {
                e.preventDefault();
            });
            
            element.addEventListener('dragleave', function() {
                this.classList.remove('drag-over');
            });
            
            element.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                
                if (draggedItem && draggedItem !== this) {
                    const bounding = this.getBoundingClientRect();
                    const offset = e.clientY - bounding.top;
                    
                    if (offset > bounding.height / 2) {
                        this.parentNode.insertBefore(draggedItem, this.nextSibling);
                    } else {
                        this.parentNode.insertBefore(draggedItem, this);
                    }
                    
                    updateSequenceNumbers();
                }
            });
        }

        // ========================================
        // UPDATE SEQUENCE NUMBERS
        // ========================================
        function updateSequenceNumbers() {
            const stages = document.querySelectorAll('.stage-card');
            stages.forEach((stage, index) => {
                const stageNumber = stage.querySelector('.stage-number');
                const sequenceInput = stage.querySelector('.sequence-input');
                
                if (stageNumber) {
                    stageNumber.textContent = index + 1;
                }
                
                if (sequenceInput) {
                    sequenceInput.value = index + 1;
                }
                
                const stageNameInput = stage.querySelector('input[name$="[stage_name]"]');
                if (stageNameInput) {
                    const name = stageNameInput.name;
                    const newName = name.replace(/stages\[(\d+)\]/, `stages[${index}]`);
                    stageNameInput.name = newName;
                    
                    stage.querySelectorAll('input, select').forEach(input => {
                        if (input.name && input.name.startsWith('stages[')) {
                            input.name = input.name.replace(/stages\[(\d+)\]/, `stages[${index}]`);
                        }
                    });
                }
            });
        }

        // ========================================
        // ADD NEW STAGE
        // ========================================
        function addNewStage() {
            const stagesContainer = document.getElementById('workflowStages');
            const stageCount = stagesContainer.children.length;
            const newIndex = stageCount;
            
            const newStage = document.createElement('div');
            newStage.className = 'stage-card p-4';
            newStage.draggable = true;
            newStage.innerHTML = `
                <div class="stage-card-overlay"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="stage-number" style="width: 36px; height: 36px; font-size: 0.9rem;">${stageCount + 1}</div>
                            <div class="drag-handle text-gray-400 hover:text-blue-400 cursor-move p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Stage Name</label>
                                <input type="text"
                                       name="stages[${newIndex}][stage_name]"
                                       class="form-input py-2 text-sm"
                                       value="New Review Stage"
                                       required>
                                <input type="hidden" 
                                       name="stages[${newIndex}][id]" 
                                       value="">
                                <input type="hidden" 
                                       name="stages[${newIndex}][sequence]" 
                                       class="sequence-input"
                                       value="${stageCount + 1}">
                            </div>

                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Reviewer</label>
                                <select name="stages[${newIndex}][assigned_user_id]"
                                        class="form-select py-2 text-sm"
                                        required>
                                    <option value="">Select Reviewer</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id_user }}">
                                            {{ $user->nama_user }}
                                            @if($user->jabatan)
                                                ({{ $user->jabatan }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Status</label>
                                <select name="stages[${newIndex}][status]"
                                        class="form-select py-2 text-sm"
                                        required>
                                    <option value="pending">Pending</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="skipped">Skipped</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="pending-badge">
                                <span class="status-badge status-pending" style="padding: 0.3rem 0.8rem; font-size: 0.7rem;">
                                    pending
                                </span>
                            </div>
                            <button type="button"
                                    class="remove-stage-btn text-gray-400 hover:text-red-400 p-1"
                                    onclick="removeNewStage(this)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="reviewer-section mt-3" style="display: none;">
                        <div class="compact-reviewer-info">
                            <div class="reviewer-avatar compact-reviewer-avatar"></div>
                            <div class="compact-reviewer-text">
                                <span class="compact-reviewer-name"></span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            stagesContainer.appendChild(newStage);
            initializeDragEvents(newStage);
            updateSequenceNumbers();
            newStage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // ========================================
        // DELETE STAGE (DATABASE) - VIA FORM SUBMIT
        // ========================================
        function deleteStage(stageId, stageName) {
            if (!confirm(`Are you sure you want to delete stage "${stageName}"?\n\nThis action cannot be undone and will be permanently removed from the database.`)) {
                return;
            }
            
            const form = document.getElementById('deleteStageForm');
            form.action = `{{ route('legal.workflow.delete', ['contract' => $contract->id, 'stage' => '__STAGE_ID__']) }}`.replace('__STAGE_ID__', stageId);
            form.submit();
        }

        // ========================================
        // REMOVE STAGE (NEW STAGE - NOT SAVED)
        // ========================================
        function removeNewStage(button) {
            const stageCard = button.closest('.stage-card');
            const stageName = stageCard.querySelector('input[name$="[stage_name]"]').value;
            
            if (!confirm(`Are you sure you want to remove stage "${stageName}"?`)) {
                return;
            }
            
            stageCard.style.opacity = '0.5';
            stageCard.style.transform = 'translateX(-100px)';
            
            setTimeout(() => {
                stageCard.remove();
                updateSequenceNumbers();
            }, 300);
        }

        // ========================================
        // RESET TO DEFAULT
        // ========================================
        function resetToDefault() {
            if (!confirm('Reset workflow to default configuration? This will remove all custom stages.')) {
                return;
            }
            window.location.reload();
        }

        // ========================================
        // SHOW LOADING
        // ========================================
        function showLoading() {
            const submitButton = document.getElementById('saveWorkflowBtn');
            if (submitButton) {
                submitButton.innerHTML = `
                    <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Saving...
                `;
                submitButton.disabled = true;
            }
        }
    </script>

</x-app-layout-dark>