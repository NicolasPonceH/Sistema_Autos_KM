@props(['variant' => 'primary', 'as' => 'button', 'type' => 'submit'])

@php
    $base = 'inline-flex items-center justify-center rounded-md text-sm font-medium transition duration-150 disabled:pointer-events-none disabled:opacity-50';

    $variantes = [
        'primary' => 'px-4 py-2 bg-slate-900 text-white hover:bg-accent active:scale-[0.97]',
        'secondary' => 'px-4 py-2 border border-border bg-surface text-text hover:bg-surface-muted active:scale-[0.97]',
        'danger' => 'text-danger hover:underline',
        'link' => 'text-text-muted hover:text-text hover:underline',
    ];

    $clases = $base.' '.($variantes[$variant] ?? $variantes['primary']);
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $clases]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $clases]) }}>{{ $slot }}</button>
@endif
