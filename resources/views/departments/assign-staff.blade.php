{{-- resources/views/departments/assign-staff.blade.php --}}
@php
    // Get contract data dari contractDepartment
    $contract = $contractDepartment->contract;
    
    // Determine route prefix based on department
    $department = $contractDepartment->department;
    $routePrefix = match($department->code) {
        'FIN' => 'finance',
        'ACC' => 'accounting',
        'TAX' => 'tax',
        default => 'finance'
    };
    
    // Check active stage
    $activeStage = $contract->activeStage();
    $isInProgress = $activeStage && $activeStage->status === 'in_progress';
    
    // Get staff count safely
    try {
        $staffCount = $department->activeStaff()->count();
    } catch (\Exception $e) {
        $staffCount = 0;
    }
    
    // Hitung jumlah stage yang sudah ada untuk department ini
    $existingStageCount = $contract->reviewStages()
        ->where('stage_type', strtolower($department->code))
        ->count();
    
    // Department colors
    $deptColors = [
        'FIN' => [
            'bg' => 'from-green-500/10 to-emerald-600/10', 
            'text' => 'text-green-400', 
            'border' => 'border-green-500/20',
            'gradient' => 'from-green-400 to-emerald-400',
            'btn' => 'from-green-600 to-emerald-600',
            'btn_hover' => 'from-green-700 to-emerald-700',
            'ring' => 'focus:ring-green-500/30',
            'select_bg' => 'peer-checked:border-green-500 peer-checked:bg-green-500/10'
        ],
        'ACC' => [
            'bg' => 'from-blue-500/10 to-cyan-600/10', 
            'text' => 'text-blue-400', 
            'border' => 'border-blue-500/20',
            'gradient' => 'from-blue-400 to-cyan-400',
            'btn' => 'from-blue-600 to-cyan-600',
            'btn_hover' => 'from-blue-700 to-cyan-700',
            'ring' => 'focus:ring-blue-500/30',
            'select_bg' => 'peer-checked:border-blue-500 peer-checked:bg-blue-500/10'
        ],
        'TAX' => [
            'bg' => 'from-purple-500/10 to-pink-600/10', 
            'text' => 'text-purple-400', 
            'border' => 'border-purple-500/20',
            'gradient' => 'from-purple-400 to-pink-400',
            'btn' => 'from-purple-600 to-pink-600',
            'btn_hover' => 'from-purple-700 to-pink-700',
            'ring' => 'focus:ring-purple-500/30',
            'select_bg' => 'peer-checked:border-purple-500 peer-checked:bg-purple-500/10'
        ],
    ];
    $currentColor = $deptColors[$department->code] ?? $deptColors['FIN'];
@endphp

