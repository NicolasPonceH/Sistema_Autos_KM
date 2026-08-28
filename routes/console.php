<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sección 4 del plan: job diario, cubre reintentos, planes recién creados,
// cambios de umbral y vehículos que ya estaban dentro de la ventana.
Schedule::command('mantencion:evaluar-avisos')->daily();
