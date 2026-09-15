# 009 — Implemented 001.1 prod parity replan

**Date:** 2026-09-13  
**Command:** `/speckit-implement`  
**Directory:** `specs/001.1-prod-parity-replan`  
**Tasks:** T001–T030 marked `[x]` in `tasks.md`

## Official docs (this implement)

- [Laravel Artisan](https://laravel.com/docs/13.x/artisan) — `site:export-cms-pages` / `site:import-cms-pages`
- [Laravel encryption](https://laravel.com/docs/13.x/encryption) — CMS `stripe.secret` via `Crypt::encryptString`
- [Laravel testing](https://laravel.com/docs/13.x/testing) — `ddev exec php artisan test`
- [Laravel Pint](https://laravel.com/docs/13.x/pint) — `ddev exec ./vendor/bin/pint --test`
- [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header) — cited; not applied on production
- [Stripe webhook signatures](https://docs.stripe.com/webhooks/signatures) — deferred to 001.2
- [Stripe Checkout](https://docs.stripe.com/payments/checkout) — deferred (later spec + `S-STRIPE` amend)
- [Filament 4.x](https://filamentphp.com/docs/4.x)
- [Spatie translatable v6](https://spatie.be/docs/laravel-translatable/v6/installation-setup)

## What changed

- Constitution **2.0.0** `S-I18N`: public UI Italian primary + English; constitution text stays English; no public `/ru`. `S-STRIPE` unchanged.
- Local published CMS pages imported from production dump (`database/seeders/data/deploy-pages.php`). Import command refuses non-local `APP_ENV`.
- Demo landing unpublished (`PageSeeder` + live local row). `/it/landing-example` 404.
- Filament/services locale fallbacks use `config('locales.available')` (`it`, `en`).
- Stripe key material removed from `database/seeders/data/local-integrations.php` (working tree). No history rewrite.
- Specs 002–006 remapped. Outline: `specs/001.1-prod-parity-replan/contracts/001.2-outline.md`.
- Navigation test no longer expects unpublished demo landing in the menu.

## Verification

- PHPUnit: 326 passed, 2 skipped ([Laravel testing](https://laravel.com/docs/13.x/testing)).
- Pint: 353 files PASS ([Laravel Pint](https://laravel.com/docs/13.x/pint)).
- Local DDEV HTTP: `/it/diventa-socio` 200, `/it/landing-example` 404, `/ru` 404, `/it/donations/5-per-thousand` 200.
- Production not written. No `ddev stop`. Local `.env` not committed. Production `.env` not read.

## Secrets note (local only)

Local `.env` has no Stripe keys. CMS `stripe.secret` / webhook secret are encrypted at rest (`config/site_settings.php` `encrypted => true` → `Crypt::encryptString`, [Laravel encryption](https://laravel.com/docs/13.x/encryption)). Test-mode `sk_test_` / `pk_test_` in GitHub alert: owner accepted; strip working tree only.

## Owner UAT (agent browser 2026-09-13)

Owner asked the agent to drive Cursor-browser locally and compare public production. `checklists/owner-user-tests.md` ticked UAT001–UAT011 with notes. Do **not** start 001.2 until the owner confirms those notes (EN stub, local dummy 5×1000 CF, extra local campaigns). Do not raise `stripe listen` until 001.2. Do not apply Caddy on production (owner-ops).

## Local page re-sync 2026-09-14

Production was not written. Pages re-dumped via SSH stdout JSON (no server files), imported locally ([Laravel Artisan](https://laravel.com/docs/13.x/artisan)). Extra local page `Soci` deleted. Donation campaigns and `site_settings` / `.env` unchanged. Payload compare vs production: 9/9 match.
