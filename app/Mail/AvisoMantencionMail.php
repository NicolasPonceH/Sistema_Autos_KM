<?php

namespace App\Mail;

use App\Models\EventoMantencion;
use App\Models\Vehiculo;
use App\ValueObjects\EstadoMantencionPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AvisoMantencionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Vehiculo $vehiculo,
        public EstadoMantencionPlan $estado,
        public ?EventoMantencion $ultimoServicio,
    ) {}

    public function envelope(): Envelope
    {
        $patente = $this->vehiculo->patente;
        $nombrePlan = $this->estado->plan->nombre;
        $porKm = $this->estado->ejeQueManda() === 'km';

        $cantidad = $porKm
            ? number_format(abs($this->estado->kmFaltantes), 0, ',', '.').' km'
            : number_format(abs($this->estado->diasFaltantes), 0, ',', '.').' días';

        $asunto = $this->estado->vencido()
            ? sprintf('[%s] %s VENCIDO — %s de atraso', $patente, $nombrePlan, $cantidad)
            : sprintf('[%s] %s próximo — faltan %s', $patente, $nombrePlan, $cantidad);

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aviso-mantencion',
            with: [
                'vehiculo' => $this->vehiculo,
                'estado' => $this->estado,
                'ultimoServicio' => $this->ultimoServicio,
            ],
        );
    }
}
