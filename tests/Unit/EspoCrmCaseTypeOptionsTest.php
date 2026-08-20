<?php

namespace Tests\Unit;

use App\Services\EspoCrm\EspoCrmCaseTypeOptions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EspoCrmCaseTypeOptionsTest extends TestCase
{
    public function test_falls_back_to_default_desk_types_when_crm_is_not_configured(): void
    {
        config()->set('espocrm.base_url', '');
        config()->set('espocrm.api_key', '');

        $options = app(EspoCrmCaseTypeOptions::class);
        $options->forgetCache();

        $mapped = $options->optionsForSelect();

        $this->assertArrayHasKey('SportelloDigitale', $mapped);
        $this->assertArrayHasKey('SportelloLegale', $mapped);
        $this->assertArrayHasKey('RichiestaGenerica', $mapped);
        $this->assertFalse($options->isLoadedFromCrm());
    }

    public function test_loads_case_types_from_crm_metadata_when_configured(): void
    {
        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'secret');

        Http::fake([
            'crm.test/api/v1/Metadata*' => Http::response([
                'type' => 'enum',
                'options' => ['SportelloDigitale', 'RichiestaGenerica', 'Other'],
            ]),
        ]);

        $options = app(EspoCrmCaseTypeOptions::class);
        $options->forgetCache();

        $mapped = $options->optionsForSelect();

        $this->assertSame([
            'SportelloDigitale' => 'SportelloDigitale',
            'RichiestaGenerica' => 'RichiestaGenerica',
            'Other' => 'Other',
        ], $mapped);
        $this->assertTrue($options->isLoadedFromCrm());
    }

    public function test_falls_back_when_crm_metadata_request_fails(): void
    {
        config()->set('espocrm.base_url', 'https://crm.test');
        config()->set('espocrm.api_key', 'secret');

        Http::fake([
            'crm.test/api/v1/Metadata*' => Http::response(['message' => 'boom'], 500),
        ]);

        $options = app(EspoCrmCaseTypeOptions::class);
        $options->forgetCache();

        $mapped = $options->optionsForSelect();

        $this->assertArrayHasKey('RichiestaGenerica', $mapped);
        $this->assertFalse($options->isLoadedFromCrm());
    }
}
