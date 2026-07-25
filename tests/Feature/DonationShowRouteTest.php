<?php

namespace Tests\Feature;

use App\Models\DonationCampaign;
use App\Services\Donations\StripeDonationThankYouSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationShowRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_active_campaign(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'show-me',
            'is_active' => true,
        ]);

        $this->get('/it/donazioni/show-me')->assertOk();
    }

    public function test_recurring_campaign_shows_cancel_notice_and_ack(): void
    {
        DonationCampaign::factory()->recurring()->create([
            'slug' => 'donazione-ricorrente',
            'title' => ['it' => 'Donazione ricorrente'],
            'is_active' => true,
        ]);

        config()->set('stripe.customer_portal_login_url', 'https://billing.stripe.com/p/login/test_example');

        $this->get('/it/donazioni/donazione-ricorrente')
            ->assertOk()
            ->assertSee(__('site.donations.recurring_frequency_badge'), false)
            ->assertSee(__('site.donations.cancel_notice_title'), false)
            ->assertSee(__('site.donations.cancel_ack_label'), false)
            ->assertSee(__('site.donations.continue_monthly_payment'), false)
            ->assertSee('https://billing.stripe.com/p/login/test_example', false)
            ->assertSee(__('site.donations.cancel_portal_cta'), false)
            ->assertSee('data-recurring="1"', false);
    }

    public function test_one_time_campaign_hides_recurring_cancel_ux(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'una-tantum',
            'title' => ['it' => 'Una tantum'],
            'is_active' => true,
            'allows_recurring' => false,
        ]);

        $this->get('/it/donazioni/una-tantum')
            ->assertOk()
            ->assertDontSee(__('site.donations.cancel_notice_title'), false)
            ->assertDontSee(__('site.donations.cancel_ack_label'), false)
            ->assertSee('Continua al pagamento', false);
    }

    public function test_thank_you_page_shows_personalized_message_and_reference(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'grazie-test',
            'is_active' => true,
        ]);

        $this->mock(StripeDonationThankYouSync::class, function ($mock): void {
            $mock->shouldReceive('ingestSucceededPaymentIntent')
                ->once()
                ->with('pi_test_123');
        });

        $this->get('/it/donazioni/grazie-test/grazie?payment_intent=pi_test_123&donor_name=Mario%20Rossi')
            ->assertOk()
            ->assertSee('Grazie, Mario!', false)
            ->assertSee('pi_test_123', false)
            ->assertDontSee(__('site.donations.thank_you_cancel_title'), false)
            ->assertDontSee('EspoCRM', false)
            ->assertDontSee('Prima nota', false);
    }

    public function test_thank_you_page_shows_cancel_notice_for_recurring(): void
    {
        DonationCampaign::factory()->recurring()->create([
            'slug' => 'grazie-ricorrente',
            'is_active' => true,
        ]);

        config()->set('stripe.customer_portal_login_url', 'https://billing.stripe.com/p/login/test_thanks');

        $this->mock(StripeDonationThankYouSync::class, function ($mock): void {
            $mock->shouldReceive('ingestSucceededPaymentIntent')
                ->once()
                ->with('pi_recurring_1');
        });

        $this->get('/it/donazioni/grazie-ricorrente/grazie?payment_intent=pi_recurring_1&donor_name=Anna')
            ->assertOk()
            ->assertSee(__('site.donations.thank_you_cancel_title'), false)
            ->assertSee(__('site.donations.thank_you_cancel_body'), false)
            ->assertSee('https://billing.stripe.com/p/login/test_thanks', false)
            ->assertSee(__('site.donations.thank_you_cancel_portal_cta'), false);
    }
}
