@extends('layouts.app')

@section('titulo', 'Dashboard')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight">Vencimientos de mantención</h1>
        <p class="mt-1 text-sm text-text-muted">
            Vehículos activos con un servicio vencido o próximo a vencer, ordenados por urgencia.
        </p>
    </div>

    @if ($filas->isEmpty())
        <div class="rounded-lg border border-dashed border-border p-10 text-center text-sm text-text-muted">
            No hay vencimientos pendientes. La flota está al día.
        </div>
    @else
        {{-- Desktop: tabla --}}
        <div class="hidden overflow-hidden rounded-lg border border-border sm:block">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead class="bg-surface-muted text-left text-xs font-medium uppercase tracking-wide text-text-muted">
                    <tr>
                        <th class="px-4 py-3">Vehículo</th>
                        <th class="px-4 py-3">Servicio</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($filas as $fila)
                        @php($estado = $fila['estado'])
                        @php($vehiculo = $fila['vehiculo'])
                        <tr class="transition-colors hover:bg-surface-muted/60">
                            <td class="px-4 py-3">
                                <a href="{{ route('vehiculos.show', $vehiculo) }}" class="font-medium hover:text-accent hover:underline">
                                    {{ $vehiculo->patente }}
                                </a>
                                <p class="text-xs text-text-muted">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $estado->plan->nombre }}</td>
                            <td class="px-4 py-3">
                                @if ($estado->vencido())
                                    <x-badge variant="danger">Vencido</x-badge>
                                @else
                                    <x-badge variant="warning">Por vencer</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 tabular-nums">
                                <span class="{{ $estado->vencidoPorKm() ? 'font-medium text-danger' : '' }}">
                                    {{ $estado->descripcion() }}
                                </span>
                                @if ($estado->descripcionTiempo())
                                    <span class="{{ $estado->vencidoPorTiempo() ? 'font-medium text-danger' : 'text-text-muted' }}">
                                        · {{ lcfirst($estado->descripcionTiempo()) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: tarjetas, la tabla no entra sin amontonarse --}}
        <div class="flex flex-col gap-3 sm:hidden">
            @foreach ($filas as $fila)
                @php($estado = $fila['estado'])
                @php($vehiculo = $fila['vehiculo'])
                <a href="{{ route('vehiculos.show', $vehiculo) }}"
                   class="block rounded-lg border border-border bg-surface p-4 transition-colors hover:bg-surface-muted/60">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <div>
                            <p class="font-medium">{{ $vehiculo->patente }}</p>
                            <p class="text-xs text-text-muted">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</p>
                        </div>
                        @if ($estado->vencido())
                            <x-badge variant="danger">Vencido</x-badge>
                        @else
                            <x-badge variant="warning">Por vencer</x-badge>
                        @endif
                    </div>
                    <p class="text-sm text-text-muted">{{ $estado->plan->nombre }}</p>
                    <p class="mt-1 text-sm tabular-nums">
                        <span class="{{ $estado->vencidoPorKm() ? 'font-medium text-danger' : '' }}">
                            {{ $estado->descripcion() }}
                        </span>
                        @if ($estado->descripcionTiempo())
                            <span class="{{ $estado->vencidoPorTiempo() ? 'font-medium text-danger' : 'text-text-muted' }}">
                                · {{ lcfirst($estado->descripcionTiempo()) }}
                            </span>
                        @endif
                    </p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
