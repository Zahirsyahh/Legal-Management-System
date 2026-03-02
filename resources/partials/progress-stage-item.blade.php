{{-- resources/views/partials/progress-stage-item.blade.php --}}
@php
    $isClickable = in_array($stage->status, ['assigned', 'in_progress', 'revision_requested']);
    $route = $stage->is_user_stage ? 
        route('review-stages.user', [$contract, $stage]) : 
        route('review-stages.show', [$contract, $stage]);
    
    // Warna berdasarkan stage type
    $bgColors = [
        'user' => 'from-green-500 to-emerald-600',
        'legal' => 'from-blue-500 to-cyan-600',
        'fat' => 'from-purple-500 to-pink-600',
        'admin_legal' => 'from-indigo-500 to-blue-600',
    ];
    
    $shadowColors = [
        'user' => 'shadow-green-500/30',
        'legal' => 'shadow-blue-500/30',
        'fat' => 'shadow-purple-500/30',
        'admin_legal' => 'shadow-indigo-500/30',
    ];
    
    $bgColor = $bgColors[$stage->stage_type] ?? 'from-gray-600 to-gray-700';
    $shadowColor = $shadowColors[$stage->stage_type] ?? '';
    
    // Icons
    $icons = [
        'user' => '<svg class="w-6 h-6 md:w-7 md:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
        'legal' => '<svg class="w-6 h-6 md:w-7 md:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>',
        'fat' => '<svg class="w-6 h-6 md:w-7 md:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        'admin_legal' => '<svg class="w-6 h-6 md:w-7 md:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
    ];
    
    $icon = $icons[$stage->stage_type] ?? '';
    
    // Status badges
    $statusBadges = [
        'completed' => '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-500/10 text-green-400"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Completed</span>',
        'assigned' => '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-500/10 text-yellow-400"><div class="w-2 h-2 rounded-full bg-yellow-400 mr-1 animate-pulse"></div>Ready</span>',
        'in_progress' => '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-500/10 text-blue-400"><div class="w-2 h-2 rounded-full bg-blue-400 mr-1 animate-pulse"></div>In Progress</span>',
        'revision_requested' => '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-500/10 text-red-400"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>Revision</span>',
        'pending' => '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-700 text-gray-400">Pending</span>',
    ];
    
    $statusBadge = $statusBadges[$stage->status] ?? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-700 text-gray-400">'.$stage->status.'</span>';
@endphp

<div class="relative z-10">
    <a href="{{ $isClickable ? $route : '#' }}"
       class="block group {{ $isClickable ? 'cursor-pointer hover:scale-105 transition-transform duration-200' : 'cursor-default' }}">
        <div class="flex flex-col items-center text-center">
            <!-- Stage Icon -->
            <div class="w-12 h-12 md:w-16 md:h-16 rounded-full mb-2 md:mb-3 flex items-center justify-center 
                bg-gradient-to-br {{ $bgColor }} shadow-lg {{ $shadowColor }}">
                {!! $icon !!}
            </div>
            
            <!-- Stage Info -->
            <div class="w-full px-1">
                <h4 class="font-semibold text-gray-300 mb-1 text-sm md:text-base truncate">
                    @if($stage->is_user_stage)
                        USER
                    @else
                        {{ strtoupper(str_replace('_', ' ', $stage->stage_name)) }}
                    @endif
                </h4>
                <p class="text-xs text-gray-400 mb-1 md:mb-2 truncate">
                    @if($stage->assignedUser)
                        {{ $stage->assignedUser->name }}
                    @else
                        Unassigned
                    @endif
                </p>
                
                <!-- Status Badge -->
                <div class="flex justify-center">
                    {!! $statusBadge !!}
                </div>
                
                <!-- Sequence Number -->
                <div class="mt-1 text-xs text-gray-500">
                    Stage {{ $stage->sequence }}
                </div>
            </div>
        </div>
    </a>
</div>