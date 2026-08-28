<?php

namespace App\Http\Controllers;

use App\Actions\EvaluarAvisosMantencion;
use App\Actions\RegistrarLecturaOdometro;
use App\Enums\OrigenLectura;
use App\Exceptions\Odometro\OdometroRetrocedeException;
use App\Exceptions\Odometro\SaltoSospechosoException;
use App\Http\Requests\StoreLecturaOdometroRequest;
use App\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;

class LecturaOdometroController extends Controller
{
    public function store(
        StoreLecturaOdometroRequest $request,
        Vehiculo $vehiculo,
        RegistrarLecturaOdometro $registrar,
        EvaluarAvisosMantencion $evaluarAvisos,
    ): RedirectResponse {
        $origen = $request->boolean('es_correccion') ? OrigenLectura::Correccion : OrigenLectura::Manual;

        try {
            $registrar->ejecutar(
                vehiculo: $vehiculo,
                km: $request->integer('km'),
                origen: $origen,
                observacion: $request->input('observacion') ?: null,
                reportadoPor: $request->user()?->id,
                saltoConfirmado: $request->boolean('confirmar_salto'),
            );
        } catch (OdometroRetrocedeException $e) {
            return back()->withInput()->withErrors(['km' => $e->getMessage()]);
        } catch (SaltoSospechosoException $e) {
            return back()->withInput()->withErrors(['km' => $e->getMessage()])
                ->with('requiere_confirmacion_salto', true);
        }

        // Evaluación inmediata, fuera de la transacción de la lectura (regla 10).
        // $vehiculo ya refleja el km_actual nuevo: RegistrarLecturaOdometro lo
        // actualiza sobre esta misma instancia.
        $evaluarAvisos->paraVehiculo($vehiculo);

        return redirect()->route('vehiculos.show', $vehiculo)->with('status', 'Lectura registrada.');
    }
}
