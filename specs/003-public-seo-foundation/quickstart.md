# Quickstart: 003 public SEO foundation

Validate after implementation. Do not treat this file as the implementation.

Prerequisites: DDEV up, `ddev exec php artisan migrate`, pages seeded.

## Head

1. Open `/it` and `/en`. Each `<title>` and `<meta name="description">` is in that language. They are not the same string.
2. Open `/it/about-us` (or the current about slug) and view source. `link rel="alternate"` lists Italian and English only if both titles exist. `og:locale` is `it_IT`.
3. In Filament (`/cms-safehouse`), set the Italian search description on one CMS page, leave English empty, publish. `/it/…` shows the override. `/en/…` does not show the Italian sentence.
4. Open a signed preview. Response has `X-Robots-Tag: noindex, nofollow` and `<meta name="robots" content="noindex, nofollow">`.

## Index

1. `GET /sitemap.xml` is XML and contains the donate hub, volunteer page, 5×1000, about, and contact for locales that exist.
2. Unpublish a CMS page, fetch the sitemap again, that URL is gone.
3. A preview path and a donation thank-you path are absent.
4. `GET /robots.txt` contains `Sitemap:` and the sitemap URL.

## Commands

```bash
ddev exec php artisan test --filter=DiscoveryHeadTest
ddev exec php artisan test --filter=SitemapTest
```

Cite: [Laravel testing](https://laravel.com/docs/13.x/testing).

Owner UAT is still required after tests. Do not push from this guide.
