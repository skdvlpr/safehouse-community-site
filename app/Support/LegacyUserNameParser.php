<?php

namespace App\Support;

final class LegacyUserNameParser
{
    /**
     * @return array{first_name: string, last_name: string, job_title: ?string}
     */
    public static function split(?string $legacyName): array
    {
        $legacyName = trim((string) $legacyName);
        $jobTitle = null;

        if (preg_match('/^(.+?)\s+\[(.+)\]$/u', $legacyName, $matches) === 1) {
            $legacyName = trim($matches[1]);
            $title = trim($matches[2]);
            $jobTitle = $title !== '' ? $title : null;
        }

        if ($legacyName === '') {
            return [
                'first_name' => '',
                'last_name' => '',
                'job_title' => $jobTitle,
            ];
        }

        $parts = preg_split('/\s+/u', $legacyName, 2) ?: [];
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? $firstName;

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'job_title' => $jobTitle,
        ];
    }
}
