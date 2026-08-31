<?php

namespace App\Console\Commands;

use App\Actions\CalcularEstadoMantencion;
use App\Actions\EvaluarAvisosMantencion;
use App\Models\LecturaOdometro;
use App\Models\PlanMantencion;
use App\Models\Vehiculo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SimularVencimientoCommand extends Command
{
    protected $signature = 'probar:vencimiento {patente? : Placa patente a simular (opcional)} {--email= : Correo destino (por defecto poncenicolas997@gmail.com)}';

    protected $description = 'Simula el avance de odómetro de un vehículo hasta vencer un plan y envía el correo de alerta real';

    public function handle(CalcularEstadoMantencion $calculador, EvaluarAvisosMantencion $evaluador): int
    {
        $this->info('===========================================================');
        $this->info('   SIMULADOR DE VENCIMIENTO Y ENVÍO DE CORREO - PDI FLEET  ');
        $this->info('===========================================================');

        $emailDestino = $this->option('email') ?: 'poncenicolas997@gmail.com';
        $patente = $this->argument('patente');

        $vehiculo = $patente
            ? Vehiculo::find(strtoupper($patente))
            : Vehiculo::where('activo', true)->first();

        if (! $vehiculo) {
            $this->error('No se encontró ningún vehículo para la prueba.');
            return 1;
        }

        // Asignar el correo destino al vehículo para la prueba
        $vehiculo->update(['email_contacto' => $emailDestino]);

        $this->line("1. Vehículo seleccionado: <fg=yellow>{$vehiculo->patente}</> ({$vehiculo->marca} {$vehiculo->modelo})");
        $this->line("2. Odómetro actual en tablero: <fg=cyan>{$vehiculo->km_actual} km</>");
        $this->line("3. Correo destino configurado: <fg=green>{$emailDestino}</>");

        // Buscar un plan de mantención
        $plan = PlanMantencion::first();
        if (! $plan) {
            $this->error('No hay planes de mantención registrados.');
            return 1;
        }

        // Calcular km para dejarlo vencido (objetivo + 250 km de atraso)
        $estadoActual = $calculador->paraPlan($vehiculo, $plan);
        $kmObjetivo = $estadoActual->kmObjetivo;
        $kmVencido = max($vehiculo->km_actual + 500, $kmObjetivo + 250);

        $this->newLine();
        $this->line("4. Simulando nueva lectura de odómetro que vence el servicio: <fg=magenta>{$plan->nombre}</>");
        $this->line("   - Km Objetivo del plan: {$kmObjetivo} km");
        $this->line("   - Nuevo odómetro a registrar: <fg=red;options=bold>{$kmVencido} km (VENCIDO por " . ($kmVencido - $kmObjetivo) . " km)</>");

        // Limpiar registro previo de dedup para esta prueba
        DB::table('notificacion_enviada')
            ->where('patente', $vehiculo->patente)
            ->where('plan_id', $plan->id)
            ->where('km_objetivo', $kmObjetivo)
            ->delete();

        // Registrar la lectura en el vehículo
        DB::transaction(function () use ($vehiculo, $kmVencido) {
            LecturaOdometro::create([
                'patente' => $vehiculo->patente,
                'km' => $kmVencido,
                'fecha' => now(),
                'origen' => 'MANUAL',
                'observacion' => 'Simulación de prueba de vencimiento automatizado',
            ]);
            $vehiculo->update([
                'km_actual' => $kmVencido,
                'fecha_km' => now(),
            ]);
        });

        $this->info("✓ Odómetro actualizado exitosamente en base de datos.");

        // Evaluar mantención y disparar correo
        $this->newLine();
        $this->line("5. Evaluando reglas de negocio y despachando alerta...");

        $evaluador->paraVehiculo($vehiculo);

        // Procesar la cola inmediatamente para despachar por SMTP Gmail
        $estadoNuevo = $calculador->paraPlan($vehiculo, $plan);
        $ultimoServicio = \App\Models\EventoMantencion::where('patente', $vehiculo->patente)
            ->where('plan_id', $plan->id)
            ->first();

        Mail::to($emailDestino)->send(
            new \App\Mail\AvisoMantencionMail($vehiculo, $estadoNuevo, $ultimoServicio)
        );

        $this->newLine();
        $this->info('===========================================================');
        $this->info("   ¡CORREO DE ALERTA ENVIADO EXITOSAMENTE A {$emailDestino}!   ");
        $this->info('===========================================================');
        $this->line("Asunto: [{$vehiculo->patente}] {$plan->nombre} VENCIDO");
        $this->line("Revisa tu bandeja de entrada en Gmail ({$emailDestino}).");

        return 0;
    }
}
