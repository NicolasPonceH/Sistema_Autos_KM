<?php

namespace Database\Seeders;

use App\Enums\OrigenLectura;
use App\Models\EventoMantencion;
use App\Models\LecturaOdometro;
use App\Models\PlanMantencion;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoFlotaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar catálogo de tipos de vehículo
        $this->call(TipoVehiculoSeeder::class);

        // 2. Planes de mantención estándar
        $planAceite = PlanMantencion::firstOrCreate(
            ['nombre' => 'Cambio de Aceite y Filtro'],
            [
                'intervalo_km' => 10000,
                'umbral_aviso' => 500,
                'intervalo_meses' => 12,
                'umbral_aviso_dias' => 30,
                'tipo_codigo' => null,
            ]
        );

        $planFrenos = PlanMantencion::firstOrCreate(
            ['nombre' => 'Revisión y Pastillas de Freno'],
            [
                'intervalo_km' => 30000,
                'umbral_aviso' => 1000,
                'intervalo_meses' => 24,
                'umbral_aviso_dias' => 45,
                'tipo_codigo' => null,
            ]
        );

        $planFiltros = PlanMantencion::firstOrCreate(
            ['nombre' => 'Filtro de Aire y Combustible'],
            [
                'intervalo_km' => 20000,
                'umbral_aviso' => 800,
                'intervalo_meses' => 12,
                'umbral_aviso_dias' => 30,
                'tipo_codigo' => null,
            ]
        );

        $planDistribucion = PlanMantencion::firstOrCreate(
            ['nombre' => 'Correa de Distribución y Bomba'],
            [
                'intervalo_km' => 60000,
                'umbral_aviso' => 2000,
                'intervalo_meses' => 48,
                'umbral_aviso_dias' => 60,
                'tipo_codigo' => null,
            ]
        );

        // 3. Flota de vehículos de prueba con diversos estados
        $vehiculosData = [
            [
                'patente' => 'PBSY69',
                'tipo_codigo' => 'SUV',
                'marca' => 'CHEVROLET',
                'modelo' => 'CAPTIVA 2.0 AUT',
                'anio' => 2019,
                'nro_motor' => 'Z20S-99124',
                'nro_chasis' => 'KL1CC52BJDB123456',
                'km_actual' => 45320,
                'email_contacto' => 'flota.operaciones@empresa.cl',
                'activo' => true,
                'lecturas' => [
                    ['km' => 38000, 'meses_atras' => 5],
                    ['km' => 40200, 'meses_atras' => 4],
                    ['km' => 42100, 'meses_atras' => 3],
                    ['km' => 43800, 'meses_atras' => 2],
                    ['km' => 44900, 'meses_atras' => 1],
                    ['km' => 45320, 'meses_atras' => 0],
                ],
                'eventos' => [
                    ['plan_id' => $planAceite->id, 'km' => 40000, 'fecha' => now()->subMonths(4), 'costo' => 75000, 'taller' => 'AutoPlanet Providencia', 'notas' => 'Aceite 5W30 Sintético y filtro original'],
                    ['plan_id' => $planFiltros->id, 'km' => 40000, 'fecha' => now()->subMonths(4), 'costo' => 35000, 'taller' => 'AutoPlanet Providencia', 'notas' => 'Filtro de aire y polen'],
                ]
            ],
            [
                'patente' => 'HGTR82',
                'tipo_codigo' => 'PU',
                'marca' => 'TOYOTA',
                'modelo' => 'HILUX 2.8 TDI 4X4',
                'anio' => 2021,
                'nro_motor' => '1GD-88231',
                'nro_chasis' => '8AJBA3CD8E0099881',
                'km_actual' => 61450, // ¡Vencido! Último servicio a los 50.000 km, intervalo 10.000 -> objetivo 60.000 km
                'email_contacto' => 'mantenimiento.hilux@empresa.cl',
                'activo' => true,
                'lecturas' => [
                    ['km' => 50000, 'meses_atras' => 5],
                    ['km' => 53100, 'meses_atras' => 4],
                    ['km' => 55800, 'meses_atras' => 3],
                    ['km' => 58200, 'meses_atras' => 2],
                    ['km' => 60100, 'meses_atras' => 1],
                    ['km' => 61450, 'meses_atras' => 0],
                ],
                'eventos' => [
                    ['plan_id' => $planAceite->id, 'km' => 50000, 'fecha' => now()->subMonths(5), 'costo' => 95000, 'taller' => 'Concesionario Toyota Bruno Fritsch', 'notas' => 'Mantención pauta 50.000 km'],
                    ['plan_id' => $planFrenos->id, 'km' => 60000, 'fecha' => now()->subMonths(1), 'costo' => 120000, 'taller' => 'Frenos Cordillera', 'notas' => 'Pastillas de freno delanteras cerámicas'],
                ]
            ],
            [
                'patente' => 'LKJP14',
                'tipo_codigo' => 'SUV',
                'marca' => 'HYUNDAI',
                'modelo' => 'TUCSON 2.0 CRDI',
                'anio' => 2022,
                'nro_motor' => 'D4HA-44120',
                'nro_chasis' => 'KMHJT81WDU1122334',
                'km_actual' => 29650, // ¡Por vencer en Frenos! (intervalo 30.000, faltan 350 km <= umbral 1.000)
                'email_contacto' => 'tucson.flota@empresa.cl',
                'activo' => true,
                'lecturas' => [
                    ['km' => 21000, 'meses_atras' => 5],
                    ['km' => 23400, 'meses_atras' => 4],
                    ['km' => 25800, 'meses_atras' => 3],
                    ['km' => 27500, 'meses_atras' => 2],
                    ['km' => 28900, 'meses_atras' => 1],
                    ['km' => 29650, 'meses_atras' => 0],
                ],
                'eventos' => [
                    ['plan_id' => $planAceite->id, 'km' => 20000, 'fecha' => now()->subMonths(5), 'costo' => 80000, 'taller' => 'Gildemeister Las Condes', 'notas' => 'Pauta 20.000 km oficial'],
                    ['plan_id' => $planFiltros->id, 'km' => 20000, 'fecha' => now()->subMonths(5), 'costo' => 40000, 'taller' => 'Gildemeister Las Condes', 'notas' => 'Filtros de aire y polen'],
                ]
            ],
            [
                'patente' => 'CDGF55',
                'tipo_codigo' => 'SD',
                'marca' => 'NISSAN',
                'modelo' => 'VERSA 1.6 SENSE',
                'anio' => 2023,
                'nro_motor' => 'HR16-77312',
                'nro_chasis' => '3N1CN7AP5KL789012',
                'km_actual' => 14200, // Al día (aceite a los 10k, próximo a los 20k)
                'email_contacto' => 'admin.flota@empresa.cl',
                'activo' => true,
                'lecturas' => [
                    ['km' => 8000, 'meses_atras' => 4],
                    ['km' => 10100, 'meses_atras' => 3],
                    ['km' => 11800, 'meses_atras' => 2],
                    ['km' => 13100, 'meses_atras' => 1],
                    ['km' => 14200, 'meses_atras' => 0],
                ],
                'eventos' => [
                    ['plan_id' => $planAceite->id, 'km' => 10000, 'fecha' => now()->subMonths(3), 'costo' => 68000, 'taller' => 'Nissan Cidef', 'notas' => 'Mantención inicial 10.000 km'],
                ]
            ],
            [
                'patente' => 'VJ9921',
                'tipo_codigo' => 'VN',
                'marca' => 'FORD',
                'modelo' => 'TRANSIT CUSTOM 2.0',
                'anio' => 2020,
                'nro_motor' => 'YNF6-31298',
                'nro_chasis' => 'WF0YXXTTGY1234567',
                'km_actual' => 58900, // ¡Por vencer en Distribución! (intervalo 60.000 km, faltan 1.100 km <= umbral 2.000)
                'email_contacto' => 'logistica.furgon@empresa.cl',
                'activo' => true,
                'lecturas' => [
                    ['km' => 48000, 'meses_atras' => 5],
                    ['km' => 50500, 'meses_atras' => 4],
                    ['km' => 53200, 'meses_atras' => 3],
                    ['km' => 55400, 'meses_atras' => 2],
                    ['km' => 57600, 'meses_atras' => 1],
                    ['km' => 58900, 'meses_atras' => 0],
                ],
                'eventos' => [
                    ['plan_id' => $planAceite->id, 'km' => 50000, 'fecha' => now()->subMonths(4), 'costo' => 110000, 'taller' => 'Salfa Ford Rancagua', 'notas' => 'Aceite 5W30 Castrol sintético'],
                    ['plan_id' => $planFiltros->id, 'km' => 50000, 'fecha' => now()->subMonths(4), 'costo' => 55000, 'taller' => 'Salfa Ford Rancagua', 'notas' => 'Filtro de combustible diésel y aire'],
                    ['plan_id' => $planFrenos->id, 'km' => 50000, 'fecha' => now()->subMonths(4), 'costo' => 140000, 'taller' => 'Salfa Ford Rancagua', 'notas' => 'Rectificado de discos y pastillas nuevas'],
                ]
            ],
        ];

        foreach ($vehiculosData as $data) {
            $vehiculo = Vehiculo::updateOrCreate(
                ['patente' => $data['patente']],
                [
                    'tipo_codigo' => $data['tipo_codigo'],
                    'marca' => $data['marca'],
                    'modelo' => $data['modelo'],
                    'anio' => $data['anio'],
                    'nro_motor' => $data['nro_motor'],
                    'nro_chasis' => $data['nro_chasis'],
                    'km_actual' => $data['km_actual'],
                    'fecha_km' => now(),
                    'email_contacto' => $data['email_contacto'],
                    'activo' => $data['activo'],
                    'creado_en' => now()->subMonths(6),
                ]
            );

            // Insertar lecturas de odómetro con fechas históricas usando insert directo
            foreach ($data['lecturas'] as $lec) {
                $fechaLectura = now()->subMonths($lec['meses_atras'])->startOfMonth()->addDays(5);
                
                // Evitar duplicados por patente y km
                $existe = DB::table('lectura_odometro')
                    ->where('patente', $vehiculo->patente)
                    ->where('km', $lec['km'])
                    ->exists();

                if (! $existe) {
                    DB::table('lectura_odometro')->insert([
                        'patente' => $vehiculo->patente,
                        'km' => $lec['km'],
                        'fecha' => $fechaLectura,
                        'origen' => 'MANUAL',
                        'anulada' => false,
                        'observacion' => $lec['meses_atras'] === 0 ? 'Lectura reciente de tablero' : 'Cierre mensual de odómetro',
                    ]);
                }
            }

            // Insertar eventos de mantención
            foreach ($data['eventos'] as $evt) {
                $existeEvento = EventoMantencion::where('patente', $vehiculo->patente)
                    ->where('plan_id', $evt['plan_id'])
                    ->where('km_evento', $evt['km'])
                    ->exists();

                if (! $existeEvento) {
                    EventoMantencion::create([
                        'patente' => $vehiculo->patente,
                        'plan_id' => $evt['plan_id'],
                        'km_evento' => $evt['km'],
                        'fecha' => $evt['fecha'],
                        'costo' => $evt['costo'],
                        'taller' => $evt['taller'],
                        'notas' => $evt['notas'],
                    ]);
                }
            }
        }
    }
}
