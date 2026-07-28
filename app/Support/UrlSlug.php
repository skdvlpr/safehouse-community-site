<?php

namespace App\Support;

use Illuminate\Support\Str;

final class UrlSlug
{
    /**
     * Build a URL-safe slug from a human label (name/title).
     * Strips accents/Cyrillic via language rules, then keeps only [a-z0-9-].
     */
    public static function from(?string $source, ?string $locale = null): string
    {
        $source = trim(strip_tags((string) $source));
        if ($source === '') {
            return '';
        }

        $language = match ($locale) {
            'ru' => 'ru',
            'it' => 'it',
            'en' => 'en',
            default => 'en',
        };

        $slug = Str::slug($source, '-', $language);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\-]+/', '', $slug) ?? '';
        $slug = preg_replace('/-+/', '-', $slug) ?? '';

        return trim($slug, '-');
    }

    /**
     * @param  callable(string): bool  $exists
     */
    public static function unique(string $base, callable $exists, int $maxAttempts = 200): string
    {
        if ($base === '') {
            return '';
        }

        if (! $exists($base)) {
            return $base;
        }

        for ($i = 2; $i <= $maxAttempts; $i++) {
            $candidate = $base.'-'.$i;
            if (! $exists($candidate)) {
                return $candidate;
            }
        }

        return $base.'-'.Str::lower(Str::random(6));
    }
}
