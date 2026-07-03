<?php

namespace App\Jobs;

use App\Models\ContactSubmission;
use App\Services\EspoCrm\EspoCrmContactIntakeService;
use App\Support\ContactDeskOptions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class LinkSportelloContactSubmissionToCrmJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 12;

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 60, 120, 180, 300, 600];
    }

    public function __construct(
        public int $submissionId,
    ) {}

    public function handle(EspoCrmContactIntakeService $intake): void
    {
        $submission = ContactSubmission::query()->find($this->submissionId);

        if ($submission === null || $submission->correlation_token === null) {
            return;
        }

        if ($submission->crm_link_status === 'linked') {
            return;
        }

        if (! $intake->isConfigured()) {
            $submission->forceFill(['crm_link_status' => 'skipped'])->save();

            return;
        }

        $caseType = ContactDeskOptions::caseTypeForDesk($submission->desk);

        if ($caseType === null) {
            $submission->forceFill(['crm_link_status' => 'failed'])->save();

            return;
        }

        try {
            $case = $intake->findCaseByCorrelationToken($submission->correlation_token);

            if ($case === null) {
                $this->release(60);

                return;
            }

            $caseId = is_string($case['id'] ?? null) ? $case['id'] : null;

            if ($caseId === null || $caseId === '') {
                throw new RuntimeException('EspoCRM Case search returned an invalid id.');
            }

            $email = $intake->findEmailForSubmission($submission);

            if ($email !== null && ! $intake->emailIsLinkedToCase($email, $caseId)) {
                Log::info('Sportello CRM linking: case found, waiting for email thread link', [
                    'submission_id' => $submission->id,
                    'case_id' => $caseId,
                ]);
            }

            if ($submission->crm_lead_id === null) {
                $lead = $intake->createLeadFromSubmission($submission);
                $leadId = is_string($lead['id'] ?? null) ? $lead['id'] : null;

                if ($leadId === null || $leadId === '') {
                    throw new RuntimeException('EspoCRM Lead create did not return an id.');
                }

                $submission->forceFill(['crm_lead_id' => $leadId])->save();
            }

            $leadId = (string) $submission->crm_lead_id;

            if ($leadId === '') {
                throw new RuntimeException('Missing Lead id for CRM linking.');
            }

            if (($case['parentType'] ?? null) !== 'Lead' || ($case['parentId'] ?? null) !== $leadId) {
                $intake->linkCaseToLead($caseId, $leadId, $caseType);
            }

            $submission->forceFill([
                'crm_case_id' => $caseId,
                'crm_link_status' => 'linked',
            ])->save();
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
