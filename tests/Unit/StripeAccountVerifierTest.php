<?php

namespace Tests\Unit;

use App\Services\Payments\StripeAccountVerifier;
use Tests\TestCase;

class StripeAccountVerifierTest extends TestCase
{
    public function test_detects_test_and_live_key_modes(): void
    {
        $verifier = new StripeAccountVerifier;

        $this->assertSame('test', $verifier->keyMode('pk_test_abc'));
        $this->assertSame('test', $verifier->keyMode('sk_test_abc'));
        $this->assertSame('live', $verifier->keyMode('pk_live_abc'));
        $this->assertSame('live', $verifier->keyMode('sk_live_abc'));
        $this->assertNull($verifier->keyMode('invalid'));
    }

    public function test_all_passed_requires_only_pass_status(): void
    {
        $verifier = new StripeAccountVerifier;

        $this->assertTrue($verifier->allPassed([
            ['status' => 'pass'],
            ['status' => 'pass'],
        ]));

        $this->assertFalse($verifier->allPassed([
            ['status' => 'pass'],
            ['status' => 'fail'],
        ]));
    }
}
