# 018 — Implemented 001.3 CMS captcha settings

**Date:** 2026-09-15  
**Command:** `/speckit-implement`  
**Directory:** `specs/001.3-cms-captcha-settings`  
**Tasks:** T001–T012 marked `[X]` in `tasks.md`

## Official docs (this implement)

- [Filament v4 custom pages](https://filamentphp.com/docs/4.x/navigation/custom-pages) — Settings page, slug, `canAccess`, subheading
- [Filament v4 text input](https://filamentphp.com/docs/4.x/forms/text-input) — password + revealable secret
- [Filament v4 testing](https://filamentphp.com/docs/4.x/testing/overview) — Livewire `test()` of the custom page
- [Laravel testing](https://laravel.com/docs/13.x/testing) — `ddev exec php artisan test`
- [Laravel Pint](https://laravel.com/docs/13.x/pint) — `ddev exec ./vendor/bin/pint --test`
- [Laravel encryption](https://laravel.com/docs/13.x/encryption) — existing `turnstile.secret_key` encrypted at rest
- [Cloudflare Turnstile get started](https://developers.cloudflare.com/turnstile/get-started/) — widget does not require Cloudflare DNS/proxy; secret stays server-side

## What changed

- New Filament page `ManageCaptchaSettings` at `/cms-safehouse/captcha` (Settings, sort 98, super-admin). Saves only `turnstile.enabled` / `site_key` / `secret_key` via `updateMany`.
- Sportelli no longer mounts or persists `turnstile.*`; leftover Livewire bag is stripped. Placeholder points to Impostazioni → Captcha.
- CMS copy it+en: nav **Captcha**, notification, Sportelli/Integrations helper no longer says captcha lives on Sportelli.
- Feature test: blank secret save does not wipe; enabled + empty site key ⇒ `TurnstileVerifier::enabled()` false.
- Public Blade / Form Requests / `TurnstileVerifier` unchanged.

## Verify

- PHPUnit: 335 passed, 2 skipped ([Laravel testing](https://laravel.com/docs/13.x/testing)).
- Pint: 358 files PASS ([Laravel Pint](https://laravel.com/docs/13.x/pint)).
- Local DDEV HTTP (from inside the web container): widget+api.js on `/it|en/contact` and `/it|en/volunteers`; **no** widget on `/it/donations`; `secret_key` / `secretKey` absent in those HTML documents.
- Local `turnstile.*` rows still present (enabled, site key and encrypted secret non-empty). Verifier enabled. Not wiped.

## Production inspect (read-only)

Contact widget Turnstile: **no** on `https://safehouse.community/it/contact` (HTTP 200, 2026-09-15). No production CMS/`.env`, no Caddy reload, no Cloudflare zone onboard.

## Stop

Owner UAT (`checklists/owner-user-tests.md`) + Russian script in chat. Do **not** start `002`. Commit only if the owner asks.
