<?php

namespace App\Services\EspoCrm;

use App\Support\IntegrationConfig;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EspoCrmClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

    public static function fromConfig(): self
    {
        $baseUrl = IntegrationConfig::string('espocrm.base_url');
        $apiKey = IntegrationConfig::string('espocrm.api_key');

        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('EspoCRM base URL and API key must be configured.');
        }

        return new self($baseUrl, $apiKey);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function search(string $entityType, array $query = []): array
    {
        return $this->request('get', $entityType, $query);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(string $entityType, array $payload): array
    {
        return $this->request('post', $entityType, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function appUser(): array
    {
        return $this->request('get', 'App/user');
    }

    public function apiUserId(): string
    {
        $response = $this->appUser();
        $id = $response['user']['id'] ?? $response['id'] ?? null;

        if (! is_string($id) || $id === '') {
            throw new RuntimeException('EspoCRM App/user did not return a user id.');
        }

        return $id;
    }

    /**
     * @return array<string, mixed>
     */
    public function userById(string $userId): array
    {
        return $this->request('get', 'User/'.$userId);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(string $entityType, string $id, array $payload): array
    {
        return $this->request('put', $entityType.'/'.$id, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function request(string $method, string $entityType, array $payload = []): array
    {
        $url = rtrim($this->baseUrl, '/').'/api/v1/'.$entityType;

        $pending = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ])->timeout(15);

        $response = match (strtolower($method)) {
            'get' => $pending->get($url, $payload),
            default => $pending->acceptJson()->asJson()->{$method}($url, $payload),
        };

        try {
            $response->throw();
        } catch (RequestException $exception) {
            $reason = $response->header('X-Status-Reason');
            $body = $response->json() ?? $response->body();

            throw new RuntimeException(
                'EspoCRM API error'.($reason ? " ({$reason})" : '').': '.json_encode($body),
                $response->status(),
                $exception,
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];

        return $json;
    }

    /**
     * @return array<string, mixed>
     */
    public function reportingSummary(string $path): array
    {
        return $this->request('get', ltrim($path, '/'));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function reportingTotals(string $path, array $payload = []): array
    {
        return $this->request('post', ltrim($path, '/'), $payload);
    }
}
