<?php

namespace App\Actions;

use App\Models\EventoMantencion;
use App\Models\PlanMantencion;
use App\Models\Vehiculo;
use App\ValueObjects\EstadoMantencionPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CalcularEstadoMantencion
{
    /**
     * @return Collection<int, EstadoMantencionPlan>
     */
    public function paraVehiculo(Vehiculo $vehiculo): Collection
    {
        return $this->planesAplicables($vehiculo)
            ->map(fn (PlanMantencion $plan) => $this->paraPlan($vehiculo, $plan));
    }

    public function paraPlan(Vehiculo $vehiculo, PlanMantencion $plan): EstadoMantencionPlan
    {
        $ultimoEvento = EventoMantencion::query()
            ->where('patente', $vehiculo->patente)
            ->where('plan_id', $plan->id)
            ->orderByDesc('km_evento')
            ->first();

        $kmUltimoServicio = $ultimoEvento->km_evento ?? 0;
        $kmObjetivo = $kmUltimoServicio + $plan->intervalo_km;
        $kmFaltantes = $kmObjetivo - $vehiculo->km_actual;

        [$fechaUltimoServicio, $fechaObjetivo, $diasFaltantes] = $this->calcularEjeTiempo($vehiculo, $plan, $ultimoEvento);

        return new EstadoMantencionPlan(
            plan: $plan,
            kmUltimoServicio: $kmUltimoServicio,
            kmObjetivo: $kmObjetivo,
            kmFaltantes: $kmFaltantes,
            fechaUltimoServicio: $fechaUltimoServicio,
            fechaObjetivo: $fechaObjetivo,
            diasFaltantes: $diasFaltantes,
        );
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: ?int}
     */
    private function calcularEjeTiempo(Vehiculo $vehiculo, PlanMantencion $plan, ?EventoMantencion $ultimoEvento): array
    {
        if ($plan->intervalo_meses === null) {
            return [null, null, null];
        }

        // Sin servicio previo, la base es el alta del vehículo — el
        // equivalente temporal de "0 km" cuando nunca se hizo el servicio.
        $fechaUltimoServicio = $ultimoEvento?->fecha ?? $vehiculo->creado_en;
        $fechaObjetivo = $fechaUltimoServicio->copy()->addMonthsNoOverflow($plan->intervalo_meses);
        $diasFaltantes = (int) Carbon::now()->startOfDay()->diffInDays($fechaObjetivo->copy()->startOfDay(), false);

        return [$fechaUltimoServicio, $fechaObjetivo, $diasFaltantes];
    }

    /**
     * Planes globales (tipo_codigo NULL) o específicos del tipo de este vehículo.
     *
     * @return Collection<int, PlanMantencion>
     */
    private function planesAplicables(Vehiculo $vehiculo): Collection
    {
        return PlanMantencion::query()
            ->where(function ($query) use ($vehiculo) {
                $query->whereNull('tipo_codigo')->orWhere('tipo_codigo', $vehiculo->tipo_codigo);
            })
            ->orderBy('nombre')
            ->get();
    }
}
