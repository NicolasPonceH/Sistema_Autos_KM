<?php

namespace App\Models;

use App\Rules\PatenteChilena;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    protected $table = 'vehiculo';

    protected $primaryKey = 'patente';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = null;

    protected $fillable = [
        'patente',
        'tipo_codigo',
        'marca',
        'modelo',
        'anio',
        'nro_motor',
        'nro_chasis',
        'km_actual',
        'fecha_km',
        'email_contacto',
        'activo',
    ];

    protected $casts = [
        'anio' => 'integer',
        'km_actual' => 'integer',
        'fecha_km' => 'datetime',
        'activo' => 'boolean',
        'creado_en' => 'datetime',
    ];

    /**
     * Regla 7: normalizar a mayúsculas, sin puntos ni guiones antes de persistir.
     * El formato (LLLL·NN, LL·NNNN, LLL·NNN) se valida en la capa de request,
     * no aquí — esto solo garantiza cómo queda guardado el valor.
     */
    public function setPatenteAttribute(string $value): void
    {
        $this->attributes['patente'] = PatenteChilena::normalizar($value);
    }

    public function tipoVehiculo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_codigo', 'codigo');
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(LecturaOdometro::class, 'patente', 'patente');
    }

    public function eventosMantencion(): HasMany
    {
        return $this->hasMany(EventoMantencion::class, 'patente', 'patente');
    }
}
