<?php

namespace App\Http\Controllers;

use App\Actions\CalcularEstadoMantencion;
use App\Models\Vehiculo;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(CalcularEstadoMantencion $calculador): View
    {
        $filas = Vehiculo::where('activo', true)
            ->with('tipoVehiculo')
            ->get()
            ->flatMap(function (Vehiculo $vehiculo) use ($calculador) {
                return $calculador->paraVehiculo($vehiculo)
                    ->filter(fn ($estado) => $estado->enVentanaAviso())
                    ->map(fn ($estado) => ['vehiculo' => $vehiculo, 'estado' => $estado]);
            })
            ->sort(function (array $a, array $b) {
                $vencidoCmp = (int) $b['estado']->vencido() <=> (int) $a['estado']->vencido();

                return $vencidoCmp !== 0 ? $vencidoCmp : $a['estado']->kmFaltantes <=> $b['estado']->kmFaltantes;
            })
            ->values();

        return view('dashboard.index', compact('filas'));
    }
}
