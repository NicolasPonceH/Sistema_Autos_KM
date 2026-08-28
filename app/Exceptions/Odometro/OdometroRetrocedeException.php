<?php

namespace App\Exceptions\Odometro;

use App\Models\Vehiculo;
use RuntimeException;

/**
 * Regla 8: una lectura menor a km_actual se rechaza, salvo
 * origen=CORRECCION con observación obligatoria.
 */
class OdometroRetrocedeException extends RuntimeException
{
    public function __construct(public readonly Vehiculo $vehiculo, public readonly int $kmIntentado)
    {
        parent::__construct(sprintf(
            'La lectura de %s km es menor al kilometraje actual (%s km) de %s. '.
            'Usa origen CORRECCION con una observación para forzarla.',
            number_format($kmIntentado, 0, ',', '.'),
            number_format($vehiculo->km_actual, 0, ',', '.'),
            $vehiculo->patente,
        ));
    }
}
