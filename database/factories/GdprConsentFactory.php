<?php

namespace Database\Factories;

use App\Models\GdprConsent;
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
            'ip_hash' => hash('sha256', fake()->ipv4()),
            'consented_at' => now(),
        ];
    }
}
