@props(['patente', 'size' => 'md'])

@php
    $clean = strtoupper(trim(str_replace(['.', '-', ' '], '', $patente)));
    
    // Formato visual chileno
    if (strlen($clean) === 6) {
        if (ctype_alpha(substr($clean, 0, 4))) {
            $formatted = substr($clean, 0, 2) . '-' . substr($clean, 2, 2) . '-' . substr($clean, 4, 2);
        } elseif (ctype_alpha(substr($clean, 0, 2))) {
            $formatted = substr($clean, 0, 2) . '-' . substr($clean, 2, 2) . '-' . substr($clean, 4, 2);
        } else {
            $formatted = $clean;
        }
    } else {
        $formatted = $clean;
    }

    $sizes = [
        'sm' => 'h-7 w-26 text-xs',
        'md' => 'h-8 w-28 text-sm',
        'lg' => 'h-10 w-36 text-base',
    ];

    $starSizes = [
        'sm' => 'w-5 text-[9px]',
        'md' => 'w-6 text-[10px]',
        'lg' => 'w-8 text-[12px]',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center bg-white border-2 border-primary rounded-sm overflow-hidden shadow-sm select-none ' . ($sizes[$size] ?? $sizes['md'])]) }}>
    <div class="bg-plate-blue h-full {{ $starSizes[$size] ?? $starSizes['md'] }} flex items-center justify-center border-r border-primary shrink-0">
        <span class="text-white font-bold leading-none">★</span>
    </div>
    <div class="flex-1 text-center font-label-mono font-bold text-primary tracking-widest leading-none px-1">
        {{ $formatted }}
    </div>
</div>
