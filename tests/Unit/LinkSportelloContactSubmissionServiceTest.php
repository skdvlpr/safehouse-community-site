<?php

namespace Tests\Unit;

use App\Models\ContactSubmission;
use App\Services\ContactDeskSettings;
use App\Services\EspoCrm\EspoCrmClient;
use App\Services\EspoCrm\EspoCrmContactIntakeService;
use App\Services\EspoCrm\LinkSportelloContactSubmissionService;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkSportelloContactSubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'secret');

        app(SiteSettingsService::class)->updateMany([
            'espocrm.base_url' => 'https://crm.test',
            'espocrm.api_key' => 'secret',
        ]);

        app(ContactDeskSettings::class)->save([
            [
                'key' => 'digital_desk',
                'label' => 'Sportello digitale',
                'inbox' => 'sportello.digitale@safehouse.community',
                'case_type' => 'SportelloDigitale',
            ],
        ]);
    }

    public function test_it_links_case_when_lead_already_exists(): void
    {
        $submission = ContactSubmission::query()->create([
            'name' => 'Sem',
            'email' => 'visitor@example.com',
            'desk' => 'digital_desk',
            'message' => 'Test message',
            'status' => 'new',
            'correlation_token' => '2b464959-7120-4d23-9d36-5138f7b47817',
            'crm_link_status' => 'pending',
            'crm_lead_id' => 'lead-1',
        ]);

        Http::fake([
            'https://crm.test/api/v1/Case*' => Http::response([
                'total' => 1,
                'list' => [[
                    'id' => 'case-1',
                    'name' => '[SH-2b464959-7120-4d23-9d36-5138f7b47817] Nuovo messaggio — Sem',
                    'parentType' => null,
                    'parentId' => null,
                    'type' => 'Other',
                ]],
            ]),
            'https://crm.test/api/v1/Case/case-1' => Http::response(['id' => 'case-1']),
        ]);

        $service = app(LinkSportelloContactSubmissionService::class);

        $this->assertSame('linked', $service->link($submission->fresh()));
        $submission->refresh();
        $this->assertSame('linked', $submission->crm_link_status);
        $this->assertSame('case-1', $submission->crm_case_id);

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'PUT' || ! str_contains($request->url(), '/Case/case-1')) {
                return false;
            }

            $payload = $request->data();

            return ($payload['sportelloDisplayName'] ?? null) === 'Sportello digitale'
                && ($payload['type'] ?? null) === 'SportelloDigitale';
        });
    }

    public function test_find_case_falls_back_when_reference_field_is_unavailable(): void
    {
        Http::fake(function ($request) {
            if (! str_contains($request->url(), '/Case')) {
                return Http::response([], 404);
            }

            if (str_contains($request->url(), 'websiteReferenceId')) {
                return Http::response(['message' => 'Unknown attribute'], 400);
            }

            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $where = $query['where'] ?? '[]';
            $where = is_string($where) ? json_decode($where, true) : $where;
            $attribute = is_array($where) ? ($where[0]['attribute'] ?? null) : null;

            if ($attribute === 'name') {
                return Http::response([
                    'total' => 1,
                    'list' => [['id' => 'case-99', 'name' => '[SH-abc] test', 'type' => 'Other']],
                ]);
            }

            return Http::response(['total' => 0, 'list' => []]);
        });

        $intake = new EspoCrmContactIntakeService(EspoCrmClient::fromConfig());
        $case = $intake->findCaseByCorrelationToken('abc');

        $this->assertSame('case-99', $case['id'] ?? null);
    }
}
