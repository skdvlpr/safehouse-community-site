# Contract: Operational cookie and privacy copy (002.3)

This is **visitor-facing operational copy**. It is **not** a DPA. Cite [GA4 cookies](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage), [Turnstile widgets](https://developers.cloudflare.com/turnstile/concepts/widget/), [Stripe Privacy Policy](https://stripe.com/privacy), [Google Workspace privacy](https://support.google.com/a/answer/60762), [Filament resources](https://filamentphp.com/docs/4.x/resources/overview).

## Must include (IT + EN)

- Controller: Safe House ETS, CF 96629270586, https://safehouse.community, info@safehouse.community, sede Italia
- Hosting: Aruba Cloud VPS, Italy (site and internal systems on that server)
- Mail: Google Workspace for Nonprofits
- Payments: Stripe; card data do not pass through association servers (keep English sentence `Card data does not pass through our servers`)
- Contact: name, email, message, desk; stored; emailed to desk + copy to sender; may join internal desk files
- Volunteer: name, last name, email, phone, message; staff mail + applicant confirmation
- Donations: identity/contact/amount/comment to Stripe and internal accounting
- Cookie audit: hashed identifiers, choice, time; no raw IP kept for measurement
- Turnstile on contact and volunteer (security)
- GA4 via Tag Manager after Accetta tutti or Salva preferenze; aggregated stats + conversions; ads/remarketing not used
- Staff Calendar/Drive section unchanged in substance (`id="google-api-services"`, `drive.file`, Google Calendar, staff area)
- No advertising of name/phone/email to third parties; confidentiality
- Cookie UX: Accetta tutti / Solo necessari / Preferenze (analytics proposed on, can uncheck) / X = necessary; reopen footer + cookie page button; lasts until change or clear cookies
- Cookie table: `XSRF-TOKEN`, `safe-house-community-session`, `sh_cookie_consent`, Stripe on checkout, Turnstile on protected forms, `_ga` / `_ga_*`, marketing **Non in uso** (IT)

## Must not include

- DPA / Data Processing Agreement / Addendum / DPIA
- EspoCRM, crm.safehouse.community, Laravel
- Aruba as mail processor
- `contact_success` as an analytics event
- demo, da approvare, bozza, “in questa versione”

## Sync

Local: `ddev exec php artisan site:sync-legal-pages --force`

Production: only when the owner asks.
