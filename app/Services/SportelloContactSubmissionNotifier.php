<?php

namespace App\Services;

use App\Jobs\LinkSportelloContactSubmissionToCrmJob;
use App\Mail\ContactSubmissionMail;
use App\Mail\ContactSubmissionMailRecipients;
use App\Models\ContactSubmission;
use App\Services\EspoCrm\LinkSportelloContactSubmissionService;
use App\Support\ContactDeskOptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SportelloContactSubmissionNotifier
{
    public function __construct(
        private readonly OutboundMailConfigurator $mail,
        private readonly LinkSportelloContactSubmissionService $crmLinker,
    ) {}

    public function notify(ContactSubmission $submission): void
    {
        $desk = ContactDeskOptions::deskConfig($submission->desk);

        if ($desk === null) {
            Log::warning('Sportello contact submission skipped: unknown desk', [
                'submission_id' => $submission->id,
                'desk' => $submission->desk,
            ]);

            return;
        }

        $inbox = strtolower(trim((string) ($desk['inbox'] ?? '')));

        if ($inbox === '' || filter_var($inbox, FILTER_VALIDATE_EMAIL) === false) {
            Log::warning('Sportello contact submission skipped: inbox not configured', [
                'submission_id' => $submission->id,
                'desk' => $submission->desk,
            ]);

            return;
        }

        if (! $this->mail->canSendSportelloNotifications()) {
            Log::warning('Sportello contact submission skipped: SMTP not configured', [
                'submission_id' => $submission->id,
                'desk' => $submission->desk,
            ]);

            return;
        }

        $token = Str::lower(Str::uuid()->toString());
        $domain = (string) config('contact_mail.correlation_domain', 'safehouse.community');
        $messageId = "contact-{$token}@{$domain}";

        $submission->forceFill([
            'correlation_token' => $token,
            'outbound_message_id' => $messageId,
            'crm_link_status' => 'pending',
        ])->save();

        $this->mail->applyForSportello();

        $locale = app()->getLocale();
        $rendered = app(ContactSubmissionMailRenderer::class)->render($submission, $locale);
        $subject = $rendered['subject'];

        try {
            Mail::send(new ContactSubmissionMail(
                $submission,
                new ContactSubmissionMailRecipients(
                    to: [$inbox],
                    cc: [$submission->email],
                    messageId: $messageId,
                    subject: $subject,
                ),
                rendered: $rendered,
            ));
        } catch (Throwable $exception) {
            $submission->forceFill(['crm_link_status' => 'failed'])->save();

            Log::warning('Sportello contact submission email failed', [
                'submission_id' => $submission->id,
                'desk' => $submission->desk,
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        try {
            $this->crmLinker->ensureLead($submission->fresh());
        } catch (Throwable $exception) {
            Log::warning('Sportello CRM lead was not created immediately; queued linking will retry', [
                'submission_id' => $submission->id,
                'error' => $exception->getMessage(),
            ]);
        }

        LinkSportelloContactSubmissionToCrmJob::dispatch($submission->id)
            ->delay(now()->addSeconds(90));
    }
}
