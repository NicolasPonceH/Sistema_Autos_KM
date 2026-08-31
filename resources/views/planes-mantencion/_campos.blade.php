@php($plan = $plan ?? null)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label for="nombre" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Nombre del Plan de Mantención <span class="text-status-danger">*</span>
        </label>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $plan?->nombre) }}" required
               placeholder="Ej. Cambio de Aceite y Filtros"
               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 text-sm font-medium focus:border-primary">
        @error('nombre')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="intervalo_km" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Intervalo en Kilómetros <span class="text-status-danger">*</span>
        </label>
        <div class="relative">
            <input type="number" name="intervalo_km" id="intervalo_km" value="{{ old('intervalo_km', $plan?->intervalo_km) }}" required min="1"
                   placeholder="Ej. 10000"
                   class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 font-label-mono text-sm font-bold focus:border-primary">
            <span class="absolute right-3.5 top-2.5 font-label-mono text-xs font-bold text-on-surface-variant">KM</span>
        </div>
        @error('intervalo_km')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="umbral_aviso" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Umbral de Aviso Preventivo <span class="text-status-danger">*</span>
        </label>
        <div class="relative">
            <input type="number" name="umbral_aviso" id="umbral_aviso" value="{{ old('umbral_aviso', $plan?->umbral_aviso ?? 500) }}" required min="0"
                   placeholder="Ej. 500"
                   class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 font-label-mono text-sm font-bold focus:border-primary">
            <span class="absolute right-3.5 top-2.5 font-label-mono text-xs font-bold text-on-surface-variant">KM</span>
        </div>
        <p class="mt-1 text-xs text-on-surface-variant">Se avisa cuando falten estos km o menos para el servicio.</p>
        @error('umbral_aviso')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="intervalo_meses" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Intervalo Temporal (Meses, opcional)
        </label>
        <input type="number" name="intervalo_meses" id="intervalo_meses" value="{{ old('intervalo_meses', $plan?->intervalo_meses) }}" min="1"
               placeholder="Ej. 12"
               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 text-sm focus:border-primary">
        <p class="mt-1 text-xs text-on-surface-variant">Lo que ocurra primero entre km y meses.</p>
        @error('intervalo_meses')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="umbral_aviso_dias" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Umbral de Aviso (Días, opcional)
        </label>
        <input type="number" name="umbral_aviso_dias" id="umbral_aviso_dias" value="{{ old('umbral_aviso_dias', $plan?->umbral_aviso_dias ?? 30) }}" min="0"
               placeholder="Ej. 30"
               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 text-sm focus:border-primary">
        @error('umbral_aviso_dias')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="tipo_codigo" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Aplicabilidad por Tipo de Vehículo
        </label>
        <select name="tipo_codigo" id="tipo_codigo"
                class="w-full rounded-lg border-surface-variant bg-surface-container-low px-3.5 py-2.5 text-sm font-medium focus:border-primary focus:bg-white">
            <option value="">Todos los tipos (Plan Global para toda la flota)</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->codigo }}" @selected(old('tipo_codigo', $plan?->tipo_codigo) === $tipo->codigo)>
                    Solo para {{ $tipo->nombre }} ({{ $tipo->codigo }})
                </option>
            @endforeach
        </select>
        @error('tipo_codigo')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>
</div>
