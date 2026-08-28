@php($plan = $plan ?? null)

<div class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
        <label for="nombre" class="block text-xs font-medium text-text-muted">Nombre</label>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $plan?->nombre) }}" required
               placeholder="Cambio de aceite"
               class="mt-1 w-full rounded-md border-border text-sm">
        @error('nombre')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="intervalo_km" class="block text-xs font-medium text-text-muted">Intervalo (km)</label>
        <input type="number" name="intervalo_km" id="intervalo_km" value="{{ old('intervalo_km', $plan?->intervalo_km) }}" required min="1"
               class="mt-1 w-full rounded-md border-border text-sm">
        @error('intervalo_km')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="umbral_aviso" class="block text-xs font-medium text-text-muted">Umbral de aviso (km)</label>
        <input type="number" name="umbral_aviso" id="umbral_aviso" value="{{ old('umbral_aviso', $plan?->umbral_aviso ?? 500) }}" required min="0"
               class="mt-1 w-full rounded-md border-border text-sm">
        <p class="mt-1 text-xs text-text-muted">Se avisa cuando falten estos km o menos para el servicio.</p>
        @error('umbral_aviso')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="intervalo_meses" class="block text-xs font-medium text-text-muted">
            También vence por tiempo (meses, opcional)
        </label>
        <input type="number" name="intervalo_meses" id="intervalo_meses" value="{{ old('intervalo_meses', $plan?->intervalo_meses) }}" min="1"
               class="mt-1 w-full rounded-md border-border text-sm">
        <p class="mt-1 text-xs text-text-muted">Lo que ocurra primero entre km y tiempo.</p>
        @error('intervalo_meses')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="umbral_aviso_dias" class="block text-xs font-medium text-text-muted">Umbral de aviso (días)</label>
        <input type="number" name="umbral_aviso_dias" id="umbral_aviso_dias" value="{{ old('umbral_aviso_dias', $plan?->umbral_aviso_dias ?? 30) }}" min="0"
               class="mt-1 w-full rounded-md border-border text-sm">
        @error('umbral_aviso_dias')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="col-span-2">
        <label for="tipo_codigo" class="block text-xs font-medium text-text-muted">Tipo de vehículo</label>
        <select name="tipo_codigo" id="tipo_codigo" class="mt-1 w-full rounded-md border-border text-sm">
            <option value="">Todos los tipos</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->codigo }}" @selected(old('tipo_codigo', $plan?->tipo_codigo) === $tipo->codigo)>
                    {{ $tipo->nombre }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-text-muted">Dejar en "Todos los tipos" aplica el plan a toda la flota.</p>
        @error('tipo_codigo')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>
</div>
