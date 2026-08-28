<?php

namespace Tests\Unit\Jobs;

use App\Jobs\EnviarAvisoMantencionJob;
use App\Mail\AvisoMantencionMail;
use App\Models\NotificacionEnviada;
use App\Models\PlanMantencion;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EnviarAvisoMantencionJobTest extends TestCase
{
    use RefreshDatabase;

    private function vehiculo(int $kmActual): Vehiculo
    {
        TipoVehiculo::firstOrCreate(['codigo' => 'SD'], ['nombre' => 'Sedán']);

        return Vehiculo::create([
            'patente' => 'ABCD12',
            'tipo_codigo' => 'SD',
            'modelo' => 'Corolla',
            'marca' => 'Toyota',
            'anio' => 2020,
            'km_actual' => $kmActual,
            'email_contacto' => 'flota@example.com',
        ]);
    }

    public function test_envia_el_correo_con_asunto_de_proximo_cuando_no_esta_vencido(): void
    {
        Mail::fake();

        $vehiculo = $this->vehiculo(9700);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        $notificacion = NotificacionEnviada::create([
            'patente' => $vehiculo->patente,
            'plan_id' => $plan->id,
            'km_objetivo' => 10000,
            'destinatario' => $vehiculo->email_contacto,
            'estado' => 'ENVIADA',
        ]);

        (new EnviarAvisoMantencionJob($notificacion))->handle();

        Mail::assertSent(AvisoMantencionMail::class, function (AvisoMantencionMail $mail) use ($vehiculo) {
            return $mail->hasTo($vehiculo->email_contacto)
                && $mail->envelope()->subject === '[ABCD12] Cambio de aceite próximo — faltan 300 km';
        });
    }

    public function test_envia_el_correo_con_asunto_de_vencido_cuando_corresponde(): void
    {
        Mail::fake();

        $vehiculo = $this->vehiculo(10400);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        $notificacion = NotificacionEnviada::create([
            'patente' => $vehiculo->patente,
            'plan_id' => $plan->id,
            'km_objetivo' => 10000,
            'destinatario' => $vehiculo->email_contacto,
            'estado' => 'ENVIADA',
        ]);

        (new EnviarAvisoMantencionJob($notificacion))->handle();

        Mail::assertSent(AvisoMantencionMail::class, function (AvisoMantencionMail $mail) {
            return $mail->envelope()->subject === '[ABCD12] Cambio de aceite VENCIDO — 400 km de atraso';
        });
    }

    public function test_marca_la_notificacion_como_fallida_si_el_envio_falla(): void
    {
        $vehiculo = $this->vehiculo(9700);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        $notificacion = NotificacionEnviada::create([
            'patente' => $vehiculo->patente,
            'plan_id' => $plan->id,
            'km_objetivo' => 10000,
            'destinatario' => $vehiculo->email_contacto,
            'estado' => 'ENVIADA',
        ]);

        (new EnviarAvisoMantencionJob($notificacion))->failed(new \RuntimeException('SMTP caído'));

        $this->assertSame('FALLIDA', $notificacion->fresh()->estado);
    }
}
