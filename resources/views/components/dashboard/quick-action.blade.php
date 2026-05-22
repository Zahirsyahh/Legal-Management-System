@props([
    'url' => '#',
    'icon' => '',
    'title' => 'Action',
    'subtitle' => 'Description',
    'variant' => 'primary',
])

<a href="{{ $url }}"
   class="btn-{{ $variant }} flex items-center justify-between p-4 rounded-xl group h-full">
    <div class="flex items-center space-x-3">
        <div class="p-2.5 rounded-lg {{ $variant === 'secondary' ? 'bg-white/5' : 'bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
        <div class="text-left">
            <h3 class="font-semibold text-sm {{ $variant === 'secondary' ? 'text-title' : 'text-white' }}">{{ $title }}</h3>
            <p class="text-xs {{ $variant === 'secondary' ? 'text-body' : 'text-white/80' }}">{{ $subtitle }}</p>
        </div>
    </div>
    <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
    </svg>
</a>
