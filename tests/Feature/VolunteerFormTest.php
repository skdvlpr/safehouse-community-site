<?php

namespace Tests\Feature;

use App\Models\Volunteer;
use App\Services\ContactSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class VolunteerFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_page_is_reachable(): void
    {
        $this->get('/it/volunteers')
            ->assertOk()
            ->assertSee(__('site.volunteer.title', [], 'it'), false)
            ->assertSee('name="gdpr_consent"', false)
            ->assertSee('template-page--volunteer', false)
            ->assertSee('volunteer-page__panel', false);
    }

    public function test_volunteer_form_stores_submission_with_hashed_fingerprint(): void
    {
        $response = $this->withHeaders(['User-Agent' => 'Safehouse Test Agent'])->post('/it/volunteers', [
            'name' => 'Maria Rossi',
            'email' => 'maria@example.com',
            'phone' => '+39 333 1234567',
            'message' => 'Vorrei aiutare con la distribuzione pasti.',
            'gdpr_consent' => '1',
        ]);

        $response
            ->assertRedirect('/it/volunteers')
            ->assertSessionHas('volunteer_success');

        $volunteer = Volunteer::query()->first();

        $this->assertNotNull($volunteer);
        $this->assertSame('Maria Rossi', $volunteer->name);
        $this->assertSame('maria@example.com', $volunteer->email);
        $this->assertSame('pending', $volunteer->status);
        $this->assertNotNull($volunteer->gdpr_consent_at);
        $this->assertSame(ContactSubmissionService::hashIp('127.0.0.1'), $volunteer->ip_hash);
        $this->assertSame(
            ContactSubmissionService::hashUserAgent('Safehouse Test Agent'),
            $volunteer->user_agent_hash,
        );
    }

    public function test_volunteer_form_requires_valid_fields_and_consent(): void
    {
        $response = $this->from('/it/volunteers')->post('/it/volunteers', [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response
            ->assertRedirect('/it/volunteers')
            ->assertSessionHasErrors(['name', 'email', 'gdpr_consent']);

        $this->assertDatabaseCount('volunteers', 0);
    }

    public function test_honeypot_submission_is_silently_accepted_without_storage(): void
    {
        $response = $this->post('/it/volunteers', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'gdpr_consent' => '1',
            'company' => 'Acme Inc.',
        ]);

        $response
            ->assertRedirect('/it/volunteers')
            ->assertSessionHas('volunteer_success');

        $this->assertDatabaseCount('volunteers', 0);
    }

    public function test_volunteer_form_is_rate_limited(): void
    {
        RateLimiter::clear('volunteers');

        $payload = [
            'name' => 'Maria Rossi',
            'email' => 'maria@example.com',
            'gdpr_consent' => '1',
        ];

        for ($i = 0; $i < 3; $i++) {
            $this->post('/it/volunteers', $payload)->assertRedirect();
        }

        $this->post('/it/volunteers', $payload)->assertStatus(429);
    }
}
