@props([
    'icon' => null,
    'iconBg' => 'bg-blue-500/20',
    'iconColor' => 'text-blue-400',
    'label' => '',
    'value' => '0',
    'description' => '',
])

<div class="stat-card rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        @if($icon)
            <div class="p-3 rounded-xl {{ $iconBg }}">
                <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            </div>
        @endif
        <span class="text-sm text-body">{{ $label }}</span>
    </div>
    <h3 class="text-3xl font-bold text-title">{{ $value }}</h3>
    <p class="text-body text-sm mt-2">{{ $description }}</p>
</div>
