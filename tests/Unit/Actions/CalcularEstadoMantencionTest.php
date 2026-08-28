<?php

namespace Tests\Unit\Actions;

use App\Actions\CalcularEstadoMantencion;
use App\Models\EventoMantencion;
use App\Models\PlanMantencion;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CalcularEstadoMantencionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function vehiculo(string $patente, string $tipoCodigo, int $kmActual): Vehiculo
    {
        TipoVehiculo::query()->firstOrCreate(['codigo' => $tipoCodigo], ['nombre' => $tipoCodigo]);

        return Vehiculo::create([
            'patente' => $patente,
            'tipo_codigo' => $tipoCodigo,
            'modelo' => 'Genérico',
            'anio' => 2020,
            'km_actual' => $kmActual,
            'email_contacto' => 'flota@example.com',
        ]);
    }

    /**
     * Sección 9, caso 1: un salto de 9.800 a 10.400 km con intervalo 10.000
     * debe detectarse como vencido, no ignorarse (rompería con un rango
     * cerrado [0, umbral]).
     */
    public function test_salto_por_sobre_la_ventana_se_detecta_como_vencido(): void
    {
        $vehiculo = $this->vehiculo('ABCD12', 'SD', 10400);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        $estado = (new CalcularEstadoMantencion)->paraPlan($vehiculo, $plan);

        $this->assertSame(10000, $estado->kmObjetivo);
        $this->assertSame(-400, $estado->kmFaltantes);
        $this->assertTrue($estado->vencido());
        $this->assertTrue($estado->enVentanaAviso());
    }

    /**
     * Sección 9, caso 2: km_faltantes negativo debe leerse como "vencido",
     * no como "faltan -400 km".
     */
    public function test_km_faltantes_negativo_se_describe_como_vencido(): void
    {
        $vehiculo = $this->vehiculo('ABCD12', 'SD', 10400);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        $estado = (new CalcularEstadoMantencion)->paraPlan($vehiculo, $plan);

        $this->assertSame('Vencido hace 400 km', $estado->descripcion());
    }

    /**
     * Sección 9, caso 4: registrar un evento_mantencion reinicia el ciclo;
     * el próximo objetivo se calcula desde el km del servicio, no desde 0.
     */
    public function test_evento_mantencion_reinicia_el_ciclo(): void
    {
        $vehiculo = $this->vehiculo('ABCD12', 'SD', 15000);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        EventoMantencion::create([
            'patente' => $vehiculo->patente,
            'plan_id' => $plan->id,
            'km_evento' => 10050,
            'fecha' => '2026-01-15',
        ]);

        $estado = (new CalcularEstadoMantencion)->paraPlan($vehiculo, $plan);

        $this->assertSame(10050, $estado->kmUltimoServicio);
        $this->assertSame(20050, $estado->kmObjetivo);
    }

    /**
     * Sección 9, caso 6: un plan con tipo_codigo='PU' no debe evaluarse
     * sobre un sedán.
     */
    public function test_plan_por_tipo_no_aplica_a_otros_tipos(): void
    {
        $vehiculo = $this->vehiculo('ABCD12', 'SD', 5000);
        TipoVehiculo::firstOrCreate(['codigo' => 'PU'], ['nombre' => 'Camioneta/Pickup']);

        PlanMantencion::create(['nombre' => 'Rotación de neumáticos', 'intervalo_km' => 10000, 'umbral_aviso' => 500, 'tipo_codigo' => 'PU']);
        $planGlobal = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        $estados = (new CalcularEstadoMantencion)->paraVehiculo($vehiculo);

        $this->assertCount(1, $estados);
        $this->assertSame($planGlobal->id, $estados->first()->plan->id);
    }

    public function test_dentro_de_ventana_normal_no_esta_vencido(): void
    {
        $vehiculo = $this->vehiculo('ABCD12', 'SD', 9700);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        $estado = (new CalcularEstadoMantencion)->paraPlan($vehiculo, $plan);

        $this->assertSame(300, $estado->kmFaltantes);
        $this->assertFalse($estado->vencido());
        $this->assertTrue($estado->enVentanaAviso());
        $this->assertSame('Faltan 300 km', $estado->descripcion());
    }

    public function test_fuera_de_la_ventana_de_aviso(): void
    {
        $vehiculo = $this->vehiculo('ABCD12', 'SD', 2000);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        $estado = (new CalcularEstadoMantencion)->paraPlan($vehiculo, $plan);

        $this->assertFalse($estado->enVentanaAviso());
    }

    /**
     * Fase 5: "cada 10.000 km o 12 meses, lo que ocurra primero" — vencido
     * por tiempo aunque el km todavía esté lejos.
     */
    public function test_vencido_por_tiempo_aunque_el_km_no_este_vencido(): void
    {
        Carbon::setTestNow('2027-01-15');

        $vehiculo = $this->vehiculo('ABCD12', 'SD', 2000); // muy lejos de vencer por km
        $plan = PlanMantencion::create([
            'nombre' => 'Revisión anual', 'intervalo_km' => 10000, 'umbral_aviso' => 500,
            'intervalo_meses' => 12, 'umbral_aviso_dias' => 30,
        ]);

        EventoMantencion::create([
            'patente' => $vehiculo->patente, 'plan_id' => $plan->id,
            'km_evento' => 1000, 'fecha' => '2025-12-01', // hace 13 meses y medio
        ]);

        $estado = (new CalcularEstadoMantencion)->paraPlan($vehiculo, $plan);

        $this->assertFalse($estado->vencidoPorKm());
        $this->assertTrue($estado->vencidoPorTiempo());
        $this->assertTrue($estado->vencido());
        $this->assertSame('tiempo', $estado->ejeQueManda());
    }

    public function test_vencido_por_km_aunque_el_tiempo_no_este_vencido(): void
    {
        Carbon::setTestNow('2026-06-01');

        $vehiculo = $this->vehiculo('ABCD12', 'SD', 10400);
        $plan = PlanMantencion::create([
            'nombre' => 'Revisión anual', 'intervalo_km' => 10000, 'umbral_aviso' => 500,
            'intervalo_meses' => 12, 'umbral_aviso_dias' => 30,
        ]);

        EventoMantencion::create([
            'patente' => $vehiculo->patente, 'plan_id' => $plan->id,
            'km_evento' => 0, 'fecha' => '2026-01-01', // hace 5 meses, lejos de vencer por tiempo
        ]);

        $estado = (new CalcularEstadoMantencion)->paraPlan($vehiculo, $plan);

        $this->assertTrue($estado->vencidoPorKm());
        $this->assertFalse($estado->vencidoPorTiempo());
        $this->assertTrue($estado->vencido());
        $this->assertSame('km', $estado->ejeQueManda());
    }

    public function test_sin_servicio_previo_usa_el_alta_del_vehiculo_como_base_temporal(): void
    {
        Carbon::setTestNow('2027-01-05'); // 4 días después del objetivo (2027-01-01)

        $vehiculo = $this->vehiculo('ABCD12', 'SD', 100);
        $vehiculo->forceFill(['creado_en' => '2026-01-01'])->save();

        $plan = PlanMantencion::create([
            'nombre' => 'Revisión anual', 'intervalo_km' => 100000, 'umbral_aviso' => 500,
            'intervalo_meses' => 12, 'umbral_aviso_dias' => 30,
        ]);

        $estado = (new CalcularEstadoMantencion)->paraPlan($vehiculo, $plan);

        $this->assertTrue($estado->vencidoPorTiempo());
        $this->assertSame('2027-01-01', $estado->fechaObjetivo->toDateString());
    }

    public function test_plan_sin_intervalo_de_tiempo_no_tiene_criterio_temporal(): void
    {
        $vehiculo = $this->vehiculo('ABCD12', 'SD', 9700);
        $plan = PlanMantencion::create(['nombre' => 'Cambio de aceite', 'intervalo_km' => 10000, 'umbral_aviso' => 500]);

        $estado = (new CalcularEstadoMantencion)->paraPlan($vehiculo, $plan);

        $this->assertFalse($estado->tieneCriterioTiempo());
        $this->assertNull($estado->descripcionTiempo());
        $this->assertSame('km', $estado->ejeQueManda());
    }
}
