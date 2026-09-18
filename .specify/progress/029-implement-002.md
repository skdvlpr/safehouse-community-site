# 029 — `/speckit-implement` for 002

**Date**: 2026-09-17

**Feature**: `specs/002-consent-gated-analytics`

**Input**: Owner clarified cookie “editing” = legal document (`LegalPagesContent`), then continue tasks. US5 copy last. No production CMS overwrite / Caddy apply / git commit unless asked.

## Checklist gate

| Checklist | Total | Checked | Unchecked | Status |
|-----------|-------|---------|-----------|--------|
| requirements.md | (spec quality) | all | 0 | PASS |
| ga4-gtm-owner-setup.md | owner-ops + do-not | mixed | owner-side remain | proceeded: user asked to continue; do-not boxes stay unchecked by design |

## Code (T001–T025)

- Kill-switch: `config/measurement.php`, `.env.example` (`MEASUREMENT_ENABLED=false`, empty `GTM_CONTAINER_ID`). No ids in `phpunit.xml` / git.
- `MeasurementBootService` + boot node on public `layouts/app.blade.php` only. CMS login has no boot node. Preview never bootable.
- Banner dismiss/X = essential. Footer reopen. `measurement.js`: basic consent, no noscript; ads keys stay denied; strip `donor_name` / `phone` / `email`.
- Markers: `donate_success` / `donate_recurring_success` / `volunteer_success` / `contact_success`. Honeypot success flash without measurement session flag.
- US5: IT+EN `LegalPagesContent` + banner strings from bootable vs off + legal status line. ru bodies unpublished. No DPA text. Preview sync: `php artisan site:sync-legal-pages --force`.
- CSP hosts documented on public matcher in `deploy/Caddyfile.snippet` ([Caddy header](https://caddyserver.com/docs/caddyfile/directives/header)). Live Caddy not reloaded.

Official pages used: [configuration](https://laravel.com/docs/13.x/configuration), [Blade](https://laravel.com/docs/13.x/blade), [localization](https://laravel.com/docs/13.x/localization), [testing](https://laravel.com/docs/13.x/testing), [Pint](https://laravel.com/docs/13.x/pint), [Vite](https://vite.dev/guide/), [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [Consent mode](https://developers.google.com/tag-platform/security/guides/consent), [GA4 custom events](https://support.google.com/analytics/answer/12229021), [Filament resources](https://filamentphp.com/docs/4.x/resources/overview).

## Verify

- `ddev exec ./vendor/bin/pint` — PASS
- `ddev exec php artisan test` — 349 passed, 2 skipped
- `bash bin/dev-rebuild-frontend.sh`

## Still owner-side (`ga4-gtm-owner-setup.md`, not ticked here)

- Do-not: paste snippets, commit ids, Ads/remarketing, products & services sharing, CMP gallery
- Consent overview UI; Publish if workspace unpublished; second GTM/GA4 admin
- Signals off; ads personalization off; DPT; key events after hits; DebugView PII re-check
- Local `.env` ids when ready; production `.env` + Caddy apply when asked
- Counsel after owner UAT

## Not done

- Git commit / push
- Production Caddy reload
- Production CMS overwrite
- Cursor-browser UAT (offer, wait)
