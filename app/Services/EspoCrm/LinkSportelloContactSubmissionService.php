<?php

namespace App\Services\EspoCrm;

use App\Models\ContactSubmission;
use App\Support\ContactDeskOptions;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class LinkSportelloContactSubmissionService
{
    public function __construct(
        private readonly EspoCrmContactIntakeService $intake,
    ) {}

    public function ensureLead(ContactSubmission $submission): ?string
    {
        if (! $this->intake->isConfigured()) {
            return null;
        }

        if ($submission->crm_lead_id !== null && $submission->crm_lead_id !== '') {
            return (string) $submission->crm_lead_id;
        }

        try {
            $lead = $this->intake->createLeadFromSubmission($submission);
            $leadId = is_string($lead['id'] ?? null) ? $lead['id'] : null;

            if ($leadId === null || $leadId === '') {
                throw new RuntimeException('EspoCRM Lead create did not return an id.');
            }

            $submission->forceFill(['crm_lead_id' => $leadId])->save();

            return $leadId;
        } catch (Throwable $exception) {
            Log::warning('Sportello CRM lead create failed', [
                'submission_id' => $submission->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return 'linked'|'waiting'|'skipped'|'failed'
     */
    public function link(ContactSubmission $submission): string
    {
        if ($submission->correlation_token === null) {
            return 'failed';
        }

        if ($submission->crm_link_status === 'linked') {
            return 'linked';
        }

        if (! $this->intake->isConfigured()) {
            $submission->forceFill(['crm_link_status' => 'skipped'])->save();

            return 'skipped';
        }

        $caseType = ContactDeskOptions::caseTypeForDesk($submission->desk);

        if ($caseType === null) {
            $submission->forceFill(['crm_link_status' => 'failed'])->save();

            return 'failed';
        }

        $leadId = $this->ensureLead($submission);

        if ($leadId === null || $leadId === '') {
            $submission->forceFill(['crm_link_status' => 'failed'])->save();

            return 'failed';
        }

        $case = $this->intake->findCaseByCorrelationToken($submission->correlation_token);

        if ($case === null) {
            return 'waiting';
        }

        $caseId = is_string($case['id'] ?? null) ? $case['id'] : null;

        if ($caseId === null || $caseId === '') {
            throw new RuntimeException('EspoCRM Case search returned an invalid id.');
        }

        if (($case['parentType'] ?? null) !== 'Lead' || ($case['parentId'] ?? null) !== $leadId) {
            $this->intake->linkCaseToLead($caseId, $leadId, $caseType, $submission);
        } else {
            $this->intake->syncCaseMetadata($caseId, $caseType, $submission);
        }

        $submission->forceFill([
            'crm_case_id' => $caseId,
            'crm_link_status' => 'linked',
        ])->save();

        return 'linked';
    }
}
