<?php

namespace Tests\Feature;

use App\Mail\VolunteerApplicantMail;
use App\Mail\VolunteerStaffMail;
use App\Models\DonationCampaign;
use App\Services\Donations\StripeDonationThankYouSync;
use App\Services\MeasurementBootService;
use App\Services\SiteSettingsService;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MeasurementConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    /**
     * @return array<string, string>
     */
    private function validVolunteerPayload(): array
    {
        return [
            'name' => 'Maria',
            'last_name' => 'Rossi',
            'email' => 'maria@example.com',
            'phone' => '+39 333 1234567',
            'message' => 'Vorrei aiutare con la distribuzione pasti.',
            'gdpr_consent' => '1',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validContactPayload(): array
    {
        return [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Buongiorno, vorrei informazioni.',
            'desk' => 'digital_desk',
            'gdpr_consent' => '1',
        ];
    }

    private function configureSmtp(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'mail.host' => 'smtp.test',
            'mail.port' => '587',
            'mail.encryption' => 'tls',
            'mail.username' => 'website@safehouse.community',
            'mail.password' => 'secret',
            'contact.website_from_address' => 'website@safehouse.community',
            'contact.website_from_name' => 'Safe House — sito web',
        ]);
    }

    public function test_one_time_thank_you_emits_donate_success_without_donor_on_marker(): void
    {
        DonationCampaign::factory()->create([
            'slug' => 'thank-you-measure',
            'is_active' => true,
            'allows_recurring' => false,
        ]);

        $this->mock(StripeDonationThankYouSync::class, function ($mock): void {
            $mock->shouldReceive('ingestSucceededPaymentIntent')
                ->once()
                ->with('pi_measure_1');
        });

        $response = $this->get('/it/donations/thank-you-measure/thank-you?payment_intent=pi_measure_1&donor_name=Mario%20Rossi')
            ->assertOk()
            ->assertSee('Grazie, Mario!', false)
            ->assertSee('data-measurement-event="donate_success"', false)
            ->assertDontSee('data-measurement-event="donate_recurring_success"', false);

        $this->assertDoesNotMatchRegularExpression(
            '/data-measurement-event="donate_success"[^>]*Mario/',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            '<span hidden data-measurement-event="donate_success"></span>',
            $response->getContent(),
        );
    }

    public function test_recurring_thank_you_emits_donate_recurring_success_only(): void
    {
        DonationCampaign::factory()->recurring()->create([
            'slug' => 'thank-you-measure-recurring',
            'is_active' => true,
        ]);

        $this->mock(StripeDonationThankYouSync::class, function ($mock): void {
            $mock->shouldReceive('ingestSucceededPaymentIntent')
                ->once()
                ->with('pi_measure_r1');
        });

        $response = $this->get('/it/donations/thank-you-measure-recurring/thank-you?payment_intent=pi_measure_r1&donor_name=Anna')
            ->assertOk()
            ->assertSee('data-measurement-event="donate_recurring_success"', false)
            ->assertDontSee('data-measurement-event="donate_success"', false);

        $this->assertStringContainsString(
            '<span hidden data-measurement-event="donate_recurring_success"></span>',
            $response->getContent(),
        );
        $this->assertDoesNotMatchRegularExpression(
            '/data-measurement-event="donate_recurring_success"[^>]*Anna/',
            $response->getContent(),
        );
    }

    public function test_volunteer_honeypot_keeps_success_flash_without_measurement_marker(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $this->post('/it/volunteers', [
            ...$this->validVolunteerPayload(),
            'company' => 'Acme Inc.',
        ])
            ->assertRedirect('/it/volunteers')
            ->assertSessionHas('volunteer_success')
            ->assertSessionMissing(MeasurementBootService::SESSION_CONVERSION);

        $this->get('/it/volunteers')
            ->assertOk()
            ->assertSee(__('site.volunteer.success', [], 'it'), false)
            ->assertDontSee('data-measurement-event="volunteer_success"', false);

        Mail::assertNothingSent();
    }

    public function test_volunteer_real_success_sets_measurement_marker(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $this->post('/it/volunteers', $this->validVolunteerPayload())
            ->assertRedirect('/it/volunteers')
            ->assertSessionHas('volunteer_success')
            ->assertSessionHas(MeasurementBootService::SESSION_CONVERSION, 'volunteer_success');

        $this->get('/it/volunteers')
            ->assertOk()
            ->assertSee('data-measurement-event="volunteer_success"', false);

        Mail::assertSent(VolunteerStaffMail::class);
        Mail::assertSent(VolunteerApplicantMail::class);
    }

    public function test_contact_honeypot_keeps_success_flash_without_measurement_marker(): void
    {
        $this->post('/it/contact', [
            ...$this->validContactPayload(),
            'company' => 'Acme Inc.',
        ])
            ->assertRedirect('/it/contact')
            ->assertSessionHas('contact_success')
            ->assertSessionMissing(MeasurementBootService::SESSION_CONVERSION);

        $this->get('/it/contact')
            ->assertOk()
            ->assertDontSee('data-measurement-event="contact_success"', false);
    }

    public function test_contact_real_success_sets_measurement_marker(): void
    {
        $this->post('/it/contact', $this->validContactPayload())
            ->assertRedirect('/it/contact')
            ->assertSessionHas('contact_success')
            ->assertSessionHas(MeasurementBootService::SESSION_CONVERSION, 'contact_success');

        $this->get('/it/contact')
            ->assertOk()
            ->assertSee('data-measurement-event="contact_success"', false);
    }

    public function test_home_impact_cards_still_render(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertSee('Pasti distribuiti', false)
            ->assertSee('Interventi sul territorio', false);
    }
}
