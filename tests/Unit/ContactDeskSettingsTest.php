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

        $this->assertCount(1, $desks);
        $this->assertSame('custom_desk', $desks[0]['key']);
        $this->assertSame('CustomCaseType', $desks[0]['case_type']);
    }
}
