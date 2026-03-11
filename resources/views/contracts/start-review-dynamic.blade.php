{{-- resources/views/contracts/start-review-dynamic.blade.php --}}
<x-app-layout-dark title="Setup Dynamic Review - {{ $contract->title }}">
    
    <style>
        /* Custom styles consistent with Master Data design */
        .gradient-border {
            position: relative;
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
            border: 1px solid transparent;
            border-radius: 0.75rem;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .action-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .badge-primary {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(2, 132, 199, 0.2));
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }
        
        .badge-success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(21, 128, 61, 0.2));
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }
        
        .badge-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2));
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }
        
        .badge-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(139, 92, 246, 0.2));
            color: #a855f7;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.2);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7, #2563eb);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #059669, #047857);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
        }
        
        .status-active {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.05));
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .input-field {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        
        .select-field {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
        
        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }
        
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .reviewer-row {
            transition: all 0.3s ease;
        }
        
        .reviewer-row:hover {
            background: rgba(255, 255, 255, 0.02);
            transform: translateY(-1px);
        }
        
        /* Stage indicator dots */
        .stage-indicator {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }
        
        .stage-indicator.active {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            box-shadow: 0 0 10px rgba(14, 165, 233, 0.3);
        }
        
        .stage-indicator.pending {
            background: rgba(255, 255, 255, 0.05);
            color: #9ca3af;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Department checkbox */
        .department-checkbox {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .department-checkbox:hover {
            border-color: #8b5cf6;
            background: rgba(139, 92, 246, 0.05);
        }
        
        .department-checkbox.selected {
            border-color: #8b5cf6;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(139, 92, 246, 0.05));
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.1);
        }
        
        .department-checkbox::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(139, 92, 246, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .department-checkbox.selected::before {
            opacity: 1;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(168, 85, 247, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        /* Selected department badge */
        .selected-dept-badge {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(139, 92, 246, 0.1));
            border: 1px solid rgba(168, 85, 247, 0.3);
            color: #a855f7;
        }
    </style>
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <a href="{{ route('contracts.show', $contract) }}" 
                       class="group flex items-center gap-2 text-gray-400 hover:text-gray-300 transition-colors action-btn p-2 rounded-lg hover:bg-gray-800/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="text-sm font-medium">Back to Contract</span>
                    </a>
                </div>
                
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500/10 to-cyan-600/10 border border-blue-500/20">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 via-cyan-400 to-blue-400 bg-clip-text text-transparent">
                            Setup Review Workflow
                        </h1>
                        <p class="text-gray-400 mt-1">Configure review stages and assign team members</p>
                    </div>
                </div>
                
                <!-- Contract Info -->
                <div class="glass-card rounded-xl p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Document Title -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm text-gray-400">Document Title</p>
                            </div>
                            <p class="text-lg font-semibold text-gray-300 truncate" title="{{ $contract->title }}">
                                {{ $contract->title }}
                            </p>
                        </div>
                        
                        <!-- Counterparty -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <p class="text-sm text-gray-400">Counterparty</p>
                            </div>
                            <p class="text-lg font-semibold text-gray-300 truncate" title="{{ $contract->counterparty_name }}">
                                {{ $contract->counterparty_name }}
                            </p>
                        </div>
                        
                        <!-- Contract Value -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-gray-400">Contract Value</p>
                            </div>
                            <p class="text-lg font-semibold text-gray-300">
                                @if($contract->contract_value)
                                    {{ number_format($contract->contract_value, 2) }} {{ $contract->currency ?? 'IDR' }}
                                @else
                                    <span class="text-gray-500 italic">Not specified</span>
                                @endif
                            </p>
                        </div>
                        
                        <!-- Document Type -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <p class="text-sm text-gray-400">Document Type</p>
                            </div>
                            <p class="text-lg font-semibold text-gray-300 truncate" title="{{ $contract->contract_type }}">
                                {{ $contract->contract_type ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col items-end">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800/50 backdrop-blur-sm rounded-full border border-gray-700/50">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-sm font-medium text-gray-300">Ready for Setup</span>
                </div>
                <span class="text-xs text-gray-500 mt-1">Contract #{{ $contract->contract_number ?? $contract->id }}</span>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="glass-card rounded-xl p-4 mb-6 border border-green-500/30 bg-green-500/10 animate-fade-in">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-green-300 font-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif
        
        @if(session('error'))
        <div class="glass-card rounded-xl p-4 mb-6 border border-red-500/30 bg-red-500/10 animate-fade-in">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-red-300 font-medium">{{ session('error') }}</span>
            </div>
        </div>
        @endif
        
        @if($errors->any())
        <div class="glass-card rounded-xl p-4 mb-6 border border-red-500/30 bg-red-500/10 animate-fade-in">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-red-300 font-medium mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-red-300/80 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Form -->
        <form action="{{ route('contracts.process-start-review-dynamic', $contract) }}" method="POST" id="reviewWorkflowForm">
            @csrf
            
            <!-- Main Container -->
            <div class="space-y-6">
                
                <!-- ============================================
                    SECTION 1: REVIEW STAGES
                ============================================ -->
                <div class="gradient-border p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-300">Review Stages</h2>
                                <p class="text-sm text-gray-400">Add legal reviewers and define stages</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    id="remove-reviewer-btn"
                                    class="px-4 py-2 rounded-lg border border-red-500/30 bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                    disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove Stage
                            </button>
                            
                            <button type="button"
                                    id="add-reviewer-btn"
                                    class="px-4 py-2 rounded-lg border border-blue-500/30 bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Stage
                            </button>
                        </div>
                    </div>
                    
                    <!-- Reviewer Stages -->
                    <div id="reviewer-container" class="space-y-4">
                        @for ($i = 0; $i < 1; $i++)
                            <div class="reviewer-row glass-card p-5 rounded-xl relative group">
                                @if($i >= 1)
                                <button type="button" 
                                        class="remove-reviewer-btn absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 z-10"
                                        title="Remove this stage">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                @endif
                                
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="stage-indicator active">{{ $i + 1 }}</div>
                                    <span class="text-sm font-medium text-gray-300">Stage {{ $i + 1 }}</span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block mb-2">
                                            <span class="text-sm font-medium text-gray-300 mb-1 block">Legal Officer</span>
                                            <div class="relative">
                                                <select name="reviewers[{{ $i }}][user_id]"
                                                        class="w-full input-field select-field rounded-lg px-4 py-3 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                                        required>
                                                    <option value="">-- Select Legal Officer --</option>
                                                    @foreach ($legalOfficers as $legal)
                                                        <option value="{{ $legal->id_user }}">
                                                            {{ $legal->nama_user }} • {{ $legal->email }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div>
                                        <label class="block mb-2">
                                            <span class="text-sm font-medium text-gray-300 mb-1 block">Stage Name</span>
                                            <input type="text"
                                                   name="reviewers[{{ $i }}][stage_name]"
                                                   value="Initial Legal Review"
                                                   class="w-full input-field rounded-lg px-4 py-3 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                                   required>
                                        </label>
                                    </div>
                                </div>
                                
                                <input type="hidden"
                                       name="reviewers[{{ $i }}][sequence]"
                                       value="{{ $i + 1 }}">
                            </div>
                        @endfor
                    </div>
                    
                    <!-- Stage Stats -->
                    <div class="mt-6 flex items-center justify-between text-sm text-gray-400">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Minimum <strong>1</strong> stage required • Current: <span id="current-stage-count" class="font-bold text-gray-300">1</span> stage</span>
                        </div>
                        <div>
                            <span class="text-blue-400"><span id="total-stage-count">1</span> stage(s) configured</span>
                        </div>
                    </div>
                </div>
                
                <!-- ============================================
                    SECTION 2: DOCUMENT LINK
                ============================================ -->
                <div class="gradient-border p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-orange-500/10 to-amber-600/10">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-300">Document Link</h2>
                            <p class="text-sm text-gray-400">Provide Synology Drive link for document access</p>
                        </div>
                    </div>
                    
                    <div>
                    <input type="text" 
                        name="synology_folder_path" 
                        id="synology_drive_link"
                        value="{{ old('synology_folder_path', '') }}"
                        placeholder="Contoh: C:\Synology\Contracts\2024"
                        class="w-full input-field rounded-lg px-4 py-3 text-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                        
                        <div class="mt-3 flex items-center gap-2 text-sm text-gray-400">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>This folder path will be accessible to all assigned reviewers</span>
                        </div>
                    </div>
                </div>
                
                <!-- ===========================================
                    SECTION 3: DEPARTMENTS (Optional)
                ============================================ -->
                @if($otherDepartments->count() > 0)
                <div class="gradient-border p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-purple-500/10 to-pink-600/10">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-300">Additional Departments (Optional)</h2>
                            <p class="text-sm text-gray-400">Select departments that need to review this contract</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
@foreach($otherDepartments as $dept)
    <label class="cursor-pointer department-checkbox-container" for="dept_{{ $dept->code }}">
        <input type="checkbox"
               name="selected_departments[]"
               value="{{ $dept->code }}"
               id="dept_{{ $dept->code }}"
               class="hidden department-checkbox-input">

        {{-- 
            Hapus semua peer-checked: class.
            Gunakan class "dept-card" sebagai hook JS, 
            dan class "selected" ditambah/hapus lewat JS.
        --}}
        <div class="dept-card h-full p-4 rounded-xl border-2 border-gray-700/50 bg-gray-800/30
                    hover:border-purple-400/50 hover:bg-gray-800/50
                    transition-all duration-300">

            <div class="flex items-start justify-between">
                <div class="flex items-start gap-3 flex-1">
                    <div class="dept-icon w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500/20 to-pink-500/20
                                flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-gray-300">{{ $dept->code }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="dept-title text-sm font-semibold text-gray-300 mb-1">
                            {{ $dept->name }}
                        </h4>
                        @if($dept->description)
                            <p class="text-xs text-gray-400">
                                {{ Str::limit($dept->description, 60) }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Checkmark circle — JS tambah class "checked" --}}
                <div class="dept-checkmark ml-3 flex-shrink-0 w-6 h-6 rounded-full border-2 border-gray-600
                            flex items-center justify-center transition-all duration-300">
                    <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            {{-- Selected badge — JS toggle class "hidden" --}}
            <div class="dept-selected-badge mt-3 pt-3 border-t border-gray-700/30 hidden">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-xs font-medium text-purple-400">Selected for review</span>
                </div>
            </div>
        </div>
    </label>
@endforeach
                    </div>
                    
                    <!-- Department selection summary -->
                    <div class="mt-6 p-4 bg-gray-800/30 rounded-lg border border-gray-700/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-gradient-to-br from-purple-500/10 to-pink-600/10">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-300">Departments Selected</p>
                                    <p class="text-xs text-gray-400">Click department cards to select/deselect</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span id="selectedDeptCount" class="text-2xl font-bold text-purple-400">0</span>
                                <span class="text-sm text-gray-400 ml-1">/ {{ $otherDepartments->count() }}</span>
                            </div>
                        </div>
                        
                        <!-- Selected departments list -->
                        <div id="selectedDeptList" class="mt-4 hidden">
                            <div class="flex flex-wrap gap-2">
                                <!-- Selected departments will appear here dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                
                <!-- ============================================
                    SECTION 5: REVIEW SUMMARY
                ============================================ -->
                <div class="glass-card rounded-xl p-6">
                    <h2 class="text-xl font-bold text-gray-300 mb-6">Review Summary</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-800/30 rounded-lg p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-2 rounded-lg bg-gradient-to-br from-blue-500/10 to-blue-600/10">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Legal Stages</p>
                                    <p class="text-2xl font-bold text-gray-300" id="stageCount">1</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Legal review stages configured</p>
                        </div>
                        
                        <div class="bg-gray-800/30 rounded-lg p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-2 rounded-lg bg-gradient-to-br from-purple-500/10 to-pink-600/10">
                                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Departments</p>
                                    <p class="text-2xl font-bold text-gray-300" id="deptCount">0</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Additional departments selected</p>
                        </div>
                        
                        <div class="bg-gray-800/30 rounded-lg p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Document Link</p>
                                    <p class="text-lg font-bold text-gray-300 truncate" id="synologyLinkStatus">Not Provided</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Synology Drive link status</p>
                        </div>
                    </div>
                </div>
                
                <!-- ============================================
                    SECTION 6: ACTION BUTTONS
                ============================================ -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-gray-700/50">
                    <div class="flex items-center gap-2 text-gray-400 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>All assignments will be notified via email</span>
                    </div>
                    
                    <div class="flex gap-3 w-full sm:w-auto">
                        <a href="{{ route('contracts.show', $contract) }}" 
                           class="flex-1 sm:flex-none px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded-lg font-medium text-gray-300 transition-all duration-300 text-center action-btn">
                            Cancel
                        </a>
                        
                        <button type="submit" 
                                id="submitBtn"
                                class="flex-1 sm:flex-none px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 rounded-lg font-medium text-white transition-all duration-300 action-btn">
                            <span class="flex items-center justify-center gap-2">
                                Start Dynamic Workflow
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Dynamic review workflow setup loaded');
        
        // Hitung dari jumlah stage yang ada sekarang
        const initialRows = document.querySelectorAll('.reviewer-row').length;
        let reviewerIndex = initialRows; // Mulai dari jumlah stage yang sudah ada
        
        const MIN_STAGES = 1;
        
        // DOM Elements
        const addBtn = document.getElementById('add-reviewer-btn');
        const removeBtn = document.getElementById('remove-reviewer-btn');
        const container = document.getElementById('reviewer-container');
        const stageCountElement = document.getElementById('stageCount');
        const currentStageCountElement = document.getElementById('current-stage-count');
        const totalStageCountElement = document.getElementById('total-stage-count');
        
        // Initialize
        updateUIState();
        
        // Update UI State
        function updateUIState() {
            const totalRows = document.querySelectorAll('.reviewer-row').length;
            currentStageCountElement.textContent = totalRows;
            if (stageCountElement) stageCountElement.textContent = totalRows;
            if (totalStageCountElement) totalStageCountElement.textContent = totalRows;
            
            // Enable/disable remove button
            if (removeBtn) {
                removeBtn.disabled = totalRows <= MIN_STAGES;
            }
            
            // Update all stage indicators
            updateStageIndicators();
            
            console.log(`📊 Current stages: ${totalRows}`);
        }
        
        // Update stage number indicators
        function updateStageIndicators() {
            const rows = document.querySelectorAll('.reviewer-row');
            rows.forEach((row, index) => {
                const stageNumber = index + 1;
                
                // Update stage indicator text
                const indicator = row.querySelector('.stage-indicator');
                if (indicator) {
                    indicator.textContent = stageNumber;
                    indicator.className = 'stage-indicator ' + (index === 0 ? 'active' : 'pending');
                }
                
                // Update stage label text
                const stageLabel = row.querySelector('.text-sm.font-medium.text-gray-300');
                if (stageLabel && stageLabel.textContent.includes('Stage')) {
                    stageLabel.textContent = `Stage ${stageNumber}`;
                }
                
                // Update sequence input
                const sequenceInput = row.querySelector('input[name$="[sequence]"]');
                if (sequenceInput) {
                    sequenceInput.value = stageNumber;
                }
                
                // Update stage name if it's using default naming
                const stageNameInput = row.querySelector('input[name$="[stage_name]"]');
                if (stageNameInput) {
                    const currentValue = stageNameInput.value;
                    // Jika masih menggunakan nama default, update
                    if (currentValue.includes('Legal Review Stage') || 
                        currentValue === 'Initial Legal Review') {
                        stageNameInput.value = `Legal Review Stage ${stageNumber}`;
                    }
                }
            });
        }
        
        // Create new reviewer row
        function createReviewerRow(index) {
            const firstSelect = container.querySelector('select');
            let optionsHtml = '<option value="">-- Select Legal Officer --</option>';
            
            if (firstSelect) {
                Array.from(firstSelect.options).forEach(option => {
                    if (option.value !== '') {
                        optionsHtml += `<option value="${option.value}">${option.textContent}</option>`;
                    }
                });
            }
            
            // Hitung nomor stage baru berdasarkan jumlah row yang ada + 1
            const totalRows = document.querySelectorAll('.reviewer-row').length;
            const newStageNumber = totalRows + 1; // Stage 2, 3, 4, dst
            
            const row = document.createElement('div');
            row.className = 'reviewer-row glass-card p-5 rounded-xl relative group animate-fade-in';
            row.innerHTML = `
                <button type="button" 
                        class="remove-reviewer-btn absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 z-10"
                        title="Remove this stage">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <div class="flex items-center gap-3 mb-4">
                    <div class="stage-indicator pending">${newStageNumber}</div>
                    <span class="text-sm font-medium text-gray-300">Stage ${newStageNumber}</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2">
                            <span class="text-sm font-medium text-gray-300 mb-1 block">Legal Officer</span>
                            <div class="relative">
                                <select name="reviewers[${index}][user_id]"
                                        class="w-full input-field select-field rounded-lg px-4 py-3 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                        required>
                                    ${optionsHtml}
                                </select>
                            </div>
                        </label>
                    </div>
                    
                    <div>
                        <label class="block mb-2">
                            <span class="text-sm font-medium text-gray-300 mb-1 block">Stage Name</span>
                            <input type="text"
                                   name="reviewers[${index}][stage_name]"
                                   value="Legal Review Stage ${newStageNumber}"
                                   class="w-full input-field rounded-lg px-4 py-3 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                   required>
                        </label>
                    </div>
                </div>
                
                <input type="hidden"
                       name="reviewers[${index}][sequence]"
                       value="${newStageNumber}">
            `;
            
            return row;
        }
        
        // Add reviewer stage
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                const totalRows = document.querySelectorAll('.reviewer-row').length;
                
                // Buat row baru dengan index yang benar
                const newRow = createReviewerRow(totalRows); // Gunakan totalRows sebagai index array
                
                // Tambahkan ke container
                container.appendChild(newRow);
                
                // Update UI state
                updateUIState();
                
                console.log(`➕ Added Stage ${totalRows + 1}`);
                
                // Add event listener to the remove button
                const removeBtnInRow = newRow.querySelector('.remove-reviewer-btn');
                if (removeBtnInRow) {
                    removeBtnInRow.addEventListener('click', function() {
                        removeReviewerRow(newRow);
                    });
                }
            });
        }
        
        // Remove reviewer stage (main button)
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const rows = document.querySelectorAll('.reviewer-row');
                if (rows.length > MIN_STAGES) {
                    const lastRow = rows[rows.length - 1];
                    removeReviewerRow(lastRow);
                }
            });
        }
        
        // Function to remove a reviewer row
        function removeReviewerRow(row) {
            const rows = document.querySelectorAll('.reviewer-row');
            if (rows.length <= MIN_STAGES) {
                alert(`Minimum ${MIN_STAGES} stage is required.`);
                return;
            }
            
            if (!confirm('Are you sure you want to remove this review stage?')) {
                return;
            }
            
            row.style.opacity = '0';
            row.style.transform = 'translateY(-10px)';
            row.style.marginBottom = '0';
            
            setTimeout(() => {
                row.remove();
                updateUIState();
                console.log(`➖ Removed a stage`);
            }, 300);
        }
        
        // Add event listeners to existing remove buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-reviewer-btn')) {
                const row = e.target.closest('.reviewer-row');
                if (row) {
                    removeReviewerRow(row);
                }
            }
        });
        
        // ── Department checkbox ──────────────────────────────────────────
        const deptCheckboxes = document.querySelectorAll('.department-checkbox-input');
        const deptCountEl = document.getElementById('deptCount');
        const selectedDeptCountEl = document.getElementById('selectedDeptCount');
        const selectedDeptListEl = document.getElementById('selectedDeptList');
        
        function refreshDeptUI() {
            const checked = Array.from(deptCheckboxes).filter(cb => cb.checked);
        
            if (deptCountEl) deptCountEl.textContent = checked.length;
            if (selectedDeptCountEl) selectedDeptCountEl.textContent = checked.length;
        
            // Rebuild badge list
            if (selectedDeptListEl) {
                const wrap = selectedDeptListEl.querySelector('.flex-wrap');
                if (wrap) {
                    wrap.innerHTML = '';
                    checked.forEach(cb => {
                        const name = cb.closest('label')?.querySelector('.dept-title')?.textContent?.trim() || cb.value;
                        const badge = document.createElement('div');
                        badge.className = 'selected-dept-badge px-3 py-1.5 rounded-full text-sm font-medium flex items-center gap-2';
                        badge.innerHTML = `<span>${name}</span>
                            <button type="button" class="deselect-btn text-xs hover:text-red-400" data-dept="${cb.value}">×</button>`;
                        wrap.appendChild(badge);
                    });
        
                    // Deselect from badge
                    wrap.querySelectorAll('.deselect-btn').forEach(btn => {
                        btn.addEventListener('click', e => {
                            e.stopPropagation();
                            const cb = document.getElementById(`dept_${btn.dataset.dept}`);
                            if (cb) { 
                                cb.checked = false; 
                                applyCardState(cb); 
                                refreshDeptUI(); 
                            }
                        });
                    });
                }
                checked.length > 0
                    ? selectedDeptListEl.classList.remove('hidden')
                    : selectedDeptListEl.classList.add('hidden');
            }
        }
        
        function applyCardState(checkbox) {
            const card      = checkbox.closest('label')?.querySelector('.dept-card');
            const checkmark = checkbox.closest('label')?.querySelector('.dept-checkmark');
            const checkIcon = checkmark?.querySelector('svg');
            const badge     = checkbox.closest('label')?.querySelector('.dept-selected-badge');
            const icon      = checkbox.closest('label')?.querySelector('.dept-icon');
            const title     = checkbox.closest('label')?.querySelector('.dept-title');
        
            if (!card) return;
        
            if (checkbox.checked) {
                card.classList.add('border-purple-500', 'bg-purple-500/10', 'shadow-lg', 'shadow-purple-500/20', '-translate-y-1', 'scale-[1.02]');
                card.classList.remove('border-gray-700/50', 'bg-gray-800/30');
        
                if (checkmark) {
                    checkmark.classList.add('bg-purple-500', 'border-purple-500');
                    checkmark.classList.remove('border-gray-600');
                }
                if (checkIcon)  checkIcon.classList.remove('hidden');
                if (badge)      badge.classList.remove('hidden');
                if (icon)       icon.classList.add('from-purple-500/30', 'to-pink-500/30');
                if (title)      title.classList.add('text-purple-300');
            } else {
                card.classList.remove('border-purple-500', 'bg-purple-500/10', 'shadow-lg', 'shadow-purple-500/20', '-translate-y-1', 'scale-[1.02]');
                card.classList.add('border-gray-700/50', 'bg-gray-800/30');
        
                if (checkmark) {
                    checkmark.classList.remove('bg-purple-500', 'border-purple-500');
                    checkmark.classList.add('border-gray-600');
                }
                if (checkIcon)  checkIcon.classList.add('hidden');
                if (badge)      badge.classList.add('hidden');
                if (icon)       icon.classList.remove('from-purple-500/30', 'to-pink-500/30');
                if (title)      title.classList.remove('text-purple-300');
            }
        }
        
        deptCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                applyCardState(cb);
                refreshDeptUI();
            });
            // Initialize state on load
            applyCardState(cb);
        });
        
        refreshDeptUI();
        // ── End Department checkbox ──────────────────────────────────────
        
        // Synology Drive link status
        const synologyInput = document.getElementById('synology_drive_link');
        const synologyStatus = document.getElementById('synologyLinkStatus');
        
        if (synologyInput && synologyStatus) {
            function updateSynologyStatus() {
                const value = synologyInput.value.trim();
                if (value) {
                    synologyStatus.textContent = 'Provided';
                    synologyStatus.classList.add('text-green-400');
                    synologyStatus.classList.remove('text-gray-500');
                } else {
                    synologyStatus.textContent = 'Not Provided';
                    synologyStatus.classList.remove('text-green-400');
                    synologyStatus.classList.add('text-gray-500');
                }
            }
            
            synologyInput.addEventListener('input', updateSynologyStatus);
            updateSynologyStatus();
        }
        
        // Form validation
        const reviewForm = document.getElementById('reviewWorkflowForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                const totalRows = document.querySelectorAll('.reviewer-row').length;
                if (totalRows < MIN_STAGES) {
                    e.preventDefault();
                    alert(`Error: Minimum ${MIN_STAGES} stage is required.`);
                    return false;
                }
                
                // Validate selects
                const emptySelects = document.querySelectorAll('select[name^="reviewers"]');
                let hasEmptySelect = false;
                
                emptySelects.forEach((select) => {
                    if (!select.value) {
                        hasEmptySelect = true;
                        select.classList.add('border-red-500');
                        select.classList.add('!border-red-500');
                    } else {
                        select.classList.remove('border-red-500');
                        select.classList.remove('!border-red-500');
                    }
                });
                
                if (hasEmptySelect) {
                    e.preventDefault();
                    alert('Error: Please select a legal officer for all review stages.');
                    return false;
                }
                
                // Validate unique reviewers
                const selectedUserIds = [];
                const selects = document.querySelectorAll('select[name^="reviewers"]');
                let hasDuplicate = false;
                
                selects.forEach((select) => {
                    if (select.value) {
                        if (selectedUserIds.includes(select.value)) {
                            hasDuplicate = true;
                            select.classList.add('border-red-500');
                            select.classList.add('!border-red-500');
                        } else {
                            selectedUserIds.push(select.value);
                            select.classList.remove('border-red-500');
                            select.classList.remove('!border-red-500');
                        }
                    }
                });
                
                if (hasDuplicate) {
                    e.preventDefault();
                    alert('Error: Each stage must have a unique legal officer assigned.');
                    return false;
                }
                
                // Check if any department is selected
                const departmentCheckboxes = document.querySelectorAll('.department-checkbox-input:checked');
                console.log(`📋 Departments selected: ${departmentCheckboxes.length}`);
                
                // Show loading state
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    `;
                }
                
                return true;
            });
        }
    });
    </script>

</x-app-layout-dark>