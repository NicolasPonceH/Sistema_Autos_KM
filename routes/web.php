<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventoMantencionController;
use App\Http\Controllers\LecturaOdometroController;
use App\Http\Controllers\PlanMantencionController;
use App\Http\Controllers\ReporteKmController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('reportes/km', [ReporteKmController::class, 'index'])->name('reportes.km');
Route::get('reportes/km/exportar', [ReporteKmController::class, 'exportar'])->name('reportes.km.exportar');
Route::post('reportes/km/enviar-correo', [ReporteKmController::class, 'enviarCorreo'])->name('reportes.km.enviar-correo');

Route::resource('vehiculos', VehiculoController::class);
Route::patch('vehiculos/{vehiculo}/activar', [VehiculoController::class, 'activar'])->name('vehiculos.activar');
Route::post('vehiculos/{vehiculo}/lecturas', [LecturaOdometroController::class, 'store'])->name('vehiculos.lecturas.store');
Route::post('vehiculos/{vehiculo}/eventos-mantencion', [EventoMantencionController::class, 'store'])->name('vehiculos.eventos-mantencion.store');

Route::resource('planes-mantencion', PlanMantencionController::class)
    ->except(['show'])
    ->parameters(['planes-mantencion' => 'plan']);

Route::get('emails/preview/{vehiculo}/{plan?}', function (\App\Models\Vehiculo $vehiculo, ?\App\Models\PlanMantencion $plan = null, \App\Actions\CalcularEstadoMantencion $calculador) {
    $plan = $plan ?? \App\Models\PlanMantencion::firstOrFail();
    $estado = $calculador->paraPlan($vehiculo, $plan);
    $ultimoServicio = \App\Models\EventoMantencion::where('patente', $vehiculo->patente)
        ->where('plan_id', $plan->id)
        ->orderByDesc('km_evento')
        ->first();

    return new \App\Mail\AvisoMantencionMail($vehiculo, $estado, $ultimoServicio);
})->name('emails.preview');

