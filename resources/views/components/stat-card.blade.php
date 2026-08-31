@props([
    'title',
    'value',
    'subtitle' => null,
    'variant' => 'primary', // primary, danger, warning, blue, success
    'icon' => 'directions_car',
    'href' => null,
    'ping' => false,
])

@php
    $configs = [
        'primary' => [
            'text' => 'text-primary',
            'icon_bg' => 'bg-primary text-on-primary',
            'blob' => 'bg-primary/5',
        ],
        'danger' => [
            'text' => 'text-status-danger',
            'icon_bg' => 'bg-status-danger text-on-error',
            'blob' => 'bg-status-danger/10',
        ],
        'warning' => [
            'text' => 'text-status-warning',
            'icon_bg' => 'bg-status-warning text-white',
            'blob' => 'bg-status-warning/10',
        ],
        'blue' => [
            'text' => 'text-primary',
            'icon_bg' => 'bg-plate-blue text-white',
            'blob' => 'bg-plate-blue/10',
        ],
        'success' => [
            'text' => 'text-primary',
            'icon_bg' => 'bg-status-success text-white',
            'blob' => 'bg-status-success/10',
        ],
    ];

    $cfg = $configs[$variant] ?? $configs['primary'];
@endphp

@if ($href)
    <a href="{{ $href }}" class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant hover:shadow-md transition-all group relative overflow-hidden block">
@else
    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant hover:shadow-md transition-shadow group relative overflow-hidden">
@endif
    <div class="absolute -right-6 -top-6 w-24 h-24 {{ $cfg['blob'] }} rounded-full group-hover:scale-150 transition-transform duration-500 blur-xl pointer-events-none"></div>
    <h3 class="font-label-mono text-label-mono text-on-surface-variant uppercase tracking-wider mb-4">{{ $title }}</h3>
    <div class="flex items-end justify-between">
        <div>
            <div class="font-display-hud text-display-hud {{ $cfg['text'] }} mb-1 relative inline-flex items-baseline">
                {{ $value }}
                @if ($ping)
                    <span class="absolute -top-1 -right-3 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-status-danger opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-status-danger"></span>
                    </span>
                @endif
            </div>
            @if ($subtitle)
                <div class="text-sm text-on-surface-variant">{{ $subtitle }}</div>
            @endif
        </div>
        <div class="w-12 h-12 rounded-lg {{ $cfg['icon_bg'] }} flex items-center justify-center shadow-sm shrink-0">
            <span class="material-symbols-outlined text-[24px]">{{ $icon }}</span>
        </div>
    </div>
@if ($href)
    </a>
@else
    </div>
@endif
