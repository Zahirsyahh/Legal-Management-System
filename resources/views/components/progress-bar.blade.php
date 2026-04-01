{{-- resources/views/components/progress-bar.blade.php --}}
@props(['contract'])

@if($contract->isInReviewStageSystem())
@php
    $stages = $contract->reviewStages->sortBy('sequence');
    $totalStages = $stages->count();
@endphp

<div class="glass-card rounded-xl p-6 mb-6 overflow-visible">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-300">Review Progress</h3>
        <span class="text-sm text-gray-400">{{ $contract->progress_with_label }}</span>
    </div>
    
    <!-- Progress Bar -->
    <div class="h-2 bg-gray-700 rounded-full overflow-hidden mb-6">
        <div class="h-full bg-gradient-to-r from-blue-500 to-green-500 transition-all duration-500"
             style="width: {{ $contract->review_progress }}%"></div>
    </div>
    
    <!-- Stage Indicators -->
    <div class="relative overflow-visible">
        <!-- Background line (GARIS ABU-ABU) -->
        <div class="absolute h-0.5 bg-gray-700 -z-10" style="top: 32px; left: 0; right: 0;"></div>

        @if($totalStages < 8)
        {{-- SEDIKIT: menggunakan grid dengan auto-fit untuk spacing yang balanced --}}
        <div class="grid relative z-10 overflow-visible" style="grid-template-columns: repeat({{ $totalStages }}, minmax(0, 1fr)); padding-top: 12px;">
            @foreach($stages as $index => $stage)
                <div class="flex flex-col items-center relative overflow-visible">
                    <div class="relative mb-2 overflow-visible">
                        @if($stage->isActive())
                        {{-- Ping indicator: posisi di dalam area yang tidak terpotong --}}
                        <div class="absolute z-20" style="right: -6px; top: -6px;">
                            <div class="w-3.5 h-3.5 bg-blue-400 rounded-full animate-ping"></div>
                        </div>
                        @endif
                        
                        <div class="w-10 h-10 rounded-full flex items-center justify-center relative z-10
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
                    </div>
                    
                    <div class="text-center max-w-[120px]">
                        <p class="text-xs font-medium text-gray-300 truncate" title="{{ $stage->stage_name }}">
                            {{ Str::limit($stage->stage_name, 20) }}
                        </p>
                        <p class="text-xs text-gray-400 truncate mt-1" title="{{ $stage->assignedUser->name ?? 'Unassigned' }}">
                            {{ $stage->assignedUser ? Str::limit($stage->assignedUser->name, 15) : 'Unassigned' }}
                        </p>
                        <p class="text-xs mt-1 {{ $stage->status === 'completed' ? 'text-green-400' : ($stage->isActive() ? 'text-blue-400' : 'text-gray-500') }}">
                            {{ ucfirst($stage->status) }}
                        </p>
                        @if($stage->completed_at)
                        <p class="text-xs text-gray-500 mt-1">{{ $stage->completed_at->format('M d') }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @else
        {{-- 
            BANYAK (>=8): scrollable horizontal
            FIX: overflow-x:auto otomatis mengubah overflow-y menjadi auto juga (CSS spec).
            Solusi: beri padding-top pada inner flex agar ping indicator (-6px dari atas)
            tidak terpotong oleh clipping context scroll container.
        --}}
        <div class="stage-scroll-wrapper">
            {{-- Wrapper luar: hanya overflow-x:auto, tambah padding-top untuk ruang ping --}}
            <div class="stage-scroll" style="padding-top: 14px; padding-bottom: 4px;">
                <div class="flex gap-8 pb-4" style="min-width: max-content;">
                    @foreach($stages as $index => $stage)
                        <div class="flex flex-col items-center" style="min-width: 120px;">
                            <div class="relative mb-2" style="overflow: visible;">
                                @if($stage->isActive())
                                {{-- 
                                    Ping indicator: karena padding-top sudah 14px,
                                    posisi top:-6px tidak akan terpotong lagi.
                                --}}
                                <div class="absolute z-20" style="right: -6px; top: -6px;">
                                    <div class="w-3.5 h-3.5 bg-blue-400 rounded-full animate-ping"></div>
                                </div>
                                @endif
                                
                                <div class="w-10 h-10 rounded-full flex items-center justify-center relative z-10
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
                            </div>
                            
                            <div class="text-center w-[140px]">
                                <p class="text-xs font-medium text-gray-300 truncate" title="{{ $stage->stage_name }}">
                                    {{ Str::limit($stage->stage_name, 20) }}
                                </p>
                                <p class="text-xs text-gray-400 truncate mt-1" title="{{ $stage->assignedUser->name ?? 'Unassigned' }}">
                                    {{ $stage->assignedUser ? Str::limit($stage->assignedUser->name, 15) : 'Unassigned' }}
                                </p>
                                <p class="text-xs mt-1 {{ $stage->status === 'completed' ? 'text-green-400' : ($stage->isActive() ? 'text-blue-400' : 'text-gray-500') }}">
                                    {{ ucfirst($stage->status) }}
                                </p>
                                @if($stage->completed_at)
                                <p class="text-xs text-gray-500 mt-1">{{ $stage->completed_at->format('M d') }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
    
    <!-- Stats Footer -->
    <div class="mt-4 pt-3 border-t border-gray-700/50">
        <div class="flex gap-4 text-sm">
            <div class="flex items-baseline gap-2">
                <span class="text-gray-400 text-xs">Reviewers:</span>
                <span class="text-base font-semibold text-gray-300">
                    {{ $contract->reviewStages->where('is_user_stage', false)->count() }}
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-gray-400 text-xs">Progress:</span>
                <span class="text-base font-semibold text-gray-300">
                    {{ rtrim(rtrim(number_format($contract->review_progress, 1), '0'), '.') }}%
                </span>
            </div>
        </div>
    </div>
</div>

<style>
/* Wrapper untuk scroll */
.stage-scroll-wrapper {
    position: relative;
    width: 100%;
}

/*
 * FIX UTAMA:
 * overflow-x: auto + overflow-y: visible tidak bisa jalan bersamaan (CSS spec).
 * Browser akan override overflow-y jadi 'auto' secara otomatis.
 * Solusinya: gunakan padding-top agar konten tidak mepet ke tepi clipping area,
 * sehingga ping indicator yang overflow ke atas tetap terlihat penuh.
 */
.stage-scroll {
    scroll-behavior: smooth;
    overflow-x: auto;
    overflow-y: auto;  /* Dibiarkan auto, tapi padding-top sudah mengkompensasi */
    position: relative;
    width: 100%;
}

/* Styling scrollbar */
.stage-scroll::-webkit-scrollbar {
    height: 8px;
}

.stage-scroll::-webkit-scrollbar-track {
    background: #1f2937;
    border-radius: 10px;
    margin: 0 10px;
}

.stage-scroll::-webkit-scrollbar-thumb {
    background: #4B5563;
    border-radius: 10px;
}

.stage-scroll::-webkit-scrollbar-thumb:hover {
    background: #6B7280;
}

/* Memastikan semua container tidak memotong elemen yang overflow (untuk mode non-scroll) */
.glass-card,
.overflow-visible,
.grid,
.relative {
    overflow: visible !important;
}

/* Override khusus untuk scroll container — jangan ikut overflow:visible */
.stage-scroll {
    overflow-x: auto !important;
    overflow-y: auto !important;
}

/* Hilangkan border yang tidak diinginkan */
.glass-card {
    border: none !important;
}

/* Indicator ping */
.animate-ping {
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Pastikan tidak ada border outline */
*:focus {
    outline: none !important;
}
</style>
@endif