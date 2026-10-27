<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaAgendada extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La cita llega por el constructor. Al ser una propiedad pública,
     * queda disponible en la vista como $cita.
     */
    public function __construct(public Cita $cita)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva cita médica agendada',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.cita-agendada',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}