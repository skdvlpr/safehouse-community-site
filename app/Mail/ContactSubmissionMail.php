<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactSubmission $submission,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = filter_var($this->submission->email, FILTER_VALIDATE_EMAIL);

        return new Envelope(
            subject: 'Nuovo messaggio dal modulo contatti — '.$this->submission->name,
            replyTo: $replyTo !== false
                ? [new Address($replyTo, $this->submission->name)]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.contact-submission',
        );
    }
}
