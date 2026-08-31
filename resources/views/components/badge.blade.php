@props(['variant' => 'neutral', 'icon' => null])

@php
    $variantes = [
        'success' => [
            'class' => 'bg-status-success/10 text-status-success border border-status-success/20',
            'icon' => 'check_circle',
        ],
        'warning' => [
            'class' => 'bg-status-warning/10 text-status-warning border border-status-warning/20',
            'icon' => 'schedule',
        ],
        'danger' => [
            'class' => 'bg-status-danger/10 text-status-danger border border-status-danger/20',
            'icon' => 'error',
        ],
        'neutral' => [
            'class' => 'bg-surface-variant text-on-surface-variant',
            'icon' => null,
        ],
    ];

    $cfg = $variantes[$variant] ?? $variantes['neutral'];
    $iconName = $icon ?? $cfg['icon'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-bold uppercase tracking-wide ' . $cfg['class']]) }}>
    @if ($iconName)
        <span class="material-symbols-outlined text-[14px] leading-none">{{ $iconName }}</span>
    @endif
    <span>{{ $slot }}</span>
</span>
