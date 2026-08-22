<?php

namespace App\Services;

use App\Models\ContactSubmission;

class ContactSubmissionNotifier
{
    public function __construct(
        private readonly SportelloContactSubmissionNotifier $sportello,
    ) {}

    public function notify(ContactSubmission $submission): void
    {
        $this->sportello->notify($submission);
    }
}
