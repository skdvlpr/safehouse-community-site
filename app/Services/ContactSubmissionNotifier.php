<?php

namespace App\Services;

use App\Mail\ContactSubmissionMail;
use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactSubmissionNotifier
{
    public function __construct(
        private readonly OutboundMailConfigurator $mail,
    ) {}

    public function notify(ContactSubmission $submission): void
    {
        if (! $this->mail->canSendContactNotifications()) {
            return;
        }

        $this->mail->apply();

        try {
            Mail::to($this->mail->contactRecipient())
                ->send(new ContactSubmissionMail($submission));
        } catch (Throwable $exception) {
            Log::warning('Contact form notification email failed', [
                'submission_id' => $submission->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
