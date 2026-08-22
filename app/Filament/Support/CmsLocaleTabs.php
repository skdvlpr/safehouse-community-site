<?php

namespace App\Filament\Support;

class CmsLocaleTabs
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'it' => '🇮🇹 Italiano',
            'ru' => '🇷🇺 Русский',
            'en' => '🇬🇧 English',
        ];
    }

    public static function label(string $locale): string
    {
        return self::labels()[$locale] ?? strtoupper($locale);
    }
}
