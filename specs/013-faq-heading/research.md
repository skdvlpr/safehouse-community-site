# Research: FAQ heading

## Decision: Reuse the Chi siamo heading

**Rationale**: Owner asked every listed page, except Diventa socio, to use that title-and-tagline bar. [Laravel Blade](https://laravel.com/docs/13.x/blade) already has the about heading partial.

**Alternatives considered**: A new heading style (rejected).

## Decision: Wide screen only for the extra columns

**Rationale**: Two-column form or side-by-side donation blocks apply from a wide screen. Phones stay stacked. [Tailwind max-width](https://tailwindcss.com/docs/max-width) is not used to shrink the page.

**Alternatives considered**: Always two columns (rejected on phones).
