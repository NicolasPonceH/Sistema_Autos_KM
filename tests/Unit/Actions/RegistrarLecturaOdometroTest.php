<?php

namespace Tests\Unit\Actions;

use App\Actions\RegistrarLecturaOdometro;
use App\Enums\OrigenLectura;
use App\Exceptions\Odometro\OdometroRetrocedeException;
use App\Exceptions\Odometro\SaltoSospechosoException;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrarLecturaOdometroTest extends TestCase
{
    use RefreshDatabase;

    private function vehiculo(int $kmActual): Vehiculo
    {
        $tipo = TipoVehiculo::create(['codigo' => 'SD', 'nombre' => 'Sedán']);

        return Vehiculo::create([
            'patente' => 'ABCD12',
            'tipo_codigo' => $tipo->codigo,
            'modelo' => 'Corolla',
            'anio' => 2020,
            'km_actual' => $kmActual,
            'email_contacto' => 'flota@example.com',
        ]);
    }

    public function test_registra_una_lectura_normal_y_actualiza_km_actual(): void
    {
        $vehiculo = $this->vehiculo(9000);

        $lectura = (new RegistrarLecturaOdometro)->ejecutar($vehiculo, 9500);

        $this->assertSame(9500, $lectura->km);
        $this->assertSame(9500, $vehiculo->fresh()->km_actual);
        $this->assertNotNull($vehiculo->fresh()->fecha_km);
    }

    public function test_rechaza_retroceso_sin_correccion(): void
    {
        $vehiculo = $this->vehiculo(9000);

        $this->expectException(OdometroRetrocedeException::class);

        (new RegistrarLecturaOdometro)->ejecutar($vehiculo, 8000);
    }

    public function test_rechaza_correccion_sin_observacion(): void
    {
        $vehiculo = $this->vehiculo(9000);

        $this->expectException(OdometroRetrocedeException::class);

        (new RegistrarLecturaOdometro)->ejecutar($vehiculo, 8000, OrigenLectura::Correccion, observacion: null);
    }

    public function test_permite_retroceso_con_correccion_y_observacion(): void
    {
        $vehiculo = $this->vehiculo(9000);

        $lectura = (new RegistrarLecturaOdometro)->ejecutar(
            $vehiculo,
            8000,
            OrigenLectura::Correccion,
            observacion: 'El tablero marcaba mal, se corrige con la boleta del taller.',
        );

        $this->assertSame(8000, $lectura->km);
        $this->assertSame(OrigenLectura::Correccion, $lectura->origen);
        $this->assertSame(8000, $vehiculo->fresh()->km_actual);
    }

    public function test_rechaza_salto_sospechoso_sin_confirmar(): void
    {
        $vehiculo = $this->vehiculo(45320);

        $this->expectException(SaltoSospechosoException::class);

        (new RegistrarLecturaOdometro)->ejecutar($vehiculo, 453200);
    }

    public function test_permite_salto_sospechoso_confirmado(): void
    {
        $vehiculo = $this->vehiculo(45320);

        $lectura = (new RegistrarLecturaOdometro)->ejecutar(
            $vehiculo,
            453200,
            saltoConfirmado: true,
        );

        $this->assertSame(453200, $lectura->km);
        $this->assertSame(453200, $vehiculo->fresh()->km_actual);
    }

    public function test_salto_justo_en_el_umbral_no_requiere_confirmacion(): void
    {
        $vehiculo = $this->vehiculo(9000);

        $lectura = (new RegistrarLecturaOdometro)->ejecutar($vehiculo, 9000 + RegistrarLecturaOdometro::KM_SALTO_SOSPECHOSO);

        $this->assertSame(9000 + RegistrarLecturaOdometro::KM_SALTO_SOSPECHOSO, $lectura->km);
    }
}
