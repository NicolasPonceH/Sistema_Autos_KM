<?php

namespace App\Http\Controllers;

use App\Actions\CalcularEstadoMantencion;
use App\Models\EventoMantencion;
use App\Models\Vehiculo;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(CalcularEstadoMantencion $calculador): View
    {
        $vehiculosActivos = Vehiculo::where('activo', true)
            ->with('tipoVehiculo')
            ->get();

        $todosLosEstados = $vehiculosActivos->flatMap(function (Vehiculo $vehiculo) use ($calculador) {
            return $calculador->paraVehiculo($vehiculo)
                ->map(fn ($estado) => ['vehiculo' => $vehiculo, 'estado' => $estado]);
        });

        $filas = $todosLosEstados
            ->filter(fn ($item) => $item['estado']->enVentanaAviso())
            ->sort(function (array $a, array $b) {
                $vencidoCmp = (int) $b['estado']->vencido() <=> (int) $a['estado']->vencido();

                return $vencidoCmp !== 0 ? $vencidoCmp : $a['estado']->kmFaltantes <=> $b['estado']->kmFaltantes;
            })
            ->values();

        // Estadísticas de KPIs
        $totalVehiculos = $vehiculosActivos->count();
        $totalInactivos = Vehiculo::where('activo', false)->count();
        $vencidosCount = $filas->filter(fn ($f) => $f['estado']->vencido())->count();
        $porVencerCount = $filas->filter(fn ($f) => ! $f['estado']->vencido())->count();
        
        // Vehículos con estado global
        $vehiculosConVencidos = $filas->filter(fn ($f) => $f['estado']->vencido())->pluck('vehiculo.patente')->unique()->count();
        $vehiculosPorVencer = $filas->filter(fn ($f) => ! $f['estado']->vencido())->pluck('vehiculo.patente')->unique()->count();
        $vehiculosAlDia = max(0, $totalVehiculos - $vehiculosConVencidos - $vehiculosPorVencer);

        $totalKmFlota = (int) $vehiculosActivos->sum('km_actual');
        $costoTotalMantencion = (float) (EventoMantencion::sum('costo') ?? 0);
        $totalServiciosRealizados = EventoMantencion::count();

        // Distribución por tipo de vehículo
        $tiposFlota = $vehiculosActivos->groupBy(fn ($v) => $v->tipoVehiculo->nombre ?? 'Sin tipo')
            ->map->count();

        return view('dashboard.index', compact(
            'filas',
            'totalVehiculos',
            'totalInactivos',
            'vencidosCount',
            'porVencerCount',
            'vehiculosAlDia',
            'vehiculosPorVencer',
            'vehiculosConVencidos',
            'totalKmFlota',
            'costoTotalMantencion',
            'totalServiciosRealizados',
            'tiposFlota'
        ));
    }
}
