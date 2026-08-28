@extends('layouts.app')

@section('titulo', 'Planes de mantención')

@section('contenido')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold tracking-tight">Planes de mantención</h1>
        <x-button as="a" href="{{ route('planes-mantencion.create') }}">
            Nuevo plan
        </x-button>
    </div>

    @error('plan')
        <div class="mb-6 rounded-md bg-danger-surface px-4 py-3 text-sm text-danger">{{ $message }}</div>
    @enderror

    @if ($planes->isEmpty())
        <div class="rounded-lg border border-dashed border-border p-10 text-center text-sm text-text-muted">
            Todavía no hay planes de mantención. Creá el primero para empezar a ver el estado de la flota.
        </div>
    @else
        {{-- Desktop: tabla --}}
        <div class="hidden overflow-hidden rounded-lg border border-border sm:block">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead class="bg-surface-muted text-left text-xs font-medium uppercase tracking-wide text-text-muted">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Intervalo</th>
                        <th class="px-4 py-3">Umbral de aviso</th>
                        <th class="px-4 py-3">Aplica a</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($planes as $plan)
                        <tr class="transition-colors hover:bg-surface-muted/60">
                            <td class="px-4 py-3 font-medium">{{ $plan->nombre }}</td>
                            <td class="px-4 py-3">
                                cada {{ number_format($plan->intervalo_km, 0, ',', '.') }} km
                                @if ($plan->intervalo_meses)
                                    <span class="text-text-muted">u {{ $plan->intervalo_meses }} meses</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                {{ number_format($plan->umbral_aviso, 0, ',', '.') }} km
                                @if ($plan->intervalo_meses)
                                    <span class="text-text-muted">/ {{ $plan->umbral_aviso_dias }} días</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-text-muted">
                                {{ $plan->tipoVehiculo->nombre ?? 'Todos los tipos' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <x-button variant="link" as="a" href="{{ route('planes-mantencion.edit', $plan) }}">
                                        Editar
                                    </x-button>
                                    <form method="POST" action="{{ route('planes-mantencion.destroy', $plan) }}"
                                          onsubmit="return confirm('¿Eliminar este plan de mantención?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" type="submit">Eliminar</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: tarjetas --}}
        <div class="flex flex-col gap-3 sm:hidden">
            @foreach ($planes as $plan)
                <div class="rounded-lg border border-border bg-surface p-4">
                    <p class="font-medium">{{ $plan->nombre }}</p>
                    <p class="mt-1 text-sm text-text-muted">
                        {{ $plan->tipoVehiculo->nombre ?? 'Todos los tipos' }}
                    </p>
                    <p class="mt-2 text-sm">
                        Cada {{ number_format($plan->intervalo_km, 0, ',', '.') }} km
                        @if ($plan->intervalo_meses)
                            u {{ $plan->intervalo_meses }} meses
                        @endif
                        · aviso a {{ number_format($plan->umbral_aviso, 0, ',', '.') }} km
                        @if ($plan->intervalo_meses)
                            / {{ $plan->umbral_aviso_dias }} días
                        @endif
                    </p>
                    <div class="mt-3 flex items-center gap-4 border-t border-border pt-3">
                        <x-button variant="link" as="a" href="{{ route('planes-mantencion.edit', $plan) }}">
                            Editar
                        </x-button>
                        <form method="POST" action="{{ route('planes-mantencion.destroy', $plan) }}"
                              onsubmit="return confirm('¿Eliminar este plan de mantención?');">
                            @csrf
                            @method('DELETE')
                            <x-button variant="danger" type="submit">Eliminar</x-button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        {{ $planes->links() }}
    </div>
@endsection
