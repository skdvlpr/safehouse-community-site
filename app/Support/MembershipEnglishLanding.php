<?php

namespace App\Support;

final class MembershipEnglishLanding
{
    public static function splitParagraphHeadings(string $html): string
    {
        if ($html === '' || preg_match('/<hr\b/i', $html) === 1) {
            return $html;
        }

        $count = 0;
        $split = preg_replace_callback(
            '/<p>\s*<strong>(.*?)<\/strong>\s*<\/p>/is',
            static function (array $match) use (&$count): string {
                $count++;
                $title = $match[1];
                if ($count === 1) {
                    return '<h2>'.$title.'</h2>';
                }

                $block = '<hr><h3>'.$title.'</h3>';
                if (preg_match('/our values/i', strip_tags($title)) === 1) {
                    $block .= '<ul><li>Solidarity</li><li>Participation</li><li>Inclusion</li><li>Transparency</li></ul>';
                }

                return $block;
            },
            $html,
        );

        return is_string($split) ? $split : $html;
    }
}
