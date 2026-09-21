<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MembershipStaffMail extends Mailable
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload) {}

    public function envelope(): Envelope
    {
        $displayName = trim(($this->payload['name'] ?? '').' '.($this->payload['last_name'] ?? ''));

        return new Envelope(
            subject: 'Nuova domanda di ammissione socio',
            to: [new Address((string) config('membership.staff_inbox'))],
            replyTo: [new Address((string) $this->payload['email'], $displayName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.membership-staff',
        );
    }
}
