<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalesConfigTest extends TestCase
{
    public function test_default_locale_is_italian(): void
    {
        $this->assertSame('it', config('locales.default'));
    }

    public function test_available_locales_include_it_and_en_in_order(): void
    {
        $this->assertSame(['it', 'en'], config('locales.available'));
    }

    public function test_default_locale_is_in_available_list(): void
    {
        $this->assertContains(config('locales.default'), config('locales.available'));
    }
}
