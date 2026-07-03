<?php

namespace Tests\Unit;

use App\Services\ContactDeskSettings;
use App\Services\ContactSportelloMailSettings;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class ManageSportelliConfigFormStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_state_keeps_desks_when_contact_sender_settings_exist(): void
    {
        app(SiteSettingsService::class)->updateMany([
            'contact.website_from_address' => 'website@safehouse.community',
            'contact.website_from_name' => 'Safe House — sito web',
        ]);

        $values = array_merge(
            app(ContactSportelloMailSettings::class)->nestedFormValues(),
            app(SiteSettingsService::class)->nestedFormValues(),
        );

        data_set($values, 'contact.desks', app(ContactDeskSettings::class)->all());

        $desks = data_get($values, 'contact.desks');

        $this->assertIsArray($desks);
        $this->assertNotEmpty($desks);
        $this->assertSame('digital_desk', $desks[0]['key']);
        $this->assertSame('website@safehouse.community', Arr::get($values, 'contact.website_from_address'));
    }
}
