@extends('layouts.app')

@section('titulo', 'Dashboard — AutoTrack PDI Fleet Control')

@section('contenido')
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 bg-surface-container-low p-6 rounded-xl shadow-sm border border-surface-variant">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-primary-container text-on-primary-container font-label-mono text-xs px-2 py-1 rounded">Panel de Control</span>
                <span class="text-on-surface-variant text-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px] animate-pulse text-status-success">radio_button_checked</span>
                    Actualizado en tiempo real
                </span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-primary mb-1">Estado Operativo de Flota</h1>
            <p class="font-body-md text-on-surface-variant">Supervisión integral de kilometrajes, vencimientos preventivos y estado de unidades.</p>
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

    <!-- KPI Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- KPI 1 -->
        <x-stat-card title="Flota Activa"
                     :value="$totalVehiculos"
                     subtitle="Unidades operativas"
                     variant="primary"
                     icon="directions_car"
                     :href="route('vehiculos.index')" />

        <!-- KPI 2 -->
        <x-stat-card title="Servicios Vencidos"
                     :value="$vencidosCount"
                     subtitle="Atención urgente"
                     variant="danger"
                     icon="warning"
                     :ping="$vencidosCount > 0" />

        <!-- KPI 3 -->
        <x-stat-card title="Próximos a Vencer"
                     :value="$porVencerCount"
                     subtitle="En ventana de aviso"
                     variant="warning"
                     icon="schedule" />

        <!-- KPI 4 -->
        <x-stat-card title="KM Totales"
                     :value="number_format($totalKmFlota, 0, ',', '.')"
                     subtitle="Acumulado flota"
                     variant="blue"
                     icon="speed"
                     :href="route('reportes.km')" />

        <!-- KPI 5 -->
        <x-stat-card title="Inversión (Servicios)"
                     :value="'$' . number_format($costoTotalMantencion, 0, ',', '.')"
                     subtitle="{{ $totalServiciosRealizados }} mantenciones"
                     variant="success"
                     icon="payments" />
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Health Chart (Left) -->
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant lg:col-span-1 flex flex-col">
            <div class="mb-6">
                <h2 class="font-headline-md text-headline-md text-primary">Salud de la Flota</h2>
                <p class="text-on-surface-variant text-sm">Estado de mantenciones activas</p>
            </div>
            
            <div class="flex-1 flex flex-col items-center justify-center relative">
                @php
                    $porcentajeSalud = $totalVehiculos > 0 ? round(($vehiculosAlDia / $totalVehiculos) * 100) : 100;
                @endphp
                
                <div class="relative w-48 h-48 mb-6 flex items-center justify-center">
                    <canvas id="chartSaludFlota"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="font-display-hud text-3xl text-primary font-bold">{{ $porcentajeSalud }}%</span>
                        <span class="text-xs text-on-surface-variant font-label-mono uppercase">
                            {{ $porcentajeSalud >= 80 ? 'Óptimo' : 'Atención' }}
                        </span>
                    </div>
                </div>

                <div class="w-full grid grid-cols-3 gap-2 text-center mt-auto">
                    <div class="bg-status-success/10 rounded p-2 border border-status-success/20">
                        <div class="font-bold text-status-success text-lg font-label-mono">{{ $vehiculosAlDia }}</div>
                        <div class="text-[10px] uppercase font-label-mono text-on-surface-variant mt-1">Al Día</div>
                    </div>
                    <div class="bg-status-warning/10 rounded p-2 border border-status-warning/20">
                        <div class="font-bold text-status-warning text-lg font-label-mono">{{ $vehiculosPorVencer }}</div>
                        <div class="text-[10px] uppercase font-label-mono text-on-surface-variant mt-1">Por Vencer</div>
                    </div>
                    <div class="bg-status-danger/10 rounded p-2 border border-status-danger/20">
                        <div class="font-bold text-status-danger text-lg font-label-mono">{{ $vehiculosConVencidos }}</div>
                        <div class="text-[10px] uppercase font-label-mono text-on-surface-variant mt-1">Vencidos</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribution Chart (Right) -->
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant lg:col-span-2 flex flex-col">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="font-headline-md text-headline-md text-primary">Distribución Operativa</h2>
                    <p class="text-on-surface-variant text-sm">Unidades vehiculares por tipo</p>
                </div>
                <a class="text-sm font-label-mono text-primary-fixed-dim hover:text-primary transition-colors flex items-center gap-1 font-bold" href="{{ route('vehiculos.index') }}">
                    Ver listado completo <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

            <div class="relative flex-1 h-64">
                <canvas id="chartTiposFlota"></canvas>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-variant overflow-hidden flex flex-col">
        <div class="p-6 border-b border-surface-variant flex justify-between items-center bg-surface-bright">
            <div>
                <h2 class="font-headline-md text-headline-md text-primary">Alertas de Mantención Priorizadas</h2>
                <p class="text-on-surface-variant text-sm">Unidades que requieren atención inmediata o próxima.</p>
            </div>
            <a href="{{ route('vehiculos.index') }}" class="text-primary hover:text-primary-container font-label-mono text-sm flex items-center gap-1 transition-colors font-bold">
                <span class="material-symbols-outlined text-[18px]">filter_list</span>
                Ver Flota
            </a>
        </div>

        @if ($filas->isEmpty())
            <div class="p-12 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-status-success/10 text-status-success mb-3">
                    <span class="material-symbols-outlined text-[32px]">verified</span>
                </div>
                <h3 class="font-headline-md text-lg font-bold text-primary">Toda la flota está al día</h3>
                <p class="text-on-surface-variant text-sm max-w-md mx-auto mt-1">
                    No hay servicios vencidos ni dentro de la ventana de aviso preventivo.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant font-label-mono text-xs uppercase tracking-wider">
                            <th class="p-4 font-medium">Placa</th>
                            <th class="p-4 font-medium">Vehículo / Asignación</th>
                            <th class="p-4 font-medium text-right">Kilometraje</th>
                            <th class="p-4 font-medium">Servicio Requerido</th>
                            <th class="p-4 font-medium text-center">Estado</th>
                            <th class="p-4 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach ($filas as $fila)
                            @php($estado = $fila['estado'])
                            @php($vehiculo = $fila['vehiculo'])
                            <tr class="border-b border-surface-variant hover:bg-surface transition-colors group">
                                <td class="p-4">
                                    <x-plate :patente="$vehiculo->patente" size="md" />
                                </td>
                                <td class="p-4">
                                    <a href="{{ route('vehiculos.show', $vehiculo) }}" class="font-bold text-primary hover:underline block">
                                        {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->anio }})
                                    </a>
                                    <div class="text-xs text-on-surface-variant">{{ $vehiculo->tipoVehiculo->nombre }} · {{ $vehiculo->email_contacto }}</div>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="font-label-mono text-primary font-bold">
                                        {{ number_format($vehiculo->km_actual, 0, ',', '.') }} <span class="text-xs text-on-surface-variant font-normal">km</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-on-background">{{ $estado->plan->nombre }}</div>
                                    <div class="text-xs {{ $estado->vencidoPorKm() ? 'text-status-danger' : 'text-status-warning' }} font-label-mono mt-1">
                                        {{ $estado->descripcion() }}
                                        @if ($estado->descripcionTiempo())
                                            · {{ $estado->descripcionTiempo() }}
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    @if ($estado->vencido())
                                        <span class="inline-flex items-center gap-1 bg-status-danger/10 text-status-danger px-2 py-1 rounded text-xs font-bold uppercase tracking-wide">
                                            <span class="material-symbols-outlined text-[14px]">error</span>
                                            Vencido
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-status-warning/10 text-status-warning px-2 py-1 rounded text-xs font-bold uppercase tracking-wide">
                                            <span class="material-symbols-outlined text-[14px]">schedule</span>
                                            Por Vencer
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="openQuickOdometroModal('{{ $vehiculo->patente }}')"
                                                class="text-xs font-bold text-primary bg-surface-container hover:bg-primary-fixed px-2.5 py-1.5 rounded transition-colors cursor-pointer">
                                            + Odómetro
                                        </button>
                                        <a href="{{ route('vehiculos.show', $vehiculo) }}"
                                           class="text-xs font-bold text-white bg-primary hover:bg-primary-container px-3 py-1.5 rounded transition-colors inline-flex items-center gap-1">
                                            Atender
                                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Scripts de Gráficos -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Gráfico de Salud (Dona)
            const ctxSalud = document.getElementById('chartSaludFlota');
            if (ctxSalud) {
                new Chart(ctxSalud, {
                    type: 'doughnut',
                    data: {
                        labels: ['Al Día', 'Por Vencer', 'Vencidos'],
                        datasets: [{
                            data: [{{ $vehiculosAlDia }}, {{ $vehiculosPorVencer }}, {{ $vehiculosConVencidos }}],
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '78%',
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            // 2. Gráfico de Distribución (Barras)
            const ctxTipos = document.getElementById('chartTiposFlota');
            if (ctxTipos) {
                const labels = @json($tiposFlota->keys());
                const data = @json($tiposFlota->values());

                new Chart(ctxTipos, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Unidades',
                            data: data,
                            backgroundColor: '#000a1f',
                            hoverBackgroundColor: '#00204a',
                            borderRadius: 4,
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
                                ticks: { precision: 0, font: { family: 'JetBrains Mono' } },
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
@endsection
