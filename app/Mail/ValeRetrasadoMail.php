<?php

namespace App\Mail;

use App\Models\Vale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ValeRetrasadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Vale $vale)
    {
    }

    public function envelope(): Envelope
    {
        $trabajador = $this->vale->trabajador;
        $nombre = $trabajador ? $trabajador->nombre . ' ' . $trabajador->apellidos : 'Trabajador desconocido';

        return new Envelope(
            subject: '⚠️ ALERTA: Vale Retrasado - ' . $this->vale->codigo_vale . ' | ' . $nombre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vale_retrasado',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
