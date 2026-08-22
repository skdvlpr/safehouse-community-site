<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

final class CanonicalSlug
{
    /**
     * @param  array<string, string|null>  $sources
     */
    public static function fromTitleSources(array $sources): string
    {
        foreach (['en', 'it'] as $locale) {
            $source = $sources[$locale] ?? null;

            if (is_string($source) && trim($source) !== '') {
                return UrlSlug::from($source);
            }
        }

        foreach ($sources as $source) {
            if (is_string($source) && trim($source) !== '') {
                return UrlSlug::from($source);
            }
        }

        return '';
    }

    /**
     * @return list<string>
     */
    public static function locales(): array
    {
        return array_values(config('locales.available', ['it', 'en']));
    }

    /**
     * @param  array<string, string|null>  $translations
     */
    public static function resolve(array $translations): ?string
    {
        foreach (array_merge(['en', 'it'], self::locales()) as $locale) {
            $slug = $translations[$locale] ?? null;

            if (is_string($slug) && $slug !== '') {
                return $slug;
            }
        }

        foreach ($translations as $slug) {
            if (is_string($slug) && $slug !== '') {
                return $slug;
            }
        }

        return null;
    }

    public static function resolveFromModel(Model $model, string $attribute): ?string
    {
        if (! method_exists($model, 'getTranslations')) {
            $value = $model->getAttribute($attribute);

            return is_string($value) && $value !== '' ? $value : null;
        }

        /** @var array<string, string|null> $translations */
        $translations = $model->getTranslations($attribute);

        return self::resolve($translations);
    }

    /**
     * @return array<string, string>
     */
    public static function replicateForLocales(string $slug): array
    {
        if ($slug === '') {
            return [];
        }

        $out = [];

        foreach (self::locales() as $locale) {
            $out[$locale] = $slug;
        }

        return $out;
    }
}
