<?php

namespace Tests\Feature;

use App\Mail\MembershipApplicantMail;
use App\Mail\MembershipStaffMail;
use App\Models\Page;
use App\Services\SiteSettingsService;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class MembershipFormTest extends TestCase
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
    private function validPayload(): array
    {
        return [
            'membership_form' => '1',
            'name' => 'Maria',
            'last_name' => 'Rossi',
            'birth_place' => 'Roma',
            'birth_province' => 'RM',
            'birth_date' => '1990-05-12',
            'tax_code' => 'RSSMRA90E52H501X',
            'city' => 'Torino',
            'province' => 'TO',
            'address' => 'Via Roma 1',
            'cap' => '10121',
            'phone' => '+39 333 1234567',
            'email' => 'maria@example.com',
            'accept_statute' => '1',
            'accept_mission' => '1',
            'accept_fee' => '1',
            'newsletter_consent' => '1',
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

    public function test_membership_landing_shows_official_form_dialog(): void
    {
        $this->get('/it/diventa-socio')
            ->assertOk()
            ->assertSee('template-landing-hero', false)
            ->assertSee('data-socio-open', false)
            ->assertSee('id="socio-dialog"', false)
            ->assertSee(__('site.membership.apply', [], 'it'), false)
            ->assertSee('name="tax_code"', false)
            ->assertSee('name="accept_statute"', false)
            ->assertSee('name="newsletter_consent"', false)
            ->assertDontSee('Libro Soci', false)
            ->assertDontSee('Matteo Grossi', false)
            ->assertDontSee('>Landing<', false)
            ->assertSee('Via Delleani 26, 00042 Anzio (RM)', false);
    }

    public function test_italian_membership_submit_sends_staff_and_applicant_mail(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $this->post('/it/membership-application', $this->validPayload())
            ->assertRedirect('/it/diventa-socio')
            ->assertSessionHas('membership_success');

        Mail::assertSent(MembershipStaffMail::class, function (MembershipStaffMail $mail): bool {
            $mail->assertHasSubject('Nuova domanda di ammissione socio');
            $mail->assertSeeInText('Abbiamo ricevuto una nuova domanda di ammissione socio');
            $mail->assertSeeInText('Nome: Maria');
            $mail->assertSeeInText('Cognome: Rossi');
            $mail->assertSeeInText('C.F.: RSSMRA90E52H501X');
            $mail->assertSeeInText('Indirizzo email: maria@example.com');
            $mail->assertSeeInText('Website | Safe House');

            return $mail->hasTo('matteo.grossi@safehouse.community')
                && $mail->hasReplyTo('maria@example.com', 'Maria Rossi');
        });

        Mail::assertSent(MembershipApplicantMail::class, function (MembershipApplicantMail $mail): bool {
            $mail->assertSeeInText('Abbiamo ricevuto la tua domanda di ammissione a socio');
            $mail->assertSeeInText('Non rispondere a questo messaggio');

            return $mail->hasTo('maria@example.com')
                && ! $mail->hasReplyTo('matteo.grossi@safehouse.community');
        });

        Mail::assertSentCount(2);
    }

    public function test_membership_lead_payload_matches_crm_contract_and_mail_survives_crm_error(): void
    {
        Mail::fake();
        $this->configureSmtp();
        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'test-espo-key');
        config()->set('espocrm.assigned_user_id', '');

        Http::fake([
            'https://crm.test/api/v1/Lead' => Http::response(['id' => 'lead-1'], 200),
        ]);

        $this->post('/it/membership-application', $this->validPayload())
            ->assertRedirect('/it/diventa-socio')
            ->assertSessionHas('membership_success');

        Http::assertSent(function (Request $request): bool {
            if ($request->method() !== 'POST' || ! str_ends_with($request->url(), '/api/v1/Lead')) {
                return false;
            }

            $body = $request->data();

            $this->assertSame(['MemberContact'], $body['contactType']);
            $this->assertSame('Yes', $body['newsletterConsent']);
            $this->assertSame('Italy', $body['addressCountry']);
            $this->assertSame('Web Site', $body['source']);
            $this->assertSame('New', $body['status']);
            $this->assertSame('RSSMRA90E52H501X', $body['taxCode']);
            $this->assertSame('1990-05-12', $body['birthDate']);
            $this->assertStringNotContainsString('Newsletter', $body['description']);
            $this->assertStringNotContainsString('acconsente', $body['description']);

            foreach ([
                'admissionBoardDate',
                'admissionOutcome',
                'memberBookNumber',
                'admissionFeePaid',
                'admissionReceiptNumber',
                'admissionForm',
                'contractType',
                'personnelStatus',
            ] as $forbidden) {
                $this->assertArrayNotHasKey($forbidden, $body);
            }

            return true;
        });

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), '/api/v1/Contact'));

        Mail::assertSentCount(2);

        Http::fake([
            'https://crm.test/api/v1/Lead' => Http::response(['message' => 'no'], 422),
        ]);

        $payload = $this->validPayload();
        $payload['email'] = 'other@example.com';
        $payload['newsletter_consent'] = '0';

        $this->post('/it/membership-application', $payload)
            ->assertRedirect('/it/diventa-socio')
            ->assertSessionHas('membership_success');

        Http::assertSent(function (Request $request): bool {
            if (! str_ends_with($request->url(), '/api/v1/Lead')) {
                return false;
            }

            return ($request->data()['newsletterConsent'] ?? null) === 'No';
        });

        Mail::assertSentCount(4);
    }

    public function test_english_membership_submit_keeps_staff_italian_and_applicant_english(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $this->post('/en/membership-application', $this->validPayload())
            ->assertRedirect('/en/diventa-socio')
            ->assertSessionHas('membership_success');

        Mail::assertSent(MembershipStaffMail::class, function (MembershipStaffMail $mail): bool {
            $mail->assertHasSubject('Nuova domanda di ammissione socio');
            $mail->assertSeeInText('Abbiamo ricevuto una nuova domanda di ammissione socio');
            $mail->assertDontSeeInText('Membership application received');

            return $mail->hasTo('matteo.grossi@safehouse.community');
        });

        Mail::assertSent(MembershipApplicantMail::class, function (MembershipApplicantMail $mail): bool {
            $mail->assertSeeInText('We have received your membership application');
            $mail->assertSeeInText('Please do not reply to this email');
            $mail->assertDontSeeInText('Non rispondere a questo messaggio');

            return $mail->hasTo('maria@example.com');
        });

        Mail::assertSentCount(2);
    }

    public function test_membership_form_requires_official_fields_and_declarations(): void
    {
        Mail::fake();

        $this->from('/it/diventa-socio')
            ->post('/it/membership-application', [
                'membership_form' => '1',
                'name' => '',
                'email' => 'not-an-email',
                'tax_code' => 'short',
            ])
            ->assertRedirect('/it/diventa-socio')
            ->assertSessionHasErrors(['name', 'last_name', 'email', 'tax_code', 'accept_statute', 'newsletter_consent']);

        Mail::assertNothingSent();
    }

    public function test_honeypot_submission_is_silently_accepted_without_mail(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $this->post('/it/membership-application', [
            ...$this->validPayload(),
            'company' => 'Acme Inc.',
        ])
            ->assertRedirect('/it/diventa-socio')
            ->assertSessionHas('membership_success');

        Mail::assertNothingSent();
    }

    public function test_membership_form_is_rate_limited(): void
    {
        RateLimiter::clear('membership');

        $payload = $this->validPayload();

        for ($i = 0; $i < 3; $i++) {
            $this->post('/it/membership-application', $payload)->assertRedirect();
        }

        $this->post('/it/membership-application', $payload)->assertStatus(429);
    }

    public function test_membership_form_rejects_missing_turnstile_when_enabled(): void
    {
        Mail::fake();
        $this->configureSmtp();

        app(SiteSettingsService::class)->updateMany([
            'turnstile.enabled' => '1',
            'turnstile.site_key' => 'site-key',
            'turnstile.secret_key' => 'secret-key',
        ]);

        $this->from('/it/diventa-socio')
            ->post('/it/membership-application', $this->validPayload())
            ->assertRedirect('/it/diventa-socio')
            ->assertSessionHasErrors(['cf-turnstile-response']);

        Mail::assertNothingSent();
    }

    public function test_membership_form_fails_closed_when_smtp_is_not_configured(): void
    {
        Mail::fake();

        $this->from('/it/diventa-socio')
            ->post('/it/membership-application', $this->validPayload())
            ->assertRedirect('/it/diventa-socio')
            ->assertSessionMissing('membership_success')
            ->assertSessionHasErrors('membership_mail');

        Mail::assertNothingSent();
    }

    public function test_membership_landing_splits_cms_body_into_cards(): void
    {
        Page::query()->where('key', 'diventa-socio')->update([
            'body' => [
                'it' => '<p>Intro visibile.</p><hr><h3>Card uno</h3><p>Uno.</p><hr><h3>Card due</h3><p>Due.</p>',
            ],
        ]);

        $this->get('/it/diventa-socio')
            ->assertOk()
            ->assertSee('Intro visibile.', false)
            ->assertSee('template-landing-cards', false)
            ->assertSee('Card uno', false)
            ->assertSee('Card due', false);
    }

    public function test_membership_landing_uses_six_cards_ticker_and_form_button_without_email(): void
    {
        Page::query()->where('key', 'diventa-socio')->update([
            'body' => [
                'it' => implode('<hr>', [
                    '<h2>Insieme</h2><p>Intro visibile.</p>',
                    '<h3>Uno</h3><p>A</p>',
                    '<h3>Due</h3><p>B</p>',
                    '<h3>Tre</h3><p>C</p>',
                    '<h3>Quattro</h3><p>D</p>',
                    '<h3>Cinque</h3><p>E</p>',
                    '<h3>Sei</h3><p>F</p>',
                    '<h3>Sette extra</h3><p>G</p>',
                    '<h1>I nostri valori</h1><ul><li>Solidarietà</li><li>Partecipazione</li></ul>',
                    '<h2>Contattaci</h2><p>info@safehouse.community</p>',
                ]),
            ],
        ]);

        $this->get('/it/diventa-socio')
            ->assertOk()
            ->assertSee('Intro visibile.', false)
            ->assertSee('landing-marquee', false)
            ->assertSee('Solidarietà', false)
            ->assertSee('landing-contact', false)
            ->assertSee('landing-contact__links', false)
            ->assertSee('landing-swipe', false)
            ->assertSee('Scorri in basso', false)
            ->assertSee(__('site.membership.statuto', [], 'it'), false)
            ->assertSee(__('site.membership.faq', [], 'it'), false)
            ->assertSee(__('site.membership.link_donate', [], 'it'), false)
            ->assertSee(__('site.membership.link_volunteer', [], 'it'), false)
            ->assertSee('/it/frequently-asked-questions', false)
            ->assertSee('/it/donations', false)
            ->assertSee('/it/volunteers', false)
            ->assertSee('data-socio-open', false)
            ->assertSee('Sede legale', false)
            ->assertSee('Via Delleani 26', false)
            ->assertDontSee('data-marquee-toggle', false)
            ->assertDontSee('landing-contact__social', false)
            ->assertDontSee('Sette extra', false)
            ->assertDontSee('>Landing<', false);
    }
}
