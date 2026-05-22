@props(['status' => 'draft'])

@php
    $statusConfig = [
        'draft' => [
            'bg' => 'bg-gray-100 dark:bg-gray-700',
            'text' => 'text-gray-600 dark:text-gray-400',
            'icon' => '<path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>'
        ],
        'submitted' => [
            'bg' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'text' => 'text-yellow-700 dark:text-yellow-400',
            'icon' => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        ],
        'under_review' => [
            'bg' => 'bg-blue-100 dark:bg-blue-900/30',
            'text' => 'text-blue-700 dark:text-blue-400',
            'icon' => '<path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>'
        ],
        'approved' => [
            'bg' => 'bg-green-100 dark:bg-green-900/30',
            'text' => 'text-green-700 dark:text-green-400',
            'icon' => '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        ],
        'archived' => [
            'bg' => 'bg-gray-100 dark:bg-gray-700',
            'text' => 'text-gray-500 dark:text-gray-400',
            'icon' => '<path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'
        ],
    ];
    $config = $statusConfig[$status] ?? $statusConfig['draft'];
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $config['icon'] !!}
    </svg>
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
