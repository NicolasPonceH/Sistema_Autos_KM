@props(['variant' => 'primary', 'as' => 'button', 'type' => 'submit', 'size' => 'md'])

@php
    $base = 'font-bold inline-flex items-center justify-center gap-2 transition-all duration-200 transform cursor-pointer disabled:pointer-events-none disabled:opacity-50 select-none';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg',
        'md' => 'px-4 py-2 text-sm rounded-lg shadow-sm hover:shadow-md hover:-translate-y-0.5',
        'lg' => 'px-6 py-2.5 text-base rounded-xl shadow-sm hover:shadow-md hover:-translate-y-0.5',
    ];

    $variantes = [
        'primary' => 'bg-primary text-on-primary hover:bg-primary-container',
        'secondary' => 'bg-surface-container-lowest text-primary border border-primary hover:bg-primary-fixed',
        'danger' => 'bg-status-danger text-white hover:bg-red-700',
        'danger-ghost' => 'text-status-danger hover:underline p-0 border-0 bg-transparent shadow-none transform-none',
        'link' => 'text-primary font-bold text-sm hover:underline p-0 border-0 bg-transparent shadow-none transform-none',
    ];

    $sizeCls = in_array($variant, ['link', 'danger-ghost']) ? '' : ($sizes[$size] ?? $sizes['md']);
    $clases = $base . ' ' . $sizeCls . ' ' . ($variantes[$variant] ?? $variantes['primary']);
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $clases]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $clases]) }}>{{ $slot }}</button>
@endif
