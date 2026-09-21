# Data model: 002.5 post-uat polish

No new database tables. Existing entities only.

## Page (CMS)

Unchanged schema. This feature **reads**:

- Membership landing `key=diventa-socio` (template `landing`)
- `key=cookie`, `key=privacy` (template `legal`)
- `key=faq` (Statuto fallback)
- Social URLs from existing site settings (`SocialLinksSettings`)

One-shot production upsert overwrites cookie/privacy **body/title/slug/template** from `LegalPagesContent` via `site:sync-legal-pages --force`.

## Locale switch

Not stored. Target URL = current path with first segment replaced (`it` ↔ `en`) plus query string (`LocalizedUrl::forLocale`).

## Cookie consent

Existing `sh_cookie_consent` cookie + localStorage. Locale switch MUST NOT delete it.

## Contattaci link set (view-time)

| Label (IT) | Destination |
| :--- | :--- |
| Statuto | FAQ page unless a later Statuto URL is added |
| FAQ | `urlForKey('faq')` |
| Donazioni | `route('donations.index')` |
| Volontariato | `route('volunteers.show')` |

Empty social hrefs are omitted (same as footer).

## Copy keys (lang)

New `site.membership.swipe_down`, `site.membership.contact_links.*`, `site.locale.english_version`, `site.locale.italian_version`, `site.cookie.lang_it`, `site.cookie.lang_en`. Remove public use of footer `site.cookie.reopen`.
