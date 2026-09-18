# Quickstart: Consent-gated audience measurement (002)

Validate after implement. Do not treat this as implement. Cite [DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/).

## Prerequisites

- DDEV project running (`ddev start` allowed; do not `ddev stop`).
- Frontend current: `bash bin/dev-rebuild-frontend.sh` if CSS/JS look stale.
- Optional for dashboard UAT: owner GA4 + GTM ids in **local** `.env` only (`MEASUREMENT_ENABLED=true`, `GTM_CONTAINER_ID=GTM-…`). Never commit them.

## Automated

```bash
ddev exec php artisan test
ddev exec ./vendor/bin/pint --test
```

Expect new/updated tests in [measurement-boot](./contracts/measurement-boot.md), [consent-ui](./contracts/consent-ui.md), [conversion-events](./contracts/conversion-events.md), [policy-copy](./contracts/policy-copy.md) to pass. Legal tests assume `PageSeeder` still calls `LegalPagesSeeder` while `LegalPagesContent.php` exists.

If implement changed legal HTML: `ddev exec php artisan site:sync-legal-pages --force` on preview, then re-open `/it/cookie-policy` and `/it/privacy-policy`.

## Manual gate (no Google account required)

1. `.env` measurement **off** or empty GTM id. Open `https://safehouse-community-site.ddev.site/it`. DevTools Network: no `googletagmanager.com` / `google-analytics.com`. Banner visible on first visit.
2. Accept all. Still no GTM if kill-switch off. Site, donate, volunteer still work.
3. Set local enabled + valid id. Hard-refresh. Essentials-only (or dismiss): still no GTM. Accept analytics: GTM requests may appear (ad blockers can hide them — note Skip).
4. Footer “cookie preferences”: switch to essentials; further GTM/GA hits stop.
5. Open a **one-time** donate thank-you, a **recurring** donate thank-you, volunteer real submit, contact real submit with analytics on: matching event names in GTM preview or GA4 Realtime (not the other donate name). Repeat essentials-only: those events absent.
6. Thank-you URL with `donor_name`: page may greet the donor; the measurement marker must not include that name.

## Policy slice (after gate works)

1. Italian and English banner no longer say analytics are inactive **while** measurement is bootable.
2. `/it/cookie-policy`, `/en/cookie-policy`, `/it/privacy-policy`, `/en/privacy-policy`: product names, consent gate, no raw IP on association servers, GA cookies listed. Status line matches kill-switch.
3. No DPA/SCC contract text added. No `/ru` legal URLs.

## Owner dashboard (Skip until credentials)

GA4 reports: page views after 20 consented hits; four conversion names present (`donate_success` vs `donate_recurring_success` split). Home CRM impact numbers unchanged.

Before that UAT, walk [ga4-gtm-owner-setup.md](./checklists/ga4-gtm-owner-setup.md) (GTM GA4 tag published, signals off, key events, no `donor_name` in page_location).

## Next

Owner UAT checklist (Russian) at implement. Then owner may show cookie/privacy pages to counsel ([owner-ops](./contracts/owner-ops.md)). `/speckit-tasks` before any PHP/JS.
