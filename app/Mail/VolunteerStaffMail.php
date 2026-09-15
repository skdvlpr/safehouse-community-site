<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VolunteerStaffMail extends Mailable
{
    public function __construct(
        public string $applicantName,
        public string $lastName,
        public string $applicantEmail,
        public string $phone,
        public string $bodyMessage,
    ) {}

    public function envelope(): Envelope
    {
        $displayName = trim($this->applicantName.' '.$this->lastName);

        return new Envelope(
            subject: 'Nuova candidatura volontario',
            to: [new Address((string) config('volunteer.staff_inbox'))],
            replyTo: [new Address($this->applicantEmail, $displayName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.volunteer-staff',
        );
    }
}
