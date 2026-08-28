<?php

namespace App\Jobs;

use App\Mail\AvisoMantencionMail;
use App\Models\EventoMantencion;
use App\Models\NotificacionEnviada;
use App\Models\PlanMantencion;
use App\Models\Vehiculo;
use App\ValueObjects\EstadoMantencionPlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Regla 10: el envío en sí vive acá, en un job encolado — nunca dentro de
 * la transacción que inserta la lectura de odómetro.
 */
class EnviarAvisoMantencionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public NotificacionEnviada $notificacion) {}

    public function handle(): void
    {
        $vehiculo = Vehiculo::findOrFail($this->notificacion->patente);
        $plan = PlanMantencion::findOrFail($this->notificacion->plan_id);

        $kmUltimoServicio = (int) EventoMantencion::query()
            ->where('patente', $vehiculo->patente)
            ->where('plan_id', $plan->id)
            ->max('km_evento');

        $ultimoServicio = EventoMantencion::query()
            ->where('patente', $vehiculo->patente)
            ->where('plan_id', $plan->id)
            ->orderByDesc('km_evento')
            ->first();

        $estado = new EstadoMantencionPlan(
            plan: $plan,
            kmUltimoServicio: $kmUltimoServicio,
            kmObjetivo: $this->notificacion->km_objetivo,
            kmFaltantes: $this->notificacion->km_objetivo - $vehiculo->km_actual,
        );

        Mail::to($this->notificacion->destinatario)->send(new AvisoMantencionMail($vehiculo, $estado, $ultimoServicio));
    }

    public function failed(Throwable $exception): void
    {
        $this->notificacion->update(['estado' => 'FALLIDA']);
    }
}
