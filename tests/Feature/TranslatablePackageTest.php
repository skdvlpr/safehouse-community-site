<?php

namespace Tests\Feature;

use Spatie\Translatable\HasTranslations;
use Tests\TestCase;

class TranslatablePackageTest extends TestCase
{
    public function test_spatie_laravel_translatable_is_installed(): void
    {
        $this->assertTrue(
            trait_exists(HasTranslations::class),
            'spatie/laravel-translatable HasTranslations trait should be autoloadable.',
        );
    }
}
