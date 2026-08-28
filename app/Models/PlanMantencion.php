<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanMantencion extends Model
{
    protected $table = 'plan_mantencion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'intervalo_km',
        'umbral_aviso',
        'intervalo_meses',
        'umbral_aviso_dias',
        'tipo_codigo',
    ];

    protected $casts = [
        'intervalo_km' => 'integer',
        'umbral_aviso' => 'integer',
        'intervalo_meses' => 'integer',
        'umbral_aviso_dias' => 'integer',
    ];

    public function tipoVehiculo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_codigo', 'codigo');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(EventoMantencion::class, 'plan_id');
    }
}
