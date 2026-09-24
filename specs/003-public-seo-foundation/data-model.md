# Data model: 003 public SEO foundation

No new entity type. Search overrides are optional strings. The public index is computed, not stored.

## Discovery text

Per locale `it` and `en`.

| Field | Rules |
| :--- | :--- |
| `seo_title` | Optional. Max 70 characters. Trimmed. Empty means "use the visible title". |
| `seo_description` | Optional. Max 160 characters. Trimmed. Empty means "use the same-locale summary". |

**Page** (existing `pages.meta` JSON, no migration):

- `meta.seo_title.it`, `meta.seo_title.en`
- `meta.seo_description.it`, `meta.seo_description.en`

**Article** and **DonationCampaign** (new nullable JSON column `seo`):

```json
{
  "title": { "it": "", "en": "" },
  "description": { "it": "", "en": "" }
}
```

Reading a locale uses only that key. It does not call `PageService::localizedMeta` and does not use Spatie fallback. [laravel-translatable](https://spatie.be/docs/laravel-translatable/v6/introduction) stays the store for visible `title` / `excerpt` / `body`.

### Fallback, same locale only

1. Title: override, else visible title, else the existing lang string for that route.
2. Description: override, else excerpt or the first plain-text paragraph of the body, else the existing lead string. Strip tags. Cut at about 160 characters on a word boundary when possible.
3. If the locale has no visible title, that locale is not a public URL and is not an alternative.

## URL index entry

Not a table. Each sitemap row:

| Field | Rules |
| :--- | :--- |
| `loc` | Absolute URL from the app URL and the public path |
| `lastmod` | `updated_at` as `Y-m-d` when the record has one; hubs use the latest related `updated_at` or omit |

A locale is listed only when that locale's document exists (published page/article/campaign with a title in that locale, or a fixed route that has lang strings).

## What is indexable

| URL | In sitemap | Robots |
| :--- | :--- | :--- |
| Published CMS page with that locale's title | yes | index |
| Home, donations index, 5×1000, volunteer, news index, editorial index | yes, both locales | index |
| Active campaign and its privacy page | yes, both locales that have a title | index |
| Inactive or missing campaign | no | page itself already 404 or hidden |
| Donation thank-you | no | `noindex, nofollow` |
| `/_preview/…` | no | existing `X-Robots-Tag` plus meta robots |
| Unpublished page or article | no | not routed as 200 |

## Share image

Not a new column. First existing carousel or background image on the page, otherwise `images/logo.png`. Absolute URL.
