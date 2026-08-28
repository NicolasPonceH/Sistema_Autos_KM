@extends('layouts.app')

@section('titulo', 'Reporte de km recorridos')

@section('contenido')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Km recorridos por mes</h1>
            <p class="mt-1 text-sm text-text-muted">Calculado a partir del historial de lecturas de odómetro.</p>
        </div>
        <x-button variant="secondary" as="a" href="{{ route('reportes.km.exportar', request()->query()) }}">
            Exportar CSV
        </x-button>
    </div>

    <form method="GET" action="{{ route('reportes.km') }}" class="mb-6 flex flex-wrap items-end gap-4">
        <div>
            <label for="patente" class="block text-xs font-medium text-text-muted">Patente</label>
            <input type="text" name="patente" id="patente" value="{{ request('patente') }}"
                   class="mt-1 rounded-md border-border text-sm">
        </div>
        <div>
            <label for="desde" class="block text-xs font-medium text-text-muted">Desde</label>
            <input type="month" name="desde" id="desde" value="{{ request('desde', $mesInicio->format('Y-m')) }}"
                   class="mt-1 rounded-md border-border text-sm">
        </div>
        <div>
            <label for="hasta" class="block text-xs font-medium text-text-muted">Hasta</label>
            <input type="month" name="hasta" id="hasta" value="{{ request('hasta', $mesFin->format('Y-m')) }}"
                   class="mt-1 rounded-md border-border text-sm">
        </div>
        <x-button variant="secondary" type="submit">Filtrar</x-button>
    </form>

    <div class="overflow-x-auto rounded-lg border border-border">
        <table class="min-w-full divide-y divide-border text-sm">
            <thead class="bg-surface-muted text-left text-xs font-medium uppercase tracking-wide text-text-muted">
                <tr>
                    <th class="sticky left-0 bg-surface-muted px-4 py-3">Vehículo</th>
                    @foreach (($filas->first()['meses'] ?? collect()) as $m)
                        <th class="px-4 py-3 text-right">{{ $m['mes']->translatedFormat('M Y') }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($filas as $fila)
                    <tr class="transition-colors hover:bg-surface-muted/60">
                        <td class="sticky left-0 bg-surface px-4 py-3 font-medium">
                            <a href="{{ route('vehiculos.show', $fila['vehiculo']) }}" class="hover:text-accent hover:underline">
                                {{ $fila['vehiculo']->patente }}
                            </a>
                        </td>
                        @foreach ($fila['meses'] as $m)
                            <td class="px-4 py-3 text-right tabular-nums {{ is_null($m['kmRecorridos']) ? 'text-text-muted' : '' }}">
                                {{ is_null($m['kmRecorridos']) ? '—' : number_format($m['kmRecorridos'], 0, ',', '.') }}
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-right font-medium tabular-nums">
                            {{ number_format($fila['meses']->sum('kmRecorridos'), 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="99" class="px-4 py-10 text-center text-sm text-text-muted">
                            No hay vehículos que coincidan con el filtro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
