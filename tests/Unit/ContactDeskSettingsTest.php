<?php

namespace Tests\Unit;

use App\Services\ContactDeskSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactDeskSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_configured_defaults_when_database_is_empty(): void
    {
        $desks = app(ContactDeskSettings::class)->all();

        $this->assertNotEmpty($desks);
        $this->assertSame('digital_desk', $desks[0]['key']);
        $this->assertSame('sportello.digitale@safehouse.community', $desks[0]['inbox']);
    }

    public function test_persists_custom_desks_to_database(): void
    {
        app(ContactDeskSettings::class)->save([
            [
                'key' => 'custom_desk',
                'label' => 'Sportello custom',
                'inbox' => 'custom@safehouse.community',
                'case_type' => 'CustomCaseType',
            ],
        ]);

        app(ContactDeskSettings::class)->forgetCache();

        $desks = app(ContactDeskSettings::class)->all();

        $this->assertCount(4, $desks);
        $this->assertSame('custom_desk', $desks[0]['key']);
        $this->assertSame('CustomCaseType', $desks[0]['case_type']);
        $this->assertContains('legal_desk', array_column($desks, 'key'));
        $this->assertContains('generic_desk', array_column($desks, 'key'));
    }

    public function test_merges_missing_default_desks_when_only_one_is_configured_in_cms(): void
    {
        app(ContactDeskSettings::class)->save([
            [
                'key' => 'digital_desk',
                'label' => 'Sportello digitale',
                'inbox' => 'sportello.digitale@safehouse.community',
                'case_type' => 'SportelloDigitale',
            ],
        ]);

        app(ContactDeskSettings::class)->forgetCache();

        $keys = array_column(app(ContactDeskSettings::class)->all(), 'key');

        $this->assertContains('digital_desk', $keys);
        $this->assertContains('legal_desk', $keys);
        $this->assertContains('generic_desk', $keys);
    }

    public function test_default_desks_include_richiesta_generica(): void
    {
        $keys = array_column(app(ContactDeskSettings::class)->all(), 'key');

        $this->assertContains('generic_desk', $keys);
        $generic = collect(app(ContactDeskSettings::class)->all())->firstWhere('key', 'generic_desk');
        $this->assertSame('RichiestaGenerica', $generic['case_type'] ?? null);
        $this->assertSame('info@safehouse.community', $generic['inbox'] ?? null);
    }
}
