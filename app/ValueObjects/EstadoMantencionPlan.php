<?php

namespace App\ValueObjects;

use App\Models\PlanMantencion;
use Carbon\CarbonInterface;

final readonly class EstadoMantencionPlan
{
    public function __construct(
        public PlanMantencion $plan,
        public int $kmUltimoServicio,
        public int $kmObjetivo,
        public int $kmFaltantes,
        public ?CarbonInterface $fechaUltimoServicio = null,
        public ?CarbonInterface $fechaObjetivo = null,
        public ?int $diasFaltantes = null,
    ) {}

    /**
     * Regla 4: comparación con `<=`, nunca un rango cerrado — un salto de
     * 9.800 a 10.400 km debe caer acá igual, aunque nunca haya pasado por
     * la ventana "normal" de aviso.
     */
    public function enVentanaAvisoPorKm(): bool
    {
        return $this->kmFaltantes <= $this->plan->umbral_aviso;
    }

    /**
     * Mismo principio que enVentanaAvisoPorKm(), para el eje temporal.
     */
    public function enVentanaAvisoPorTiempo(): bool
    {
        return $this->tieneCriterioTiempo()
            && $this->plan->umbral_aviso_dias !== null
            && $this->diasFaltantes <= $this->plan->umbral_aviso_dias;
    }

    /**
     * "Lo que ocurra primero": basta que un eje entre en ventana.
     */
    public function enVentanaAviso(): bool
    {
        return $this->enVentanaAvisoPorKm() || $this->enVentanaAvisoPorTiempo();
    }

    public function vencidoPorKm(): bool
    {
        return $this->kmFaltantes < 0;
    }

    public function vencidoPorTiempo(): bool
    {
        return $this->tieneCriterioTiempo() && $this->diasFaltantes < 0;
    }

    public function vencido(): bool
    {
        return $this->vencidoPorKm() || $this->vencidoPorTiempo();
    }

    public function tieneCriterioTiempo(): bool
    {
        return $this->diasFaltantes !== null;
    }

    /**
     * Descripción del eje km. Sin cambios de comportamiento respecto de
     * antes de Fase 5 — el eje tiempo se expone aparte en
     * descripcionTiempo() para no romper nada que ya dependa de este texto.
     */
    public function descripcion(): string
    {
        return $this->vencidoPorKm()
            ? sprintf('Vencido hace %s km', number_format(abs($this->kmFaltantes), 0, ',', '.'))
            : sprintf('Faltan %s km', number_format($this->kmFaltantes, 0, ',', '.'));
    }

    /**
     * null si el plan no tiene criterio de tiempo (intervalo_meses).
     */
    public function descripcionTiempo(): ?string
    {
        if (! $this->tieneCriterioTiempo()) {
            return null;
        }

        return $this->vencidoPorTiempo()
            ? sprintf('Vencido hace %s días', number_format(abs($this->diasFaltantes), 0, ',', '.'))
            : sprintf('Faltan %s días', number_format($this->diasFaltantes, 0, ',', '.'));
    }

    /**
     * Qué eje explica el estado actual (para asuntos de correo, etc.).
     * Prioriza km cuando ambos aplican — mantiene el comportamiento previo
     * a Fase 5 para planes sin criterio de tiempo.
     */
    public function ejeQueManda(): string
    {
        if ($this->vencidoPorKm() || (! $this->vencidoPorTiempo() && $this->enVentanaAvisoPorKm())) {
            return 'km';
        }

        return 'tiempo';
    }
}
