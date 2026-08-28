@extends('layouts.app')

@section('titulo', $vehiculo->patente.' — Vehículo')

@section('contenido')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">{{ $vehiculo->patente }}</h1>
            <p class="text-sm text-text-muted">
                {{ $vehiculo->tipoVehiculo->nombre }} — {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->anio }})
            </p>
        </div>
        <x-button variant="link" as="a" href="{{ route('vehiculos.edit', $vehiculo) }}">
            Editar datos
        </x-button>
    </div>

    {{-- Odómetro --}}
    <div class="mb-10 grid grid-cols-1 gap-6 md:grid-cols-2">
        <section class="rounded-lg border border-border bg-surface p-5">
            <h2 class="mb-4 text-sm font-semibold text-text">Kilometraje actual</h2>
            <p class="text-3xl font-semibold tabular-nums">{{ number_format($vehiculo->km_actual, 0, ',', '.') }} km</p>
            @if ($vehiculo->fecha_km)
                <p class="mt-1 text-xs text-text-muted">
                    Última lectura: {{ $vehiculo->fecha_km->format('d-m-Y H:i') }}
                </p>
            @endif
        </section>

        <section class="rounded-lg border border-border bg-surface p-5">
            <h2 class="mb-4 text-sm font-semibold text-text">Reportar lectura</h2>

            <form method="POST" action="{{ route('vehiculos.lecturas.store', $vehiculo) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="km" class="block text-xs font-medium text-text-muted">Kilometraje informado</label>
                    <input type="number" name="km" id="km" value="{{ old('km') }}" required min="0"
                           class="mt-1 w-full rounded-md border-border text-sm">
                    @error('km')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-text-muted">
                    <input type="checkbox" name="es_correccion" value="1" class="rounded border-border text-accent focus:ring-accent"
                           onchange="document.getElementById('acordeon_observacion').classList.toggle('abierto', this.checked)"
                           @checked(old('es_correccion'))>
                    Es una corrección (el kilometraje anterior estaba mal)
                </label>

                <div id="acordeon_observacion" class="acordeon {{ old('es_correccion') ? 'abierto' : '' }}">
                    <div>
                        <label for="observacion" class="block text-xs font-medium text-text-muted">
                            Observación (obligatoria para corregir)
                        </label>
                        <textarea name="observacion" id="observacion" rows="2"
                                  class="mt-1 w-full rounded-md border-border text-sm">{{ old('observacion') }}</textarea>
                        @error('observacion')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if (session('requiere_confirmacion_salto'))
                    <div class="animar-entrada rounded-md bg-warning-surface p-3">
                        <label class="flex items-center gap-2 text-sm text-warning">
                            <input type="checkbox" name="confirmar_salto" value="1" required class="rounded border-border text-accent focus:ring-accent">
                            Confirmo que el salto de kilometraje es correcto, no un error de tipeo.
                        </label>
                    </div>
                @endif

                <x-button type="submit">Registrar lectura</x-button>
            </form>
        </section>
    </div>

    {{-- Mantención --}}
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-text">Estado de mantención</h2>
        <x-button variant="link" as="a" href="{{ route('planes-mantencion.index') }}" class="text-xs">
            Administrar planes
        </x-button>
    </div>

    @if ($estadosMantencion->isEmpty())
        <div class="mb-10 rounded-lg border border-dashed border-border p-6 text-center text-sm text-text-muted">
            No hay planes de mantención aplicables a este vehículo todavía.
        </div>
    @else
        <div class="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($estadosMantencion as $estado)
                <div class="rounded-lg border border-border bg-surface p-4">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <h3 class="text-sm font-medium">{{ $estado->plan->nombre }}</h3>
                        @if ($estado->vencido())
                            <x-badge variant="danger">Vencido</x-badge>
                        @elseif ($estado->enVentanaAviso())
                            <x-badge variant="warning">Por vencer</x-badge>
                        @else
                            <x-badge variant="success">Al día</x-badge>
                        @endif
                    </div>
                    <p class="text-lg font-semibold tabular-nums {{ $estado->vencidoPorKm() ? 'text-danger' : '' }}">
                        {{ $estado->descripcion() }}
                    </p>
                    @if ($estado->descripcionTiempo())
                        <p class="text-sm tabular-nums {{ $estado->vencidoPorTiempo() ? 'text-danger' : 'text-text-muted' }}">
                            {{ $estado->descripcionTiempo() }}
                        </p>
                    @endif
                    <p class="mt-1 text-xs text-text-muted">
                        Cada {{ number_format($estado->plan->intervalo_km, 0, ',', '.') }} km
                        @if ($estado->plan->intervalo_meses)
                            u {{ $estado->plan->intervalo_meses }} meses
                        @endif
                        · próximo servicio a los {{ number_format($estado->kmObjetivo, 0, ',', '.') }} km
                        @if ($estado->fechaObjetivo)
                            o el {{ $estado->fechaObjetivo->format('d-m-Y') }}
                        @endif
                    </p>
                </div>
            @endforeach
        </div>

        <div class="mb-10 grid grid-cols-1 gap-6 md:grid-cols-2">
            <section class="rounded-lg border border-border bg-surface p-5">
                <h3 class="mb-4 text-sm font-semibold text-text">Registrar servicio realizado</h3>

                <form method="POST" action="{{ route('vehiculos.eventos-mantencion.store', $vehiculo) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="plan_id" class="block text-xs font-medium text-text-muted">Plan</label>
                        <select name="plan_id" id="plan_id" required class="mt-1 w-full rounded-md border-border text-sm">
                            <option value="">Seleccionar…</option>
                            @foreach ($planesDisponibles as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->nombre }}</option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="km_evento" class="block text-xs font-medium text-text-muted">Km del servicio</label>
                            <input type="number" name="km_evento" id="km_evento" value="{{ old('km_evento', $vehiculo->km_actual) }}" required min="0"
                                   class="mt-1 w-full rounded-md border-border text-sm">
                            @error('km_evento')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="fecha" class="block text-xs font-medium text-text-muted">Fecha</label>
                            <input type="date" name="fecha" id="fecha" value="{{ old('fecha', now()->toDateString()) }}" required
                                   class="mt-1 w-full rounded-md border-border text-sm">
                            @error('fecha')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="costo" class="block text-xs font-medium text-text-muted">Costo (opcional)</label>
                            <input type="number" name="costo" id="costo" value="{{ old('costo') }}" min="0"
                                   class="mt-1 w-full rounded-md border-border text-sm">
                            @error('costo')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="taller" class="block text-xs font-medium text-text-muted">Taller (opcional)</label>
                            <input type="text" name="taller" id="taller" value="{{ old('taller') }}"
                                   class="mt-1 w-full rounded-md border-border text-sm">
                            @error('taller')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="notas" class="block text-xs font-medium text-text-muted">Notas (opcional)</label>
                        <textarea name="notas" id="notas" rows="2"
                                  class="mt-1 w-full rounded-md border-border text-sm">{{ old('notas') }}</textarea>
                        @error('notas')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-button type="submit">Registrar servicio</x-button>
                </form>
            </section>

            <section class="rounded-lg border border-border bg-surface p-5">
                <h3 class="mb-4 text-sm font-semibold text-text">Últimos servicios</h3>

                @forelse ($eventosMantencion as $evento)
                    <div class="flex items-start justify-between border-b border-border py-2 text-sm last:border-0">
                        <div>
                            <p class="font-medium">{{ $evento->plan->nombre }}</p>
                            <p class="text-xs text-text-muted">
                                {{ $evento->fecha->format('d-m-Y') }} · {{ number_format($evento->km_evento, 0, ',', '.') }} km
                                @if ($evento->taller)
                                    · {{ $evento->taller }}
                                @endif
                            </p>
                        </div>
                        @if ($evento->costo)
                            <p class="tabular-nums text-text-muted">${{ number_format($evento->costo, 0, ',', '.') }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-text-muted">Todavía no hay servicios registrados.</p>
                @endforelse
            </section>
        </div>
    @endif

    {{-- Historial de odómetro --}}
    <h2 class="mb-4 text-sm font-semibold text-text">Historial de lecturas</h2>

    <div class="overflow-hidden rounded-lg border border-border">
        <table class="min-w-full divide-y divide-border text-sm">
            <thead class="bg-surface-muted text-left text-xs font-medium uppercase tracking-wide text-text-muted">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Km</th>
                    <th class="px-4 py-3">Origen</th>
                    <th class="px-4 py-3">Observación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($lecturas as $lectura)
                    <tr>
                        <td class="px-4 py-3">{{ $lectura->fecha->format('d-m-Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium tabular-nums">{{ number_format($lectura->km, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $lectura->origen->value === 'CORRECCION' ? 'warning' : 'neutral' }}">
                                {{ $lectura->origen->value }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-text-muted">{{ $lectura->observacion }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-sm text-text-muted">
                            Todavía no hay lecturas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $lecturas->links() }}
    </div>

    <p class="mt-6">
        <x-button variant="link" as="a" href="{{ route('vehiculos.index') }}">← Volver al listado</x-button>
    </p>
@endsection
