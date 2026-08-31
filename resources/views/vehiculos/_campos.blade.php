@php($vehiculo = $vehiculo ?? null)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @if ($vehiculo)
        <div class="sm:col-span-2 rounded-lg bg-surface-container-low p-4 border border-surface-variant">
            <span class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-2">Placa Patente</span>
            <x-plate :patente="$vehiculo->patente" size="md" />
        </div>
    @else
        <div class="sm:col-span-2">
            <label for="patente" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
                Placa Patente <span class="text-status-danger">*</span>
            </label>
            <input type="text" name="patente" id="patente" value="{{ old('patente') }}" required
                   placeholder="Ej. ABCD12 o AB1234"
                   class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 font-label-mono text-sm font-bold uppercase tracking-wider focus:border-primary">
            <p class="mt-1 text-xs text-on-surface-variant font-label-mono">Formato chileno válido: 4 letras + 2 números o 2 letras + 4 números.</p>
            @error('patente')
                <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div>
        <label for="tipo_codigo" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Tipo de Vehículo <span class="text-status-danger">*</span>
        </label>
        <select name="tipo_codigo" id="tipo_codigo" required
                class="w-full rounded-lg border-surface-variant bg-surface-container-low px-3.5 py-2.5 text-sm font-medium focus:border-primary focus:bg-white">
            <option value="">Seleccionar tipo…</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->codigo }}"
                        @selected(old('tipo_codigo', $vehiculo?->tipo_codigo) === $tipo->codigo)>
                    {{ $tipo->nombre }} ({{ $tipo->codigo }})
                </option>
            @endforeach
        </select>
        @error('tipo_codigo')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="anio" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Año de Fabricación <span class="text-status-danger">*</span>
        </label>
        <input type="number" name="anio" id="anio" value="{{ old('anio', $vehiculo?->anio ?? date('Y')) }}" required
               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 font-label-mono text-sm font-bold focus:border-primary">
        @error('anio')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="marca" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Marca <span class="text-status-danger">*</span>
        </label>
        <input type="text" name="marca" id="marca" value="{{ old('marca', $vehiculo?->marca) }}" placeholder="Ej. TOYOTA, CHEVROLET"
               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 text-sm font-medium focus:border-primary">
        @error('marca')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="modelo" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Modelo <span class="text-status-danger">*</span>
        </label>
        <input type="text" name="modelo" id="modelo" value="{{ old('modelo', $vehiculo?->modelo) }}" required placeholder="Ej. HILUX 2.8 TDI"
               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 text-sm font-medium focus:border-primary">
        @error('modelo')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nro_motor" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">N.º de Motor</label>
        <input type="text" name="nro_motor" id="nro_motor" value="{{ old('nro_motor', $vehiculo?->nro_motor) }}" placeholder="Ej. 1GD-88231"
               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 font-label-mono text-xs focus:border-primary">
    </div>

    <div>
        <label for="nro_chasis" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">N.º de Chasis / VIN</label>
        <input type="text" name="nro_chasis" id="nro_chasis" value="{{ old('nro_chasis', $vehiculo?->nro_chasis) }}" placeholder="Ej. 8AJBA3CD8E0099881"
               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 font-label-mono text-xs focus:border-primary">
    </div>

    <div class="sm:col-span-2">
        <label for="email_contacto" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
            Email de Contacto / Brigada <span class="text-status-danger">*</span>
        </label>
        <input type="email" name="email_contacto" id="email_contacto"
               value="{{ old('email_contacto', $vehiculo?->email_contacto) }}" required
               placeholder="ejemplo.brigada@pdi.cl"
               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 text-sm focus:border-primary">
        <p class="mt-1 text-xs text-on-surface-variant">Destino de las notificaciones preventivas y de vencimiento.</p>
        @error('email_contacto')
            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
        @enderror
    </div>
</div>
