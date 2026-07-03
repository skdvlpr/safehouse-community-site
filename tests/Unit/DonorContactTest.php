<?php

namespace Tests\Unit;

use App\Support\DonorContact;
use Tests\TestCase;

class DonorContactTest extends TestCase
{
    public function test_normalizes_email_and_phone(): void
    {
        $contact = DonorContact::fromInput('  Mario@Example.COM ', '+39 333 111 2222');

        $this->assertSame('mario@example.com', $contact->email);
        $this->assertSame('+393331112222', $contact->phone);
        $this->assertTrue($contact->hasChannel());
    }

    public function test_accepts_phone_only(): void
    {
        $contact = DonorContact::fromInput(null, '3331112222');

        $this->assertNull($contact->email);
        $this->assertSame('3331112222', $contact->phone);
        $this->assertTrue($contact->hasChannel());
    }
}