<x-app-layout-dark title="Assign Staff Reviewer - {{ $contract->title }}">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <a href="{{ route($routePrefix . '-admin.dashboard') }}" 
                       class="group flex items-center gap-2 text-gray-400 hover:text-gray-300 transition-colors action-btn p-2 rounded-lg hover:bg-gray-800/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="text-sm font-medium">Back to Dashboard</span>
                    </a>
                </div>
                
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 rounded-xl bg-gradient-to-br {{ $currentColor['bg'] }} border {{ $currentColor['border'] }}">
                        <svg class="w-6 h-6 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold {{ $currentColor['text'] }}">
                            Assign Staff Reviewer
                        </h1>
                        <p class="text-gray-400 mt-1">Select a reviewer from {{ $department->name }} department</p>
                    </div>
                </div>
                
                <!-- Department Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r {{ $currentColor['bg'] }} border {{ $currentColor['border'] }}">
                    <svg class="w-4 h-4 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="text-sm font-medium {{ $currentColor['text'] }}">
                        {{ $department->name }} Department • {{ $staffCount }} Active Staff
                    </span>
                    <span class="ml-2 px-2 py-0.5 rounded-full bg-gray-700 text-xs text-white">
                        Stage #{{ $existingStageCount + 1 }}
                    </span>
                </div>
            </div>
            
            <div class="flex flex-col items-end">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800/50 backdrop-blur-sm rounded-full border border-gray-700/50">
                    <div class="w-2 h-2 rounded-full {{ $isInProgress ? 'bg-blue-500 animate-pulse' : 'bg-green-500' }}"></div>
                    <span class="text-sm font-medium text-gray-300">
                        {{ $isInProgress ? 'In Progress' : 'Ready for Assignment' }}
                    </span>
                </div>
                <span class="text-xs text-gray-500 mt-1">Contract #{{ $contract->contract_number ?? $contract->id }}</span>
            </div>
        </div>

        <!-- Contract Overview Card with Expandable Details -->
        <div class="glass-card rounded-xl p-6 mb-6" id="contractOverviewCard">
            <!-- Header dengan tombol expand -->
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-300 mb-1">Contract Overview</h3>
                    <p class="text-sm text-gray-400">Click arrow to view detailed information</p>
                </div>
                <button type="button" 
                        id="expandContractBtn"
                        class="expand-btn p-2 rounded-lg {{ $currentColor['bg'] }} hover:bg-opacity-20 transition-all duration-300"
                        aria-expanded="false"
                        aria-controls="contractDetails">
                    <svg id="expandArrow" class="w-5 h-5 {{ $currentColor['text'] }} transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
            
            <!-- Basic Info Grid -->
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
            
            <!-- Expandable Details Section (Hidden by default) -->
            <div id="contractDetails" class="hidden mt-6 pt-6 border-t border-gray-700/50">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up">
                    
                    <!-- Full Contract Information -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-300 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Contract Details
                        </h4>
                        
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Contract Number</p>
                                <p class="text-gray-300 font-medium">#{{ $contract->contract_number ?? $contract->id }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Description</p>
                                <p class="text-gray-300 text-sm leading-relaxed">
                                    {{ $contract->description ?? 'No description provided' }}
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Effective Date</p>
                                    <p class="text-gray-300">
                                        @if($contract->effective_date)
                                            {{ \Carbon\Carbon::parse($contract->effective_date)->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-500 italic">Not set</span>
                                        @endif
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Expiry Date</p>
                                    <p class="text-gray-300">
                                        @if($contract->expiry_date)
                                            {{ \Carbon\Carbon::parse($contract->expiry_date)->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-500 italic">Not set</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Drafting Deadline</p>
                                <p class="text-gray-300">
                                    @if($contract->drafting_deadline)
                                        <span class="font-medium {{ \Carbon\Carbon::parse($contract->drafting_deadline)->isPast() ? 'text-red-400' : 'text-green-400' }}">
                                            {{ \Carbon\Carbon::parse($contract->drafting_deadline)->format('M d, Y') }}
                                        </span>
                                        @if(\Carbon\Carbon::parse($contract->drafting_deadline)->isPast())
                                            <span class="text-xs text-red-400 ml-2">(Overdue)</span>
                                        @elseif(\Carbon\Carbon::parse($contract->drafting_deadline)->diffInDays(now()) <= 3)
                                            <span class="text-xs text-yellow-400 ml-2">(Due soon)</span>
                                        @endif
                                    @else
                                        <span class="text-gray-500 italic">Not set</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Financial Information -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-300 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Financial Information
                        </h4>
                        
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Contract Value</p>
                                <p class="text-2xl font-bold text-gray-300">
                                    @if($contract->contract_value)
                                        {{ number_format($contract->contract_value, 2) }}
                                    @else
                                        <span class="text-gray-500 italic">Not specified</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-400">
                                    Currency: {{ $contract->currency ?? 'IDR' }}
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Additional Notes</p>
                                <div class="p-3 bg-gray-800/30 rounded-lg">
                                    <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">
                                        {{ $contract->additional_notes ?? 'No additional notes' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Counterparty Details -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-300 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Counterparty Details
                        </h4>
                        
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Company Name</p>
                                <p class="text-gray-300 font-medium">{{ $contract->counterparty_name }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Contact Person</p>
                                    <p class="text-gray-300">
                                        {{ $contract->counterparty_contact_person ?? 'Not specified' }}
                                    </p>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Email</p>
                                <p class="text-gray-300">
                                    @if($contract->counterparty_email)
                                        <a href="mailto:{{ $contract->counterparty_email }}" 
                                           class="text-blue-400 hover:text-blue-300 hover:underline">
                                            {{ $contract->counterparty_email }}
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">Not specified</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Address</p>
                                <p class="text-gray-300 text-sm">
                                    {{ $contract->counterparty_address ?? 'Address not provided' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status Info -->
            <div class="mt-6 pt-6 border-t border-gray-700/50 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Current Status</p>
                        <span class="px-3 py-1.5 rounded-full text-sm font-medium {{ $contract->status_color }}">
                            {{ $contract->status_label }}
                        </span>
                    </div>
                    
                    @if($isInProgress)
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                        <span class="text-blue-400 text-sm font-medium">Stage In Progress</span>
                    </div>
                    @endif
                </div>
                
                <div class="text-right">
                    <p class="text-sm text-gray-400 mb-1">Department Assignment</p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full {{ $currentColor['bg'] }} border {{ $currentColor['border'] }}">
                        <svg class="w-4 h-4 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-sm {{ $currentColor['text'] }} font-medium">
                            {{ $department->name }} Review • Stage #{{ $existingStageCount + 1 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const expandBtn = document.getElementById('expandContractBtn');
            const contractDetails = document.getElementById('contractDetails');
            const expandArrow = document.getElementById('expandArrow');
            
            if (expandBtn && contractDetails) {
                expandBtn.addEventListener('click', function() {
                    const isExpanded = contractDetails.classList.contains('hidden');
                    
                    if (isExpanded) {
                        contractDetails.classList.remove('hidden');
                        expandArrow.style.transform = 'rotate(180deg)';
                        expandBtn.setAttribute('aria-expanded', 'true');
                        expandBtn.classList.add('bg-opacity-20');
                        
                        setTimeout(() => {
                            contractDetails.classList.add('animate-slide-up');
                        }, 10);
                    } else {
                        contractDetails.classList.add('hidden');
                        expandArrow.style.transform = 'rotate(0deg)';
                        expandBtn.setAttribute('aria-expanded', 'false');
                        expandBtn.classList.remove('bg-opacity-20');
                        contractDetails.classList.remove('animate-slide-up');
                    }
                });
                
                expandBtn.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        expandBtn.click();
                    }
                });
            }
        });
        </script>

        <style>
        .expand-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .expand-btn:hover {
            transform: scale(1.1);
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .expand-btn:focus {
            outline: 2px solid rgba(99, 102, 241, 0.5);
            outline-offset: 2px;
        }
        
        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }
        
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        </style>

        <!-- Assignment Form Card -->
        <div class="gradient-border p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-gradient-to-br {{ $currentColor['bg'] }}">
                    <svg class="w-5 h-5 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-300">Select Staff Reviewer</h2>
                    <p class="text-sm text-gray-400">Choose a qualified staff member from {{ $department->name }} department</p>
                </div>
            </div>
            
            <form action="{{ route($routePrefix . '-admin.assign.post', $contractDepartment) }}" method="POST" id="assignForm">
                @csrf

                <!-- Hidden field untuk stage type -->
                <input type="hidden" name="stage_type" value="{{ strtolower($department->code) }}">
                
                <!-- Hidden field untuk sequence -->
                <input type="hidden" name="sequence" value="{{ $existingStageCount + 1 }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Staff Selection -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-gray-800/50">
                                <svg class="w-4 h-4 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h3 class="text-md font-semibold text-gray-300">Select Reviewer</h3>
                        </div>

                        @if($staffMembers->count() > 0)
                            <div class="space-y-3">
                                @foreach($staffMembers as $staff)
                                    <div class="assignment-option">
                                        <input type="radio" name="staff_user_id" id="staff_{{ $staff->id }}" 
                                               value="{{ $staff->id }}" 
                                               class="hidden peer"
                                               required>
                                        <label for="staff_{{ $staff->id }}" 
                                               class="staff-option-label flex items-center justify-between p-4 bg-gray-800/30 border-2 border-gray-700/50 rounded-xl cursor-pointer hover:border-gray-600 {{ $currentColor['select_bg'] }} peer-checked:border-{{ $department->code == 'FIN' ? 'green' : ($department->code == 'ACC' ? 'blue' : 'purple') }}-500 transition-all duration-300">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-r {{ $currentColor['bg'] }} flex items-center justify-center border {{ $currentColor['border'] }}">
                                                    <span class="text-white font-medium text-sm">
                                                        {{ substr($staff->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-white">{{ $staff->name }}</p>
                                                    <p class="text-sm text-gray-400">{{ $staff->email }}</p>
                                                    @if($staff->position)
                                                        <p class="text-xs {{ $currentColor['text'] }} mt-1">{{ $staff->position }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Checkmark indicator -->
                                            <div class="checkmark-indicator ml-3 flex-shrink-0">
                                                <div class="w-6 h-6 rounded-full border-2 border-gray-600 
                                                          flex items-center justify-center 
                                                          peer-checked:bg-{{ $department->code == 'FIN' ? 'green' : ($department->code == 'ACC' ? 'blue' : 'purple') }}-500 peer-checked:border-{{ $department->code == 'FIN' ? 'green' : ($department->code == 'ACC' ? 'blue' : 'purple') }}-500 
                                                          transition-all duration-300">
                                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200" 
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            
                            @error('staff_user_id')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        @else
                            <div class="text-center py-8 border-2 border-dashed border-gray-700/50 rounded-xl bg-gray-800/20">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-yellow-500/20 mb-4">
                                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-white mb-2">No Staff Available</h4>
                                <p class="text-gray-400 mb-4">There are no active staff members in the {{ $department->name }} department.</p>
                                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium {{ $currentColor['text'] }} hover:underline">
                                    Contact Administrator →
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Stage Configuration -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-gray-800/50">
                                <svg class="w-4 h-4 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-md font-semibold text-gray-300">Stage Configuration</h3>
                        </div>

                        <!-- Stage Name Input -->
                        <div class="glass-card rounded-lg p-5">
                            <label class="block mb-3">
                                <span class="text-sm font-medium text-gray-300 mb-1 block">Stage Name</span>
                                <div class="relative">
                                    <input type="text"
                                           name="stage_name"
                                           id="stage_name"
                                           value="{{ $department->name }} Review Stage {{ $existingStageCount + 1 }}"
                                           class="w-full input-field rounded-lg px-4 py-3 text-gray-300 focus:outline-none focus:ring-2 {{ $currentColor['ring'] }}"
                                           placeholder="Enter stage name"
                                           required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </div>
                                </div>
                            </label>

                            <!-- Stage Info -->
                            <div class="mt-4 p-3 bg-gray-800/40 rounded-lg border border-gray-700/50">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-gray-400">Stage Sequence</span>
                                    <div class="flex items-center gap-1">
                                        <div class="stage-indicator {{ $currentColor['bg'] }} w-6 h-6 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-bold {{ $currentColor['text'] }}">{{ $existingStageCount + 1 }}</span>
                                        </div>
                                        <span class="text-sm text-gray-300 ml-1">of total workflow</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-gray-400">This will be stage #{{ $existingStageCount + 1 }} in the review workflow</span>
                                </div>
                            </div>

                            <!-- Workflow Preview -->
                            <div class="mt-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span class="text-xs font-medium text-gray-400">Workflow Position</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= $existingStageCount; $i++)
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-green-500/20 border border-green-500/30 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            @if($i < $existingStageCount)
                                                <svg class="w-4 h-4 text-gray-600 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            @endif
                                        </div>
                                    @endfor
                                    
                                    @if($existingStageCount > 0)
                                        <svg class="w-4 h-4 text-gray-600 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    @endif
                                    
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full {{ $currentColor['bg'] }} border-2 {{ $currentColor['border'] }} flex items-center justify-center">
                                            <span class="text-xs font-bold {{ $currentColor['text'] }}">{{ $existingStageCount + 1 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-gray-500/10 to-gray-600/10">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-300">Additional Instructions</h3>
                            <p class="text-sm text-gray-400">Optional notes for the reviewer</p>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-lg p-4">
                        <textarea name="notes" id="notes" rows="4"
                                  class="w-full bg-transparent border-none focus:ring-0 text-white placeholder-gray-500 resize-none input-field"
                                  placeholder="Add any special instructions, deadlines, or notes for the reviewer..."></textarea>
                        <div class="flex justify-between items-center mt-2 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Optional
                            </div>
                            <span id="charCount" class="text-gray-400">0/1000 characters</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-gray-700/50">
                    <div class="flex items-center gap-2 text-gray-400 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Selected staff will be notified via email</span>
                    </div>
                    
                    <div class="flex gap-3 w-full sm:w-auto">
                        <a href="{{ route($routePrefix . '-admin.dashboard') }}" 
                           class="flex-1 sm:flex-none px-6 py-3 bg-gray-800 hover:bg-gray-700 rounded-lg font-medium text-gray-300 transition-all duration-300 text-center action-btn">
                            Cancel
                        </a>
                        
                        <button type="submit" 
                                id="assignButton"
                                class="flex-1 sm:flex-none px-6 py-3 bg-gradient-to-r {{ $currentColor['btn'] }} hover:{{ $currentColor['btn_hover'] }} text-white rounded-lg font-medium transition-all duration-300 action-btn disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                {{ $staffMembers->count() == 0 ? 'disabled' : '' }}>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Assign & Start Review</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="glass-card p-5 rounded-xl">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 rounded-lg {{ $currentColor['bg'] }}">
                        <svg class="w-5 h-5 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Active Staff</p>
                        <p class="text-2xl font-bold text-white">{{ $staffCount }}</p>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-5 rounded-xl">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-600/10">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Previous Reviews</p>
                        <p class="text-2xl font-bold text-white">{{ $existingStageCount }}</p>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-5 rounded-xl">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 rounded-lg bg-gradient-to-br from-amber-500/10 to-orange-600/10">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Contract Revisions</p>
                        <p class="text-2xl font-bold text-white">{{ $contract->revision_count ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Assign Staff Reviewer page loaded');
            
            // Character counter for notes
            const notesTextarea = document.getElementById('notes');
            const charCount = document.getElementById('charCount');
            
            if (notesTextarea && charCount) {
                notesTextarea.addEventListener('input', function() {
                    const length = this.value.length;
                    charCount.textContent = `${length}/1000 characters`;
                    
                    if (length > 1000) {
                        charCount.classList.add('text-red-400');
                        charCount.classList.remove('text-gray-400');
                    } else {
                        charCount.classList.remove('text-red-400');
                        charCount.classList.add('text-gray-400');
                    }
                });
            }
            
            // Form validation
            const assignForm = document.getElementById('assignForm');
            if (assignForm) {
                assignForm.addEventListener('submit', function(e) {
                    const selectedStaff = document.querySelector('input[name="staff_user_id"]:checked');
                    const stageName = document.getElementById('stage_name');
                    const assignButton = document.getElementById('assignButton');
                    
                    if ({{ $staffMembers->count() }} > 0 && !selectedStaff) {
                        e.preventDefault();
                        alert('Please select a staff member to assign.');
                        return false;
                    }
                    
                    if (stageName && !stageName.value.trim()) {
                        e.preventDefault();
                        alert('Please enter a stage name.');
                        stageName.focus();
                        return false;
                    }
                    
                    // Disable button and show loading
                    if (assignButton) {
                        assignButton.disabled = true;
                        assignButton.innerHTML = `
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Assigning...
                        `;
                    }
                    
                    return true;
                });
            }
            
            // Staff option click effect
            document.querySelectorAll('.staff-option-label').forEach(label => {
                label.addEventListener('click', function(e) {
                    if (!e.target.closest('.checkmark-indicator')) {
                        // Add ripple effect
                        const ripple = document.createElement('span');
                        ripple.className = 'ripple-effect';
                        ripple.style.cssText = `
                            position: absolute;
                            border-radius: 50%;
                            background: rgba(99, 102, 241, 0.3);
                            transform: scale(0);
                            animation: ripple-animation 0.6s linear;
                            pointer-events: none;
                        `;
                        
                        const rect = this.getBoundingClientRect();
                        const size = Math.max(rect.width, rect.height);
                        const x = e.clientX - rect.left - size / 2;
                        const y = e.clientY - rect.top - size / 2;
                        
                        ripple.style.width = ripple.style.height = size + 'px';
                        ripple.style.left = x + 'px';
                        ripple.style.top = y + 'px';
                        
                        this.appendChild(ripple);
                        
                        setTimeout(() => {
                            ripple.remove();
                        }, 600);
                    }
                });
            });

            // Auto-suggest stage name based on staff selection
            const staffRadios = document.querySelectorAll('input[name="staff_user_id"]');
            const stageNameInput = document.getElementById('stage_name');
            
            if (staffRadios.length > 0 && stageNameInput) {
                staffRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.checked) {
                            const label = this.nextElementSibling;
                            const staffName = label.querySelector('.font-medium.text-white')?.textContent || '';
                            const currentStageName = stageNameInput.value;
                            
                            // Only auto-suggest if current value is the default
                            if (currentStageName === '{{ $department->name }} Review Stage {{ $existingStageCount + 1 }}' && staffName) {
                                stageNameInput.value = `{{ $department->name }} Review - ${staffName.split(' ')[0]}`;
                            }
                        }
                    });
                });
            }
        });
    </script>

    <style>
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
        
        .input-field {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            border-color: {{ $department->code == 'FIN' ? '#10b981' : ($department->code == 'ACC' ? '#3b82f6' : '#a855f7') }};
            box-shadow: 0 0 0 3px {{ $department->code == 'FIN' ? 'rgba(16, 185, 129, 0.1)' : ($department->code == 'ACC' ? 'rgba(59, 130, 246, 0.1)' : 'rgba(168, 85, 247, 0.1)') }};
        }
        
        .staff-option-label {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .staff-option-label:hover {
            transform: translateY(-1px);
        }
        
        .stage-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
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
            background: rgba(99, 102, 241, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
</x-app-layout-dark>