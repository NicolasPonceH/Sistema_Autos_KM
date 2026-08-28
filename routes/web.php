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

Route::resource('vehiculos', VehiculoController::class);
Route::patch('vehiculos/{vehiculo}/activar', [VehiculoController::class, 'activar'])->name('vehiculos.activar');
Route::post('vehiculos/{vehiculo}/lecturas', [LecturaOdometroController::class, 'store'])->name('vehiculos.lecturas.store');
Route::post('vehiculos/{vehiculo}/eventos-mantencion', [EventoMantencionController::class, 'store'])->name('vehiculos.eventos-mantencion.store');

Route::resource('planes-mantencion', PlanMantencionController::class)
    ->except(['show'])
    ->parameters(['planes-mantencion' => 'plan']);
