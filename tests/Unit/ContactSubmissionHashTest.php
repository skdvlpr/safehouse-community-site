<?php

namespace Tests\Unit;

use App\Services\ContactSubmissionService;
use Tests\TestCase;

class ContactSubmissionHashTest extends TestCase
{
    public function test_ip_hash_is_hmac_not_unsalted_sha256(): void
    {
        $ip = '127.0.0.1';
        $hashed = ContactSubmissionService::hashIp($ip);

        $this->assertSame(hash_hmac('sha256', $ip, (string) config('app.key')), $hashed);
        $this->assertNotSame(hash('sha256', $ip), $hashed);
    }

    public function test_user_agent_hash_is_hmac_not_unsalted_sha256(): void
    {
        $ua = 'Safehouse Test Agent';
        $hashed = ContactSubmissionService::hashUserAgent($ua);

        $this->assertSame(hash_hmac('sha256', $ua, (string) config('app.key')), $hashed);
        $this->assertNotSame(hash('sha256', $ua), $hashed);
    }
}
