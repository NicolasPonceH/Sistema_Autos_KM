<?php

namespace Tests\Unit\Actions;

use App\Actions\EvaluarAvisosMantencion;
use App\Jobs\EnviarAvisoMantencionJob;
use App\Models\EventoMantencion;
use App\Models\NotificacionEnviada;
use App\Models\PlanMantencion;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class EvaluarAvisosMantencionTest extends TestCase
{
    use RefreshDatabase;

    private function vehiculo(int $kmActual): Vehiculo
    {
        TipoVehiculo::firstOrCreate(['codigo' => 'SD'], ['nombre' => 'Sedán']);

        return Vehiculo::create([
            'patente' => 'ABCD12',
            'tipo_codigo' => 'SD',
            'modelo' => 'Corolla',
            'anio' => 2020,
            'km_actual' => $kmActual,
            'email_contacto' => 'flota@example.com',
        ]);
    }

    /**
     * Sección 9, caso 3: tres lecturas seguidas dentro de la ventana de
     * aviso deben producir un solo correo, gracias al UNIQUE de
     * notificacion_enviada — no a un chequeo previo con condición de carrera.
     */
    public function test_reportes_repetidos_dentro_de_la_ventana_generan_un_solo_aviso(): void
    {
        Bus::fake();

        $vehiculo = $this->vehiculo(9500);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);
        $evaluar = new EvaluarAvisosMantencion(app(\App\Actions\CalcularEstadoMantencion::class));

        foreach ([9500, 9700, 9900] as $km) {
            $vehiculo->update(['km_actual' => $km]);
            $evaluar->paraVehiculo($vehiculo->fresh());
        }

        Bus::assertDispatchedTimes(EnviarAvisoMantencionJob::class, 1);
        $this->assertSame(1, NotificacionEnviada::count());

        $notificacion = NotificacionEnviada::first();
        $this->assertSame('ABCD12', $notificacion->patente);
        $this->assertSame($plan->id, $notificacion->plan_id);
        $this->assertSame(10000, $notificacion->km_objetivo);
        $this->assertSame('ENVIADA', $notificacion->estado);
    }

    public function test_no_notifica_fuera_de_la_ventana_de_aviso(): void
    {
        Bus::fake();

        $vehiculo = $this->vehiculo(2000);
        PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        (new EvaluarAvisosMantencion(app(\App\Actions\CalcularEstadoMantencion::class)))->paraVehiculo($vehiculo);

        Bus::assertNotDispatched(EnviarAvisoMantencionJob::class);
        $this->assertSame(0, NotificacionEnviada::count());
    }

    public function test_vencido_tambien_notifica(): void
    {
        Bus::fake();

        $vehiculo = $this->vehiculo(10400);
        PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        (new EvaluarAvisosMantencion(app(\App\Actions\CalcularEstadoMantencion::class)))->paraVehiculo($vehiculo);

        Bus::assertDispatchedTimes(EnviarAvisoMantencionJob::class, 1);
    }

    /**
     * Sección 3: registrar un evento_mantencion reinicia el ciclo, y la
     * próxima notificación tiene otra clave única — puede volver a avisar.
     */
    public function test_evento_mantencion_habilita_una_nueva_notificacion(): void
    {
        Bus::fake();

        $vehiculo = $this->vehiculo(9500);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);
        $evaluar = new EvaluarAvisosMantencion(app(\App\Actions\CalcularEstadoMantencion::class));

        $evaluar->paraVehiculo($vehiculo);
        Bus::assertDispatchedTimes(EnviarAvisoMantencionJob::class, 1);

        EventoMantencion::create([
            'patente' => $vehiculo->patente,
            'plan_id' => $plan->id,
            'km_evento' => 10050,
            'fecha' => now()->toDateString(),
        ]);

        $vehiculo->update(['km_actual' => 19700]);
        $evaluar->paraVehiculo($vehiculo->fresh());

        Bus::assertDispatchedTimes(EnviarAvisoMantencionJob::class, 2);
        $this->assertSame(2, NotificacionEnviada::count());
        $this->assertSame([10000, 20050], NotificacionEnviada::orderBy('km_objetivo')->pluck('km_objetivo')->all());
    }
}
