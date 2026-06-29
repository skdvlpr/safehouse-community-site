<?php

namespace Tests\Feature;

use Tests\TestCase;

class SessionConfigTest extends TestCase
{
    public function test_session_security_defaults_are_hardened(): void
    {
        $this->assertSame(120, config('session.lifetime'));
        $this->assertTrue(config('session.secure'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('strict', config('session.same_site'));
    }
}
