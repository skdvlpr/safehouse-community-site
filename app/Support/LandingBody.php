<?php

namespace App\Support;

final class LandingBody
{
    /**
     * @return list<string>
     */
    public static function sections(?string $html): array
    {
        $html = trim((string) $html);

        if ($html === '') {
            return [];
        }

        $parts = preg_split('/<hr\s*\/?>/i', $html) ?: [];

        $sections = [];

        foreach ($parts as $part) {
            $trimmed = trim($part);

            if ($trimmed !== '') {
                $sections[] = $trimmed;
            }
        }

        return $sections;
    }
}
