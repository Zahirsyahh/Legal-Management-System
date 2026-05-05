{{-- resources/views/archives/_table_partial.blade.php --}}
@forelse($archives as $archive)
<tr class="group">
    <td class="font-mono text-cyan-300 font-medium">{{ $archive->record_id }}</td>
    <td>
        <div class="font-medium text-white">{{ $archive->doc_name }}</div>
        <div class="text-xs text-gray-500 mt-0.5">{{ $archive->created_at?->format('d M Y') ?? 'N/A' }}</div>
    </td>
    <td>
        @php
            $vClass = match($archive->version_status) {
                'latest'     => 'latest',
                'obsolete'   => 'obsolete',
                'superseded' => 'superseded',
                default      => 'latest'
            };
            $vLabel = \App\Models\Archive::VERSION_STATUS_LABEL[$archive->version_status] ?? $archive->version_status;
        @endphp
        <span class="version-badge {{ $vClass }}">{{ $vLabel }}</span>
    </td>
    <td>
        @php
            $vs = $archive->validity_status ?? 'valid';
            if (!in_array($vs, ['valid', 'expired', 'terminated'])) $vs = 'valid';
            $badgeLabel = match($vs) {
                'valid'      => 'Valid',
                'expired'    => 'Expired',
                'terminated' => 'Terminated',
            };
        @endphp
        <div class="flex flex-col gap-1.5">
            <span class="text-sm text-gray-300">
                @if($archive->end_date)
                    {{ $archive->end_date->format('d M Y') }}
                @else
                    <span class="text-gray-500 italic text-xs">No end date</span>
                @endif
            </span>
            <span class="validity-badge {{ $vs }} self-start">
                <span class="validity-dot"></span>{{ $badgeLabel }}
            </span>
        </div>
    </td>
    <td>
        <span class="text-sm text-gray-400">{{ $archive->doc_number ?: '—' }}</span>
    </td>
    <td>
        <div class="flex items-center justify-center gap-2">
            <a href="{{ route('archives.show', $archive->id) }}" class="action-icon view inline-flex" title="View">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </a>
            <a href="{{ route('archives.edit', $archive->id) }}" class="action-icon edit inline-flex" title="Edit">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </a>
            <form action="{{ route('archives.destroy', $archive->id) }}" method="POST" class="inline"
                  onsubmit="return confirmDelete('{{ addslashes($archive->doc_name) }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="action-icon delete inline-flex" title="Delete">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="py-16">
        <div class="empty-state">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-800/50 flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">No Archives Found</h3>
            <p class="text-gray-400">No archives match your search or filter criteria</p>
            <a href="{{ route('archives.create') }}"
               class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 rounded-lg text-white hover:shadow-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Your First Archive
            </a>
        </div>
    </td>
</tr>
@endforelse