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

        $this->get('/it/donations/show-me')->assertOk();
    }

    public function test_recurring_campaign_shows_cancel_notice_and_ack(): void
    {
        DonationCampaign::factory()->recurring()->create([
            'slug' => 'recurring-donation',
            'title' => ['it' => 'Donazione ricorrente'],
            'description' => [
                'it' => '<p>Sostieni Safe House ogni mese con un contributo ricorrente. Puoi interrompere in qualsiasi momento tramite il portale Stripe dedicato ai donatori.</p>',
            ],
            'is_active' => true,
        ]);

        config()->set('stripe.customer_portal_login_url', 'https://billing.stripe.com/p/login/test_example');

        $this->get('/it/donations/recurring-donation')
            ->assertOk()
            ->assertSee('page-hero', false)
            ->assertSee('page-hero__headline--center', false)
            ->assertSee('donation-form', false)
            ->assertSee('donation-form__columns', false)
            ->assertSee('max-w-2xl', false)
            ->assertSee('lg:max-w-5xl', false)
            ->assertSee('id="payment-element"', false)
            ->assertSee('data-reveal-from="left"', false)
            ->assertDontSee('data-reveal-from="up"', false)
            ->assertSee('Donazione ricorrente', false)
            ->assertSee('Sostieni Safe House ogni mese con un contributo ricorrente.', false)
            ->assertSee(__('site.donations.cancel_notice_title'), false)
            ->assertSee(__('site.donations.cancel_ack_label'), false)
            ->assertSee(__('site.donations.continue_monthly_payment'), false)
            ->assertSee('https://billing.stripe.com/p/login/test_example', false)
            ->assertSee(__('site.donations.cancel_portal_cta'), false)
            ->assertSee('data-recurring="1"', false)
            ->assertDontSee('Puoi interrompere in qualsiasi momento tramite il portale Stripe dedicato ai donatori.', false)
            ->assertDontSee(__('site.donations.recurring_frequency_badge'), false);
    }

    public function test_one_time_campaign_hides_recurring_cancel_ux(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'una-tantum',
            'title' => ['it' => 'Dona a Safe House'],
            'is_active' => true,
            'allows_recurring' => false,
        ]);

        $this->get('/it/donations/una-tantum')
            ->assertOk()
            ->assertSee('page-hero', false)
            ->assertSee('page-hero__headline--center', false)
            ->assertSee('donation-form', false)
            ->assertSee('donation-form__columns', false)
            ->assertSee('max-w-2xl', false)
            ->assertSee('lg:max-w-5xl', false)
            ->assertSee('id="payment-element"', false)
            ->assertSee('Dona a Safe House', false)
            ->assertSee(__('site.donations.campaign_tagline', [], 'it'), false)
            ->assertDontSee(__('site.donations.cancel_notice_title'), false)
            ->assertDontSee(__('site.donations.cancel_ack_label'), false)
            ->assertSee(__('site.donations.continue_payment', [], 'it'), false);
    }

    public function test_thank_you_page_shows_personalized_message_and_reference(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'thank-you-test',
            'is_active' => true,
        ]);

        $this->mock(StripeDonationThankYouSync::class, function ($mock): void {
            $mock->shouldReceive('ingestSucceededPaymentIntent')
                ->once()
                ->with('pi_test_123');
        });

        $this->get('/it/donations/thank-you-test/thank-you?payment_intent=pi_test_123&donor_name=Mario%20Rossi')
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
            'slug' => 'thank-you-recurring',
            'is_active' => true,
        ]);

        config()->set('stripe.customer_portal_login_url', 'https://billing.stripe.com/p/login/test_thanks');

        $this->mock(StripeDonationThankYouSync::class, function ($mock): void {
            $mock->shouldReceive('ingestSucceededPaymentIntent')
                ->once()
                ->with('pi_recurring_1');
        });

        $this->get('/it/donations/thank-you-recurring/thank-you?payment_intent=pi_recurring_1&donor_name=Anna')
            ->assertOk()
            ->assertSee(__('site.donations.thank_you_cancel_title'), false)
            ->assertSee(__('site.donations.thank_you_cancel_body'), false)
            ->assertSee('https://billing.stripe.com/p/login/test_thanks', false)
            ->assertSee(__('site.donations.thank_you_cancel_portal_cta'), false);
    }
}
