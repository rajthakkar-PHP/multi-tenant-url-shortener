<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invitation $invitation,
        public string $acceptanceToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited to Company URL Shortener',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'acceptanceUrl' => route('invitations.accept', $this->acceptanceToken),
            ],
        );
    }
}
