<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Container\Container;

final class LandingContent
{
    public const MAX_CARDS = 6;

    /**
     * @param  list<string>  $values
     * @param  list<string>  $cards
     */
    public function __construct(
        public readonly string $intro,
        public readonly array $values,
        public readonly array $cards,
        public readonly string $contactHeading,
    ) {}

    public static function fromPage(Page $page, string $locale, ?string $body): self
    {
        $fromMeta = self::fromMeta(is_array($page->meta) ? $page->meta : [], $locale, $body);

        if ($fromMeta !== null) {
            return $fromMeta;
        }

        return self::fromHtml((string) $body);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function hydrateForm(array $data): array
    {
        if (($data['template'] ?? '') !== 'landing') {
            return $data;
        }

        $meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];

        if (self::hasStructuredCards($meta)) {
            return $data;
        }

        $locales = self::cmsLocales();
        $cards = [];
        $values = [];

        foreach ($locales as $locale) {
            if (! is_string($locale) || $locale === '') {
                continue;
            }

            $parsed = self::fromHtml((string) ($data['body'][$locale] ?? ''));
            $data['body'][$locale] = $parsed->intro;

            if ($parsed->contactHeading !== '') {
                $meta['landing_contact_heading'][$locale] = $parsed->contactHeading;
            }

            foreach ($parsed->values as $index => $label) {
                $values[$index]['label'][$locale] = $label;
            }

            foreach ($parsed->cards as $index => $cardHtml) {
                $cards[$index]['body'][$locale] = $cardHtml;
                $heading = self::headingText($cardHtml);
                if ($heading !== '') {
                    $cards[$index]['title'][$locale] = $heading;
                }
            }
        }

        $meta['landing_values'] = array_values($values);
        $meta['landing_cards'] = array_values($cards);
        $data['meta'] = $meta;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function fromMeta(array $meta, string $locale, ?string $body): ?self
    {
        if (! self::hasStructuredCards($meta)) {
            return null;
        }

        $intro = trim((string) $body);
        $values = [];

        foreach ($meta['landing_values'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = self::pickLocalized($row['label'] ?? null, $locale);

            if ($label !== null) {
                $values[] = $label;
            }
        }

        $cards = [];

        foreach ($meta['landing_cards'] ?? [] as $row) {
            if (count($cards) >= self::MAX_CARDS) {
                break;
            }

            if (! is_array($row)) {
                continue;
            }

            $html = self::pickLocalized($row['body'] ?? null, $locale) ?? '';
            $title = self::pickLocalized($row['title'] ?? null, $locale);

            if ($html === '' && $title === null) {
                continue;
            }

            if ($title !== null && $title !== '' && ! str_contains($html, '<h')) {
                $html = '<h3>'.e($title).'</h3>'.$html;
            }

            $cards[] = $html;
        }

        $contact = self::pickLocalized($meta['landing_contact_heading'] ?? null, $locale)
            ?? __('site.membership.contact_heading', [], $locale);

        return new self($intro, $values, $cards, $contact);
    }

    public static function fromHtml(string $html): self
    {
        $sections = LandingBody::sections($html);
        $intro = $sections[0] ?? '';
        $values = [];
        $cards = [];
        $contactHeading = '';

        foreach (array_slice($sections, 1) as $section) {
            $heading = self::headingText($section);

            if (self::isValuesHeading($heading)) {
                $values = self::valueLabelsFromHtml($section);

                continue;
            }

            if (self::isContactHeading($heading)) {
                if (preg_match('/contattaci|contact us/i', $heading) === 1 || $contactHeading === '') {
                    $contactHeading = $heading;
                }

                continue;
            }

            if (count($cards) < self::MAX_CARDS) {
                $cards[] = $section;
            }
        }

        return new self($intro, $values, $cards, $contactHeading);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function hasStructuredCards(array $meta): bool
    {
        $cards = $meta['landing_cards'] ?? null;

        return is_array($cards) && $cards !== [];
    }

    public static function headingText(string $html): string
    {
        if (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $html, $match) !== 1) {
            return '';
        }

        return trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * @return list<string>
     */
    public static function valueLabelsFromHtml(string $html): array
    {
        preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $html, $matches);

        $labels = [];

        foreach ($matches[1] as $item) {
            $text = trim(html_entity_decode(strip_tags((string) $item), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

            if ($text !== '' && mb_strlen($text) <= 40 && is_array($words) && count($words) <= 2) {
                $labels[] = $text;
            }
        }

        return $labels;
    }

    private static function isValuesHeading(string $heading): bool
    {
        return $heading !== '' && preg_match('/valori|values/i', $heading) === 1;
    }

    private static function isContactHeading(string $heading): bool
    {
        return $heading !== '' && preg_match('/contattaci|contact us|unisciti|join us/i', $heading) === 1;
    }

    private static function pickLocalized(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        if (! is_array($value)) {
            return null;
        }

        foreach ([$locale, 'it'] as $key) {
            $candidate = $value[$key] ?? null;

            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function cmsLocales(): array
    {
        $container = Container::getInstance();

        if ($container->bound('config')) {
            $locales = $container->make('config')->get('locales.available', ['it', 'en']);

            if (is_array($locales) && $locales !== []) {
                return array_values(array_filter($locales, 'is_string'));
            }
        }

        return ['it', 'en'];
    }
}
