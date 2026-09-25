<?php

namespace App\Support;

final class MembershipLandingCardOrder
{
    /**
     * Swap the “who can join” card with the “what membership means” card when
     * the who-can card currently comes first (site cards 04 and 06).
     *
     * https://laravel.com/docs/13.x/migrations
     */
    public static function swapHtml(string $html): string
    {
        $sections = LandingBody::sections($html);

        if ($sections === []) {
            return $html;
        }

        $who = self::indexOf($sections, self::sectionIsWhoCan(...));
        $means = self::indexOf($sections, self::sectionIsWhatItMeans(...));

        if ($who === null || $means === null || $who >= $means) {
            return $html;
        }

        [$sections[$who], $sections[$means]] = [$sections[$means], $sections[$who]];

        return implode('<hr>', $sections);
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @return list<array<string, mixed>>
     */
    public static function swapMetaCards(array $cards): array
    {
        $who = self::indexOf($cards, self::isWhoCanCard(...));
        $means = self::indexOf($cards, self::isWhatItMeansCard(...));

        if ($who === null || $means === null || $who >= $means) {
            return $cards;
        }

        [$cards[$who], $cards[$means]] = [$cards[$means], $cards[$who]];

        return array_values($cards);
    }

    /**
     * @param  list<mixed>  $items
     * @param  callable(mixed): bool  $match
     */
    private static function indexOf(array $items, callable $match): ?int
    {
        foreach ($items as $index => $item) {
            if ($match($item)) {
                return (int) $index;
            }
        }

        return null;
    }

    private static function sectionIsWhoCan(mixed $section): bool
    {
        return is_string($section) && self::isWhoCanHeading(LandingContent::headingText($section));
    }

    private static function sectionIsWhatItMeans(mixed $section): bool
    {
        return is_string($section) && self::isWhatItMeansHeading(LandingContent::headingText($section));
    }

    private static function isWhoCanCard(mixed $card): bool
    {
        return is_array($card) && self::isWhoCanHeading(self::cardHeading($card));
    }

    private static function isWhatItMeansCard(mixed $card): bool
    {
        return is_array($card) && self::isWhatItMeansHeading(self::cardHeading($card));
    }

    /**
     * @param  array<string, mixed>  $card
     */
    private static function cardHeading(array $card): string
    {
        foreach (['it', 'en'] as $locale) {
            $title = $card['title'][$locale] ?? null;

            if (is_string($title) && trim($title) !== '') {
                return trim($title);
            }
        }

        $body = $card['body']['it'] ?? $card['body']['en'] ?? '';

        return is_string($body) ? LandingContent::headingText($body) : '';
    }

    private static function isWhoCanHeading(string $heading): bool
    {
        return preg_match('/^Chi può diventare socio\??$/u', $heading) === 1
            || preg_match('/^Who can become a member\??$/iu', $heading) === 1;
    }

    private static function isWhatItMeansHeading(string $heading): bool
    {
        return preg_match('/^Cosa significa essere socio\??$/u', $heading) === 1
            || preg_match('/^What does it mean to be a member\??$/iu', $heading) === 1;
    }
}
