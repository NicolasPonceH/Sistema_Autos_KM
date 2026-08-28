<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoMantencion extends Model
{
    protected $table = 'evento_mantencion';

    public $timestamps = false;

    protected $fillable = [
        'patente',
        'plan_id',
        'km_evento',
        'fecha',
        'costo',
        'taller',
        'notas',
    ];

    protected $casts = [
        'km_evento' => 'integer',
        'fecha' => 'date',
        'costo' => 'decimal:0',
    ];

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'patente', 'patente');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanMantencion::class, 'plan_id');
    }
}
