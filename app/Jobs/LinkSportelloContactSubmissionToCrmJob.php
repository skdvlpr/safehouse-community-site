<?php

namespace App\Jobs;

use App\Models\ContactSubmission;
use App\Services\EspoCrm\LinkSportelloContactSubmissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class LinkSportelloContactSubmissionToCrmJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 24;

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 45, 60, 90, 120, 180, 300, 600];
    }

    public function __construct(
        public int $submissionId,
    ) {}

    public function handle(LinkSportelloContactSubmissionService $linker): void
    {
        $submission = ContactSubmission::query()->find($this->submissionId);

        if ($submission === null) {
            return;
        }

        try {
            $result = $linker->link($submission);

            if ($result === 'waiting') {
                $this->release(now()->addSeconds(60));

                return;
            }
        } catch (Throwable $exception) {
            Log::warning('Sportello CRM linking failed', [
                'submission_id' => $submission->id,
                'attempt' => $this->attempts(),
                'error' => $exception->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $submission->forceFill(['crm_link_status' => 'failed'])->save();
            }

            throw $exception;
        }
    }
}
