<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedIfEmptyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_refuses_to_run_outside_local_environment(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $this->artisan('db:seed-if-empty')
            ->assertFailed()
            ->expectsOutputToContain('local development only');
    }
}
