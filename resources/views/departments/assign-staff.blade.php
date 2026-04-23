{{-- resources/views/departments/assign-staff.blade.php --}}
@php
    $contract = $contractDepartment->contract;
    $department = $contractDepartment->department;
    
    $routePrefix = match($department->code) {
        'FIN' => 'finance',
        'ACC' => 'accounting',
        'TAX' => 'tax',
        default => 'finance'
    };
    
    $existingStageCount = $contract->reviewStages()
        ->where('stage_type', strtolower($department->code))
        ->count();
    
    $deptColors = [
        'FIN' => ['bg' => 'from-emerald-500/10 to-green-600/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/20', 'btn' => 'from-emerald-600 to-green-600', 'btn_hover' => 'from-emerald-700 to-green-700'],
        'ACC' => ['bg' => 'from-cyan-500/10 to-blue-600/10', 'text' => 'text-cyan-400', 'border' => 'border-cyan-500/20', 'btn' => 'from-cyan-600 to-blue-600', 'btn_hover' => 'from-cyan-700 to-blue-700'],
        'TAX' => ['bg' => 'from-purple-500/10 to-pink-600/10', 'text' => 'text-purple-400', 'border' => 'border-purple-500/20', 'btn' => 'from-purple-600 to-pink-600', 'btn_hover' => 'from-purple-700 to-pink-700'],
    ];
    $color = $deptColors[$department->code] ?? $deptColors['FIN'];
@endphp

