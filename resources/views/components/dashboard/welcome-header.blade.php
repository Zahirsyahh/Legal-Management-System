@php
    $roleEmoji = match(true) {
        auth()->user()->hasRole('admin') => '👑',
        auth()->user()->hasRole('legal') => '⚖️',
        auth()->user()->hasRole('user') => '👤',
        default => '📋',
    };

    $roleTitle = match(true) {
        auth()->user()->hasRole('admin') => 'System Overview',
        auth()->user()->hasRole('legal') => 'Legal Review Dashboard',
        auth()->user()->hasRole('user') => 'My Dashboard',
        default => 'Dashboard',
    };
@endphp

<div class="mb-10 animate-fade-in">
    <h1 class="text-3xl md:text-4xl font-bold mb-2 text-title">
        {{ $roleTitle }} {{ $roleEmoji }}
    </h1>
    <p class="text-body">
        Welcome back, <span class="text-primary font-semibold">{{ Auth::user()->name }}</span>
    </p>
</div>
