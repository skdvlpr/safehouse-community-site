# Contract: document head

Rendered from `resources/views/layouts/partials/discovery-head.blade.php` via the existing `@stack('head')` in `layouts/app.blade.php` ([Blade](https://laravel.com/docs/13.x/blade)).

On every public HTML response that is not a preview:

- `<title>` is the discovery title plus the existing suffix `— Safe House`.
- `<meta name="description" content="…">` is the discovery description for the URL locale.
- `<link rel="canonical" href="…">` is the current absolute URL.
- `<link rel="alternate" hreflang="it" href="…">` and `hreflang="en"` only for locales that have their own title. No `ru`.
- `og:title`, `og:description`, `og:url`, `og:locale` (`it_IT` or `en_US`) match that same text and URL.
- `og:image` is the content image when one already exists, otherwise the site logo.

On a preview response:

- `<meta name="robots" content="noindex, nofollow">`
- The existing `X-Robots-Tag: noindex, nofollow` stays.
- No alternate links that would advertise the preview URL as a translation.

Donation thank-you uses the same noindex pair and is not given alternate links.

Must not: add measurement scripts, change visible body copy, or read the other locale when this locale's override and summary are empty.
