<?php

namespace Tests\Unit;

use App\Support\DonorContact;
use Tests\TestCase;

class DonorContactTest extends TestCase
{
    public function test_normalizes_email_and_e164_phone_with_plus(): void
    {
        $contact = DonorContact::fromInput('  Mario@Example.COM ', '+39 333 111 2222');

        $this->assertSame('mario@example.com', $contact->email);
        $this->assertSame('+393331112222', $contact->phone);
        $this->assertTrue($contact->hasChannel());
    }

    public function test_normalizes_local_italian_phone_with_default_country_code(): void
    {
        $contact = DonorContact::fromInput(null, '333 111 2222', '39');

        $this->assertNull($contact->email);
        $this->assertSame('+393331112222', $contact->phone);
    }

    public function test_normalizes_phone_starting_with_zero(): void
    {
        $contact = DonorContact::fromInput(null, '0333 111 2222');

        $this->assertSame('+393331112222', $contact->phone);
    }

    public function test_phone_numeric_key_strips_non_digits(): void
    {
        $this->assertSame('393331112222', DonorContact::phoneNumericKey('+39 333 111 2222'));
    }

    public function test_normalizes_pasted_italian_mobile_with_country_prefix(): void
    {
        $contact = DonorContact::fromInput('donor@example.com', '+39 3202696323');

        $this->assertSame('+393202696323', $contact->phone);
        $this->assertTrue($contact->hasChannel());
    }

    public function test_normalizes_italian_mobile_without_prefix_using_country_code(): void
    {
        $contact = DonorContact::fromInput(null, '320 269 6323', '39');

        $this->assertSame('+393202696323', $contact->phone);
    }

    public function test_rejects_invalid_phone(): void
    {
        $contact = DonorContact::fromInput(null, 'abc');

        $this->assertNull($contact->phone);
        $this->assertFalse($contact->hasChannel());
    }
}
