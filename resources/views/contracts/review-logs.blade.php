<x-app-layout-dark title="Review History - {{ $contract->title }}">
    <div class="pb-8 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">
        
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
                    <h1 class="text-3xl font-bold text-gray-300">Review History</h1>
                    <p class="text-gray-400">{{ $contract->title }} • {{ $contract->contract_number }}</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-400">
                        {{ $reviewLogs->total() }} total entries
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter Options -->
        <div class="glass-card rounded-xl p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <div>
                    <label class="text-sm text-gray-400 mr-2">Filter by Action:</label>
                    <select class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300">
                        <option value="">All Actions</option>
                        <option value="approve">Approval</option>
                        <option value="revision">Revision</option>
                        <option value="reject">Rejection</option>
                        <option value="final_approve">Final Approval</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-sm text-gray-400 mr-2">Filter by Stage:</label>
                    <select class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300">
                        <option value="">All Stages</option>
                        @foreach($contract->reviewStages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->stage_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="ml-auto">
                    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm">
                        Export to CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Full History Logs -->
        <div class="glass-card rounded-xl p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-400">Date & Time</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-400">User</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-400">Action</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-400">Stage</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-400">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviewLogs as $log)
                        <tr class="border-b border-gray-800/50 hover:bg-gray-900/50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="text-sm text-gray-300">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center">
                                        <span class="text-xs text-blue-400">{{ substr($log->user->nama_user ?? 'S', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-300">{{ $log->user->nama_user ?? 'System' }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="py-3 px-4">
                                @php
                                    $actionColors = [
                                        'approve' => 'text-green-400 bg-green-500/10',
                                        'revision' => 'text-yellow-400 bg-yellow-500/10',
                                        'reject' => 'text-red-400 bg-red-500/10',
                                        'final_approve' => 'text-emerald-400 bg-emerald-500/10',
                                    ];
                                    $color = $actionColors[$log->action] ?? 'text-gray-400 bg-gray-500/10';
                                @endphp
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-medium {{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            
                            <td class="py-3 px-4">
                                @if($log->stage)
                                    <div class="text-sm text-gray-300">{{ $log->stage->stage_name }}</div>
                                    <div class="text-xs text-gray-500">
                                        Stage {{ $log->stage->sequence }} • {{ strtoupper($log->stage->stage_type) }}
                                    </div>
                                @else
                                    <span class="text-gray-500 text-sm">-</span>
                                @endif
                            </td>
                            
                            <td class="py-3 px-4">
                                @if($log->notes)
                                    <div class="max-w-xs">
                                        <div class="text-sm text-gray-300 line-clamp-2">{{ $log->notes }}</div>
                                        @if(strlen($log->notes) > 100)
                                            <button class="text-xs text-blue-400 hover:text-blue-300 mt-1" 
                                                    onclick="showFullNote(this, '{{ $log->id }}')">
                                                Show more
                                            </button>
                                            <div id="full-note-{{ $log->id }}" class="hidden">
                                                <div class="mt-2 p-3 bg-gray-800/50 rounded text-sm text-gray-300 whitespace-pre-line">
                                                    {{ $log->notes }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500 text-sm">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($reviewLogs->hasPages())
                <div class="mt-6">
                    {{ $reviewLogs->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function showFullNote(button, logId) {
            const noteDiv = document.getElementById('full-note-' + logId);
            if (noteDiv.classList.contains('hidden')) {
                noteDiv.classList.remove('hidden');
                button.textContent = 'Show less';
            } else {
                noteDiv.classList.add('hidden');
                button.textContent = 'Show more';
            }
        }
    </script>
</x-app-layout-dark>