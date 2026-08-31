<?php

namespace Tests\Feature;

use App\Mail\ReporteKmMail;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReporteKmExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TipoVehiculo::create(['codigo' => 'SUV', 'nombre' => 'Vehículo SUV']);
        Vehiculo::create([
            'patente' => 'PBSY69',
            'tipo_codigo' => 'SUV',
            'marca' => 'Toyota',
            'modelo' => 'RAV4',
            'anio' => 2022,
            'email_contacto' => 'test@pdi.cl',
            'km_actual' => 45000,
            'activo' => true,
        ]);
    }

    public function test_reporte_km_vista_carga_correctamente(): void
    {
        $response = $this->get(route('reportes.km'));
        $response->assertOk();
        $response->assertSee('PBSY69');
    }

    public function test_exportar_excel_oficial_pdi(): void
    {
        $response = $this->get(route('reportes.km.exportar'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->assertStringContainsString('POLICÍA DE INVESTIGACIONES DE CHILE', $response->getContent());
    }

    public function test_exportar_csv_con_delimitador_punto_y_coma(): void
    {
        $response = $this->get(route('reportes.km.exportar', ['formato' => 'csv']));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_enviar_reporte_por_correo(): void
    {
        Mail::fake();

        $response = $this->post(route('reportes.km.enviar-correo'), [
            'email' => 'jefatura@pdi.cl',
            'mensaje' => 'Informe mensual para revisión.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Mail::assertSent(ReporteKmMail::class, function ($mail) {
            return $mail->hasTo('jefatura@pdi.cl');
        });
    }
}
