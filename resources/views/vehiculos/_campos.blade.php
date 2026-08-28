@php($vehiculo = $vehiculo ?? null)

<div class="grid grid-cols-2 gap-4">
    @if ($vehiculo)
        <div class="col-span-2">
            <span class="block text-xs font-medium text-text-muted">Patente</span>
            <p class="mt-1 text-sm font-medium">{{ $vehiculo->patente }}</p>
        </div>
    @else
        <div class="col-span-2">
            <label for="patente" class="block text-xs font-medium text-text-muted">Patente</label>
            <input type="text" name="patente" id="patente" value="{{ old('patente') }}" required
                   placeholder="AB·CD12"
                   class="mt-1 w-full rounded-md border-border text-sm">
            @error('patente')
                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div>
        <label for="tipo_codigo" class="block text-xs font-medium text-text-muted">Tipo de vehículo</label>
        <select name="tipo_codigo" id="tipo_codigo" required class="mt-1 w-full rounded-md border-border text-sm">
            <option value="">Seleccionar…</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->codigo }}"
                        @selected(old('tipo_codigo', $vehiculo?->tipo_codigo) === $tipo->codigo)>
                    {{ $tipo->nombre }}
                </option>
            @endforeach
        </select>
        @error('tipo_codigo')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="anio" class="block text-xs font-medium text-text-muted">Año</label>
        <input type="number" name="anio" id="anio" value="{{ old('anio', $vehiculo?->anio) }}" required
               class="mt-1 w-full rounded-md border-border text-sm">
        @error('anio')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="marca" class="block text-xs font-medium text-text-muted">Marca</label>
        <input type="text" name="marca" id="marca" value="{{ old('marca', $vehiculo?->marca) }}"
               class="mt-1 w-full rounded-md border-border text-sm">
        @error('marca')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="modelo" class="block text-xs font-medium text-text-muted">Modelo</label>
        <input type="text" name="modelo" id="modelo" value="{{ old('modelo', $vehiculo?->modelo) }}" required
               class="mt-1 w-full rounded-md border-border text-sm">
        @error('modelo')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nro_motor" class="block text-xs font-medium text-text-muted">N.º de motor</label>
        <input type="text" name="nro_motor" id="nro_motor" value="{{ old('nro_motor', $vehiculo?->nro_motor) }}"
               class="mt-1 w-full rounded-md border-border text-sm">
        @error('nro_motor')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nro_chasis" class="block text-xs font-medium text-text-muted">N.º de chasis</label>
        <input type="text" name="nro_chasis" id="nro_chasis" value="{{ old('nro_chasis', $vehiculo?->nro_chasis) }}"
               class="mt-1 w-full rounded-md border-border text-sm">
        @error('nro_chasis')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="col-span-2">
        <label for="email_contacto" class="block text-xs font-medium text-text-muted">Email de contacto</label>
        <input type="email" name="email_contacto" id="email_contacto"
               value="{{ old('email_contacto', $vehiculo?->email_contacto) }}" required
               class="mt-1 w-full rounded-md border-border text-sm">
        <p class="mt-1 text-xs text-text-muted">Destino de los avisos de mantención (Fase 4).</p>
        @error('email_contacto')
            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
        @enderror
    </div>
</div>
