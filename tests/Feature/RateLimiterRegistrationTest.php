<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimiterRegistrationTest extends TestCase
{
    public function test_named_rate_limiters_are_registered(): void
    {
        foreach (['api', 'contact', 'volunteers', 'donations', 'gdpr'] as $name) {
            $this->assertNotNull(
                RateLimiter::limiter($name),
                "Rate limiter [{$name}] is not registered.",
            );
        }
    }
}
