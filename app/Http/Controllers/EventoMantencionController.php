<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventoMantencionRequest;
use App\Models\EventoMantencion;
use App\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;

class EventoMantencionController extends Controller
{
    public function store(StoreEventoMantencionRequest $request, Vehiculo $vehiculo): RedirectResponse
    {
        EventoMantencion::create([
            ...$request->validated(),
            'patente' => $vehiculo->patente,
        ]);

        return redirect()->route('vehiculos.show', $vehiculo)->with('status', 'Servicio registrado.');
    }
}
