@extends('layouts.app')

@section('titulo', 'Planes de Mantención — AutoTrack PDI Fleet Control')

@section('contenido')
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 bg-surface-container-low p-6 rounded-xl shadow-sm border border-surface-variant">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-primary-container text-on-primary-container font-label-mono text-xs px-2 py-1 rounded">Pautas Técnicas</span>
                <span class="text-on-surface-variant text-sm">Protocolos Preventivos</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-primary mb-1">Planes de Mantención</h1>
            <p class="font-body-md text-on-surface-variant">Parámetros de intervalos por kilometraje y/o tiempo para la flota policial.</p>
        </div>
        <a href="{{ route('planes-mantencion.create') }}"
           class="bg-primary text-on-primary px-4 py-2 rounded-lg font-bold flex items-center gap-2 hover:bg-primary-container transition-colors shadow-sm hover:shadow-md hover:-translate-y-0.5 transform duration-200 cursor-pointer">
            <span class="material-symbols-outlined">add</span>
            Nuevo Plan
        </a>
    </div>

    @error('plan')
        <div class="rounded-xl border border-status-danger/30 bg-status-danger/10 p-4 text-sm text-status-danger font-bold">
            {{ $message }}
        </div>
    @enderror

    @if ($planes->isEmpty())
        <div class="bg-surface-container-lowest p-12 rounded-xl shadow-sm border border-dashed border-surface-variant text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-surface-container text-primary mb-3">
                <span class="material-symbols-outlined text-[32px]">build</span>
            </div>
            <h3 class="font-headline-md text-lg font-bold text-primary">No hay planes de mantención</h3>
            <p class="text-on-surface-variant text-sm max-w-sm mx-auto mt-1">Crea el primer plan para que el sistema supervise la flota automáticamente.</p>
        </div>
    @else
        <!-- Planes Table -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-variant overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant font-label-mono text-xs uppercase tracking-wider">
                            <th class="p-4 font-medium">Servicio / Plan</th>
                            <th class="p-4 font-medium">Intervalo Programado</th>
                            <th class="p-4 font-medium">Umbral de Aviso</th>
                            <th class="p-4 font-medium">Alcance</th>
                            <th class="p-4 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach ($planes as $plan)
                            <tr class="border-b border-surface-variant hover:bg-surface transition-colors group">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-white shrink-0">
                                            <span class="material-symbols-outlined text-[20px]">build</span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-primary">{{ $plan->nombre }}</p>
                                            <p class="text-xs font-label-mono text-on-surface-variant">Código: PM-{{ str_pad($plan->id, 3, '0', STR_PAD_LEFT) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="font-label-mono text-xs">
                                        <span class="font-bold text-primary">Cada {{ number_format($plan->intervalo_km, 0, ',', '.') }} km</span>
                                        @if ($plan->intervalo_meses)
                                            <span class="text-on-surface-variant"> / {{ $plan->intervalo_meses }} meses</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="font-label-mono text-xs text-status-warning font-bold bg-status-warning/10 px-2 py-0.5 rounded inline-block">
                                        {{ number_format($plan->umbral_aviso, 0, ',', '.') }} km antes
                                        @if ($plan->intervalo_meses)
                                            · {{ $plan->umbral_aviso_dias }} días
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if ($plan->tipoVehiculo)
                                        <span class="font-label-mono text-xs font-semibold bg-surface-container text-on-surface-variant px-2.5 py-1 rounded">
                                            {{ $plan->tipoVehiculo->nombre }}
                                        </span>
                                    @else
                                        <span class="font-label-mono text-xs font-semibold bg-primary-container text-primary-fixed px-2.5 py-1 rounded">
                                            Toda la flota (Global)
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('planes-mantencion.edit', $plan) }}"
                                           class="text-xs font-bold text-primary hover:underline">
                                            Editar
                                        </a>
                                        <form method="POST" action="{{ route('planes-mantencion.destroy', $plan) }}"
                                              onsubmit="return confirm('¿Eliminar este plan de mantención?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-status-danger hover:underline cursor-pointer">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-4">
        {{ $planes->links() }}
    </div>
@endsection
