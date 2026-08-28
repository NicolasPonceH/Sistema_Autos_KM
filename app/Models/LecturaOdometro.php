<?php

namespace App\Models;

use App\Enums\OrigenLectura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturaOdometro extends Model
{
    protected $table = 'lectura_odometro';

    /**
     * 'fecha' es el CREATED_AT de Eloquent: Eloquent lo autogestiona y
     * sobrescribe con la hora actual en cada create(), aunque se lo pase
     * explícitamente en el array. Para backfill/import con fecha real, usar
     * LecturaOdometro::withoutTimestamps(fn () => ...) o un insert directo.
     */
    const CREATED_AT = 'fecha';

    const UPDATED_AT = null;

    protected $fillable = [
        'patente',
        'km',
        'reportado_por',
        'origen',
        'anulada',
        'observacion',
    ];

    protected $casts = [
        'km' => 'integer',
        'fecha' => 'datetime',
        'origen' => OrigenLectura::class,
        'anulada' => 'boolean',
    ];

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'patente', 'patente');
    }

    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por');
    }
}
