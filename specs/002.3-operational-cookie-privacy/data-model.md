# Data model: Operational cookie and privacy pages

No new tables.

Existing `pages` rows `key = privacy` and `key = cookie` keep `template = legal`, published, slugs `privacy-policy` / `cookie-policy` (it+en). Bodies are HTML translations.

Unpublished Russian translation keys may exist in the seeder array; they MUST NOT gain a public `/ru` route.

Measurement status line stays a Blade include, not CMS HTML.
