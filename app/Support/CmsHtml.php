<?php

namespace App\Support;

/**
 * Renders CMS HTML the same way everywhere (RichEditor + legacy plain text).
 */
final class CmsHtml
{
    public static function render(?string $content): string
    {
        if ($content === null) {
            return '';
        }

        $trimmed = trim($content);

        if ($trimmed === '') {
            return '';
        }

        // TipTap / RichEditor already stores HTML tags.
        if (preg_match('/<[a-z!][\s\S]*>/i', $trimmed) === 1) {
            return (string) preg_replace(
                '/<table\b[^>]*>.*?<\/table>/is',
                '<div class="prose-table-scroll">$0</div>',
                $content,
            );
        }

        return nl2br(e($content));
    }
}
