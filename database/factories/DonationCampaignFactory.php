<?php

namespace Database\Factories;

use App\Models\DonationCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DonationCampaign>
 */
class DonationCampaignFactory extends Factory
{
    protected $model = DonationCampaign::class;

    public function definition(): array
    {
        $titleIt = fake()->sentence(3);

        return [
            'slug' => Str::slug($titleIt).'-'.fake()->unique()->numerify('###'),
            'title' => [
                'it' => $titleIt,
                'en' => fake()->sentence(3),
            ],
            'description' => [
                'it' => fake()->paragraph(),
            ],
            'privacy_notice' => [
                'it' => 'I dati di pagamento (carta) sono elaborati da Stripe. Non conserviamo numeri di carta.',
            ],
            'form_notice' => [
                'it' => 'I dati della carta non transitano sui nostri server: vengono inviati direttamente a Stripe.',
            ],
            'preset_amounts' => [500, 1000, 2500, 5000],
            'allow_custom_amount' => true,
            'allows_recurring' => false,
            'min_amount_cents' => 50,
            'currency' => 'EUR',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function recurring(): static
    {
        return $this->state(fn (): array => [
            'allows_recurring' => true,
            'fundraising_goal_cents' => null,
        ]);
    }
}
