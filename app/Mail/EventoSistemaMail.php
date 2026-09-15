<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventoSistemaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $accion,
        public string $tabla,
        public string $descripcion,
        public ?string $usuario = null,
        public ?string $fecha   = null,
        public ?int    $itemId  = null
    ) {}

    public function envelope(): Envelope
    {
        $emoji = match(true) {
            str_contains(strtolower($this->accion), 'elimin') ||
            str_contains(strtolower($this->accion), 'borr')   => '🗑️',
            str_contains(strtolower($this->accion), 'cre')     => '✅',
            str_contains(strtolower($this->accion), 'actu') ||
            str_contains(strtolower($this->accion), 'edit') ||
            str_contains(strtolower($this->accion), 'modif')   => '✏️',
            str_contains(strtolower($this->accion), 'login')    => '🔐',
            str_contains(strtolower($this->accion), 'logout')   => '🚪',
            str_contains(strtolower($this->accion), 'vale')     => '📋',
            default => '🔔',
        };

        $tabla_display = ucfirst(str_replace('_', ' ', $this->tabla));
        return new Envelope(
            subject: "{$emoji} [{$tabla_display}] {$this->accion} — Cortex NOC",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.evento_sistema');
    }

    public function attachments(): array { return []; }
}
