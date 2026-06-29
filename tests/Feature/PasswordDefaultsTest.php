<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class PasswordDefaultsTest extends TestCase
{
    public function test_weak_password_fails_default_rules(): void
    {
        $validator = Validator::make(
            ['password' => 'short'],
            ['password' => ['required', Password::defaults()]],
        );

        $this->assertTrue($validator->fails());
    }

    public function test_strong_password_passes_default_rules(): void
    {
        $validator = Validator::make(
            ['password' => 'SafeHouse!2026Xy'],
            ['password' => ['required', Password::defaults()]],
        );

        $this->assertFalse($validator->fails());
    }
}
