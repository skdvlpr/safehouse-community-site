# Owner-ops: Consent-gated measurement (002)

Cite: [DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/), [GTM account](https://support.google.com/tagmanager/answer/6103696), [Consent mode](https://developers.google.com/tag-platform/security/guides/consent), [DPT](https://support.google.com/analytics/answer/3379636), [GA4 EU IP](https://support.google.com/analytics/answer/12017362), [Ad Grants conversions](https://support.google.com/grants/answer/9841491), [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header).

## Agent MUST NOT (this plan/tasks/implement)

- `git push`, production `.env`, production CMS overwrite, production Caddy reload — unless the owner asks in that instruction
- Author a DPA, SCC contract, or DPIA
- Put `GTM-` / GA4 ids in git, specs, or chat
- Install Google Ads / remarketing tags
- `ddev stop`, `php artisan config:cache` in DDEV
- Write `/home/skoksharov/safehouse/nonprofit-espocrm`

## Local preview (implement session)

1. Keep `MEASUREMENT_ENABLED=false` until gate + copy are ready to try.
2. For dashboard UAT, set local `.env` only, then rebuild frontend if JS changed: `bash bin/dev-rebuild-frontend.sh`.
3. After legal HTML edits: `ddev exec php artisan site:sync-legal-pages --force`.
4. `ddev exec php artisan test` and Pint.

Living checklist (wizard already started 2026-09-16; remaining Admin ticks): [checklists/ga4-gtm-owner-setup.md](../checklists/ga4-gtm-owner-setup.md). **Hit proof (Home zeros are not UAT):** [checklists/ga4-hit-verification.md](../checklists/ga4-hit-verification.md). Re-open both before UAT and before production.

Local measurement after 002.1: staff toggle is CMS Impostazioni → Integrazioni (not `.env`). Env remains fallback only.

## Google accounts (owner)

1. Create a GA4 property and a GTM **web** container for `safehouse.community`.
2. In GTM: GA4 Configuration tag; triggers after consent (container will only load after site consent anyway). Map dataLayer events `donate_success`, `donate_recurring_success`, `volunteer_success`, `contact_success`. Publish the container.
3. In GA4: mark those three as key events when ready. Google signals **off**. Ads personalization **off**. Do not enable “Google products and services” sharing. Do not collect user-provided data.
4. Accept Google Ads Data Processing Terms in Analytics admin if the UI still requires a click (EEA often already incorporated).
5. Ads-UI **import** of key events is later ops (Grants). Not an on-site pixel in 002.

## Production when the owner later asks to publish

1. Deploy the git revision the owner triggers.
2. Set production measurement in **live CMS** (toggle + `GTM-…`). Env `MEASUREMENT_ENABLED` is fallback only.
3. Sync or paste legal pages if production CMS still has “analytics not active”.
4. **Caddy CSP** (owner/root): allow Tag Manager / Analytics hosts on the **public** matcher only (not `/cms-safehouse`). Update `deploy/Caddyfile.snippet` in git first; apply live only when asked. Suggested directives to merge into the existing public CSP string: `script-src` / `img-src` / `connect-src` additions for `https://www.googletagmanager.com https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com` (confirm against Google’s current list at apply time).
5. Without that CSP apply, production may block GTM even with correct `.env`.

## Counsel (after owner UAT, not during implement)

Owner sends Italian and English cookie + privacy pages (and banner) to lawyers. They may ask to correct wording. Capture corrections in `002.K` or `006`. Do not block 002 UAT on a legal memo.
