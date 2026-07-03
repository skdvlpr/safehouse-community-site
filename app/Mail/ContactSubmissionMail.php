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

    /**
     * @param  array{html?: string, text?: string, subject?: string}|null  $rendered
     */
    public function __construct(
        public ContactSubmission $submission,
        public ?ContactSubmissionMailRecipients $recipients = null,
        public ?array $rendered = null,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = filter_var($this->submission->email, FILTER_VALIDATE_EMAIL);
        $recipients = $this->recipients ?? new ContactSubmissionMailRecipients(to: []);

        return new Envelope(
            subject: $recipients->subject
                ?? $this->rendered['subject']
                ?? ('Nuovo messaggio dal modulo contatti — '.$this->submission->name),
            to: $this->addresses($recipients->to),
            cc: $this->addresses($recipients->cc),
            bcc: $this->addresses($recipients->bcc),
            replyTo: $replyTo !== false
                ? [new Address($replyTo, $this->submission->name)]
                : [],
            using: [
                function ($message) use ($recipients): void {
                    if ($recipients->messageId === null) {
                        return;
                    }

                    $message->getHeaders()->addIdHeader('Message-ID', $recipients->messageId);
                },
            ],
        );
    }

    public function content(): Content
    {
        if ($this->rendered !== null) {
            return new Content(
                htmlString: $this->rendered['html'] ?? '',
                text: 'mail.contact-submission-text',
                with: [
                    'textBody' => $this->rendered['text'] ?? '',
                ],
            );
        }

        return new Content(
            text: 'mail.contact-submission',
        );
    }

    /**
     * @param  list<string>  $emails
     * @return list<Address>
     */
    private function addresses(array $emails): array
    {
        $addresses = [];

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            $addresses[] = new Address($email);
        }

        return $addresses;
    }
}
