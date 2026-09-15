<?php

namespace Database\Factories;

use App\Models\GdprConsent;
use App\Services\ContactSubmissionService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GdprConsent>
 */
class GdprConsentFactory extends Factory
{
    protected $model = GdprConsent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'consent_type' => fake()->randomElement(['cookie_banner', 'volunteer_form', 'contact_form']),
            'granted' => true,
            'ip_hash' => ContactSubmissionService::hashIp(fake()->ipv4()) ?? hash_hmac('sha256', 'unknown', (string) config('app.key')),
            'user_agent_hash' => ContactSubmissionService::hashUserAgent(fake()->userAgent()),
            'consented_at' => now(),
        ];
    }
}
