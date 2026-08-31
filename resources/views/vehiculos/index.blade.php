@extends('layouts.app')

@section('titulo', 'Vehículos — AutoTrack PDI Fleet Control')

@section('contenido')
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 bg-surface-container-low p-6 rounded-xl shadow-sm border border-surface-variant">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-primary-container text-on-primary-container font-label-mono text-xs px-2 py-1 rounded">Parque Automotor</span>
                <span class="text-on-surface-variant text-sm">Registro de Unidades Policiales</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-primary mb-1">Vehículos de la Flota</h1>
            <p class="font-body-md text-on-surface-variant">Gestión de patentes, asignaciones de brigada y odómetros actuales.</p>
        </div>
        <div class="flex gap-3">
            <button type="button" onclick="openQuickOdometroModal()"
                    class="bg-surface-container-lowest text-primary border border-primary px-4 py-2 rounded-lg font-bold flex items-center gap-2 hover:bg-primary-fixed transition-colors shadow-sm hover:shadow-md hover:-translate-y-0.5 transform duration-200 cursor-pointer">
                <span class="material-symbols-outlined">speed</span>
                Registrar Odómetro
            </button>
            <a href="{{ route('vehiculos.create') }}"
               class="bg-primary text-on-primary px-4 py-2 rounded-lg font-bold flex items-center gap-2 hover:bg-primary-container transition-colors shadow-sm hover:shadow-md hover:-translate-y-0.5 transform duration-200 cursor-pointer">
                <span class="material-symbols-outlined">add</span>
                Nuevo Vehículo
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant">
        <form method="GET" action="{{ route('vehiculos.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full sm:w-48">
                <label for="patente" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Patente</label>
                <input type="text" name="patente" id="patente" value="{{ request('patente') }}" placeholder="Ej. PBSY69"
                       class="w-full rounded-lg border-surface-variant bg-surface-container-low px-3 py-2 text-sm font-label-mono uppercase focus:border-primary focus:bg-white">
            </div>

            <div class="w-full sm:w-56">
                <label for="tipo_codigo" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Tipo de Vehículo</label>
                <select name="tipo_codigo" id="tipo_codigo"
                        class="w-full rounded-lg border-surface-variant bg-surface-container-low px-3 py-2 text-sm font-medium focus:border-primary focus:bg-white">
                    <option value="">Todos los tipos</option>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->codigo }}" @selected(request('tipo_codigo') === $tipo->codigo)>
                            {{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center pb-2">
                <label class="flex cursor-pointer items-center gap-2 text-xs font-bold text-on-surface-variant">
                    <input type="checkbox" name="mostrar_inactivos" value="1"
                           class="rounded border-outline text-primary focus:ring-primary"
                           @checked(request()->boolean('mostrar_inactivos'))>
                    <span>Mostrar dados de baja</span>
                </label>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="bg-primary text-on-primary px-4 py-2 rounded-lg font-bold flex items-center gap-1.5 hover:bg-primary-container transition-colors text-xs cursor-pointer shadow-xs">
                    <span class="material-symbols-outlined text-[16px]">search</span>
                    Filtrar
                </button>
                @if (request()->anyFilled(['patente', 'tipo_codigo', 'mostrar_inactivos']))
                    <a href="{{ route('vehiculos.index') }}" class="text-xs font-bold text-on-surface-variant hover:text-primary underline px-2">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Vehicles Table -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-variant overflow-hidden flex flex-col">
        @if ($vehiculos->isEmpty())
            <div class="p-12 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-surface-container text-on-surface-variant mb-3">
                    <span class="material-symbols-outlined text-[32px]">directions_car</span>
                </div>
                <h3 class="font-headline-md text-lg font-bold text-primary">No se encontraron vehículos</h3>
                <p class="text-on-surface-variant text-sm max-w-sm mx-auto mt-1">No hay unidades vehiculares que coincidan con los filtros aplicados.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant font-label-mono text-xs uppercase tracking-wider">
                            <th class="p-4 font-medium">Placa</th>
                            <th class="p-4 font-medium">Vehículo / Modelo</th>
                            <th class="p-4 font-medium">Categoría</th>
                            <th class="p-4 font-medium text-right">Odómetro Actual</th>
                            <th class="p-4 font-medium text-center">Estado</th>
                            <th class="p-4 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach ($vehiculos as $vehiculo)
                            <tr class="border-b border-surface-variant hover:bg-surface transition-colors group {{ $vehiculo->activo ? '' : 'opacity-60 bg-surface-container-low/50' }}">
                                <td class="p-4">
                                    <a href="{{ route('vehiculos.show', $vehiculo) }}" class="inline-block transition-transform hover:scale-105">
                                        <x-plate :patente="$vehiculo->patente" size="md" />
                                    </a>
                                </td>
                                <td class="p-4">
                                    <a href="{{ route('vehiculos.show', $vehiculo) }}" class="font-bold text-primary hover:underline block">
                                        {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->anio }})
                                    </a>
                                    <div class="text-xs text-on-surface-variant">Contacto: {{ $vehiculo->email_contacto }}</div>
                                </td>
                                <td class="p-4">
                                    <span class="font-label-mono text-xs font-semibold bg-surface-container text-on-surface-variant px-2.5 py-1 rounded">
                                        {{ $vehiculo->tipoVehiculo->nombre }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="font-label-mono text-primary font-bold">
                                        {{ number_format($vehiculo->km_actual, 0, ',', '.') }} <span class="text-xs text-on-surface-variant font-normal">km</span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    @if ($vehiculo->activo)
                                        <x-badge variant="success">Operativo</x-badge>
                                    @else
                                        <x-badge variant="neutral">De baja</x-badge>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($vehiculo->activo)
                                            <button type="button" onclick="openQuickOdometroModal('{{ $vehiculo->patente }}')"
                                                    class="text-xs font-bold text-primary bg-surface-container hover:bg-primary-fixed px-2.5 py-1.5 rounded transition-colors cursor-pointer">
                                                + Odómetro
                                            </button>
                                        @endif
                                        <a href="{{ route('vehiculos.show', $vehiculo) }}"
                                           class="text-xs font-bold text-primary bg-surface-container-low hover:bg-surface-container px-3 py-1.5 rounded border border-surface-variant transition-colors">
                                            Ficha
                                        </a>
                                        <a href="{{ route('vehiculos.edit', $vehiculo) }}"
                                           class="text-xs font-bold text-on-surface-variant hover:text-primary px-2 py-1.5">
                                            Editar
                                        </a>
                                        @if ($vehiculo->activo)
                                            <form method="POST" action="{{ route('vehiculos.destroy', $vehiculo) }}"
                                                  onsubmit="return confirm('¿Dar de baja este vehículo?');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-status-danger hover:underline px-2 py-1.5 cursor-pointer">
                                                    Baja
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('vehiculos.activar', $vehiculo) }}" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs font-bold text-status-success hover:underline px-2 py-1.5 cursor-pointer">
                                                    Reactivar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $vehiculos->links() }}
    </div>
@endsection
