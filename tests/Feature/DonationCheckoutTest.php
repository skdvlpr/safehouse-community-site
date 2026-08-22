<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use App\Services\Payments\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class DonationCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('stripe.key', 'pk_test_mock');
    }

    public function test_store_creates_payment_intent_via_stripe_service(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'slug' => 'raccolta-test',
            'allow_custom_amount' => true,
            'min_amount_cents' => 50,
        ]);

        $this->mockStripeService(function (MockInterface $mock) use ($campaign): void {
            $mock->shouldReceive('createDonationIntent')
                ->once()
                ->with(
                    Mockery::on(fn ($model) => $model->is($campaign)),
                    2500,
                    'Mario Rossi',
                    'individual',
                    'Grazie per il lavoro',
                    'mario@example.com',
                    null,
                )
                ->andReturn([
                    'client_secret' => 'cs_test_secret',
                    'payment_intent_id' => 'pi_test_checkout',
                ]);
            $mock->shouldNotReceive('createDonationSubscription');
        });

        $this->postJson('/api/donations/intents/raccolta-test', [
            'amount_cents' => 2500,
            'donor_name' => 'Mario Rossi',
            'donor_type' => 'individual',
            'donor_email' => 'mario@example.com',
            'comment' => 'Grazie per il lavoro',
        ])
            ->assertOk()
            ->assertJson([
                'client_secret' => 'cs_test_secret',
                'payment_intent_id' => 'pi_test_checkout',
                'publishable_key' => 'pk_test_mock',
                'subscription_id' => null,
            ]);
    }

    public function test_store_creates_subscription_for_recurring_campaign(): void
    {
        $campaign = DonationCampaign::factory()->recurring()->create([
            'slug' => 'recurring-donation',
            'allow_custom_amount' => true,
            'min_amount_cents' => 50,
        ]);

        $this->mockStripeService(function (MockInterface $mock) use ($campaign): void {
            $mock->shouldReceive('createDonationSubscription')
                ->once()
                ->with(
                    Mockery::on(fn ($model) => $model->is($campaign)),
                    1500,
                    'Anna Mensile',
                    'individual',
                    null,
                    'anna@example.com',
                    null,
                )
                ->andReturn([
                    'client_secret' => 'cs_sub_secret',
                    'payment_intent_id' => 'pi_sub_checkout',
                    'subscription_id' => 'sub_checkout',
                    'customer_id' => 'cus_checkout',
                ]);
            $mock->shouldNotReceive('createDonationIntent');
        });

        $this->postJson('/api/donations/intents/recurring-donation', [
            'amount_cents' => 1500,
            'donor_name' => 'Anna Mensile',
            'donor_type' => 'individual',
            'donor_email' => 'anna@example.com',
        ])
            ->assertOk()
            ->assertJson([
                'client_secret' => 'cs_sub_secret',
                'payment_intent_id' => 'pi_sub_checkout',
                'subscription_id' => 'sub_checkout',
                'publishable_key' => 'pk_test_mock',
            ]);
    }

    public function test_store_returns_404_for_inactive_campaign(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'inactive-campaign',
            'is_active' => false,
        ]);

        $this->mockStripeService(fn (MockInterface $mock) => $mock->shouldNotReceive('createDonationIntent'));

        $this->postJson('/api/donations/intents/inactive-campaign', [
            'amount_cents' => 1000,
            'donor_name' => 'Anna Bianchi',
            'donor_type' => 'organization',
            'donor_phone' => '+39333111222',
        ])->assertNotFound();
    }

    public function test_store_returns_422_when_stripe_service_rejects_amount(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'fixed-amounts',
            'allow_custom_amount' => false,
            'preset_amounts' => [1000, 2000],
        ]);

        $this->mockStripeService(function (MockInterface $mock): void {
            $mock->shouldReceive('createDonationIntent')
                ->once()
                ->andThrow(new RuntimeException('Custom amounts are not allowed for this campaign.'));
        });

        $this->postJson('/api/donations/intents/fixed-amounts', [
            'amount_cents' => 1500,
            'donor_name' => 'Anna Bianchi',
            'donor_type' => 'organization',
            'donor_email' => 'anna@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Custom amounts are not allowed for this campaign.');
    }

    public function test_store_requires_email_or_phone(): void
    {
        DonationCampaign::factory()->create(['slug' => 'contact-required']);

        $this->mockStripeService(fn (MockInterface $mock) => $mock->shouldNotReceive('createDonationIntent'));

        $this->postJson('/api/donations/intents/contact-required', [
            'amount_cents' => 1000,
            'donor_name' => 'Anna Bianchi',
            'donor_type' => 'individual',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['donor_email']);
    }

    public function test_store_validates_required_fields(): void
    {
        DonationCampaign::factory()->create(['slug' => 'validate-me']);

        $this->mockStripeService(fn (MockInterface $mock) => $mock->shouldNotReceive('createDonationIntent'));

        $this->postJson('/api/donations/intents/validate-me', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount_cents', 'donor_name', 'donor_type']);
    }

    /**
     * @param  callable(MockInterface): void  $configure
     */
    private function mockStripeService(callable $configure): void
    {
        $mock = Mockery::mock(StripePaymentService::class);
        $configure($mock);
        $this->instance(StripePaymentService::class, $mock);
    }
}
