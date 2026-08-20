<?php

namespace Tests\Feature;

use App\Jobs\LinkSportelloContactSubmissionToCrmJob;
use App\Mail\ContactSubmissionMail;
use App\Services\ContactDeskSettings;
use App\Services\SiteSettingsService;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_sportello_contact_form_sends_from_website_address_to_inbox_with_user_in_cc(): void
    {
        Mail::fake();
        Bus::fake([LinkSportelloContactSubmissionToCrmJob::class]);

        app(SiteSettingsService::class)->updateMany([
            'mail.host' => 'smtp.test',
            'mail.port' => '587',
            'mail.encryption' => 'tls',
            'mail.username' => 'website@safehouse.community',
            'mail.password' => 'secret',
            'contact.website_from_address' => 'website@safehouse.community',
            'contact.website_from_name' => 'Safe House — sito web',
        ]);

        app(ContactDeskSettings::class)->save([
            [
                'key' => 'digital_desk',
                'label' => 'Sportello digitale',
                'inbox' => 'sportello.digitale@safehouse.community',
                'case_type' => 'SportelloDigitale',
            ],
        ]);

        $this->post('/it/contact', [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Buongiorno, vorrei informazioni.',
            'desk' => 'digital_desk',
            'gdpr_consent' => '1',
        ])->assertRedirect();

        Mail::assertSent(ContactSubmissionMail::class, function (ContactSubmissionMail $mail): bool {
            $text = (string) ($mail->rendered['text'] ?? '');

            return $mail->hasTo('sportello.digitale@safehouse.community')
                && $mail->hasCc('luca@example.com')
                && $mail->hasReplyTo('luca@example.com', 'Luca Bianchi')
                && $mail->submission->desk === 'digital_desk'
                && str_contains($text, 'Tipo segnalazione: SportelloDigitale')
                && str_contains($text, 'Sportello: Sportello digitale');
        });

        Mail::assertSentCount(1);

        Bus::assertDispatched(LinkSportelloContactSubmissionToCrmJob::class);
    }

    public function test_contact_form_uses_custom_desk_from_cms(): void
    {
        Mail::fake();
        Bus::fake([LinkSportelloContactSubmissionToCrmJob::class]);

        app(SiteSettingsService::class)->updateMany([
            'mail.host' => 'smtp.test',
            'mail.port' => '587',
            'mail.encryption' => 'tls',
            'mail.username' => 'website@safehouse.community',
            'mail.password' => 'secret',
            'contact.website_from_address' => 'website@safehouse.community',
        ]);

        app(ContactDeskSettings::class)->save([
            [
                'key' => 'youth_desk',
                'label' => 'Sportello giovani',
                'inbox' => 'giovani@safehouse.community',
                'case_type' => 'SportelloGiovani',
            ],
        ]);

        $this->post('/it/contact', [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Buongiorno.',
            'desk' => 'youth_desk',
            'gdpr_consent' => '1',
        ])->assertRedirect();

        Mail::assertSent(ContactSubmissionMail::class, fn (ContactSubmissionMail $mail): bool => $mail->hasTo('giovani@safehouse.community'));
    }

    public function test_contact_form_still_sends_mail_when_espocrm_is_not_configured(): void
    {
        Mail::fake();
        Bus::fake([LinkSportelloContactSubmissionToCrmJob::class]);

        config()->set('espocrm.base_url', '');
        config()->set('espocrm.api_key', '');

        app(SiteSettingsService::class)->updateMany([
            'mail.host' => 'smtp.test',
            'mail.port' => '587',
            'mail.encryption' => 'tls',
            'mail.username' => 'website@safehouse.community',
            'mail.password' => 'secret',
            'contact.website_from_address' => 'website@safehouse.community',
            'espocrm.base_url' => '',
            'espocrm.api_key' => '',
        ]);

        app(ContactDeskSettings::class)->save([
            [
                'key' => 'generic_desk',
                'label' => 'Richiesta generica',
                'inbox' => 'info@safehouse.community',
                'case_type' => 'RichiestaGenerica',
            ],
        ]);

        $this->post('/it/contact', [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Richiesta generica senza CRM.',
            'desk' => 'generic_desk',
            'gdpr_consent' => '1',
        ])->assertRedirect();

        Mail::assertSent(ContactSubmissionMail::class, function (ContactSubmissionMail $mail): bool {
            return $mail->hasTo('info@safehouse.community')
                && $mail->submission->desk === 'generic_desk';
        });

        Bus::assertDispatched(LinkSportelloContactSubmissionToCrmJob::class);
    }

    public function test_contact_form_skips_mail_when_smtp_is_not_configured(): void
    {
        Mail::fake();

        $this->post('/it/contact', [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Buongiorno, vorrei informazioni.',
            'desk' => 'legal_desk',
            'gdpr_consent' => '1',
        ])->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_contact_form_requires_sportello_desk(): void
    {
        $this->post('/it/contact', [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Buongiorno, vorrei informazioni.',
            'gdpr_consent' => '1',
        ])->assertSessionHasErrors('desk');
    }
}
