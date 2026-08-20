<?php

namespace App\Services\EspoCrm;

use App\Support\IntegrationConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class EspoCrmAssignedUserOptions
{
    private const CACHE_KEY = 'espocrm:assigned.user.options';

    private const CACHE_SECONDS = 300;

    /**
     * Options for Filament Select: value => label (no empty key — use nullable + placeholder).
     *
     * @return array<string, string>
     */
    public function optionsForSelect(): array
    {
        try {
            /** @var array<string, string> $cached */
            $cached = Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, function (): array {
                return $this->buildOptions();
            });

            return $this->withCurrentValue($cached);
        } catch (Throwable $exception) {
            Log::warning('EspoCRM assigned user options cache failed', [
                'error' => $exception->getMessage(),
            ]);

            return $this->withCurrentValue($this->buildOptions());
        }
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, string>
     */
    private function buildOptions(): array
    {
        $options = [];
        $client = EspoCrmClient::tryFromConfig();

        if ($client === null) {
            return $options;
        }

        $apiUserId = null;

        try {
            $appUser = $client->appUser();
            $user = is_array($appUser['user'] ?? null) ? $appUser['user'] : $appUser;
            $apiUserId = is_string($user['id'] ?? null) ? $user['id'] : null;
            $apiName = trim((string) ($user['name'] ?? $user['userName'] ?? 'API'));

            if (is_string($apiUserId) && $apiUserId !== '') {
                $options[$apiUserId] = __('cms.fields.assigned_user_site_api', ['name' => $apiName]);
            }
        } catch (Throwable $exception) {
            Log::warning('EspoCRM App/user failed while building assigned user options', [
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            $response = $client->search('User', [
                'select' => 'id,name,userName,isActive',
                'maxSize' => 200,
                'orderBy' => 'userName',
                'order' => 'asc',
                'where' => [
                    [
                        'type' => 'isTrue',
                        'attribute' => 'isActive',
                    ],
                ],
            ]);

            foreach ($response['list'] ?? [] as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $id = trim((string) ($row['id'] ?? ''));
                if ($id === '' || ($apiUserId !== null && $id === $apiUserId)) {
                    continue;
                }

                $userName = trim((string) ($row['userName'] ?? ''));
                $name = trim((string) ($row['name'] ?? ''));
                $label = $name !== '' ? $name : $userName;
                if ($userName !== '' && $name !== '' && $name !== $userName) {
                    $label = $name.' ('.$userName.')';
                }

                $options[$id] = $label !== '' ? $label : $id;
            }
        } catch (Throwable $exception) {
            Log::warning('EspoCRM User list failed while building assigned user options', [
                'error' => $exception->getMessage(),
            ]);
        }

        return $options;
    }

    /**
     * @param  array<string, string>  $options
     * @return array<string, string>
     */
    private function withCurrentValue(array $options): array
    {
        $current = trim(IntegrationConfig::string('espocrm.assigned_user_id'));

        if ($current !== '' && ! array_key_exists($current, $options)) {
            $options[$current] = __('cms.fields.assigned_user_unknown', ['id' => $current]);
        }

        return $options;
    }
}
