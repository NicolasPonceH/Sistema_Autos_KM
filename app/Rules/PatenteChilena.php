<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Formatos chilenos válidos, ya normalizados (mayúsculas, sin separadores):
 * LLLLNN (desde 2007), LLNNNN y LLLNNN (antiguas).
 */
class PatenteChilena implements ValidationRule
{
    public static function normalizar(string $valor): string
    {
        return strtoupper(preg_replace('/[.\x{00B7}-]/u', '', trim($valor)));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalizada = self::normalizar((string) $value);

        $formatos = [
            '/^[A-Z]{4}[0-9]{2}$/',
            '/^[A-Z]{2}[0-9]{4}$/',
            '/^[A-Z]{3}[0-9]{3}$/',
        ];

        foreach ($formatos as $formato) {
            if (preg_match($formato, $normalizada) === 1) {
                return;
            }
        }

        $fail('El campo :attribute no tiene un formato de patente chilena válido (LLLL·NN, LL·NNNN o LLL·NNN).');
    }
}
