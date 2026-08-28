<?php

namespace App\Console\Commands;

use App\Actions\EvaluarAvisosMantencion;
use App\Models\Vehiculo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mantencion:evaluar-avisos')]
#[Description('Recorre los vehículos activos y encola los avisos de mantención pendientes (job diario, sección 4 del plan)')]
class EvaluarAvisosMantencionCommand extends Command
{
    public function handle(EvaluarAvisosMantencion $evaluarAvisos): int
    {
        $vehiculos = Vehiculo::where('activo', true)->get();

        $this->withProgressBar($vehiculos, fn (Vehiculo $vehiculo) => $evaluarAvisos->paraVehiculo($vehiculo));

        $this->newLine(2);
        $this->info("Evaluados {$vehiculos->count()} vehículos activos.");

        return self::SUCCESS;
    }
}
