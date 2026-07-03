<?php

namespace App\Mail;

readonly class ContactSubmissionMailRecipients
{
    /**
     * @param  list<string>  $to
     * @param  list<string>  $cc
     * @param  list<string>  $bcc
     */
    public function __construct(
        public array $to,
        public array $cc = [],
        public array $bcc = [],
        public ?string $messageId = null,
        public ?string $subject = null,
    ) {}
}
