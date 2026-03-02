@props(['logs', 'maxItems' => 10, 'showViewAll' => true])

<div class="review-history-card">
    <h3 class="text-lg font-semibold text-gray-300 mb-4 flex items-center justify-between">
        <span>Review History</span>
        <span class="text-xs text-gray-500 font-normal">
            {{ $logs->count() }} {{ Str::plural('entry', $logs->count()) }}
        </span>
    </h3>
    
    @if($logs->isEmpty())
        <div class="text-center py-8">
            <div class="w-12 h-12 mx-auto mb-3 text-gray-500">
                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-gray-500 text-sm">No review history yet.</p>
        </div>
    @else
        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2 scrollbar-thin">
            @foreach ($logs->take($maxItems) as $log)
                <div class="border border-gray-700/30 rounded-lg p-3 bg-gray-900/30 hover:bg-gray-900/50 transition-colors">
                    <!-- Same content as above -->
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-blue-500/20 flex items-center justify-center">
                                <span class="text-xs text-blue-400">
                                    {{ substr($log->user->name ?? 'S', 0, 1) }}
                                </span>
                            </div>
                            <span class="font-medium text-gray-200 text-sm">
                                {{ $log->user->name ?? 'System' }}
                            </span>
                        </div>
                        
                        @php
                            $actionColors = [
                                'approve' => 'bg-green-500/10 text-green-400 border-green-500/30',
                                'approve_jump' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                'revision' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                'reject' => 'bg-red-500/10 text-red-400 border-red-500/30',
                            ];
                            $actionColor = $actionColors[$log->action] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/30';
                        @endphp
                        
                        <span class="text-xs px-2 py-1 rounded-full border {{ $actionColor }}">
                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                        </span>
                    </div>
                    
                    @if($log->stage)
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="text-xs text-gray-400">
                                {{ $log->stage->stage_name }}
                            </span>
                        </div>
                    @endif
                    
                    @if($log->notes)
                        <div class="mt-2 p-2 bg-gray-800/50 rounded text-xs text-gray-300">
                            {{ Str::limit($log->notes, 100) }}
                        </div>
                    @endif
                    
                    <div class="mt-2 text-xs text-gray-500">
                        {{ $log->created_at->format('M d, H:i') }}
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($showViewAll && $logs->count() > $maxItems)
            <div class="mt-4 pt-4 border-t border-gray-700/30">
                <a href="#" 
                   class="text-sm text-gray-400 hover:text-gray-300 flex items-center justify-center gap-1">
                    View All ({{ $logs->count() }})
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        @endif
    @endif
</div>