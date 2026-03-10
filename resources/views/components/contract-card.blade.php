@props([
    'contract',
    'compact'     => false,
    'showStatus'  => true,
    'actionLabel' => null,
    'actionUrl'   => null,
    'extraInfo'   => null,   // array: ['icon' => '...svg path...', 'label' => 'Submitted by', 'value' => 'John', 'extra' => '2 days ago']
])

@php
    $isStaticWorkflow = $contract->contract_type === 'surat'
                        && $contract->workflow_type === 'static';

    $route = $isStaticWorkflow
        ? route('surat.show', $contract)
        : route('contracts.show', $contract);

    $link = $actionUrl ?? $route;

    $displayNumber = $contract->contract_number
        ? (strlen($contract->contract_number) > 15
            ? substr($contract->contract_number, 0, 12) . '...'
            : $contract->contract_number)
        : '#' . ($contract->id ?? 'N/A');

    $statusColors = [
        'draft'           => 'bg-gray-500/20 text-gray-400',
        'submitted'       => 'bg-yellow-500/20 text-yellow-400',
        'under_review'    => 'bg-blue-500/20 text-blue-400',
        'revision_needed' => 'bg-amber-500/20 text-amber-400',
        'final_approved'  => 'bg-green-500/20 text-green-400',
        'number_issued'   => 'bg-green-500/20 text-green-400',
        'released'        => 'bg-emerald-500/20 text-emerald-400',
        'declined'        => 'bg-red-500/20 text-red-400',
        'cancelled'       => 'bg-gray-500/20 text-gray-400',
    ];

    $statusColor = $statusColors[$contract->status] ?? 'bg-gray-500/20 text-gray-400';
    $statusLabel = $contract->display_status ?? ucfirst(str_replace('_', ' ', $contract->status));
@endphp

@if($compact)
    {{-- ================================================ --}}
    {{-- COMPACT CARD                                     --}}
    {{-- ================================================ --}}
    <div class="relative group hover:bg-white/5 transition-all duration-200 rounded-lg px-3 py-3 border border-transparent hover:border-gray-700/50">
        <div class="flex items-start gap-3">

            {{-- Icon --}}
            <div class="flex-shrink-0 pt-0.5">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500/20 to-purple-500/20
                            flex items-center justify-center group-hover:scale-110 transition-transform">
                    @if($contract->contract_type === 'surat')
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @endif
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">

                {{-- Baris 1: Judul + Tombol --}}
                <div class="flex items-start justify-between gap-2">
                    <a href="{{ $link }}"
                       class="block text-sm font-medium text-title truncate hover:text-primary transition-colors leading-snug">
                        {{ $contract->title }}
                    </a>

                    @if($actionLabel)
                        <a href="{{ $link }}"
                           class="flex-shrink-0 px-2.5 py-1 text-xs bg-primary/10 text-primary rounded-lg
                                  hover:bg-primary/20 transition-colors flex items-center gap-1 whitespace-nowrap">
                            <span>{{ $actionLabel }}</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>

                {{-- Baris 2: Nomor • Tanggal • Status --}}
                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                    <span class="text-xs text-body">{{ $displayNumber }}</span>
                    <span class="text-xs text-body opacity-50">•</span>
                    <span class="text-xs text-body">{{ $contract->created_at->format('d/m') }}</span>

                    @if($showStatus)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }} max-w-[200px] truncate">
                            {{ $statusLabel }}
                        </span>
                    @endif
                </div>

                {{-- Baris 3 (opsional): Extra Info --}}
                @if($extraInfo)
                    <div class="flex items-center gap-1.5 mt-1.5 pt-1.5 border-t border-gray-700/20">

                        {{-- Icon --}}
                        @if(!empty($extraInfo['icon']))
                            <svg class="w-3 h-3 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $extraInfo['icon'] !!}
                            </svg>
                        @endif

                        {{-- Label --}}
                        @if(!empty($extraInfo['label']))
                            <span class="text-xs text-gray-500 flex-shrink-0">{{ $extraInfo['label'] }}:</span>
                        @endif

                        {{-- Value --}}
                        <span class="text-xs text-gray-400 font-medium truncate">
                            {{ $extraInfo['value'] ?? '-' }}
                        </span>

                        {{-- Extra (tanggal / waktu / info tambahan di kanan) --}}
                        @if(!empty($extraInfo['extra']))
                            <span class="text-xs text-gray-600 ml-auto flex-shrink-0">
                                {{ $extraInfo['extra'] }}
                            </span>
                        @endif

                    </div>
                @endif

            </div>
        </div>
    </div>

@else
    {{-- ================================================ --}}
    {{-- FULL CARD                                        --}}
    {{-- ================================================ --}}
    <div class="relative group hover:bg-white/5 transition-all duration-200 rounded-lg p-4 border border-gray-800/30 hover:border-gray-700">
        <div class="flex items-start gap-4">

            {{-- Icon --}}
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20
                            flex items-center justify-center group-hover:scale-110 transition-transform">
                    @if($contract->contract_type === 'surat')
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @endif
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-title">
                            <a href="{{ $link }}" class="hover:text-primary transition-colors">
                                {{ $contract->title }}
                            </a>
                        </h3>
                        <p class="text-sm text-body mt-1">{{ $contract->description ?? 'No description' }}</p>
                    </div>

                    @if($showStatus)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColor }} flex-shrink-0">
                            {{ $statusLabel }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                    <div>
                        <p class="text-xs text-body">Contract #</p>
                        <p class="text-sm font-medium text-title">{{ $displayNumber }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-body">Counterparty</p>
                        <p class="text-sm font-medium text-title">{{ $contract->counterparty_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-body">Type</p>
                        <p class="text-sm font-medium text-title">{{ ucfirst($contract->contract_type ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-body">Created</p>
                        <p class="text-sm font-medium text-title">{{ $contract->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endif