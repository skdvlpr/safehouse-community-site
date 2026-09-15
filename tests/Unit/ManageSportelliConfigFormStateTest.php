<?php

namespace Tests\Unit;

use App\Services\ContactDeskSettings;
use App\Services\ContactSportelloMailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageSportelliConfigFormStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_state_keeps_desks_without_hydrating_the_full_settings_bag(): void
    {
        $values = app(ContactSportelloMailSettings::class)->nestedFormValues();
        data_set($values, 'contact.desks', app(ContactDeskSettings::class)->all());

        $desks = data_get($values, 'contact.desks');

        $this->assertIsArray($desks);
        $this->assertNotEmpty($desks);
        $this->assertSame('digital_desk', $desks[0]['key']);
        $this->assertArrayNotHasKey('turnstile', $values);
    }
}
