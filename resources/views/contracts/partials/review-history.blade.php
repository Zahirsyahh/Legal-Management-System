{{-- resources/views/contracts/partials/review-history.blade.php --}}
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

            $isJumpAction = in_array($log->action, [
                'approve_jump',
                'request_revision',
                'revision_requested',
            ]);

            $jumpToStage = null;
            $jumpToReviewer = null;

            if ($isJumpAction) {
                if (!empty($metadata['to_stage']['stage_name'])) {
                    $jumpToStage = $metadata['to_stage']['stage_name'];
                }

                if (!empty($metadata['to_reviewer']['name'])) {
                    $jumpToReviewer = $metadata['to_reviewer']['name'];
                }

                $jumpToStage ??= $metadata['to_stage_name'] ?? null;
                $jumpToReviewer ??= $metadata['to_reviewer_name'] ?? $metadata['target_reviewer'] ?? null;
            }

            $actionColors = [
                'approve' => 'bg-green-500/10 text-green-400 border-green-500/30',
                'approve_jump' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                'request_revision' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                'revision_requested' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                'revision' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                'user_response' => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                'reject' => 'bg-red-500/10 text-red-400 border-red-500/30',
                'final_approve' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                'stage_started' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
                'stage_completed' => 'bg-green-500/10 text-green-400 border-green-500/30',
                'stage_added' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
                'stage_deleted' => 'bg-red-500/10 text-red-400 border-red-500/30',
                'stage_removed' => 'bg-red-500/10 text-red-400 border-red-500/30',
                'workflow_updated' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
            ];

            $actionColor = $actionColors[$log->action]
                ?? 'bg-gray-500/10 text-gray-400 border-gray-500/30';

            $actionLabels = [
                'approve' => 'Approved',
                'approve_jump' => 'Approved & Jump',
                'request_revision' => 'Revision Requested',
                'revision_requested' => 'Revision Requested',
                'revision' => 'Revision Submitted',
                'user_response' => 'User Response',
                'reject' => 'Rejected',
                'final_approve' => 'Final Approved',
                'stage_started' => 'Stage Started',
                'stage_completed' => 'Stage Completed',
                'stage_added' => 'Stage Added',
                'stage_deleted' => 'Stage Deleted',
                'stage_removed' => 'Stage Removed',
                'workflow_updated' => 'Workflow Updated',
                'sequence_changed' => 'Sequence Changed',
                'reviewer_changed' => 'Reviewer Changed',
            ];

            $actionLabel = $actionLabels[$log->action]
                ?? ucfirst(str_replace('_', ' ', $log->action));
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
                                            'ADMIN_FIN' => 'Admin Finance',
                                            'ADMIN_ACC' => 'Admin Accounting',
                                            'ADMIN_TAX' => 'Admin Tax',
                                            'STAFF_FIN' => 'Staff Finance',
                                            'STAFF_ACC' => 'Staff Accounting',
                                            'STAFF_TAX' => 'Staff Tax',
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
                            
                            @if($log->user && $log->user->email)
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $log->user->email }}
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <span class="text-xs px-3 py-1.5 rounded-full border font-medium {{ $actionColor }} shadow-sm">
                        {{ $actionLabel }}
                    </span>
                </div>
                
                @if($log->stage_id && $log->stage)
                    <div class="mb-3 p-3 bg-gray-800/40 rounded-lg border border-gray-700/50">
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="text-gray-300 font-medium">
                                Stage {{ $log->stage->sequence }}: {{ $log->stage->stage_name }}
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-700 text-gray-300">
                                @if($log->stage->is_user_stage)
                                    USER
                                @else
                                    {{ strtoupper($log->stage->stage_type) }}
                                @endif
                            </span>
                        </div>
                        
                        @if($log->stage->assignedUser)
                            <div class="flex items-center gap-2 mt-2 ml-6 text-xs">
                                <div class="w-5 h-5 rounded-full bg-indigo-500/20 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-1 flex-wrap">
                                    <span class="text-gray-400">Reviewer:</span>
                                    <span class="font-medium text-indigo-300">
                                        {{ $log->stage->assignedUser->nama_user }}
                                    </span>
                                    @if($log->stage->assignedUser->jabatan)
                                        <span class="px-1.5 py-0.5 rounded bg-indigo-900/30 text-indigo-300 text-xs">
                                            {{ $log->stage->assignedUser->jabatan }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-2 mt-2 ml-6 text-xs">
                                <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <span class="text-gray-500">No reviewer assigned</span>
                            </div>
                        @endif
                        
                        <div class="flex items-center gap-4 mt-2 ml-6 text-xs text-gray-500">
                            @if($log->stage->assigned_at)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Assigned: {{ $log->stage->assigned_at->format('M d, H:i') }}
                                </span>
                            @endif
                            @if($log->stage->started_at)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    </svg>
                                    Started: {{ $log->stage->started_at->format('M d, H:i') }}
                                </span>
                            @endif
                            @if($log->stage->completed_at)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Completed: {{ $log->stage->completed_at->format('M d, H:i') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($log->action === 'stage_added' && isset($metadata['reviewer_name']))
                    <div class="mb-3 p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-lg">
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span class="text-indigo-300 font-medium">New Stage Added</span>
                        </div>
                        <div class="mt-2 ml-6 space-y-1">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-400">Stage:</span>
                                <span class="font-medium text-gray-300">{{ $metadata['stage_name'] ?? 'Unknown' }}</span>
                                @if(isset($metadata['sequence']))
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-900/30 text-indigo-300">
                                        Sequence {{ $metadata['sequence'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-400">Assigned to:</span>
                                <span class="font-medium text-indigo-300">{{ $metadata['reviewer_name'] }}</span>
                                @if(isset($metadata['reviewer_role']))
                                    <span class="text-xs px-1.5 py-0.5 rounded bg-indigo-900/30 text-indigo-300">
                                        {{ $metadata['reviewer_role'] }}
                                    </span>
                                @endif
                            </div>
                            @if(isset($metadata['added_by']))
                                <div class="text-xs text-gray-500">
                                    Added by: {{ $metadata['added_by'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($log->action === 'stage_deleted' && isset($metadata['stage_name']))
                    <div class="mb-3 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span class="text-red-300 font-medium">Stage Deleted</span>
                        </div>
                        <div class="mt-2 ml-6 space-y-1">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-400">Stage:</span>
                                <span class="font-medium text-gray-300">{{ $metadata['stage_name'] }}</span>
                                @if(isset($metadata['sequence']))
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-900/30 text-red-300">
                                        Sequence {{ $metadata['sequence'] }}
                                    </span>
                                @endif
                            </div>
                            @if(isset($metadata['reviewer']))
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-400">Reviewer:</span>
                                    <span class="font-medium text-red-300">{{ $metadata['reviewer'] }}</span>
                                </div>
                            @endif
                            @if(isset($metadata['deleted_by']))
                                <div class="text-xs text-gray-500">
                                    Deleted by: {{ $metadata['deleted_by'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($log->action === 'workflow_updated' && !empty($log->metadata['changes']))
                    <div class="mt-2 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                        <div class="flex items-center gap-2 text-sm font-medium text-yellow-400 mb-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Workflow Changes:
                        </div>
                        <div class="text-sm text-yellow-300 ml-6 space-y-1">
                            @foreach($log->metadata['changes'] as $change)
                                <div class="flex items-start gap-2">
                                    <span class="text-yellow-400">•</span>
                                    <span>{{ is_array($change) ? json_encode($change) : $change }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($isJumpAction && ($jumpToStage || $jumpToReviewer))
                    <div class="mb-3 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                            <span class="text-blue-300 font-medium">
                                {{ $log->action === 'approve_jump' ? 'Jumped to:' : 'Requested revision to:' }}
                            </span>
                        </div>
                        
                        <div class="mt-2 ml-6 space-y-2">
                            @if($jumpToStage)
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-400">Stage:</span>
                                    <span class="font-medium text-blue-300">{{ $jumpToStage }}</span>
                                </div>
                            @endif
                            
                            @if($jumpToReviewer)
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-400">Reviewer:</span>
                                    <span class="font-medium text-blue-300">{{ $jumpToReviewer }}</span>
                                </div>
                            @endif
                            
                            @if(isset($metadata['jump_reason']))
                                <div class="text-xs text-gray-400 mt-1">
                                    <span class="text-gray-500">Reason:</span> {{ $metadata['jump_reason'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($displayNotes)
                    <div class="mt-3 p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                        <div class="flex items-start gap-2 mb-2">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-300">Notes:</span>
                        </div>
                        <div class="ml-6">
                            <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">
                                {{ $displayNotes }}
                            </p>
                        </div>
                        
                        @if($log->action === 'request_revision' && isset($metadata['feedback_for_user']))
                            <div class="mt-3 pt-3 border-t border-gray-700">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-yellow-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                    <span class="text-sm font-medium text-yellow-300">Feedback for User:</span>
                                </div>
                                <div class="ml-6 mt-1">
                                    <p class="text-yellow-200 text-sm leading-relaxed whitespace-pre-line">
                                        {{ $metadata['feedback_for_user'] }}
                                    </p>
                                </div>
                            </div>
                        @endif
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
                        <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 rounded-full text-xs">
                            Today
                        </span>
                    @elseif($log->created_at->isYesterday())
                        <span class="px-2 py-0.5 bg-gray-500/10 text-gray-400 rounded-full text-xs">
                            Yesterday
                        </span>
                    @elseif($log->created_at->gt(now()->subDays(7)))
                        <span class="px-2 py-0.5 bg-green-500/10 text-green-400 rounded-full text-xs">
                            This week
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif