<?php

namespace App\Actions;

use App\Enums\OrigenLectura;
use App\Exceptions\Odometro\OdometroRetrocedeException;
use App\Exceptions\Odometro\SaltoSospechosoException;
use App\Models\LecturaOdometro;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;

class RegistrarLecturaOdometro
{
    /**
     * Umbral de sección 5 ("salto sospechoso"). A diferencia de los
     * intervalos de plan_mantencion, este valor no vive en ninguna tabla
     * del plan — es un chequeo de sanidad de datos, no una regla de negocio
     * configurable, así que va como constante.
     */
    public const KM_SALTO_SOSPECHOSO = 5000;

    /**
     * @throws OdometroRetrocedeException si la lectura retrocede sin origen CORRECCION + observación.
     * @throws SaltoSospechosoException si el salto supera el umbral y no viene confirmado.
     */
    public function ejecutar(
        Vehiculo $vehiculo,
        int $km,
        OrigenLectura $origen = OrigenLectura::Manual,
        ?string $observacion = null,
        ?int $reportadoPor = null,
        bool $saltoConfirmado = false,
    ): LecturaOdometro {
        if ($km < $vehiculo->km_actual && ($origen !== OrigenLectura::Correccion || blank($observacion))) {
            throw new OdometroRetrocedeException($vehiculo, $km);
        }

        $diferencia = abs($km - $vehiculo->km_actual);

        if ($diferencia > self::KM_SALTO_SOSPECHOSO && ! $saltoConfirmado) {
            throw new SaltoSospechosoException($vehiculo, $km, $diferencia);
        }

        return DB::transaction(function () use ($vehiculo, $km, $origen, $observacion, $reportadoPor) {
            $lectura = LecturaOdometro::create([
                'patente' => $vehiculo->patente,
                'km' => $km,
                'origen' => $origen,
                'observacion' => $observacion,
                'reportado_por' => $reportadoPor,
            ]);

            $vehiculo->update([
                'km_actual' => $km,
                'fecha_km' => $lectura->fecha,
            ]);

            return $lectura;
        });
    }
}
