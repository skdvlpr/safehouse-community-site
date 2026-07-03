<?php

namespace App\Support;

use App\Services\ContactDeskSettings;

class ContactDeskOptions
{
    /**
     * @return array<string, string>
     */
    public static function forForm(): array
    {
        return app(ContactDeskSettings::class)->labelsForForm();
    }

    public static function isKnownDesk(string $desk): bool
    {
        return self::deskConfig($desk) !== null;
    }

    /**
     * @return array{key: string, label: string, inbox: string, case_type: string}|null
     */
    public static function deskConfig(string $desk): ?array
    {
        return app(ContactDeskSettings::class)->find($desk);
    }

    public static function caseTypeForDesk(string $desk): ?string
    {
        $config = self::deskConfig($desk);

        if ($config === null) {
            return null;
        }

        $caseType = trim($config['case_type']);

        return $caseType !== '' ? $caseType : null;
    }

    /**
     * @return list<string>
     */
    public static function deskKeys(): array
    {
        return array_keys(app(ContactDeskSettings::class)->labelsForForm());
    }
}
