<?php

namespace App\Services\EspoCrm;

use App\Support\IntegrationConfig;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Resolves which EspoCRM User id to set as assignedUserId on site-created records.
 */
class EspoCrmAssignedUserResolver
{
    public function resolveUsing(EspoCrmClient $client): ?string
    {
        $configured = trim(IntegrationConfig::string('espocrm.assigned_user_id'));
        if ($configured === '') {
            return null;
        }

        try {
            $client->userById($configured);

            return $configured;
        } catch (Throwable $exception) {
            Log::warning('Configured EspoCRM assignedUserId is not readable by the API user; falling back to API user.', [
                'configured_assigned_user_id' => $configured,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            return $client->apiUserId();
        } catch (Throwable $exception) {
            Log::warning('Could not resolve EspoCRM API user id for assignedUser fallback.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function resolveFromConfig(): ?string
    {
        $client = EspoCrmClient::tryFromConfig();

        if ($client === null) {
            return null;
        }

        return $this->resolveUsing($client);
    }
}
