<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionEnviada extends Model
{
    protected $table = 'notificacion_enviada';

    const CREATED_AT = 'enviada_en';

    const UPDATED_AT = null;

    protected $fillable = [
        'patente',
        'plan_id',
        'km_objetivo',
        'destinatario',
        'estado',
    ];

    protected $casts = [
        'plan_id' => 'integer',
        'km_objetivo' => 'integer',
        'enviada_en' => 'datetime',
    ];
}
