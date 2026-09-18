# 025 — Specify 002 product lock (GA4 + Tag Manager) and cookie/privacy

**Date:** 2026-09-16  
**Agent:** Cursor Grok 4.6  
**Active spec:** `specs/002-consent-gated-analytics` (`.specify/feature.json`)

## Current state

Owner asked for the most suitable measurement stack by **law**, not hosting preference, plus operational cookie/privacy/consent in the same living spec (still no DPA). S01 UAT is closed.

Locked product: **Google Analytics 4 via Google Tag Manager**, consent-gated. Necessary tools automatic. Analytics off until explicit first-visit (or later reopen) accept. Association does not store raw IP for measurement. Cookie/privacy operational texts update **after** gated measurement works.

Rejected for this org: Plausible-only, Matomo-only, gtag-only, dual Plausible+GA4 (extra processor without removing the Google consent duty).

## Files

- `specs/002-consent-gated-analytics/spec.md` (changelog 2026-09-16)
- `specs/002-consent-gated-analytics/checklists/requirements.md`
- `specs/006-gdpr-operational-alignment/spec.md` (boundary: leftover GDPR)
- `specs/004-ad-grants-landings/spec.md` (consume 002 events; still no Ads pixels)
- `.specify/feature.json`

## Official pages opened this turn

- https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9677876
- https://www.garanteprivacy.it/faq/cookie
- https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9782874
- https://support.google.com/analytics/answer/3379636
- https://support.google.com/analytics/answer/12017362
- https://support.google.com/tagmanager/answer/6103696
- https://support.google.com/grants/answer/9841491
- https://developers.google.com/tag-platform/security/guides/consent
- https://laravel.com/docs/13.x/blade
- https://laravel.com/docs/13.x/localization
- https://filamentphp.com/docs/4.x/introduction/overview

## Verification

Specify-only. No plan, tasks, or implement. No DPA text. No Caddy apply. No git commit.

## Next

`/speckit-plan` for 002 (unblocked). Then `/speckit-tasks`, owner keep/change/drop table, then implement from `tasks.md`.
