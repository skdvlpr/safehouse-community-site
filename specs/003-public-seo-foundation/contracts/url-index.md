# Contract: URL index

## `GET /sitemap.xml`

Outside the `/{locale}` prefix ([routing](https://laravel.com/docs/13.x/routing)).

- Status 200
- `Content-Type: application/xml; charset=UTF-8` ([responses](https://laravel.com/docs/13.x/responses))
- URL set is `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">`
- Each `<url>` has `<loc>` and, when a timestamp exists, `<lastmod>`
- Membership, cookie, and privacy appear when those CMS pages are published and have a title in that locale
- Donation thank-you never appears
- `/_preview/…` never appears
- Unpublished pages and articles never appear
- A campaign privacy URL appears only for an active campaign

Publishing or unpublishing in Filament is enough. There is no separate regenerate command.

## `GET /robots.txt`

Replaces `public/robots.txt`. The static file is removed so the route is what clients receive.

```text
User-agent: *
Disallow:

Sitemap: {absolute url of /sitemap.xml}
```

`Disallow` stays empty. CMS is not a secret path to hide from a crawler that already cannot log in; previews are noindex and unlisted. Do not `Disallow: /cms-safehouse` in this feature unless the owner asks — it is outside the spec.

Must not: list a locale the document does not have, or include demo pages that are unpublished.
