<?php

namespace Tests\Feature;

use App\Models\ContactSubmission;
use App\Services\ContactSubmissionRateLimiter;
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

    private function clearContactRateLimit(): void
    {
        RateLimiter::clear(app(ContactSubmissionRateLimiter::class)->key(
            request()->create('/', 'POST', server: ['REMOTE_ADDR' => '127.0.0.1']),
        ));
    }

    public function test_contact_form_stores_submission_with_hashed_fingerprint(): void
    {
        $response = $this->withHeaders(['User-Agent' => 'Safehouse Test Agent'])->post('/it/contact', $this->validContactPayload());

        $response
            ->assertRedirect('/it/contact')
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
        $response = $this->from('/it/contact')->post('/it/contact', [
            'name' => '',
            'email' => 'not-an-email',
            'message' => '',
        ]);

        $response
            ->assertRedirect('/it/contact')
            ->assertSessionHasErrors(['name', 'email', 'message', 'gdpr_consent']);

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_honeypot_submission_is_silently_accepted_without_storage(): void
    {
        $response = $this->post('/it/contact', [
            ...$this->validContactPayload(),
            'company' => 'Acme Inc.',
        ]);

        $response
            ->assertRedirect('/it/contact')
            ->assertSessionHas('contact_success');

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_contact_form_is_rate_limited(): void
    {
        $this->clearContactRateLimit();

        $payload = $this->validContactPayload();
        $payload['message'] = 'Messaggio di prova.';

        for ($i = 0; $i < 10; $i++) {
            $this->post('/it/contact', $payload)
                ->assertRedirect('/it/contact')
                ->assertSessionHas('contact_success');
        }

        $this->post('/it/contact', $payload)
            ->assertRedirect('/it/contact')
            ->assertSessionHasErrors('contact_rate_limit');
    }

    public function test_failed_validation_does_not_count_toward_rate_limit(): void
    {
        $this->clearContactRateLimit();

        for ($i = 0; $i < 20; $i++) {
            $this->from('/it/contact')->post('/it/contact', [
                'name' => '',
                'email' => 'not-an-email',
                'message' => '',
            ])->assertSessionHasErrors(['name', 'email', 'message', 'gdpr_consent']);
        }

        $this->post('/it/contact', $this->validContactPayload())
            ->assertRedirect('/it/contact')
            ->assertSessionHas('contact_success');
    }

    public function test_contact_page_renders_working_form(): void
    {
        $this->get('/it/contact')
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee('name="gdpr_consent"', false)
            ->assertDontSee(__('site.pages.contact_form_placeholder'), false);
    }
}
