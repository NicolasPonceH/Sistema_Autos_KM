<?php

namespace App\Actions;

use App\Jobs\EnviarAvisoMantencionJob;
use App\Models\NotificacionEnviada;
use App\Models\Vehiculo;
use App\ValueObjects\EstadoMantencionPlan;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Sección 3 del plan: para cada plan aplicable dentro de la ventana de
 * aviso, reclama la fila única en notificacion_enviada y solo si la
 * reclama (es decir, no existía ya para ese patente+plan+km_objetivo)
 * encola el correo. La dedup vive en el índice UNIQUE, no en un chequeo
 * previo con condición de carrera.
 */
class EvaluarAvisosMantencion
{
    public function __construct(private CalcularEstadoMantencion $calculador) {}

    public function paraVehiculo(Vehiculo $vehiculo): void
    {
        foreach ($this->calculador->paraVehiculo($vehiculo) as $estado) {
            if ($estado->enVentanaAviso()) {
                $this->notificarSiCorresponde($vehiculo, $estado);
            }
        }
    }

    private function notificarSiCorresponde(Vehiculo $vehiculo, EstadoMantencionPlan $estado): void
    {
        try {
            // En savepoint propio: si el INSERT choca con el UNIQUE, Postgres
            // aborta la transacción hasta el próximo ROLLBACK. DB::transaction()
            // ya hace ese rollback (a nivel de savepoint) al relanzar la
            // excepción, así que el resto de la evaluación no queda envenenado.
            $notificacion = DB::transaction(fn () => NotificacionEnviada::create([
                'patente' => $vehiculo->patente,
                'plan_id' => $estado->plan->id,
                'km_objetivo' => $estado->kmObjetivo,
                'destinatario' => $vehiculo->email_contacto,
                'estado' => 'ENVIADA',
            ]));
        } catch (QueryException $e) {
            if ($this->esViolacionDeUnicidad($e)) {
                return;
            }

            throw $e;
        }

        EnviarAvisoMantencionJob::dispatch($notificacion);
    }

    private function esViolacionDeUnicidad(QueryException $e): bool
    {
        return $e->getCode() === '23505';
    }
}
