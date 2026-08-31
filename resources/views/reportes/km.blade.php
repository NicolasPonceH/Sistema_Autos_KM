@extends('layouts.app')

@section('titulo', 'Reportes de KM — AutoTrack PDI Fleet Control')

@section('contenido')
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 bg-surface-container-low p-6 rounded-xl shadow-sm border border-surface-variant">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-primary-container text-on-primary-container font-label-mono text-xs px-2 py-1 rounded">Auditoría Operativa</span>
                <span class="text-on-surface-variant text-sm">Historial de Odómetros</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-primary mb-1">Kilómetros Recorridos por Mes</h1>
            <p class="font-body-md text-on-surface-variant">Consolidado mensual de rodaje calculado por diferencias de odómetro.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('reportes.km.exportar', array_merge(request()->query(), ['formato' => 'csv'])) }}"
               class="bg-surface-container-lowest text-primary border border-surface-variant px-3.5 py-2 rounded-lg font-bold flex items-center gap-1.5 hover:bg-surface-container transition-colors shadow-xs text-xs cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">table_view</span>
                CSV
            </a>
            <button type="button" onclick="openEnviarReporteModal()"
                    class="bg-surface-container-lowest text-primary border border-primary px-3.5 py-2 rounded-lg font-bold flex items-center gap-1.5 hover:bg-primary-fixed transition-colors shadow-xs text-xs cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">mail</span>
                Enviar por Correo
            </button>
            <a href="{{ route('reportes.km.exportar', request()->query()) }}"
               class="bg-primary text-on-primary px-4 py-2 rounded-lg font-bold flex items-center gap-2 hover:bg-primary-container transition-colors shadow-sm hover:shadow-md hover:-translate-y-0.5 transform duration-200 cursor-pointer text-xs">
                <span class="material-symbols-outlined text-[18px] text-secondary-fixed">download</span>
                Descargar Excel Oficial (PDI)
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant">
        <form method="GET" action="{{ route('reportes.km') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-full sm:w-44">
                <label for="patente" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Placa Patente</label>
                <input type="text" name="patente" id="patente" value="{{ request('patente') }}" placeholder="Todas o ej. PBSY69"
                       class="w-full rounded-lg border-surface-variant bg-surface-container-low px-3 py-2 text-sm font-label-mono uppercase focus:border-primary focus:bg-white">
            </div>

            <div class="w-full sm:w-44">
                <label for="desde" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Mes Inicio</label>
                <input type="month" name="desde" id="desde" value="{{ request('desde', $mesInicio->format('Y-m')) }}"
                       class="w-full rounded-lg border-surface-variant bg-surface-container-low px-3 py-2 text-sm font-label-mono focus:border-primary focus:bg-white">
            </div>

            <div class="w-full sm:w-44">
                <label for="hasta" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">Mes Fin</label>
                <input type="month" name="hasta" id="hasta" value="{{ request('hasta', $mesFin->format('Y-m')) }}"
                       class="w-full rounded-lg border-surface-variant bg-surface-container-low px-3 py-2 text-sm font-label-mono focus:border-primary focus:bg-white">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="bg-primary text-on-primary px-4 py-2 rounded-lg font-bold flex items-center gap-1.5 hover:bg-primary-container transition-colors text-xs cursor-pointer shadow-xs">
                    <span class="material-symbols-outlined text-[16px]">search</span>
                    Consultar
                </button>
                @if (request()->anyFilled(['patente', 'desde', 'hasta']))
                    <a href="{{ route('reportes.km') }}" class="text-xs font-bold text-on-surface-variant hover:text-primary underline px-2">
                        Restablecer
                    </a>
                @endif
            </div>
        </form>
    </div>

    @php
        $mesesHeaders = $filas->first()['meses'] ?? collect();
        $mesesLabels = $mesesHeaders->map(fn($m) => $m['mes']->translatedFormat('M Y'))->values();
        $totalesPorMes = $mesesHeaders->map(function ($m, $idx) use ($filas) {
            return $filas->sum(fn ($f) => $f['meses'][$idx]['kmRecorridos'] ?? 0);
        })->values();
        $granTotal = $filas->sum(fn ($f) => $f['meses']->sum('kmRecorridos'));
    @endphp

    <!-- Monthly KM Evolution Chart -->
    @if ($filas->isNotEmpty() && $granTotal > 0)
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant">
            <div class="flex items-center justify-between pb-4 border-b border-surface-variant mb-4">
                <div>
                    <h2 class="font-headline-md text-base font-bold text-primary">Evolución de Rodaje Mensual</h2>
                    <p class="text-xs text-on-surface-variant">Kilómetros totales recorridos por la flota en el período</p>
                </div>
                <div class="bg-primary-container text-primary-fixed px-3 py-1.5 rounded-lg text-right font-label-mono">
                    <span class="block text-[10px] uppercase tracking-wider text-on-primary-container">Total Rodaje</span>
                    <span class="font-bold text-sm text-white">{{ number_format($granTotal, 0, ',', '.') }} KM</span>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="chartReporteKm"></canvas>
            </div>
        </div>
    @endif

    <!-- Data Table -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-variant overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant font-label-mono text-xs uppercase tracking-wider">
                        <th class="p-4 font-medium sticky left-0 bg-surface-container-low z-10">Unidad Policial</th>
                        @foreach ($mesesHeaders as $m)
                            <th class="p-4 font-medium text-right">{{ $m['mes']->translatedFormat('M Y') }}</th>
                        @endforeach
                        <th class="p-4 font-medium text-right text-primary font-bold">Total Acumulado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-variant">
                    @forelse ($filas as $fila)
                        @php($totalVehiculo = $fila['meses']->sum('kmRecorridos'))
                        <tr class="hover:bg-surface transition-colors">
                            <td class="p-4 font-medium sticky left-0 bg-surface-container-lowest z-10">
                                <div class="flex items-center gap-3">
                                    <x-plate :patente="$fila['vehiculo']->patente" size="sm" />
                                    <div>
                                        <a href="{{ route('vehiculos.show', $fila['vehiculo']) }}" class="font-bold text-primary hover:underline block">
                                            {{ $fila['vehiculo']->marca }} {{ $fila['vehiculo']->modelo }}
                                        </a>
                                        <div class="text-xs text-on-surface-variant">{{ $fila['vehiculo']->tipoVehiculo->nombre }}</div>
                                    </div>
                                </div>
                            </td>
                            @foreach ($fila['meses'] as $m)
                                <td class="p-4 text-right font-label-mono text-xs {{ is_null($m['kmRecorridos']) ? 'text-outline-variant' : 'text-primary font-semibold' }}">
                                    {{ is_null($m['kmRecorridos']) ? '—' : number_format($m['kmRecorridos'], 0, ',', '.') }}
                                </td>
                            @endforeach
                            <td class="p-4 text-right font-label-mono font-bold text-primary bg-surface-container-low/60">
                                {{ number_format($totalVehiculo, 0, ',', '.') }} <span class="text-xs text-on-surface-variant font-normal">km</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="99" class="p-12 text-center text-sm text-on-surface-variant">
                                No se encontraron registros con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($filas->isNotEmpty())
                    <tfoot class="bg-surface-container font-bold border-t-2 border-surface-variant">
                        <tr>
                            <td class="p-4 text-xs font-label-mono uppercase tracking-wider text-primary sticky left-0 bg-surface-container z-10">
                                Totales Flota
                            </td>
                            @foreach ($totalesPorMes as $totalMes)
                                <td class="p-4 text-right font-label-mono text-xs text-primary font-bold">
                                    {{ number_format($totalMes, 0, ',', '.') }}
                                </td>
                            @endforeach
                            <td class="p-4 text-right font-label-mono text-sm text-primary font-bold">
                                {{ number_format($granTotal, 0, ',', '.') }} km
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Modal Enviar Reporte por Correo -->
    <div id="modal-enviar-reporte" class="fixed inset-0 z-50 hidden items-center justify-center bg-primary/60 backdrop-blur-xs p-4 transition-opacity duration-200"
         role="dialog" aria-modal="true" aria-labelledby="modal-enviar-titulo">
        <div class="animar-entrada w-full max-w-lg rounded-xl border border-surface-variant bg-surface-container-lowest p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-surface-variant pb-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white shadow-xs">
                        <span class="material-symbols-outlined text-[22px]">send</span>
                    </div>
                    <div>
                        <h3 id="modal-enviar-titulo" class="text-base font-bold font-headline-md text-primary">Enviar Informe por Correo</h3>
                        <p class="text-xs text-on-surface-variant">Con planilla Excel institucional PDI adjunta</p>
                    </div>
                </div>
                <button type="button" onclick="closeEnviarReporteModal()"
                        class="rounded-lg p-1 text-on-surface-variant hover:bg-surface-container hover:text-primary cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('reportes.km.enviar-correo') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="desde" value="{{ request('desde', $mesInicio->format('Y-m')) }}">
                <input type="hidden" name="hasta" value="{{ request('hasta', $mesFin->format('Y-m')) }}">
                <input type="hidden" name="patente" value="{{ request('patente') }}">

                <div class="rounded-lg bg-surface-container-low p-3.5 border border-surface-variant text-xs space-y-1">
                    <div class="flex items-center justify-between font-label-mono">
                        <span class="text-on-surface-variant">Período:</span>
                        <span class="font-bold text-primary">{{ $mesInicio->translatedFormat('F Y') }} - {{ $mesFin->translatedFormat('F Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between font-label-mono">
                        <span class="text-on-surface-variant">Adjunto:</span>
                        <span class="font-bold text-status-success">📊 INFORME_PDI_KM_{{ $mesInicio->format('Y-m') }}_a_{{ $mesFin->format('Y-m') }}.xls</span>
                    </div>
                </div>

                <div>
                    <label for="email_destino" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
                        Correo Electrónico Destino <span class="text-status-danger">*</span>
                    </label>
                    <input type="email" name="email" id="email_destino" value="ponceclaudio9971@gmail.com" required placeholder="ej. jefatura.logistica@pdi.cl"
                           class="w-full rounded-lg border-surface-variant px-3.5 py-2.5 text-sm font-medium text-primary focus:border-primary">
                </div>

                <div>
                    <label for="mensaje_adicional" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
                        Observaciones / Mensaje Opcional
                    </label>
                    <textarea name="mensaje" id="mensaje_adicional" rows="3" placeholder="Ingresa notas o comentarios para la jefatura..."
                              class="w-full rounded-lg border-surface-variant px-3.5 py-2 text-xs text-on-surface focus:border-primary"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-surface-variant pt-4">
                    <button type="button" onclick="closeEnviarReporteModal()"
                            class="rounded-lg border border-surface-variant bg-surface-container-lowest px-4 py-2 text-sm font-bold text-primary hover:bg-surface-container cursor-pointer">
                        Cancelar
                    </button>
                    <x-button type="submit" variant="primary">
                        <span class="material-symbols-outlined text-[18px]">send</span>
                        Enviar Correo
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEnviarReporteModal() {
            const m = document.getElementById('modal-enviar-reporte');
            m.classList.remove('hidden');
            m.classList.add('flex');
            setTimeout(() => document.getElementById('email_destino').focus(), 100);
        }
        function closeEnviarReporteModal() {
            const m = document.getElementById('modal-enviar-reporte');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
    </script>

    @if ($filas->isNotEmpty() && $granTotal > 0)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ctx = document.getElementById('chartReporteKm');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($mesesLabels),
                            datasets: [{
                                label: 'Km Recorridos Flota',
                                data: @json($totalesPorMes),
                                borderColor: '#000a1f',
                                backgroundColor: 'rgba(0, 10, 31, 0.06)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#000a1f',
                                pointBorderColor: '#ffe08b',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (val) => new Intl.NumberFormat('es-CL').format(val) + ' km',
                                        font: { family: 'JetBrains Mono' }
                                    },
                                    grid: { color: '#e0e3e5' }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { family: 'Hanken Grotesk', weight: 'bold' } }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endif
@endsection
