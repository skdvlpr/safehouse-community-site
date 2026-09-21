# Research: Production analytics ready (002.2)

**Date**: 2026-09-21

## R1 — Why live GA4 is empty

- **Decision**: Treat empty Home **and** empty Realtime on **production** as “hits never reached this property” until CSP + consent + toggle are proven. Local donate last week does not fill the live property.
- **Rationale**: 002 kill-switch default off; live Caddy snippet in git was **not** applied (`S-CSP`). Browser CSP on production can block `gtm.js` / collect even when CMS toggle is on. [GTM CSP](https://developers.google.com/tag-platform/security/guides/csp), [Realtime](https://support.google.com/analytics/answer/9271392).
- **Alternatives considered**: Enable `debug_mode` for all visitors (pollutes reports; Google says filter debug traffic). Rejected as default. Owner uses Preview/DebugView for immediate proof.

## R2 — “Realtime” vs Home

- **Decision**: Success gate = Realtime (and DebugView when Preview is connected). Home / Explorations stay delayed; this feature does not fight Google batching.
- **Rationale**: Owner asked for live proof before combined UAT. Realtime is the documented live report; it has no SLO and may lag minutes. [Realtime](https://support.google.com/analytics/answer/9271392).
- **Alternatives considered**: Custom first-party dashboard (forbidden 002). Measurement Protocol server-side (out of stack / extra keys).

## R3 — CSP host list

- **Decision**: Keep Stripe + Turnstile + `'unsafe-inline'` as today. Add Google’s **GA4 without Ads** plus **Preview Mode** hosts from the CSP guide: `www.googletagmanager.com`, `tagmanager.google.com`, `*.google-analytics.com`, `www.google.com` (connect), `ssl.gstatic.com` / `www.gstatic.com` (img), `fonts.googleapis.com` / `fonts.gstatic.com` as listed for Preview. Do **not** add doubleclick / googleadservices.
- **Rationale**: Combined UAT needs Preview on `https://safehouse.community`. [GTM CSP](https://developers.google.com/tag-platform/security/guides/csp). Matcher `@public_csp not path /cms-safehouse*` stays. [Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header).
- **Alternatives considered**: Nonce on GTM snippet (002 injects from first-party JS; nonce needs per-response Caddy/PHP — later spec). `'unsafe-eval'` (only if Custom JS variables; we do not).

## R4 — Apply mechanism

- **Decision**: Reuse `deploy/apply-caddy-site-once.sh` (backup, validate, reload, self-delete). Restored on next git deploy. Implement tries SSH as deploy + sudo; if not, print root commands.
- **Rationale**: Owner required one-shot self-delete; script already exists. Do not invent a second apply path.
- **Alternatives considered**: Manual paste into Caddyfile (error-prone). Ansible (not in stack).

## R5 — Contact event names

- **Decision**: Map desks exactly as owner named: `generic_desk` → `contact_generic_success`; `legal_desk` → `contact_slegale_success`; `digital_desk` → `contact_sdigitale_success`. Stop emitting `contact_success`. Unknown desk: no conversion flag.
- **Rationale**: Distinguishable rows in GA4 without custom dimensions. [Custom events](https://support.google.com/analytics/answer/12229021).
- **Alternatives considered**: One event + `desk` parameter (needs custom dimension; owner asked for names). Keep `contact_success` as fallback (fails distinguishability).

## R6 — GTM owner work vs site code

- **Decision**: Site pushes `dataLayer` event names. Owner creates three GA4 Event tags + Custom Event triggers, pauses/deletes `contact_success`, Publishes. Documented in `contracts/gtm-owner.md`. Agent does not access the Google account.
- **Rationale**: Owner said they will edit tags before implement UAT. [Publish](https://support.google.com/tagmanager/answer/6107163).
- **Alternatives considered**: Agent logs into GTM (no).

## R7 — Legal pages

- **Decision**: If live measurement is on, implement MAY run `site:sync-legal-pages --force` so cookie/privacy no longer say “not active”. Not a DPA.
- **Rationale**: 002 FR-009 operational match. Owner wants one UAT round.
- **Alternatives considered**: Leave old copy (contradicts toggle-on).
