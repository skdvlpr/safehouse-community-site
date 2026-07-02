<?php

namespace App\Services\EspoCrm;

use App\Support\IntegrationConfig;
use RuntimeException;
use Throwable;

class EspoCrmVerifier
{
    public function __construct(
        private readonly EspoCrmClient $client,
    ) {}

    /**
     * @return list<array{label: string, status: string, detail: string}>
     */
    public function runChecks(): array
    {
        $checks = [];

        $baseUrl = IntegrationConfig::string('espocrm.base_url');
        $apiKey = IntegrationConfig::string('espocrm.api_key');
        $assignedUserId = IntegrationConfig::string('espocrm.assigned_user_id');

        $checks[] = $this->checkPresent('ESPOCRM base URL', $baseUrl);
        $checks[] = $this->checkPresent('ESPOCRM API key', $apiKey);

        if ($baseUrl === '' || $apiKey === '') {
            return $checks;
        }

        try {
            $user = $this->client->appUser();
        } catch (Throwable $exception) {
            $checks[] = $this->fail('API connection', $exception->getMessage());

            return $checks;
        }

        $apiUserId = $this->client->apiUserId();
        $apiUserName = is_string($user['user']['userName'] ?? $user['userName'] ?? null)
            ? ($user['user']['userName'] ?? $user['userName'])
            : 'unknown';

        $checks[] = $this->pass('API connection', "user {$apiUserName} ({$apiUserId})");

        if ($assignedUserId === '') {
            $checks[] = $this->warn(
                'Assigned user id',
                'Not set — Prima Nota will use CRM defaults.',
            );
        } elseif ($assignedUserId === $apiUserId) {
            $checks[] = $this->pass('Assigned user id', "{$assignedUserId} (same as API user)");
        } else {
            $checks[] = $this->pass(
                'Assigned user id',
                "{$assignedUserId} — Prima Nota will be assigned to this user, not the API user.",
            );

            try {
                $assignee = $this->client->userById($assignedUserId);
                $assigneeName = is_string($assignee['name'] ?? null) ? $assignee['name'] : $assignedUserId;
                $checks[] = $this->pass('Assignee lookup', "Found {$assigneeName}");
            } catch (Throwable $exception) {
                $checks[] = $this->fail(
                    'Assignee lookup',
                    "Cannot read user {$assignedUserId}. Fix the id in CMS or grant the API user access. {$exception->getMessage()}",
                );
            }
        }

        try {
            $this->client->search('PrimaNota', [
                'select' => 'id',
                'maxSize' => 1,
            ]);
            $checks[] = $this->pass('PrimaNota read', 'API user can list Prima Nota');
        } catch (Throwable $exception) {
            $checks[] = $this->fail('PrimaNota read', $exception->getMessage());
        }

        return $checks;
    }

    /**
     * @param  list<array{label: string, status: string, detail: string}>  $checks
     */
    public function allPassed(array $checks): bool
    {
        foreach ($checks as $check) {
            if ($check['status'] === 'fail') {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{label: string, status: string, detail: string}
     */
    private function checkPresent(string $label, string $value): array
    {
        return $value !== ''
            ? $this->pass($label, 'Set')
            : $this->fail($label, 'Missing — set in CMS → Integrations or .env');
    }

    /**
     * @return array{label: string, status: string, detail: string}
     */
    private function pass(string $label, string $detail): array
    {
        return ['label' => $label, 'status' => 'pass', 'detail' => $detail];
    }

    /**
     * @return array{label: string, status: string, detail: string}
     */
    private function warn(string $label, string $detail): array
    {
        return ['label' => $label, 'status' => 'warn', 'detail' => $detail];
    }

    /**
     * @return array{label: string, status: string, detail: string}
     */
    private function fail(string $label, string $detail): array
    {
        return ['label' => $label, 'status' => 'fail', 'detail' => $detail];
    }
}
