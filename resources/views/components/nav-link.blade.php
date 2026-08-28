@props(['route', 'pattern', 'mobile' => false])

@php
    $activo = request()->routeIs($pattern);
    $base = $mobile
        ? 'flex items-center gap-2.5 rounded-md px-3 py-2 text-sm transition-colors'
        : 'flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm transition-colors';
    $estado = $activo
        ? 'bg-accent-surface font-medium text-accent'
        : 'text-text-muted hover:bg-surface-muted hover:text-text';
@endphp

<a href="{{ route($route) }}" {{ $attributes->merge(['class' => $base.' '.$estado]) }}>
    {{ $icon }}
    {{ $slot }}
</a>
