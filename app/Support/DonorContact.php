<?php

namespace App\Support;

readonly class DonorContact
{
    private const DEFAULT_COUNTRY_CODE = '39';

    public function __construct(
        public ?string $email,
        public ?string $phone,
    ) {}

    public static function fromInput(?string $email, ?string $phone, ?string $phoneCountryCode = null): self
    {
        return new self(
            self::normalizeEmail($email),
            self::normalizePhone($phone, $phoneCountryCode),
        );
    }

    public function hasChannel(): bool
    {
        return $this->email !== null || $this->phone !== null;
    }

    public static function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $email = strtolower(trim($email));

        return $email !== '' ? $email : null;
    }

    /**
     * Normalize to E.164 (+39…) for CRM storage and lookup.
     */
    public static function normalizePhone(?string $phone, ?string $countryCode = null): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        $compact = preg_replace('/[\s\-\(\)]+/', '', $phone) ?? '';
        if ($compact === '') {
            return null;
        }

        if (str_starts_with($compact, '+')) {
            $e164 = '+'.preg_replace('/\D/', '', substr($compact, 1));

            return self::isValidE164($e164) ? $e164 : null;
        }

        if (str_starts_with($compact, '00')) {
            $e164 = '+'.substr(preg_replace('/\D/', '', $compact) ?? '', 2);

            return self::isValidE164($e164) ? $e164 : null;
        }

        $digits = preg_replace('/\D/', '', $compact) ?? '';
        if ($digits === '') {
            return null;
        }

        $dialCode = preg_replace('/\D/', '', $countryCode ?? '') ?: self::DEFAULT_COUNTRY_CODE;

        if (str_starts_with($digits, '0')) {
            $digits = ltrim($digits, '0');
        }

        $e164 = '+'.$dialCode.$digits;

        return self::isValidE164($e164) ? $e164 : null;
    }

    /**
     * Digits-only key used by EspoCRM phoneNumberNumeric search.
     */
    public static function phoneNumericKey(string $e164Phone): string
    {
        return preg_replace('/\D/', '', $e164Phone) ?? '';
    }

    public static function isValidE164(string $number): bool
    {
        return (bool) preg_match('/^\+[1-9]\d{7,14}$/', $number);
    }
}
