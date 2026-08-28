@props(['variant' => 'neutral'])

@php
    $variantes = [
        'success' => 'bg-success-surface text-success',
        'warning' => 'bg-warning-surface text-warning',
        'danger' => 'bg-danger-surface text-danger',
        'neutral' => 'bg-surface-muted text-text-muted',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium '.($variantes[$variant] ?? $variantes['neutral'])]) }}>
    {{ $slot }}
</span>
