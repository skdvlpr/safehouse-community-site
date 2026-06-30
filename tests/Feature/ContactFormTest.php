<?php

namespace Tests\Feature;

use App\Models\ContactSubmission;
use App\Services\ContactSubmissionService;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_contact_form_stores_submission_with_hashed_fingerprint(): void
    {
        $response = $this->withHeaders(['User-Agent' => 'Safehouse Test Agent'])->post('/it/contact', [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Buongiorno, vorrei informazioni.',
            'gdpr_consent' => '1',
        ]);

        $response
            ->assertRedirect('/it/contatti')
            ->assertSessionHas('contact_success');

        $submission = ContactSubmission::query()->first();

        $this->assertNotNull($submission);
        $this->assertSame('Luca Bianchi', $submission->name);
        $this->assertSame('luca@example.com', $submission->email);
        $this->assertSame('new', $submission->status);
        $this->assertNotNull($submission->gdpr_consent_at);
        $this->assertSame(ContactSubmissionService::hashIp('127.0.0.1'), $submission->ip_hash);
        $this->assertSame(
            ContactSubmissionService::hashUserAgent('Safehouse Test Agent'),
            $submission->user_agent_hash,
        );
    }

    public function test_contact_form_requires_valid_fields_and_consent(): void
    {
        $response = $this->from('/it/contatti')->post('/it/contact', [
            'name' => '',
            'email' => 'not-an-email',
            'message' => '',
        ]);

        $response
            ->assertRedirect('/it/contatti')
            ->assertSessionHasErrors(['name', 'email', 'message', 'gdpr_consent']);

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_honeypot_submission_is_silently_accepted_without_storage(): void
    {
        $response = $this->post('/it/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Spam',
            'gdpr_consent' => '1',
            'company' => 'Acme Inc.',
        ]);

        $response
            ->assertRedirect('/it/contatti')
            ->assertSessionHas('contact_success');

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_contact_form_is_rate_limited(): void
    {
        RateLimiter::clear('contact');

        $payload = [
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Messaggio di prova.',
            'gdpr_consent' => '1',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post('/it/contact', $payload)->assertRedirect();
        }

        $this->post('/it/contact', $payload)->assertStatus(429);
    }

    public function test_contact_page_renders_working_form(): void
    {
        $this->get('/it/contatti')
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee('name="gdpr_consent"', false)
            ->assertDontSee(__('site.pages.contact_form_placeholder'), false);
    }
}
