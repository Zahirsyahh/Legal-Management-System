{{-- resources/views/components/progress-bar.blade.php --}}
@props(['contract'])

@if($contract->isInReviewStageSystem())
<div class="glass-card rounded-xl p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-300">Review Progress</h3>
        <span class="text-sm text-gray-400">{{ $contract->progress_with_label }}</span>
    </div>
    
    <!-- Progress Bar -->
    <div class="h-2 bg-gray-700 rounded-full overflow-hidden mb-6">
        <div class="h-full bg-gradient-to-r from-blue-500 to-green-500 transition-all duration-500"
             style="width: {{ $contract->review_progress }}%"></div>
    </div>
    
    <!-- Stage Indicators - DYNAMIC LAYOUT -->
    <div class="relative">
        <!-- Connecting Line -->
        <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-700 -z-10"></div>
        
        <!-- Stages Container -->
        <div class="flex justify-between relative z-10">
            @php
                $stages = $contract->reviewStages->sortBy('sequence');
                $totalStages = $stages->count();
                $stageWidth = $totalStages > 1 ? (100 / ($totalStages - 1)) : 100;
            @endphp
            
            @foreach($stages as $index => $stage)
                <div class="flex flex-col items-center"
                     style="width: {{ $stageWidth }}%; 
                            margin-left: {{ $index === 0 ? '0' : '0' }};
                            margin-right: {{ $index === $totalStages - 1 ? '0' : '0' }};">
                    
                    <!-- Stage Circle with Status -->
                    <div class="relative mb-2">
                        <!-- Active Indicator -->
                        @if($stage->isActive())
                        <div class="absolute -top-1 -right-1">
                            <div class="w-3 h-3 bg-blue-400 rounded-full animate-ping"></div>
                        </div>
                        @endif
                        
                        <!-- Stage Circle -->
                        <div class="w-10 h-10 rounded-full flex items-center justify-center 
                            {{ $stage->status === 'completed' ? 'bg-green-500 text-white shadow-lg shadow-green-500/30' : 
                               ($stage->isActive() ? 'bg-blue-500 text-white border-2 border-blue-300 shadow-lg shadow-blue-500/30' : 
                               ($stage->status === 'pending' ? 'bg-gray-700 text-gray-400' : 
                               'bg-gray-800 text-gray-300 border border-gray-600')) }}">
                            
                            @if($stage->status === 'completed')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            @elseif($stage->is_user_stage)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            @else
                            <span class="text-sm font-semibold">{{ $stage->sequence }}</span>
                            @endif
                        </div>
                        
                        <!-- Line Connection (kecuali stage terakhir) -->
                        @if($index < $totalStages - 1)
                        <div class="absolute top-5 left-10 right-0 h-0.5 
                            {{ $stage->status === 'completed' ? 'bg-green-500' : 'bg-gray-700' }} 
                            -z-10"></div>
                        @endif
                    </div>
                    
                    <!-- Stage Info -->
                    <div class="text-center max-w-[120px]">
                        <p class="text-xs font-medium text-gray-300 truncate" 
                           title="{{ $stage->stage_name }}">
                            {{ Str::limit($stage->stage_name, 20) }}
                        </p>
                        <p class="text-xs text-gray-400 truncate mt-1"
                           title="{{ $stage->assignedUser->name ?? 'Unassigned' }}">
                            {{ $stage->assignedUser ? Str::limit($stage->assignedUser->name, 15) : 'Unassigned' }}
                        </p>
                        <p class="text-xs mt-1 
                            {{ $stage->status === 'completed' ? 'text-green-400' : 
                               ($stage->isActive() ? 'text-blue-400' : 'text-gray-500') }}">
                            {{ ucfirst($stage->status) }}
                        </p>
                        
                        <!-- Dates -->
                        @if($stage->completed_at)
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $stage->completed_at->format('M d') }}
                        </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Stats Footer -->
    <div class="mt-6 pt-4 border-t border-gray-700/50 grid grid-cols-2 gap-4">
        <div class="text-center">
            <p class="text-sm text-gray-400">Reviewers</p>
            <p class="text-lg font-semibold text-gray-300">
                {{ $contract->reviewStages->where('is_user_stage', false)->count() }}
            </p>
        </div>
        <div class="text-center">
            <p class="text-sm text-gray-400">Progress</p>
            <p class="text-lg font-semibold text-gray-300">
                {{ $contract->review_progress }}%
            </p>
        </div>
    </div>
</div>
@endif