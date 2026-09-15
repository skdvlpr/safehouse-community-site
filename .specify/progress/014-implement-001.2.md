# 014 — Implemented 001.2 audit repairs

**Date:** 2026-09-15  
**Command:** `/speckit-implement`  
**Directory:** `specs/001.2-audit-repairs`

## Done locally

- F-002 webhook signature/payload → 400 `Invalid signature` (Stripe PHP exceptions + RuntimeException)
- F-003/F-009 donate it+en keys; checkout JSON generic `site.donations.checkout_failed`
- F-004 volunteer Turnstile when CMS challenge is on
- F-005 `deploy/Caddyfile.snippet` `X-Frame-Options DENY` (not applied live)
- F-007 HomeController constructor injection
- F-008 PrimaNota empty catches now `Log::warning`
- F-010 `preventRequestForgery(except: ['api/webhooks/stripe'])`
- F-011/HMAC `user_agent_hash` on consents; shared `hash_hmac` helpers
- F-012 trusted proxies = loopback + RFC1918 (`TRUSTED_PROXIES` escape hatch)
- F-013 CMS last-error: no stack; secret-shape redaction
- F-014 title suffix `__('site.layout.title_suffix')`

## Verify

`ddev exec php artisan test` — 333 passed, 2 skipped  
`ddev exec ./vendor/bin/pint --test` — PASS  
Migrated: `2026_09_15_120000_add_user_agent_hash_to_gdpr_consents_table`

## Production inspect (read-only)

Contact widget Turnstile: **no** on `https://safehouse.community/it/contact` (2026-09-15). No `.env`, no Caddy reload, no Dashboard webhook change. `stripe listen` not started.

## Stop

Owner UAT (`checklists/owner-user-tests.md`). Do **not** start `002`.
