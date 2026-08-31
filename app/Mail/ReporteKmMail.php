<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReporteKmMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $filas,
        public Carbon $mesInicio,
        public Carbon $mesFin,
        public ?string $mensajeAdicional = null,
    ) {}

    public function envelope(): Envelope
    {
        $asunto = sprintf(
            '[PDI] Informe Oficial de Kilometraje y Flota (%s - %s)',
            $this->mesInicio->translatedFormat('M Y'),
            $this->mesFin->translatedFormat('M Y')
        );

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reporte-km',
            with: [
                'filas' => $this->filas,
                'mesInicio' => $this->mesInicio,
                'mesFin' => $this->mesFin,
                'mensajeAdicional' => $this->mensajeAdicional,
                'granTotal' => $this->filas->sum(fn ($f) => $f['meses']->sum('kmRecorridos')),
            ],
        );
    }

    public function attachments(): array
    {
        $contenidoExcel = view('reportes.excel', [
            'filas' => $this->filas,
            'mesInicio' => $this->mesInicio,
            'mesFin' => $this->mesFin,
        ])->render();

        $nombreArchivo = sprintf(
            'INFORME_PDI_KM_%s_a_%s.xls',
            $this->mesInicio->format('Y-m'),
            $this->mesFin->format('Y-m')
        );

        return [
            Attachment::fromData(fn () => $contenidoExcel, $nombreArchivo)
                ->withMime('application/vnd.ms-excel'),
        ];
    }
}
