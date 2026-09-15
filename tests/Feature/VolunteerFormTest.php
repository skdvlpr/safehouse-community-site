<?php

namespace Tests\Feature;

use App\Mail\VolunteerApplicantMail;
use App\Mail\VolunteerStaffMail;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VolunteerFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
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

    public function test_volunteer_page_is_reachable(): void
    {
        $this->get('/it/volunteers')
            ->assertOk()
            ->assertSee(__('site.volunteer.title', [], 'it'), false)
            ->assertSee('name="gdpr_consent"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('template-page--volunteer', false)
            ->assertSee('volunteer-page__panel', false);
    }

    public function test_italian_volunteer_submit_sends_staff_and_applicant_mail(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $this->post('/it/volunteers', $this->validPayload())
            ->assertRedirect('/it/volunteers')
            ->assertSessionHas('volunteer_success');

        Mail::assertSent(VolunteerStaffMail::class, function (VolunteerStaffMail $mail): bool {
            $mail->assertHasSubject('Nuova candidatura volontario');
            $mail->assertSeeInText('Abbiamo ricevuto una nuova candidatura di volontario');
            $mail->assertSeeInText('Nome: Maria');
            $mail->assertSeeInText('Cognome: Rossi');
            $mail->assertSeeInText('Indirizzo email: maria@example.com');
            $mail->assertSeeInText('Tel.: +39 333 1234567');
            $mail->assertSeeInText('Messaggio: Vorrei aiutare con la distribuzione pasti.');
            $mail->assertSeeInText('Website | Safe House');

            return $mail->hasTo('matteo.grossi@safehouse.community')
                && $mail->hasReplyTo('maria@example.com', 'Maria Rossi');
        });

        Mail::assertSent(VolunteerApplicantMail::class, function (VolunteerApplicantMail $mail): bool {
            $mail->assertSeeInText('Abbiamo ricevuto la tua candidatura da volontario');
            $mail->assertSeeInText('Non rispondere a questo messaggio');

            return $mail->hasTo('maria@example.com')
                && ! $mail->hasReplyTo('matteo.grossi@safehouse.community');
        });

        Mail::assertSentCount(2);
        $this->assertFalse(Schema::hasTable('volunteers'));
    }

    public function test_english_volunteer_submit_keeps_staff_italian_and_applicant_english(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $this->post('/en/volunteers', $this->validPayload())
            ->assertRedirect('/en/volunteers')
            ->assertSessionHas('volunteer_success');

        Mail::assertSent(VolunteerStaffMail::class, function (VolunteerStaffMail $mail): bool {
            $mail->assertHasSubject('Nuova candidatura volontario');
            $mail->assertSeeInText('Abbiamo ricevuto una nuova candidatura di volontario');
            $mail->assertDontSeeInText('Application received');

            return $mail->hasTo('matteo.grossi@safehouse.community');
        });

        Mail::assertSent(VolunteerApplicantMail::class, function (VolunteerApplicantMail $mail): bool {
            $mail->assertSeeInText('We have received your volunteer application');
            $mail->assertSeeInText('Please do not reply to this email');
            $mail->assertDontSeeInText('Non rispondere a questo messaggio');

            return $mail->hasTo('maria@example.com')
                && ! $mail->hasReplyTo('matteo.grossi@safehouse.community');
        });

        Mail::assertSentCount(2);
    }

    public function test_volunteer_form_requires_last_name_phone_and_message(): void
    {
        Mail::fake();
        $this->configureSmtp();

        foreach (['last_name', 'phone', 'message'] as $field) {
            $payload = $this->validPayload();
            $payload[$field] = '';

            $this->from('/it/volunteers')
                ->post('/it/volunteers', $payload)
                ->assertRedirect('/it/volunteers')
                ->assertSessionHasErrors($field);
        }

        Mail::assertNothingSent();
    }

    public function test_volunteer_form_requires_valid_fields_and_consent(): void
    {
        Mail::fake();

        $response = $this->from('/it/volunteers')->post('/it/volunteers', [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response
            ->assertRedirect('/it/volunteers')
            ->assertSessionHasErrors(['name', 'email', 'gdpr_consent']);

        Mail::assertNothingSent();
    }

    public function test_honeypot_submission_is_silently_accepted_without_mail(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $this->post('/it/volunteers', [
            ...$this->validPayload(),
            'company' => 'Acme Inc.',
        ])
            ->assertRedirect('/it/volunteers')
            ->assertSessionHas('volunteer_success');

        Mail::assertNothingSent();
    }

    public function test_volunteer_form_is_rate_limited(): void
    {
        RateLimiter::clear('volunteers');

        $payload = $this->validPayload();

        for ($i = 0; $i < 3; $i++) {
            $this->post('/it/volunteers', $payload)->assertRedirect();
        }

        $this->post('/it/volunteers', $payload)->assertStatus(429);
    }

    public function test_volunteer_form_rejects_missing_turnstile_when_enabled(): void
    {
        Mail::fake();
        $this->configureSmtp();

        app(SiteSettingsService::class)->updateMany([
            'turnstile.enabled' => '1',
            'turnstile.site_key' => 'site-key',
            'turnstile.secret_key' => 'secret-key',
        ]);

        $this->from('/it/volunteers')
            ->post('/it/volunteers', $this->validPayload())
            ->assertRedirect('/it/volunteers')
            ->assertSessionHasErrors(['cf-turnstile-response']);

        Mail::assertNothingSent();
    }

    public function test_volunteer_form_fails_closed_when_smtp_is_not_configured(): void
    {
        Mail::fake();

        $this->from('/it/volunteers')
            ->post('/it/volunteers', $this->validPayload())
            ->assertRedirect('/it/volunteers')
            ->assertSessionMissing('volunteer_success')
            ->assertSessionHasErrors('volunteer_mail');

        Mail::assertNothingSent();
    }

    public function test_volunteers_table_is_absent_after_migrate(): void
    {
        $this->assertFalse(Schema::hasTable('volunteers'));
    }
}
