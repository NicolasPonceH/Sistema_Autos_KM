@extends('layouts.app')

@section('titulo', 'Vehículos')

@section('contenido')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold tracking-tight">Vehículos</h1>
        <x-button as="a" href="{{ route('vehiculos.create') }}">
            Nuevo vehículo
        </x-button>
    </div>

    <form method="GET" action="{{ route('vehiculos.index') }}" class="mb-6 flex flex-wrap items-end gap-4">
        <div>
            <label for="patente" class="block text-xs font-medium text-text-muted">Patente</label>
            <input type="text" name="patente" id="patente" value="{{ request('patente') }}"
                   class="mt-1 rounded-md border-border text-sm">
        </div>
        <div>
            <label for="tipo_codigo" class="block text-xs font-medium text-text-muted">Tipo</label>
            <select name="tipo_codigo" id="tipo_codigo" class="mt-1 rounded-md border-border text-sm">
                <option value="">Todos</option>
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo->codigo }}" @selected(request('tipo_codigo') === $tipo->codigo)>
                        {{ $tipo->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-text-muted">
            <input type="checkbox" name="mostrar_inactivos" value="1" class="rounded border-border text-accent focus:ring-accent"
                   @checked(request()->boolean('mostrar_inactivos'))>
            Mostrar dados de baja
        </label>
        <x-button variant="secondary" type="submit">Filtrar</x-button>
        @if (request()->anyFilled(['patente', 'tipo_codigo', 'mostrar_inactivos']))
            <x-button variant="link" as="a" href="{{ route('vehiculos.index') }}">Limpiar</x-button>
        @endif
    </form>

    @if ($vehiculos->isEmpty())
        <div class="rounded-lg border border-dashed border-border p-10 text-center text-sm text-text-muted">
            No hay vehículos que coincidan con el filtro.
        </div>
    @else
        {{-- Desktop: tabla --}}
        <div class="hidden overflow-hidden rounded-lg border border-border sm:block">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead class="bg-surface-muted text-left text-xs font-medium uppercase tracking-wide text-text-muted">
                    <tr>
                        <th class="px-4 py-3">Patente</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Marca / Modelo</th>
                        <th class="px-4 py-3">Año</th>
                        <th class="px-4 py-3">Km actual</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($vehiculos as $vehiculo)
                        <tr class="transition-colors hover:bg-surface-muted/60 {{ $vehiculo->activo ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('vehiculos.show', $vehiculo) }}" class="hover:text-accent hover:underline">
                                    {{ $vehiculo->patente }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-text-muted">{{ $vehiculo->tipoVehiculo->nombre }}</td>
                            <td class="px-4 py-3">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</td>
                            <td class="px-4 py-3">{{ $vehiculo->anio }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ number_format($vehiculo->km_actual, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if ($vehiculo->activo)
                                    <x-badge variant="success">Activo</x-badge>
                                @else
                                    <x-badge variant="neutral">De baja</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <x-button variant="link" as="a" href="{{ route('vehiculos.edit', $vehiculo) }}">
                                        Editar
                                    </x-button>
                                    @if ($vehiculo->activo)
                                        <form method="POST" action="{{ route('vehiculos.destroy', $vehiculo) }}"
                                              onsubmit="return confirm('¿Dar de baja este vehículo?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-button variant="danger" type="submit">Dar de baja</x-button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('vehiculos.activar', $vehiculo) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-sm text-success hover:underline">Reactivar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: tarjetas --}}
        <div class="flex flex-col gap-3 sm:hidden">
            @foreach ($vehiculos as $vehiculo)
                <div class="rounded-lg border border-border bg-surface p-4 {{ $vehiculo->activo ? '' : 'opacity-60' }}">
                    <div class="mb-1 flex items-start justify-between gap-2">
                        <a href="{{ route('vehiculos.show', $vehiculo) }}" class="font-medium hover:text-accent hover:underline">
                            {{ $vehiculo->patente }}
                        </a>
                        @if ($vehiculo->activo)
                            <x-badge variant="success">Activo</x-badge>
                        @else
                            <x-badge variant="neutral">De baja</x-badge>
                        @endif
                    </div>
                    <p class="text-sm text-text-muted">
                        {{ $vehiculo->tipoVehiculo->nombre }} · {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->anio }})
                    </p>
                    <p class="mt-1 text-sm tabular-nums">{{ number_format($vehiculo->km_actual, 0, ',', '.') }} km</p>
                    <div class="mt-3 flex items-center gap-4 border-t border-border pt-3">
                        <x-button variant="link" as="a" href="{{ route('vehiculos.edit', $vehiculo) }}">
                            Editar
                        </x-button>
                        @if ($vehiculo->activo)
                            <form method="POST" action="{{ route('vehiculos.destroy', $vehiculo) }}"
                                  onsubmit="return confirm('¿Dar de baja este vehículo?');">
                                @csrf
                                @method('DELETE')
                                <x-button variant="danger" type="submit">Dar de baja</x-button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('vehiculos.activar', $vehiculo) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-sm text-success hover:underline">Reactivar</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        {{ $vehiculos->links() }}
    </div>
@endsection
