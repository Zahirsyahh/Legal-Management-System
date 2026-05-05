{{-- resources/views/archives/_pagination_partial.blade.php --}}
{{-- Dipakai oleh AJAX response untuk render ulang pagination (tabel & card) --}}
@if($archives->hasPages())
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 w-full">
    <div class="text-sm text-gray-400">
        Showing {{ $archives->firstItem() ?? 0 }} to {{ $archives->lastItem() ?? 0 }} of {{ $archives->total() }} records
    </div>
    <div class="pagination-modern">
        {{-- Prev --}}
        <button data-page="{{ $archives->currentPage() - 1 }}"
                class="page-link ajax-page {{ $archives->onFirstPage() ? 'disabled' : '' }}"
                {{ $archives->onFirstPage() ? 'disabled' : '' }}>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        @php
            $current = $archives->currentPage();
            $last    = $archives->lastPage();
            $start   = max(1, $current - 2);
            $end     = min($last, $current + 2);
        @endphp

        @if($start > 1)
            <button data-page="1" class="page-link ajax-page">1</button>
            @if($start > 2)<span class="text-gray-500 px-1">…</span>@endif
        @endif

        @for($i = $start; $i <= $end; $i++)
            <button data-page="{{ $i }}"
                    class="page-link ajax-page {{ $i === $current ? 'active' : '' }}"
                    {{ $i === $current ? 'disabled' : '' }}>{{ $i }}</button>
        @endfor

        @if($end < $last)
            @if($end < $last - 1)<span class="text-gray-500 px-1">…</span>@endif
            <button data-page="{{ $last }}" class="page-link ajax-page">{{ $last }}</button>
        @endif

        {{-- Next --}}
        <button data-page="{{ $archives->currentPage() + 1 }}"
                class="page-link ajax-page {{ !$archives->hasMorePages() ? 'disabled' : '' }}"
                {{ !$archives->hasMorePages() ? 'disabled' : '' }}>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</div>
@else
<div class="text-sm text-gray-400">
    Showing {{ $archives->count() }} of {{ $archives->total() }} records
</div>
@endif