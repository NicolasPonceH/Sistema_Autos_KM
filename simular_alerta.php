<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$emailDestino = $argv[1] ?? 'poncenicolas997@gmail.com';
$patente = $argv[2] ?? 'PBSY69';

echo "\n";
echo "=================================================================\n";
echo "    SCRIPT DE PRUEBA: SIMULACIÓN DE VENCIMIENTO Y ENVÍO PDI     \n";
echo "=================================================================\n\n";

$vehiculo = \App\Models\Vehiculo::find($patente) ?: \App\Models\Vehiculo::first();
if (!$vehiculo) {
    die("ERROR: No hay vehículos registrados en el sistema.\n");
}

$vehiculo->update(['email_contacto' => $emailDestino]);

echo "1. Vehículo: {$vehiculo->patente} ({$vehiculo->marca} {$vehiculo->modelo})\n";
echo "2. Odómetro actual: " . number_format($vehiculo->km_actual, 0, ',', '.') . " km\n";
echo "3. Correo destino: {$emailDestino}\n\n";

$calculador = $app->make(\App\Actions\CalcularEstadoMantencion::class);
$evaluador = $app->make(\App\Actions\EvaluarAvisosMantencion::class);
$plan = \App\Models\PlanMantencion::first();

$estado = $calculador->paraPlan($vehiculo, $plan);
$kmObjetivo = $estado->kmObjetivo;
$nuevoKm = max($vehiculo->km_actual + 300, $kmObjetivo + 450);

echo "4. Aumentando odómetro para forzar el VENCIMIENTO del plan '{$plan->nombre}'...\n";
echo "   - Intervalo objetivo: " . number_format($kmObjetivo, 0, ',', '.') . " km\n";
echo "   - Nuevo odómetro asignado: " . number_format($nuevoKm, 0, ',', '.') . " km (Atraso: " . number_format($nuevoKm - $kmObjetivo, 0, ',', '.') . " km)\n\n";

// Limpiar historial de dedup para permitir el envío en esta prueba
\Illuminate\Support\Facades\DB::table('notificacion_enviada')
    ->where('patente', $vehiculo->patente)
    ->where('plan_id', $plan->id)
    ->where('km_objetivo', $kmObjetivo)
    ->delete();

// Actualizar en base de datos
\Illuminate\Support\Facades\DB::transaction(function () use ($vehiculo, $nuevoKm) {
    \App\Models\LecturaOdometro::create([
        'patente' => $vehiculo->patente,
        'km' => $nuevoKm,
        'fecha' => now(),
        'origen' => 'MANUAL',
        'observacion' => 'Simulación de prueba de alerta automática',
    ]);
    $vehiculo->update([
        'km_actual' => $nuevoKm,
        'fecha_km' => now(),
    ]);
});

echo "✓ Odómetro guardado en el sistema.\n";
echo "5. Enviando correo de alerta por Gmail SMTP...\n";

$estadoNuevo = $calculador->paraPlan($vehiculo, $plan);
$ultimoServicio = \App\Models\EventoMantencion::where('patente', $vehiculo->patente)
    ->where('plan_id', $plan->id)
    ->first();

try {
    \Illuminate\Support\Facades\Mail::to($emailDestino)->send(
        new \App\Mail\AvisoMantencionMail($vehiculo, $estadoNuevo, $ultimoServicio)
    );
    echo "\n=================================================================\n";
    echo "  ¡ÉXITO! Alerta de mantención enviada a: {$emailDestino}\n";
    echo "=================================================================\n";
    echo "Revisa tu bandeja de entrada en Gmail ({$emailDestino}).\n\n";
} catch (\Throwable $e) {
    echo "ERROR al enviar correo: " . $e->getMessage() . "\n\n";
}
