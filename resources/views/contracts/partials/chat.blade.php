<x-app-layout-dark title="Contract Discussion - {{ $contract->title }}">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('contracts.show', $contract) }}" 
                           class="text-gray-400 hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </a>
                        <h1 class="text-2xl md:text-3xl font-bold">Contract Discussion</h1>
                    </div>
                    <div class="flex items-center gap-4 text-gray-400 flex-wrap">
                        <span class="font-medium text-gray-300">{{ $contract->title }}</span>
                        <span>•</span>
                        <span class="px-2 py-1 text-xs rounded-full {{ $contract->status_color }}">
                            {{ $contract->status_label }}
                        </span>
                        <span>•</span>
                        <span>#{{ $contract->contract_number ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <!-- New Comment Button -->
                    <button onclick="toggleNewCommentForm()"
                            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Comment
                    </button>
                    
                    <!-- Download Chat -->
                    <button onclick="exportChat()" 
                            class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Chat
                    </button>
                    
                    <!-- Back to Contract -->
                    <a href="{{ route('contracts.show', $contract) }}" 
                       class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Back to Contract
                    </a>
                </div>
            </div>
        </div>

        <!-- New Comment Form (Hidden by default) -->
        <div id="newCommentForm" class="glass-card rounded-xl p-6 mb-6 hidden animate-slide-down">
            <h3 class="text-lg font-semibold text-gray-300 mb-4">Add New Comment</h3>
            <form id="commentForm" action="{{ route('contracts.comments.store', $contract) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <!-- Comment Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Comment Type</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="comment_type" value="general" checked 
                                       class="text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-gray-300">General Comment</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="comment_type" value="question" 
                                       class="text-yellow-600 focus:ring-yellow-500">
                                <span class="ml-2 text-gray-300">Question</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="comment_type" value="feedback" 
                                       class="text-green-600 focus:ring-green-500">
                                <span class="ml-2 text-gray-300">Feedback</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Comment Content -->
                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-300 mb-2">Your Comment</label>
                        <textarea id="comment" name="comment" rows="4" 
                                  class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                  placeholder="Type your comment here..."></textarea>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="toggleNewCommentForm()"
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                            Post Comment
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Chat Container -->
        <div class="glass-card rounded-xl p-6">
            <!-- Chat Header -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-700/50">
                <div>
                    <h3 class="text-lg font-semibold text-gray-300">Contract Discussion</h3>
                    <p class="text-sm text-gray-500">All review history and discussions</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-400">
                        {{ $reviewLogs->total() }} total entries
                    </span>
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" 
                               placeholder="Search in discussion..." 
                               class="px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-gray-300 w-48 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="space-y-4">
                @forelse($reviewLogs as $log)
                    @php
                        // Helper variables
                        $metadata = is_array($log->metadata ?? null) ? $log->metadata : [];
                        
                        // Display notes priority
                        $displayNotes =
                            !empty(trim($log->notes ?? '')) ? $log->notes
                            : (!empty(trim($metadata['notes'] ?? '')) ? $metadata['notes']
                            : (!empty(trim($metadata['revision_notes'] ?? '')) ? $metadata['revision_notes']
                            : (!empty(trim($metadata['rejection_reason'] ?? '')) ? $metadata['rejection_reason']
                            : (!empty(trim($metadata['response'] ?? '')) ? $metadata['response']
                            : (!empty(trim($metadata['feedback'] ?? '')) ? $metadata['feedback']
                            : null)))));
                        
                        // Action colors
                        $actionColors = [
                            'approve' => 'bg-green-500/10 text-green-400 border-green-500/30',
                            'approve_jump' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                            'request_revision' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                            'revision_requested' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                            'revision' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                            'user_response' => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                            'reject' => 'bg-red-500/10 text-red-400 border-red-500/30',
                            'final_approve' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                        ];
                        
                        $actionColor = $actionColors[$log->action] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/30';
                        
                        // Action labels
                        $actionLabels = [
                            'approve' => 'Approved',
                            'approve_jump' => 'Approved & Jump',
                            'request_revision' => 'Revision Requested',
                            'revision_requested' => 'Revision Requested',
                            'revision' => 'Revision Submitted',
                            'user_response' => 'User Response',
                            'reject' => 'Rejected',
                            'final_approve' => 'Final Approved',
                        ];
                        
                        $actionLabel = $actionLabels[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action));
                    @endphp
                    
                    <div class="border border-gray-700/30 rounded-xl p-4 bg-gray-900/30 hover:bg-gray-900/50 transition-colors"
                         data-chat-entry
                         data-log-id="{{ $log->id }}"
                         data-date="{{ $log->created_at->format('M d, Y H:i') }}"
                         data-user="{{ $log->user->name ?? 'System' }}"
                         data-role="{{ $log->user->roles->first()->name ?? '' }}"
                         data-action="{{ $actionLabel }}"
                         data-stage="{{ $log->stage ? 'Stage ' . $log->stage->sequence . ': ' . $log->stage->stage_name : 'No Stage' }}"
                         data-notes="{{ $displayNotes ?? '' }}">
                        
                        <!-- Message Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-start gap-3">
                                <!-- User Avatar -->
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                                        <span class="text-sm font-semibold text-blue-400">
                                            {{ substr($log->user->name ?? 'S', 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- User Info -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-200">{{ $log->user->name ?? 'System' }}</span>
                                        @if($log->user->roles->first())
                                            <span class="px-2 py-0.5 text-xs bg-gray-800 text-gray-300 rounded-full">
                                                {{ $log->user->roles->first()->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $log->created_at->format('M d, Y H:i') }}
                                        @if($log->created_at->isToday())
                                            <span class="ml-2 text-blue-400">Today</span>
                                        @elseif($log->created_at->isYesterday())
                                            <span class="ml-2 text-gray-400">Yesterday</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Badge and Reply Button -->
                            <div class="flex items-center gap-2">
                                <!-- Reply Button -->
                                <button onclick="replyToMessage('{{ $log->user->name ?? 'System' }}', '{{ $log->id }}')"
                                        class="flex items-center gap-1 px-3 py-1.5 text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg transition-colors"
                                        title="Reply to this message">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    Reply
                                </button>
                                
                                <!-- Action Badge -->
                                <span class="text-xs px-3 py-1.5 rounded-full border font-medium {{ $actionColor }}">
                                    {{ $actionLabel }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Stage Info -->
                        @if($log->stage)
                            <div class="mb-3 p-3 bg-gray-800/40 rounded-lg">
                                <div class="flex items-center gap-2 mb-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-300">
                                        Stage {{ $log->stage->sequence }}: {{ $log->stage->stage_name }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        @if($log->stage->is_user_stage)
                                            (USER)
                                        @else
                                            ({{ strtoupper($log->stage->stage_type) }})
                                        @endif
                                    </span>
                                </div>
                                @if($log->stage->assignedUser)
                                    <div class="text-xs text-gray-400 ml-6">
                                        Assigned to: {{ $log->stage->assignedUser->name }}
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Message Content -->
                        @if($displayNotes)
                            <div class="mt-3 p-4 bg-gray-800/50 rounded-lg">
                                <p class="text-gray-300 whitespace-pre-line leading-relaxed">{{ $displayNotes }}</p>
                            </div>
                        @else
                            <div class="mt-3 p-3 bg-gray-800/30 rounded-lg">
                                <p class="text-gray-400 italic">No additional notes provided.</p>
                            </div>
                        @endif
                        
                        <!-- Replies Section (if any) -->
                        @if($log->replies && $log->replies->count() > 0)
                            <div class="mt-4 ml-8 space-y-3 border-l-2 border-gray-700 pl-4">
                                <div class="text-xs text-gray-500 font-medium mb-2">Replies ({{ $log->replies->count() }})</div>
                                @foreach($log->replies as $reply)
                                    <div class="border border-gray-700/20 rounded-lg p-3 bg-gray-900/30">
                                        <div class="flex items-start gap-2 mb-2">
                                            <div class="w-6 h-6 rounded-full bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs font-semibold text-purple-400">
                                                    {{ substr($reply->user->name ?? 'U', 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-300">{{ $reply->user->name ?? 'User' }}</span>
                                                    <span class="text-xs text-gray-500">{{ $reply->created_at->format('M d, H:i') }}</span>
                                                </div>
                                                <p class="text-sm text-gray-400 mt-1">{{ $reply->comment }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        <!-- Quick Reply Form (Hidden by default) -->
                        <div id="replyForm-{{ $log->id }}" class="mt-4 hidden">
                            <form class="space-y-2" action="{{ route('contracts.comments.reply', $log) }}" method="POST">
                                @csrf
                                <div class="flex gap-2">
                                    <textarea name="reply" rows="2" 
                                              class="flex-1 px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                              placeholder="Write a reply..."></textarea>
                                    <button type="submit"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm transition-colors self-end">
                                        Send
                                    </button>
                                    <button type="button" onclick="cancelReply('{{ $log->id }}')"
                                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm transition-colors self-end">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Metadata Info -->
                        @if(!empty($metadata))
                            <div class="mt-3 pt-3 border-t border-gray-700/30">
                                <details class="text-sm">
                                    <summary class="text-gray-400 cursor-pointer hover:text-gray-300">
                                        View Details
                                        <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </summary>
                                    <div class="mt-2 p-3 bg-gray-900/50 rounded-lg">
                                        <pre class="text-xs text-gray-400 overflow-x-auto">{{ json_encode($metadata, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </details>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto mb-4 text-gray-500">
                            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-medium text-gray-300 mb-2">No Discussion Yet</h4>
                        <p class="text-gray-500 max-w-md mx-auto">
                            Review actions and discussions will appear here as the contract progresses through the review stages.
                        </p>
                        <button onclick="toggleNewCommentForm()"
                                class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                            Start the Discussion
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($reviewLogs->hasPages())
                <div class="mt-8 pt-6 border-t border-gray-700/50">
                    {{ $reviewLogs->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .glass-card {
            backdrop-filter: blur(10px);
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        details[open] summary svg {
            transform: rotate(180deg);
        }
        
        details[open] summary {
            margin-bottom: 8px;
        }
        
        pre {
            font-family: 'Fira Code', 'Consolas', 'Monaco', monospace;
            font-size: 11px;
            line-height: 1.4;
        }
        
        @keyframes slide-down {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-down {
            animation: slide-down 0.3s ease-out;
        }
    </style>

    @push('scripts')
    <script>
        // Toggle new comment form
        function toggleNewCommentForm() {
            const form = document.getElementById('newCommentForm');
            const isHidden = form.classList.contains('hidden');
            
            if (isHidden) {
                form.classList.remove('hidden');
                form.classList.add('animate-slide-down');
                document.getElementById('comment').focus();
            } else {
                form.classList.add('hidden');
                form.classList.remove('animate-slide-down');
            }
        }
        
        // Reply to a specific message
        function replyToMessage(userName, logId) {
            // Hide all other reply forms
            document.querySelectorAll('[id^="replyForm-"]').forEach(form => {
                form.classList.add('hidden');
            });
            
            // Show the selected reply form
            const replyForm = document.getElementById(`replyForm-${logId}`);
            replyForm.classList.remove('hidden');
            replyForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            // Focus on textarea
            const textarea = replyForm.querySelector('textarea');
            if (textarea) {
                textarea.focus();
                textarea.value = `@${userName} `;
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
            }
        }
        
        // Cancel reply
        function cancelReply(logId) {
            const replyForm = document.getElementById(`replyForm-${logId}`);
            replyForm.classList.add('hidden');
        }
        
        // Handle new comment form submission
        document.getElementById('commentForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Disable button and show loading
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Posting...
            `;
            
            // Submit via AJAX
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('Comment posted successfully!', 'success');
                    
                    // Reset form
                    this.reset();
                    toggleNewCommentForm();
                    
                    // Reload page to show new comment
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message || 'Error posting comment', 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Post Comment';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error posting comment', 'error');
                submitButton.disabled = false;
                submitButton.textContent = 'Post Comment';
            });
        });
        
        // Handle reply form submissions
        document.querySelectorAll('form[action*="comments.reply"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                
                // Disable button and show loading
                submitButton.disabled = true;
                submitButton.innerHTML = `
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sending...
                `;
                
                // Submit via AJAX
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Reply posted successfully!', 'success');
                        
                        // Hide reply form
                        const replyForm = this.closest('[id^="replyForm-"]');
                        if (replyForm) {
                            replyForm.classList.add('hidden');
                        }
                        
                        // Clear textarea
                        const textarea = this.querySelector('textarea');
                        if (textarea) {
                            textarea.value = '';
                        }
                        
                        // Reload page to show new reply
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification(data.message || 'Error posting reply', 'error');
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error posting reply', 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
            });
        });
        
        function exportChat() {
            // Create a simple CSV export
            const rows = [];
            
            // Add header
            rows.push(['Date', 'User', 'Role', 'Action', 'Stage', 'Notes']);
            
            // Collect data from page
            document.querySelectorAll('[data-chat-entry]').forEach(entry => {
                const date = entry.dataset.date || '';
                const user = entry.dataset.user || '';
                const role = entry.dataset.role || '';
                const action = entry.dataset.action || '';
                const stage = entry.dataset.stage || '';
                const notes = entry.dataset.notes || '';
                
                rows.push([date, user, role, action, stage, notes.replace(/\n/g, ' ')]);
            });
            
            // Convert to CSV
            const csvContent = rows.map(row => 
                row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')
            ).join('\n');
            
            // Download
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            link.setAttribute('href', url);
            link.setAttribute('download', `contract-discussion-{{ $contract->contract_number }}-${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show notification
            showNotification('Discussion exported successfully!', 'success');
        }
        
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 animate-fade-in ${
                type === 'success' ? 'bg-green-600 text-white' : 
                type === 'error' ? 'bg-red-600 text-white' : 
                'bg-blue-600 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('animate-fade-out');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Add search functionality
        document.querySelector('input[placeholder="Search in discussion..."]')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const entries = document.querySelectorAll('[data-chat-entry]');
            
            entries.forEach(entry => {
                const text = entry.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    entry.style.display = '';
                    entry.classList.add('bg-yellow-500/10');
                } else {
                    entry.style.display = 'none';
                    entry.classList.remove('bg-yellow-500/10');
                }
            });
        });
    </script>
    @endpush
</x-app-layout-dark>