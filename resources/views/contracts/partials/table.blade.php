<div id="contractsTable">
    @if($contracts->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-800/50">
                <thead>
                    <tr class="bg-dark-800/30 backdrop-blur-sm">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-2/5">
                            Document Details
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-1/5">
                            Counterparty
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-1/6">
                            Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-1/6">
                            Created On
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-1/6">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-800/30">
                    @foreach($contracts as $contract)
                    <tr class="hover:bg-white/2 transition-all duration-200 group">
                        
                        <!-- Contract Details -->
                        <td class="px-4 py-3">
                            <div class="flex items-start gap-2">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500/20 to-purple-500/20 
                                                flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-300 group-hover:text-white transition-colors text-xs truncate max-w-[180px]">
                                        {{ Str::limit($contract->title, 35) }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5 truncate max-w-[150px]">
                                        #{{ $contract->contract_number ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-600 mt-0.5">
                                        {{ $contract->contract_type ?? 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Counterparty -->
                        <td class="px-4 py-3">
                            <div class="text-xs text-gray-300 truncate max-w-[120px]">
                                {{ $contract->counterparty_name ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500 truncate max-w-[120px]">
                                @if($contract->counterparty_email)
                                    {{ $contract->counterparty_email }}
                                @endif
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3">
                            @php
                                $statusConfig = [
                                    'draft' => [
                                        'bg'     => 'bg-gray-700/30',
                                        'text'   => 'text-gray-300',
                                        'border' => 'border-gray-600/30',
                                        'icon'   => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
                                    ],
                                    'submitted' => [
                                        'bg'     => 'bg-yellow-500/20',
                                        'text'   => 'text-yellow-300',
                                        'border' => 'border-yellow-500/30',
                                        'icon'   => 'M13 5l7 7-7 7M5 5l7 7-7 7'
                                    ],
                                    'under_review' => [
                                        'bg'     => 'bg-blue-500/20',
                                        'text'   => 'text-blue-300',
                                        'border' => 'border-blue-500/30',
                                        'icon'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                                    ],
                                    'revision_needed' => [
                                        'bg'     => 'bg-amber-500/20',
                                        'text'   => 'text-amber-300',
                                        'border' => 'border-amber-500/30',
                                        'icon'   => 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                                    ],
                                    'final_approved' => [
                                        'bg'     => 'bg-green-500/20',
                                        'text'   => 'text-green-300',
                                        'border' => 'border-green-500/30',
                                        'icon'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                                    ],
                                    'number_issued' => [
                                        'bg'     => 'bg-green-500/20',
                                        'text'   => 'text-green-300',
                                        'border' => 'border-green-500/30',
                                        'icon'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                                    ],
                                    'declined' => [
                                        'bg'     => 'bg-red-500/20',
                                        'text'   => 'text-red-300',
                                        'border' => 'border-red-500/30',
                                        'icon'   => 'M6 18L18 6M6 6l12 12'
                                    ],
                                    'released' => [
                                        'bg'     => 'bg-emerald-500/20',
                                        'text'   => 'text-emerald-300',
                                        'border' => 'border-emerald-500/30',
                                        'icon'   => 'M5 13l4 4L19 7'
                                    ],
                                    'cancelled' => [
                                        'bg'     => 'bg-gray-600/30',
                                        'text'   => 'text-gray-400',
                                        'border' => 'border-gray-500/30',
                                        'icon'   => 'M6 18L18 6M6 6l12 12'
                                    ],
                                ];

                                $config = $statusConfig[$contract->status] ?? $statusConfig['draft'];

                                // Hanya ambil stage info untuk status yang relevan
                                $needsStageInfo = in_array($contract->status, ['under_review', 'revision_needed']);

                                $reviewerName = null;
                                $stageTotal   = 0;
                                $stageDone    = 0;

                                if ($needsStageInfo) {
                                    $activeStage  = $contract->currentReviewStage();
                                    $reviewerName = $activeStage?->assignedUser?->nama_user;
                                    $stageTotal   = $contract->reviewStages->count();
                                    $stageDone    = $contract->reviewStages->where('status', 'completed')->count();
                                }
                            @endphp

                            {{-- Badge Status --}}
                            <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium
                                        {{ $config['bg'] }} {{ $config['text'] }} border {{ $config['border'] }}">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}" />
                                </svg>
                                <span class="text-xs">{{ $contract->status_label }}</span>
                            </div>

                            {{-- Info Reviewer + Stage (hanya muncul saat under_review / revision_needed) --}}
                            @if($needsStageInfo)
                                {{-- Nama Reviewer --}}
                                <div class="flex items-center gap-1 mt-1.5">
                                    <svg class="w-3 h-3 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-xs text-gray-400 truncate max-w-[110px]">
                                        {{ $reviewerName ?? 'Unassigned' }}
                                    </span>
                                </div>

                                {{-- Progress Stage (hanya tampil jika ada stage) --}}
                                @if($stageTotal > 0)
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <span class="text-xs text-gray-500">
                                            Stage {{ $stageDone }}/{{ $stageTotal }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        </td>

                        <!-- Timeline -->
                        <td class="px-4 py-3">
                            <div class="text-xs text-gray-300">
                                {{ $contract->created_at->format('M d, Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $contract->created_at->diffForHumans() }}
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1" style="min-width: 140px;">
                                <!-- View -->
                                <a href="{{ route('contracts.show', $contract) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-blue-400 hover:text-blue-300 
                                          hover:bg-blue-500/10 transition-all duration-200"
                                   title="View Details">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                <!-- Edit (draft only) -->
                                @if($contract->status === 'draft' && auth()->id() === $contract->user_id)
                                    <a href="{{ route('contracts.edit', $contract) }}" 
                                       class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-green-400 hover:text-green-300 
                                              hover:bg-green-500/10 transition-all duration-200"
                                       title="Edit Draft">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                @else
                                    <div class="w-7 h-7"></div>
                                @endif

                                <!-- Submit (draft only) -->
                                @if($contract->status === 'draft' && auth()->id() === $contract->user_id)
                                    <form action="{{ route('contracts.submit', $contract) }}" method="POST" class="inline" onsubmit="return confirm('Submit this contract for review?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-yellow-400 hover:text-yellow-300 hover:bg-yellow-500/10 transition-all duration-200" title="Submit for Review">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <div class="w-7 h-7"></div>
                                @endif

                                <!-- Return to Draft (Admin only, for submitted contracts) -->
                                @role('admin')
                                    @if($contract->status === 'submitted')
                                        <form action="{{ route('contracts.return-to-draft', $contract->id) }}"  
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Return this contract to draft? This will allow the creator to edit it again.')">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-orange-400 hover:text-orange-300 hover:bg-orange-500/10 transition-all duration-200"
                                                    title="Return to Draft">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <div class="w-7 h-7"></div>
                                    @endif
                                @endrole

                                <!-- Delete -->
                                @can('delete', $contract)
                                    <form action="{{ route('contracts.destroy', $contract) }}" method="POST" class="inline" onsubmit="return confirmDeleteSimple('{{ addslashes($contract->title) }}', '{{ auth()->user()->hasRole('admin') ? 'true' : 'false' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all duration-200" title="{{ auth()->user()->hasRole('admin') ? 'Admin Delete' : 'Delete Draft' }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <div class="w-7 h-7"></div>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-800/30">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-400">
                    Page {{ $contracts->currentPage() }} of {{ $contracts->lastPage() }}
                </div>
                <div class="flex items-center gap-2">
                    {{ $contracts->withQueryString()->links() }}
                </div>
            </div>
        </div>

    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="relative inline-block mb-6">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500/10 to-purple-500/10 
                            flex items-center justify-center mx-auto">
                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="absolute -inset-4 bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-full blur-xl"></div>
            </div>
            <h3 class="text-xl font-semibold text-gray-300 mb-2">
                @if(request()->hasAny(['status', 'search', 'contract_type', 'date_range']))
                    No documents found
                @else
                    No documents yet
                @endif
            </h3>
            <p class="text-gray-500 max-w-md mx-auto mb-8">
                @if(request()->hasAny(['status', 'search', 'contract_type', 'date_range']))
                    No documents match your current filters. Try adjusting your search criteria.
                @else
                    Start by creating your first contract review request.
                @endif
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4">
                @if(request()->hasAny(['status', 'search', 'contract_type', 'date_range']))
                    <a href="{{ route('contracts.index') }}" class="glass-btn-secondary px-6 py-3 rounded-xl font-medium flex items-center gap-2 transition-all hover:scale-[1.02]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear All Filters
                    </a>
                @endif
                <a href="{{ route('contracts.create') }}" class="glass-btn-primary px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 rounded-xl font-medium flex items-center gap-2 transition-all hover:scale-[1.02] group">
                    <svg class="w-5 h-5 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Contract
                </a>
            </div>
        </div>
    @endif
</div>