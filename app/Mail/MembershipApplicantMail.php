<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MembershipApplicantMail extends Mailable
{
    public function __construct(
        public string $applicantEmail,
        public string $mailLocale,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('site.membership.mail.applicant_subject', [], $this->mailLocale),
            to: [new Address($this->applicantEmail)],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.membership-applicant',
        );
    }
}
