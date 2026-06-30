<?php

namespace App\Services\EspoCrm;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class EspoCrmFinanziamentoService
{
    public function __construct(
        private readonly EspoCrmClient $client,
    ) {}

    /**
     * Find Opportunity by exact name, or create it for a donation campaign.
     */
    public function ensureExists(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new RuntimeException('Finanziamento name is empty.');
        }

        $existingId = $this->findIdByName($name);
        if ($existingId !== null) {
            return $existingId;
        }

        $createdId = $this->create($name);

        Log::info('EspoCRM Finanziamento created for donation campaign.', [
            'name' => $name,
            'financing_id' => $createdId,
        ]);

        return $createdId;
    }

    public function findIdByName(string $name): ?string
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $entity = (string) config('espocrm.finanziamento.entity');

        $existing = $this->client->search($entity, [
            'select' => 'id,name,stage',
            'maxSize' => 5,
            'where' => [
                [
                    'type' => 'equals',
                    'attribute' => 'name',
                    'value' => $name,
                ],
            ],
        ]);

        $matches = $existing['list'] ?? [];
        $count = is_array($matches) ? count($matches) : 0;

        if ($count === 0) {
            return null;
        }

        if ($count > 1) {
            throw new RuntimeException(
                "Multiple EspoCRM Finanziamenti match name \"{$name}\"."
            );
        }

        $existingId = $matches[0]['id'] ?? null;

        return is_string($existingId) && $existingId !== '' ? $existingId : null;
    }

    private function create(string $name): string
    {
        $entity = (string) config('espocrm.finanziamento.entity');

        $payload = [
            'name' => $name,
            'stage' => (string) config('espocrm.finanziamento.default_stage'),
            'closeDate' => (string) config('espocrm.finanziamento.default_close_date'),
            'probability' => (int) config('espocrm.finanziamento.default_probability'),
        ];

        $assignedUserId = (string) config('espocrm.assigned_user_id', '');
        if ($assignedUserId !== '') {
            $payload['assignedUserId'] = $assignedUserId;
        }

        $result = $this->client->create($entity, $payload);
        $id = $result['id'] ?? null;

        if (! is_string($id) || $id === '') {
            throw new RuntimeException('EspoCRM Finanziamento create returned an invalid id.');
        }

        return $id;
    }
}
