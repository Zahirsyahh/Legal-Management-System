<x-app-layout-dark title="User Review - {{ $contract->title }}">
    <div class="min-h-screen bg-gradient-to-br from-gray-900 to-black pb-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Background Pattern -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-green-500/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/5 rounded-full blur-3xl"></div>
        </div>

        <!-- Main Content -->
        <div class="relative max-w-6xl mx-auto pt-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <a href="{{ route('contracts.show', $contract) }}" 
                           class="group flex items-center gap-2 text-gray-400 hover:text-gray-300 transition-colors mb-3">
                            <div class="w-8 h-8 rounded-full bg-gray-800 group-hover:bg-gray-700 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium">Back to Contract</span>
                        </a>
                        <h1 class="text-3xl font-bold text-gray-300">
                            User Review Required
                        </h1>
                        <p class="text-gray-400">{{ $contract->title }}</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <div class="text-sm text-gray-400">Contract #</div>
                            <div class="font-mono text-gray-300">{{ $contract->contract_number }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Revision Notice -->
                @if($stage->revision_requested_by)
                <div class="glass-card rounded-xl p-5 mb-6 border-l-4 border-yellow-500/50 bg-yellow-500/5">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-yellow-300 mb-1">Revision Requested</h3>
                            <p class="text-gray-300">{{ $stage->revision_requested_by }} has requested your review of this contract.</p>
                            @if($stage->notes)
                            <div class="mt-2 p-3 bg-yellow-500/10 rounded-lg">
                                <p class="text-sm text-gray-300">{{ $stage->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Contract Details & Actions -->
                <div class="lg:col-span-2">
                    <!-- Contract Information -->
                    <div class="glass-card rounded-2xl p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-300 mb-4">Contract Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <div class="text-sm text-gray-400">Contract Type</div>
                                <div class="font-medium text-gray-300">{{ $contract->contract_type ?? 'Not specified' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-400">Counterparty</div>
                                <div class="font-medium text-gray-300">{{ $contract->counterparty_name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-400">Effective Date</div>
                                <div class="font-medium text-gray-300">
                                    @if($contract->effective_date)
                                        {{ \Carbon\Carbon::parse($contract->effective_date)->format('d M Y') }}
                                    @else
                                        Not set
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-400">Expiry Date</div>
                                <div class="font-medium text-gray-300">
                                    @if($contract->expiry_date)
                                        {{ \Carbon\Carbon::parse($contract->expiry_date)->format('d M Y') }}
                                    @else
                                        Not set
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="text-sm text-gray-400 mb-2">Description</div>
                            <div class="text-gray-300">{{ $contract->description ?? 'No description provided' }}</div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="text-sm text-gray-400 mb-2">Purpose</div>
                            <div class="text-gray-300">{{ $contract->purpose ?? 'Not specified' }}</div>
                        </div>
                    </div>

                    <!-- User Actions -->
                    <div class="glass-card rounded-2xl p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-300 mb-4">Your Actions</h2>
                        
                        @if($stage->status === 'assigned' || $stage->status === 'in_progress')
                        <!-- Continue Review to Next Stage -->
                        <div class="space-y-6">
                            <div class="p-4 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-xl border border-green-500/20">
                                <h3 class="font-semibold text-green-400 mb-3">▶️ Continue Review Process</h3>
                                <p class="text-sm text-gray-400 mb-4">
                                    After reviewing, you can continue the review process by jumping to a reviewer stage.
                                </p>
                                
                                <form action="{{ route('review-stages.user-continue-review', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                                Continue to Reviewer *
                                            </label>
                                            <select name="jump_to_stage_id" required
                                                    class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                                <option value="">-- Select reviewer to continue --</option>
                                                @foreach($availableStages as $availableStage)
                                                    @if(!$availableStage['is_user_stage'])
                                                    <option value="{{ $availableStage['id'] }}">
                                                        {{ ucfirst(str_replace('_', ' ', $availableStage['stage_name'])) }} 
                                                        (Assigned to: {{ $availableStage['assigned_user']['name'] ?? 'Unassigned' }})
                                                    </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                                Your Response *
                                            </label>
                                            <textarea name="user_response" rows="4" required
                                                      class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                      placeholder="Describe your response or the changes you made..."></textarea>
                                        </div>
                                        
                                        <button type="submit" 
                                                class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 rounded-lg font-medium text-white transition-all duration-300">
                                            ▶️ Continue Review
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Request Clarification -->
                            <div class="p-4 bg-gradient-to-r from-blue-500/10 to-cyan-500/10 rounded-xl border border-blue-500/20">
                                <h3 class="font-semibold text-blue-400 mb-3">❓ Need Clarification?</h3>
                                <p class="text-sm text-gray-400 mb-4">
                                    If you need clarification from a reviewer before proceeding.
                                </p>
                                
                                <form id="clarificationForm" action="{{ route('review-stages.user-request-clarification', [$contract, $stage]) }}" method="POST">
                                    @csrf
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                                Ask Clarification From *
                                            </label>
                                            <select name="clarification_stage_id" required
                                                    class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">-- Select reviewer --</option>
                                                @foreach($availableStages as $availableStage)
                                                    @if(!$availableStage['is_user_stage'])
                                                    <option value="{{ $availableStage['id'] }}">
                                                        {{ ucfirst(str_replace('_', ' ', $availableStage['stage_name'])) }} 
                                                        ({{ $availableStage['assigned_user']['name'] ?? 'Unassigned' }})
                                                    </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                                Your Questions *
                                            </label>
                                            <textarea name="clarification_questions" rows="4" required
                                                      class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                      placeholder="What do you need clarification on?"></textarea>
                                        </div>
                                        
                                        <button type="submit"
                                                class="w-full py-3 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 rounded-lg font-medium text-white transition-all duration-300">
                                            ❓ Ask for Clarification
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @elseif($stage->status === 'completed')
                        <div class="p-4 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-xl border border-green-500/20 text-center">
                            <div class="w-16 h-16 mx-auto rounded-full bg-green-500/20 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-green-400 mb-2">Review Completed</h3>
                            <p class="text-gray-300">You have completed your review for this stage.</p>
                            <p class="text-sm text-gray-400 mt-2">
                                Completed on: {{ $stage->completed_at ? $stage->completed_at->format('d M Y, H:i') : 'N/A' }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Info & Timeline -->
                <div class="space-y-6">
                    <!-- Stage Info -->
                    <div class="glass-card rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-gray-300 mb-4">Stage Information</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm text-gray-400">Stage Type</div>
                                <div class="font-medium text-gray-300">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/10 text-green-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        User Review
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <div class="text-sm text-gray-400">Assigned To</div>
                                <div class="font-medium text-gray-300">{{ auth()->user()->name }} (You)</div>
                            </div>
                            
                            <div>
                                <div class="text-sm text-gray-400">Status</div>
                                <div class="font-medium">
                                    @if($stage->status === 'assigned')
                                    <span class="text-yellow-400">Assigned - Action Required</span>
                                    @elseif($stage->status === 'in_progress')
                                    <span class="text-blue-400">In Progress</span>
                                    @elseif($stage->status === 'completed')
                                    <span class="text-green-400">Completed</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($stage->assigned_at)
                            <div>
                                <div class="text-sm text-gray-400">Assigned At</div>
                                <div class="font-medium text-gray-300">{{ $stage->assigned_at->format('d M Y, H:i') }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Review Timeline -->
                    <div class="glass-card rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-gray-300 mb-4">Review Timeline</h3>
                        
                        <div class="space-y-4">
                            @foreach($contract->reviewStages->sortBy('sequence') as $stageItem)
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    @if($stageItem->status === 'completed')
                                    <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    @elseif($stageItem->id === $stage->id)
                                    <div class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center">
                                        <div class="w-3 h-3 rounded-full bg-yellow-400 animate-pulse"></div>
                                    </div>
                                    @else
                                    <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center">
                                        <span class="text-xs text-gray-400">{{ $stageItem->sequence }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1">
                                    <div class="font-medium text-gray-300">
                                        @if($stageItem->is_user_stage)
                                            USER
                                        @else
                                            {{ strtoupper(str_replace('_', ' ', $stageItem->stage_name)) }}
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-400">
                                        {{ $stageItem->assignedUser->name ?? 'Unassigned' }}
                                    </div>
                                </div>
                                
                                <div class="text-sm">
                                    @if($stageItem->status === 'completed')
                                    <span class="text-green-400">✓</span>
                                    @elseif($stageItem->id === $stage->id)
                                    <span class="text-yellow-400">Current</span>
                                    @else
                                    <span class="text-gray-500">Pending</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="glass-card rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-gray-300 mb-4">Quick Links</h3>
                        
                        <div class="space-y-3">
                            <a href="{{ route('contracts.show', $contract) }}" 
                               class="flex items-center gap-3 p-3 bg-gray-800/50 hover:bg-gray-700/50 rounded-lg transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <span class="text-gray-300">View Contract Details</span>
                            </a>
                            
                            @if($contract->synology_folder_path)
                                <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-lg">
                                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-300 text-sm font-mono">{{ $contract->synology_folder_path }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout-dark>