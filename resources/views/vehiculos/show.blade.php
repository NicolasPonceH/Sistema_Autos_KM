@extends('layouts.app')

@section('titulo', $vehiculo->patente . ' — Ficha Técnica de Unidad')

@section('contenido')
    <!-- Vehicle Hero Header -->
    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <x-plate :patente="$vehiculo->patente" size="lg" />
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="font-headline-lg text-2xl sm:text-3xl font-bold text-primary">
                        {{ $vehiculo->marca }} {{ $vehiculo->modelo }}
                    </h1>
                    @if ($vehiculo->activo)
                        <x-badge variant="success">Operativo</x-badge>
                    @else
                        <x-badge variant="neutral">De baja</x-badge>
                    @endif
                </div>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs font-label-mono text-on-surface-variant">
                    <span class="font-bold text-primary">{{ $vehiculo->tipoVehiculo->nombre }}</span>
                    <span>·</span>
                    <span>Año {{ $vehiculo->anio }}</span>
                    <span>·</span>
                    <span>Contacto: <strong>{{ $vehiculo->email_contacto }}</strong></span>
                    @if ($vehiculo->nro_motor)
                        <span>· Motor: {{ $vehiculo->nro_motor }}</span>
                    @endif
                    @if ($vehiculo->nro_chasis)
                        <span>· VIN: {{ $vehiculo->nro_chasis }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('vehiculos.edit', $vehiculo) }}"
               class="bg-surface-container-lowest text-primary border border-primary px-4 py-2 rounded-lg font-bold flex items-center gap-1.5 hover:bg-primary-fixed transition-colors shadow-sm text-sm">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                Editar Ficha
            </a>
            <a href="{{ route('vehiculos.index') }}" class="text-primary font-bold text-sm hover:underline">
                ← Volver
            </a>
        </div>
    </div>

    <!-- Odometer & Reading Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Digital Cluster HUD -->
        <div class="lg:col-span-5 bg-hud-bg text-white p-6 rounded-xl shadow-lg border border-primary-container flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-hud-glow/10 rounded-full blur-2xl pointer-events-none"></div>

            <div>
                <div class="flex items-center justify-between border-b border-primary-container/80 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-status-success animate-pulse"></span>
                        <span class="font-label-mono text-xs font-bold uppercase tracking-wider text-on-primary-container">Odómetro en Tablero</span>
                    </div>
                    <span class="bg-primary-container text-primary-fixed font-label-mono text-[10px] px-2 py-0.5 rounded border border-primary-fixed/20">
                        TELEMETRÍA PDI
                    </span>
                </div>

                <div class="my-6 text-center">
                    <div class="font-label-mono text-xs text-on-primary-container uppercase tracking-widest mb-1">Kilometraje Actual</div>
                    <div class="font-display-hud text-4xl sm:text-5xl font-bold text-hud-glow tracking-tight drop-shadow-[0_0_12px_rgba(56,189,248,0.4)]">
                        {{ number_format($vehiculo->km_actual, 0, ',', '.') }}
                        <span class="text-lg text-on-primary-container font-normal">KM</span>
                    </div>
                    @if ($vehiculo->fecha_km)
                        <div class="mt-2 text-xs font-label-mono text-on-primary-container flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">schedule</span>
                            Última lectura: {{ $vehiculo->fecha_km->format('d-m-Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="border-t border-primary-container/80 pt-3 flex items-center justify-between text-xs font-label-mono text-on-primary-container">
                <span>Historial: <strong>{{ $lecturas->total() }} registros</strong></span>
                <span class="text-hud-glow">Lectura Absoluta ✓</span>
            </div>
        </div>

        <!-- Report Reading Form -->
        <div class="lg:col-span-7 bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant">
            <div class="flex items-center gap-2 pb-3 border-b border-surface-variant mb-4">
                <span class="material-symbols-outlined text-primary">speed</span>
                <div>
                    <h2 class="font-headline-md text-base font-bold text-primary">Reportar Nueva Lectura</h2>
                    <p class="text-xs text-on-surface-variant">Ingresa el valor total absoluto del tablero</p>
                </div>
            </div>

            <form method="POST" action="{{ route('vehiculos.lecturas.store', $vehiculo) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="km" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
                        Kilometraje Total Informado
                    </label>
                    <div class="relative">
                        <input type="number" name="km" id="km" value="{{ old('km') }}" required min="0"
                               placeholder="Ej. {{ $vehiculo->km_actual + 300 }}"
                               class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 font-label-mono text-base font-bold text-primary focus:border-primary">
                        <span class="absolute right-3.5 top-2.5 font-label-mono text-sm font-bold text-on-surface-variant">KM</span>
                    </div>
                    @error('km')
                        <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-xs font-bold text-on-surface-variant">
                    <input type="checkbox" name="es_correccion" value="1" class="rounded border-outline text-primary focus:ring-primary"
                           onchange="document.getElementById('acordeon_observacion').classList.toggle('abierto', this.checked)"
                           @checked(old('es_correccion'))>
                    <span>Es una corrección excepcional (el kilometraje anterior estaba erróneo)</span>
                </label>

                <div id="acordeon_observacion" class="acordeon {{ old('es_correccion') ? 'abierto' : '' }}">
                    <div class="pt-1">
                        <label for="observacion" class="block text-xs font-bold text-on-surface-variant mb-1">
                            Motivo de la corrección <span class="text-status-danger">*</span>
                        </label>
                        <textarea name="observacion" id="observacion" rows="2" placeholder="Explica la razón de la corrección..."
                                  class="w-full rounded-lg border-surface-variant text-xs text-on-surface">{{ old('observacion') }}</textarea>
                        @error('observacion')
                            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if (session('requiere_confirmacion_salto'))
                    <div class="animar-entrada rounded-lg bg-status-warning/10 border border-status-warning/30 p-3.5">
                        <label class="flex items-center gap-2 text-xs font-bold text-status-warning cursor-pointer">
                            <input type="checkbox" name="confirmar_salto" value="1" required class="rounded border-status-warning text-status-warning">
                            <span>Confirmo que el salto mayor a 5.000 km es correcto y no un error de tipeo.</span>
                        </label>
                    </div>
                @endif

                <div class="pt-1 flex justify-end">
                    <x-button type="submit" variant="primary">
                        Registrar Odómetro
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Maintenance Health Matrix -->
    <div>
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="font-headline-md text-xl font-bold text-primary">Estado de Planes de Mantención</h2>
                <p class="text-xs text-on-surface-variant">Desgaste acumulado e intervalos calculados para esta unidad policial</p>
            </div>
            <a href="{{ route('planes-mantencion.index') }}" class="text-primary font-bold text-sm hover:underline">
                Administrar Planes →
            </a>
        </div>

        @if ($estadosMantencion->isEmpty())
            <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-dashed border-surface-variant text-center">
                <p class="text-sm text-on-surface-variant">No hay planes de mantención aplicables a esta unidad.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($estadosMantencion as $estado)
                    @php
                        $kmDesdeUltimo = max(0, $vehiculo->km_actual - $estado->kmUltimoServicio);
                        $porcentajeKm = $estado->plan->intervalo_km > 0 
                            ? min(100, round(($kmDesdeUltimo / $estado->plan->intervalo_km) * 100))
                            : 100;
                        
                        $isVencido = $estado->vencido();
                        $isPorVencer = $estado->enVentanaAviso() && !$isVencido;
                        
                        $barColor = $isVencido ? 'bg-status-danger' : ($isPorVencer ? 'bg-status-warning' : 'bg-status-success');
                    @endphp

                    <div class="bg-surface-container-lowest p-5 rounded-xl shadow-sm border border-surface-variant flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div>
                                    <h3 class="font-bold text-primary text-sm">{{ $estado->plan->nombre }}</h3>
                                    <p class="text-xs text-on-surface-variant font-label-mono">
                                        Cada {{ number_format($estado->plan->intervalo_km, 0, ',', '.') }} km
                                        @if ($estado->plan->intervalo_meses)
                                            / {{ $estado->plan->intervalo_meses }} meses
                                        @endif
                                    </p>
                                </div>
                                @if ($isVencido)
                                    <x-badge variant="danger">Vencido</x-badge>
                                @elseif ($isPorVencer)
                                    <x-badge variant="warning">Por vencer</x-badge>
                                @else
                                    <x-badge variant="success">Al día</x-badge>
                                @endif
                            </div>

                            <!-- Progress Bar -->
                            <div class="my-3">
                                <div class="flex items-center justify-between text-xs font-label-mono mb-1">
                                    <span class="text-on-surface-variant">Uso del intervalo</span>
                                    <span class="font-bold {{ $isVencido ? 'text-status-danger' : 'text-primary' }}">{{ $porcentajeKm }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-container">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ min(100, $porcentajeKm) }}%"></div>
                                </div>
                            </div>

                            <div class="bg-surface-container-low p-3 rounded-lg text-xs space-y-1 font-label-mono">
                                <div class="flex items-center justify-between">
                                    <span class="text-on-surface-variant">Kilometraje:</span>
                                    <span class="font-bold {{ $estado->vencidoPorKm() ? 'text-status-danger' : 'text-primary' }}">
                                        {{ $estado->descripcion() }}
                                    </span>
                                </div>
                                @if ($estado->descripcionTiempo())
                                    <div class="flex items-center justify-between">
                                        <span class="text-on-surface-variant">Tiempo:</span>
                                        <span class="font-medium {{ $estado->vencidoPorTiempo() ? 'text-status-danger font-bold' : 'text-on-surface-variant' }}">
                                            {{ $estado->descripcionTiempo() }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-surface-variant flex items-center justify-between text-xs font-label-mono text-on-surface-variant">
                            <div>
                                <span>Próximo: </span>
                                <span class="font-bold text-primary">{{ number_format($estado->kmObjetivo, 0, ',', '.') }} km</span>
                            </div>
                            <a href="{{ route('emails.preview', [$vehiculo, $estado->plan]) }}" target="_blank"
                               class="text-primary font-bold hover:underline flex items-center gap-0.5" title="Previsualizar correo de alerta">
                                <span class="material-symbols-outlined text-[14px]">mail</span>
                                Correo
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Service Registration & Timeline Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Service Form -->
        <div class="lg:col-span-6 bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant">
            <div class="flex items-center gap-2 pb-3 border-b border-surface-variant mb-4">
                <span class="material-symbols-outlined text-primary">build</span>
                <div>
                    <h3 class="font-headline-md text-base font-bold text-primary">Registrar Servicio Realizado</h3>
                    <p class="text-xs text-on-surface-variant">Asienta un mantenimiento para reiniciar el ciclo</p>
                </div>
            </div>

            <form method="POST" action="{{ route('vehiculos.eventos-mantencion.store', $vehiculo) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="plan_id" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
                        Plan de Mantención
                    </label>
                    <select name="plan_id" id="plan_id" required
                            class="w-full rounded-lg border-surface-variant bg-surface-container-low px-3 py-2 text-sm font-medium text-primary focus:border-primary focus:bg-white">
                        <option value="">Seleccionar servicio ejecutado…</option>
                        @foreach ($planesDisponibles as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                {{ $plan->nombre }} (Cada {{ number_format($plan->intervalo_km, 0, ',', '.') }} km)
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="km_evento" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Km del Servicio</label>
                        <input type="number" name="km_evento" id="km_evento" value="{{ old('km_evento', $vehiculo->km_actual) }}" required min="0"
                               class="w-full rounded-lg border-surface-variant px-3 py-2 font-label-mono text-sm font-bold focus:border-primary">
                        @error('km_evento')
                            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="fecha" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Fecha</label>
                        <input type="date" name="fecha" id="fecha" value="{{ old('fecha', now()->toDateString()) }}" required
                               class="w-full rounded-lg border-surface-variant px-3 py-2 text-sm focus:border-primary">
                        @error('fecha')
                            <p class="mt-1 text-xs font-bold text-status-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="costo" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Costo ($ CLP)</label>
                        <input type="number" name="costo" id="costo" value="{{ old('costo') }}" min="0" placeholder="Ej. 75000"
                               class="w-full rounded-lg border-surface-variant px-3 py-2 text-sm focus:border-primary">
                    </div>
                    <div>
                        <label for="taller" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Taller / Proveedor</label>
                        <input type="text" name="taller" id="taller" value="{{ old('taller') }}" placeholder="Ej. Maestranza Central PDI"
                               class="w-full rounded-lg border-surface-variant px-3 py-2 text-sm focus:border-primary">
                    </div>
                </div>

                <div>
                    <label for="notas" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Notas u Observaciones</label>
                    <textarea name="notas" id="notas" rows="2" placeholder="Detalle de trabajos y repuestos..."
                              class="w-full rounded-lg border-surface-variant px-3 py-2 text-xs text-on-surface focus:border-primary">{{ old('notas') }}</textarea>
                </div>

                <div class="pt-2 flex justify-end">
                    <x-button type="submit" variant="primary">
                        Guardar Mantención
                    </x-button>
                </div>
            </form>
        </div>

        <!-- Service Timeline -->
        <div class="lg:col-span-6 bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-surface-variant mb-4">
                    <div>
                        <h3 class="font-headline-md text-base font-bold text-primary">Historial de Mantenciones</h3>
                        <p class="text-xs text-on-surface-variant">Servicios ejecutados previamente</p>
                    </div>
                    <span class="font-label-mono text-xs font-bold text-primary bg-surface-container px-2.5 py-1 rounded">
                        {{ $eventosMantencion->count() }} registros
                    </span>
                </div>

                @if ($eventosMantencion->isEmpty())
                    <div class="py-12 text-center text-on-surface-variant text-sm">
                        Todavía no hay servicios registrados para este vehículo.
                    </div>
                @else
                    <div class="divide-y divide-surface-variant max-h-96 overflow-y-auto pr-1">
                        @foreach ($eventosMantencion as $evento)
                            <div class="py-3 flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="font-bold text-sm text-primary">{{ $evento->plan->nombre }}</p>
                                    <div class="flex flex-wrap items-center gap-2 text-xs font-label-mono text-on-surface-variant">
                                        <span>{{ $evento->fecha->format('d-m-Y') }}</span>
                                        <span>·</span>
                                        <span class="font-bold text-primary">{{ number_format($evento->km_evento, 0, ',', '.') }} km</span>
                                        @if ($evento->taller)
                                            <span>·</span>
                                            <span>{{ $evento->taller }}</span>
                                        @endif
                                    </div>
                                    @if ($evento->notas)
                                        <p class="text-xs text-on-surface bg-surface-container-low rounded p-2 mt-1">{{ $evento->notas }}</p>
                                    @endif
                                </div>
                                @if ($evento->costo)
                                    <span class="font-label-mono font-bold text-xs text-status-success bg-status-success/10 border border-status-success/20 px-2 py-1 rounded">
                                        ${{ number_format($evento->costo, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Odometer History Table -->
    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant">
        <div class="flex items-center justify-between pb-3 border-b border-surface-variant mb-4">
            <div>
                <h2 class="font-headline-md text-base font-bold text-primary">Auditoría de Odómetro</h2>
                <p class="text-xs text-on-surface-variant">Historial inmutable de lecturas reportadas</p>
            </div>
            <span class="font-label-mono text-xs text-on-surface-variant font-bold">
                Total: {{ $lecturas->total() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant font-label-mono text-xs uppercase tracking-wider">
                        <th class="p-3 font-medium">Fecha y Hora</th>
                        <th class="p-3 font-medium">Lectura Odómetro</th>
                        <th class="p-3 font-medium">Origen</th>
                        <th class="p-3 font-medium">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-variant">
                    @forelse ($lecturas as $lectura)
                        <tr class="hover:bg-surface transition-colors">
                            <td class="p-3 font-label-mono text-xs text-on-surface-variant">
                                {{ $lectura->fecha->format('d-m-Y H:i') }}
                            </td>
                            <td class="p-3 font-label-mono font-bold text-primary">
                                {{ number_format($lectura->km, 0, ',', '.') }} km
                            </td>
                            <td class="p-3">
                                @if ($lectura->origen->value === 'CORRECCION')
                                    <x-badge variant="warning">Corrección</x-badge>
                                @else
                                    <x-badge variant="neutral">Manual</x-badge>
                                @endif
                            </td>
                            <td class="p-3 text-xs text-on-surface-variant">
                                {{ $lectura->observacion ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-xs text-on-surface-variant">
                                No hay lecturas previas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $lecturas->links() }}
        </div>
    </div>
@endsection
