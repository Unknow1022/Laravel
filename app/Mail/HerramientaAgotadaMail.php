<?php

namespace App\Mail;

use App\Models\Herramienta;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HerramientaAgotadaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param Herramienta  $herramienta  La herramienta con stock agotado / crítico
     * @param array        $trabajadores Lista de trabajadores que tienen unidades en préstamo activo
     * @param string       $tipoAlerta   'agotada' | 'critica'
     */
    public function __construct(
        public Herramienta $herramienta,
        public array $trabajadores = [],
        public string $tipoAlerta = 'agotada'
    ) {}

    public function envelope(): Envelope
    {
        $emoji = $this->tipoAlerta === 'agotada' ? '🔴' : '🟡';
        $tipo  = $this->tipoAlerta === 'agotada' ? 'AGOTADA' : 'STOCK CRÍTICO';

        return new Envelope(
            subject: "{$emoji} ALERTA: Herramienta {$tipo} · {$this->herramienta->nombre} | CORTEX NOC",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.herramienta_agotada',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