<x-app-layout-dark title="Assign Reviewer - {{ $contract->title }}">

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 24px -1px rgba(0, 0, 0, 0.2);
    }
    
    .gradient-border {
        position: relative;
        background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
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
    
    .action-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .action-btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background: rgba(255, 255, 255, 0.3);
        opacity: 0;
        border-radius: 100%;
        transform: scale(1, 1) translate(-50%);
        transform-origin: 50% 50%;
    }
    
    .action-btn:focus:not(:active)::after {
        animation: ripple 1s ease-out;
    }
    
    .staff-card {
        transition: all 0.2s ease;
        cursor: pointer;
        background: rgba(15, 23, 42, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .staff-card:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(59, 130, 246, 0.4);
        transform: translateY(-1px);
    }
    
    .staff-card.selected {
        border-color: {{ $department->code == 'FIN' ? '#10b981' : ($department->code == 'ACC' ? '#06b6d4' : '#a855f7') }} !important;
        background: {{ $department->code == 'FIN' ? 'rgba(16, 185, 129, 0.1)' : ($department->code == 'ACC' ? 'rgba(6, 182, 212, 0.1)' : 'rgba(168, 85, 247, 0.1)') }} !important;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, {{ $department->code == 'FIN' ? '#10b981' : ($department->code == 'ACC' ? '#06b6d4' : '#a855f7') }}, {{ $department->code == 'FIN' ? '#059669' : ($department->code == 'ACC' ? '#0284c7' : '#7e22ce') }});
        color: white;
        border: none;
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.2);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.3);
    }
    
    @keyframes ripple {
        0% { transform: scale(0, 0); opacity: 0.5; }
        20% { transform: scale(25, 25); opacity: 0.3; }
        100% { opacity: 0; transform: scale(40, 40); }
    }
    
    @keyframes slide-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-slide-up {
        animation: slide-up 0.3s ease-out;
    }
    
    .status-badge {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.05));
        color: #4ade80;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    /* Contract Info Dropdown */
    .contract-info-header {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .contract-info-header:hover {
        background: rgba(255, 255, 255, 0.03);
    }
    .contract-info-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .contract-info-content.expanded {
        max-height: 800px;
    }
    .rotate-180 {
        transform: rotate(180deg);
    }
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route($routePrefix . '-admin.dashboard') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-white mb-6 transition-colors group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
        
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-gradient-to-br {{ $color['bg'] }} border {{ $color['border'] }}">
                    <svg class="w-6 h-6 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">Assign Reviewer</h1>
                    <p class="text-gray-400 mt-1">{{ $department->name }} Department • Stage {{ $existingStageCount + 1 }}</p>
                </div>
            </div>
            
            <div class="glass-card rounded-xl px-5 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-sm text-gray-300">{{ $contract->contract_number ?? '#' . $contract->id }}</span>
                    <span class="text-gray-600">|</span>
                    <span class="text-sm text-gray-400 truncate max-w-[250px]">{{ $contract->title }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== CONTRACT INFO DROPDOWN (TAMBAHAN) ===== --}}
    <div class="glass-card rounded-2xl overflow-hidden mb-8">
        <!-- Dropdown Header -->
        <div class="contract-info-header flex items-center justify-between px-6 py-4" id="contractInfoHeader">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $color['bg'] }} flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-300">Contract Information</h3>
                    <p class="text-xs text-gray-500">Click to view complete contract details</p>
                </div>
            </div>
            <svg id="contractInfoArrow" class="w-5 h-5 text-gray-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
        
        <!-- Dropdown Content -->
        <div id="contractInfoContent" class="contract-info-content border-t border-gray-800/50">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Column 1 -->
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Document Title</p>
                            <p class="text-sm text-gray-300 font-medium">{{ $contract->title }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Document Type</p>
                            <p class="text-sm text-gray-300">{{ $contract->contract_type ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Description</p>
                            <p class="text-sm text-gray-400">{{ $contract->description ?? 'No description provided' }}</p>
                        </div>
                    </div>
                    
                    <!-- Column 2 -->
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Counterparty</p>
                            <p class="text-sm text-gray-300 font-medium">{{ $contract->counterparty_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Counterparty Email</p>
                            <p class="text-sm text-gray-400">{{ $contract->counterparty_email ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Counterparty Phone</p>
                            <p class="text-sm text-gray-400">{{ $contract->counterparty_phone ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Counterparty Address</p>
                            <p class="text-sm text-gray-400">{{ $contract->counterparty_address ?? 'Not specified' }}</p>
                        </div>
                    </div>
                    
                    <!-- Column 3 -->
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Contract Value</p>
                            <p class="text-sm text-gray-300 font-medium">
                                {{ $contract->contract_value ? number_format($contract->contract_value, 2) . ' ' . ($contract->currency ?? 'IDR') : 'Not specified' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Effective Date</p>
                            <p class="text-sm text-gray-400">
                                {{ $contract->effective_date ? \Carbon\Carbon::parse($contract->effective_date)->format('d M Y') : 'Not set' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Expiry Date</p>
                            <p class="text-sm text-gray-400">
                                {{ $contract->expiry_date ? \Carbon\Carbon::parse($contract->expiry_date)->format('d M Y') : 'Not set' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Deadline</p>
                            <p class="text-sm {{ $contract->drafting_deadline && \Carbon\Carbon::parse($contract->drafting_deadline)->isPast() ? 'text-red-400' : 'text-gray-400' }}">
                                {{ $contract->drafting_deadline ? \Carbon\Carbon::parse($contract->drafting_deadline)->format('d M Y') : 'Not set' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Notes -->
                @if($contract->additional_notes)
                <div class="mt-6 pt-6 border-t border-gray-800/50">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Additional Notes</p>
                    <p class="text-sm text-gray-400">{{ $contract->additional_notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Assignment Form --}}
    <form action="{{ route($routePrefix . '-admin.assign.post', $contractDepartment) }}" method="POST" id="assignForm">
        @csrf
        <input type="hidden" name="stage_type" value="{{ strtolower($department->code) }}">
        <input type="hidden" name="sequence" value="{{ $existingStageCount + 1 }}">

        <div class="gradient-border rounded-xl overflow-hidden animate-slide-up">
            <div class="p-6">
                {{-- Header Form --}}
                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-gray-800/50">
                    <div class="p-2 rounded-lg bg-gradient-to-br {{ $color['bg'] }}">
                        <svg class="w-5 h-5 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Select Reviewer</h2>
                        <p class="text-xs text-gray-500">Choose a qualified staff member from {{ $department->name }} department</p>
                    </div>
                </div>

                {{-- Staff List --}}
                @if($staffMembers->count() > 0)
                    <div class="space-y-3">
                        @foreach($staffMembers as $staff)
                            <label for="staff_{{ $staff->id }}" 
                                   class="staff-card flex items-center justify-between p-4 rounded-xl transition-all duration-200">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br {{ $color['bg'] }} flex items-center justify-center border {{ $color['border'] }}">
                                        <span class="text-white font-bold text-md">
                                            {{ strtoupper(substr($staff->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-white">{{ $staff->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $staff->email }}</p>
                                        @if($staff->position)
                                            <p class="text-xs {{ $color['text'] }} mt-1 font-medium">{{ $staff->position }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="relative">
                                    <input type="radio" name="staff_user_id" id="staff_{{ $staff->id }}" 
                                           value="{{ $staff->id }}" 
                                           class="hidden peer"
                                           required>
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-600 flex items-center justify-center peer-checked:border-{{ $department->code == 'FIN' ? 'emerald' : ($department->code == 'ACC' ? 'cyan' : 'purple') }}-500 peer-checked:bg-{{ $department->code == 'FIN' ? 'emerald' : ($department->code == 'ACC' ? 'cyan' : 'purple') }}-500 transition-all duration-200">
                                        <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    
                    @error('staff_user_id')
                        <p class="mt-3 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                @else
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-800/50 mb-4">
                            <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-300 mb-2">No Staff Available</h3>
                        <p class="text-gray-500">There are no active staff members in the {{ $department->name }} department.</p>
                        <a href="{{ route('admin.users.index') }}" class="inline-block mt-4 text-sm {{ $color['text'] }} hover:underline">
                            Contact Administrator →
                        </a>
                    </div>
                @endif
            </div>

            {{-- Footer Buttons --}}
            <div class="flex items-center justify-end gap-4 px-6 py-4 bg-gray-900/30 border-t border-gray-800/50">
                <a href="{{ route($routePrefix . '-admin.dashboard') }}" 
                   class="px-6 py-2.5 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                    Cancel
                </a>
                
                <button type="submit" 
                        class="btn-primary px-6 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 flex items-center gap-2 action-btn"
                        {{ $staffMembers->count() == 0 ? 'disabled' : '' }}>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Assign & Start Review
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== CONTRACT INFO DROPDOWN =====
        const contractInfoHeader = document.getElementById('contractInfoHeader');
        const contractInfoContent = document.getElementById('contractInfoContent');
        const contractInfoArrow = document.getElementById('contractInfoArrow');
        
        if (contractInfoHeader && contractInfoContent) {
            contractInfoHeader.addEventListener('click', function() {
                contractInfoContent.classList.toggle('expanded');
                contractInfoArrow.classList.toggle('rotate-180');
            });
        }
        
        // Staff card selection styling
        const staffCards = document.querySelectorAll('.staff-card');
        const radioButtons = document.querySelectorAll('input[name="staff_user_id"]');
        
        function updateSelectedCard() {
            staffCards.forEach(card => {
                card.classList.remove('selected');
                const radio = card.querySelector('input[type="radio"]');
                if (radio && radio.checked) {
                    card.classList.add('selected');
                }
            });
        }
        
        // Add click handler to cards
        staffCards.forEach(card => {
            card.addEventListener('click', function(e) {
                const radio = this.querySelector('input[type="radio"]');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    updateSelectedCard();
                    
                    // Trigger change event
                    const changeEvent = new Event('change', { bubbles: true });
                    radio.dispatchEvent(changeEvent);
                }
            });
        });
        
        radioButtons.forEach(radio => {
            radio.addEventListener('change', updateSelectedCard);
        });
        
        // Form submission with loading state
        const form = document.getElementById('assignForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const selected = document.querySelector('input[name="staff_user_id"]:checked');
                
                if ({{ $staffMembers->count() }} > 0 && !selected) {
                    e.preventDefault();
                    alert('Please select a reviewer to assign.');
                    return false;
                }
                
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Assigning...
                    `;
                }
                
                return true;
            });
        }
    });
</script>
</x-app-layout-dark>