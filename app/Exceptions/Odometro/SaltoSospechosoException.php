<?php

namespace App\Exceptions\Odometro;

use App\Models\Vehiculo;
use RuntimeException;

/**
 * Sección 5: si la diferencia con la lectura anterior supera el umbral,
 * se pide confirmación explícita antes de persistir (evita que un
 * "453200" en vez de "45320" arruine el historial).
 */
class SaltoSospechosoException extends RuntimeException
{
    public function __construct(
        public readonly Vehiculo $vehiculo,
        public readonly int $kmIntentado,
        public readonly int $diferencia,
    ) {
        parent::__construct(sprintf(
            'La diferencia entre %s km y el kilometraje actual (%s km) de %s es de %s km, '.
            'por sobre el umbral de confirmación. Confirma explícitamente para continuar.',
            number_format($kmIntentado, 0, ',', '.'),
            number_format($vehiculo->km_actual, 0, ',', '.'),
            $vehiculo->patente,
            number_format($diferencia, 0, ',', '.'),
        ));
    }
}
