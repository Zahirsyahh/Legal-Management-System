<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="bg-gradient-to-r from-dark-800 to-dark-900 border-b border-gray-800">
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">NO</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">NOMOR SURAT KELUAR</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">DEPT</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">COUNTERPARTY</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">DESKRIPSI</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">EFFECTIVE DATE</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">EXPIRY DATE</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">SUBMITTED AT</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">REQUESTED BY</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">STATUS</span>
                </th>
                <th class="py-4 px-4 text-left">
                    <span class="font-semibold text-gray-300">TIPE</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
            @forelse ($contracts as $index => $contract)
            <tr class="table-row-hover group">
                <td class="py-4 px-4">
                    <span class="font-medium text-gray-300">{{ $contracts->firstItem() + $index }}</span>
                </td>
                <td class="py-4 px-4">
                    <span class="font-mono text-sm text-white">{{ $contract->contract_number ?? '-' }}</span>
                </td>
                <td class="py-4 px-4">
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-br from-cyan-500/10 to-blue-600/10 text-cyan-300 border border-cyan-500/20">
                        {{ $contract->department_code ?? '-' }}
                    </span>
                </td>
                <td class="py-4 px-4">
                    <span class="text-gray-300">{{ $contract->counterparty_name ?? '-' }}</span>
                </td>
                <td class="py-4 px-4">
                    <span class="text-gray-300 line-clamp-2">{{ $contract->description ?? '-' }}</span>
                </td>
                <td class="py-4 px-4">
                    <span class="text-gray-400 text-sm">{{ $contract->effective_date ? \Carbon\Carbon::parse($contract->effective_date)->format('d/m/Y') : '-' }}</span>
                </td>
                <td class="py-4 px-4">
                    <span class="text-gray-400 text-sm">{{ $contract->expiry_date ? \Carbon\Carbon::parse($contract->expiry_date)->format('d/m/Y') : '-' }}</span>
                </td>
                <td class="py-4 px-4">
                    <span class="text-gray-400 text-sm">{{ $contract->created_at ? \Carbon\Carbon::parse($contract->created_at)->format('d/m/Y H:i') : '-' }}</span>
                </td>
                <td class="py-4 px-4">
                    <span class="text-gray-300">{{ $contract->user->nama_user ?? $contract->user_id ?? '-' }}</span>
                </td>
                <td class="py-4 px-4">
                    @php
                        $statusClass = 'status-' . str_replace('_', '-', $contract->status);
                        $statusLabel = $statuses[$contract->status] ?? $contract->status;
                    @endphp
                    <span class="{{ $statusClass }} px-3 py-1.5 rounded-full text-sm font-medium">
                        {{ $statusLabel }}
                    </span>
                </td>
                <td class="py-4 px-4">
                    @if($contract->contract_type === 'surat')
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        Surat
                    </span>
                    @elseif($contract->contract_type === 'kontrak')
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium bg-purple-500/10 text-purple-400 border border-purple-500/20">
                        Kontrak
                    </span>
                    @else
                    <span class="text-gray-500">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="py-12 px-4 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div class="p-4 rounded-full bg-gradient-to-br from-gray-800/50 to-gray-900/50 mb-4">
                            <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-300 mb-2">Tidak Ada Data</h3>
                        <p class="text-gray-500 max-w-md">Tidak ada kontrak atau surat yang ditemukan dengan filter yang dipilih</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($contracts->hasPages())
<div class="px-6 py-4 border-t border-gray-800 bg-gradient-to-r from-dark-800/50 to-dark-900/50 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="text-sm text-gray-400">
        Menampilkan <span class="font-medium text-white">{{ $contracts->firstItem() ?? 0 }}</span> 
        sampai <span class="font-medium text-white">{{ $contracts->lastItem() ?? 0 }}</span> 
        dari <span class="font-medium text-white">{{ $contracts->total() }}</span> data
    </div>
    
    <div class="flex items-center gap-2">
        @if($contracts->onFirstPage())
        <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        @else
        <a href="{{ $contracts->previousPageUrl() }}" 
           class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        @endif
        
        @php
            $current = $contracts->currentPage();
            $last = $contracts->lastPage();
            $start = max(1, $current - 2);
            $end = min($last, $current + 2);
            
            if($start > 1) {
                echo '<a href="' . $contracts->url(1) . '" class="pagination-btn px-4 py-2 rounded-lg">1</a>';
                if($start > 2) {
                    echo '<span class="px-2 text-gray-500">...</span>';
                }
            }
            
            for($i = $start; $i <= $end; $i++) {
                if($i == $current) {
                    echo '<button class="pagination-btn active px-4 py-2 rounded-lg">' . $i . '</button>';
                } else {
                    echo '<a href="' . $contracts->url($i) . '" class="pagination-btn px-4 py-2 rounded-lg">' . $i . '</a>';
                }
            }
            
            if($end < $last) {
                if($end < $last - 1) {
                    echo '<span class="px-2 text-gray-500">...</span>';
                }
                echo '<a href="' . $contracts->url($last) . '" class="pagination-btn px-4 py-2 rounded-lg">' . $last . '</a>';
            }
        @endphp
        
        @if($contracts->hasMorePages())
        <a href="{{ $contracts->nextPageUrl() }}" 
           class="pagination-btn px-4 py-2 rounded-lg hover:bg-white/10">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @else
        <button class="pagination-btn px-4 py-2 rounded-lg disabled opacity-50 cursor-not-allowed">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        @endif
    </div>
</div>
@endif