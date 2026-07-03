<?php

namespace App\Services\EspoCrm;

use App\Models\ContactSubmission;
use App\Support\ContactDeskOptions;
use App\Support\IntegrationConfig;
use RuntimeException;

class EspoCrmContactIntakeService
{
    public function __construct(
        private readonly EspoCrmClient $client,
    ) {}

    public static function fromConfig(): self
    {
        return new self(EspoCrmClient::fromConfig());
    }

    public function isConfigured(): bool
    {
        try {
            EspoCrmClient::fromConfig();

            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findCaseByCorrelationToken(string $token): ?array
    {
        $needle = '[SH-'.$token.']';

        $response = $this->client->search('Case', [
            'where' => [
                [
                    'type' => 'or',
                    'value' => [
                        [
                            'type' => 'contains',
                            'attribute' => 'description',
                            'value' => $needle,
                        ],
                        [
                            'type' => 'contains',
                            'attribute' => 'name',
                            'value' => $needle,
                        ],
                    ],
                ],
            ],
            'maxSize' => 1,
            'orderBy' => 'createdAt',
            'order' => 'desc',
            'select' => 'id,name,description,parentType,parentId,type',
        ]);

        $row = $response['list'][0] ?? null;

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findEmailForSubmission(ContactSubmission $submission): ?array
    {
        $messageId = trim((string) $submission->outbound_message_id);
        $token = trim((string) $submission->correlation_token);

        if ($messageId !== '') {
            $byMessageId = $this->searchEmail([
                [
                    'type' => 'contains',
                    'attribute' => 'messageId',
                    'value' => $messageId,
                ],
            ]);

            if ($byMessageId !== null) {
                return $byMessageId;
            }
        }

        if ($token === '') {
            return null;
        }

        return $this->searchEmail([
            [
                'type' => 'contains',
                'attribute' => 'body',
                'value' => '[SH-'.$token.']',
            ],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $where
     * @return array<string, mixed>|null
     */
    private function searchEmail(array $where): ?array
    {
        $response = $this->client->search('Email', [
            'where' => $where,
            'maxSize' => 1,
            'orderBy' => 'createdAt',
            'order' => 'desc',
            'select' => 'id,messageId,parentType,parentId,name',
        ]);

        $row = $response['list'][0] ?? null;

        return is_array($row) ? $row : null;
    }

    /**
     * @param  array<string, mixed>  $email
     */
    public function emailIsLinkedToCase(array $email, string $caseId): bool
    {
        return ($email['parentType'] ?? null) === 'Case'
            && ($email['parentId'] ?? null) === $caseId;
    }

    /**
     * @return array<string, mixed>
     */
    public function createLeadFromSubmission(ContactSubmission $submission): array
    {
        [$firstName, $lastName] = $this->splitName($submission->name);
        $token = (string) $submission->correlation_token;
        $deskLabel = (string) (ContactDeskOptions::deskConfig($submission->desk)['label'] ?? $submission->desk);

        $payload = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'emailAddress' => $submission->email,
            'source' => 'Web Site',
            'status' => 'New',
            'description' => implode("\n\n", array_filter([
                'Richiesta dal modulo contatti del sito.',
                'Sportello: '.$deskLabel,
                'Token: [SH-'.$token.']',
                'Messaggio:',
                $submission->message,
            ])),
        ];

        $assignedUserId = IntegrationConfig::string('espocrm.assigned_user_id');

        if ($assignedUserId !== '') {
            $payload['assignedUserId'] = $assignedUserId;
        }

        return $this->client->create('Lead', $payload);
    }

    public function linkCaseToLead(string $caseId, string $leadId, string $caseType): void
    {
        $this->client->update('Case', $caseId, [
            'parentType' => 'Lead',
            'parentId' => $leadId,
            'type' => $caseType,
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            return ['Sconosciuto', ''];
        }

        $parts = preg_split('/\s+/', $name, 2) ?: [];

        return [
            $parts[0] ?? $name,
            $parts[1] ?? '',
        ];
    }
}
