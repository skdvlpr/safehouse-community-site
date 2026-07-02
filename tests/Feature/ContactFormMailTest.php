<?php

namespace Tests\Feature;

use App\Mail\ContactSubmissionMail;
use App\Services\SiteSettingsService;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_contact_form_sends_notification_when_mail_is_configured(): void
    {
        Mail::fake();

        app(SiteSettingsService::class)->updateMany([
            'mail.host' => 'smtp.test',
            'mail.port' => '587',
            'mail.encryption' => 'tls',
            'mail.from_address' => 'noreply@safehouse.community',
            'mail.from_name' => 'Safe House',
            'contact.notification_email' => 'team@safehouse.community',
        ]);

        $this->post('/it/contact', [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Buongiorno, vorrei informazioni.',
            'gdpr_consent' => '1',
        ])->assertRedirect();

        Mail::assertSent(ContactSubmissionMail::class, function (ContactSubmissionMail $mail): bool {
            return $mail->hasTo('team@safehouse.community')
                && $mail->submission->email === 'luca@example.com';
        });
    }

    public function test_contact_form_skips_mail_when_smtp_is_not_configured(): void
    {
        Mail::fake();

        $this->post('/it/contact', [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Buongiorno, vorrei informazioni.',
            'gdpr_consent' => '1',
        ])->assertRedirect();

        Mail::assertNothingSent();
    }
}
