<?php

namespace App\Http\Controllers;

use App\Actions\CalcularEstadoMantencion;
use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehiculoController extends Controller
{
    public function index(Request $request): View
    {
        $vehiculos = Vehiculo::query()
            ->with('tipoVehiculo')
            ->when(
                $request->filled('patente'),
                fn ($query) => $query->where('patente', 'like', '%'.strtoupper($request->string('patente')).'%')
            )
            ->when(
                $request->filled('tipo_codigo'),
                fn ($query) => $query->where('tipo_codigo', $request->string('tipo_codigo'))
            )
            ->when(
                ! $request->boolean('mostrar_inactivos'),
                fn ($query) => $query->where('activo', true)
            )
            ->orderBy('patente')
            ->paginate(20)
            ->withQueryString();

        $tipos = TipoVehiculo::orderBy('nombre')->get();

        return view('vehiculos.index', compact('vehiculos', 'tipos'));
    }

    public function show(Vehiculo $vehiculo, CalcularEstadoMantencion $calculador): View
    {
        $vehiculo->load('tipoVehiculo');

        $lecturas = $vehiculo->lecturas()
            ->with('reportadoPor')
            ->orderByDesc('fecha')
            ->paginate(15);

        $estadosMantencion = $calculador->paraVehiculo($vehiculo);
        $planesDisponibles = $estadosMantencion->pluck('plan');

        $eventosMantencion = $vehiculo->eventosMantencion()
            ->with('plan')
            ->orderByDesc('fecha')
            ->limit(10)
            ->get();

        return view('vehiculos.show', compact(
            'vehiculo', 'lecturas', 'estadosMantencion', 'planesDisponibles', 'eventosMantencion'
        ));
    }

    public function create(): View
    {
        $tipos = TipoVehiculo::orderBy('nombre')->get();

        return view('vehiculos.create', compact('tipos'));
    }

    public function store(StoreVehiculoRequest $request): RedirectResponse
    {
        Vehiculo::create($request->validated());

        return redirect()->route('vehiculos.index')->with('status', 'Vehículo registrado.');
    }

    public function edit(Vehiculo $vehiculo): View
    {
        $tipos = TipoVehiculo::orderBy('nombre')->get();

        return view('vehiculos.edit', compact('vehiculo', 'tipos'));
    }

    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->update($request->validated());

        return redirect()->route('vehiculos.index')->with('status', 'Vehículo actualizado.');
    }

    public function destroy(Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->update(['activo' => false]);

        return redirect()->route('vehiculos.index')->with('status', 'Vehículo dado de baja.');
    }

    public function activar(Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->update(['activo' => true]);

        return redirect()->route('vehiculos.index')->with('status', 'Vehículo reactivado.');
    }
}
