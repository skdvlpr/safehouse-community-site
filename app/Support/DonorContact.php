<?php

namespace App\Support;

readonly class DonorContact
{
    public function __construct(
        public ?string $email,
        public ?string $phone,
    ) {}

    public static function fromInput(?string $email, ?string $phone): self
    {
        return new self(
            self::normalizeEmail($email),
            self::normalizePhone($phone),
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

    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', $phone) ?? '';
        $digits = trim($digits);

        return $digits !== '' ? $digits : null;
    }
}
