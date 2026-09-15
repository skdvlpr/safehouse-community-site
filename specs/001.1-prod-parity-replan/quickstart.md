# Quickstart: validate 001.1 (after implement)

This feature changes local content, constitution locale law, later specs, and the local-integrations seeder. It must **not** change production or checkout.

Docs: [Laravel testing](https://laravel.com/docs/13.x/testing) · [Laravel Pint](https://laravel.com/docs/13.x/pint) · [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header) · [Spatie translatable](https://spatie.be/docs/laravel-translatable/v6/installation-setup)

## Prerequisites

- DDEV already up. **Do not** `ddev stop`.
- Production inspect contract: [contracts/production-inspect-2026-09-13.md](./contracts/production-inspect-2026-09-13.md)
- Optional refresh inspect: [contracts/ssh-allowlist.md](./contracts/ssh-allowlist.md) only

## 1. Working tree has no Stripe key material

```bash
rg -n "sk_test_|pk_test_|sk_live_|pk_live_" database/seeders/data/local-integrations.php
ddev exec php artisan test --filter=LocalIntegrations
```

Expect: no matches in that file (placeholders/comments without live-looking keys). The new test passes. Docs: https://laravel.com/docs/13.x/testing

## 2. Local published pages match the snapshot

On DDEV, confirm HTTP:

- `/it/diventa-socio` 200
- `/it/landing-example` not a public official page (404 or unpublished)
- `/ru` 404
- `/it/donations/5-per-thousand` 200 (header 5×1000 still present)
- `/it/volunteers`, `/it/contact`, `/it/privacy-policy` 200

Compare page keys to the inspect table (about, services, contact, privacy, cookie, trasparenza, home, faq, diventa-socio).

## 3. Product law and later specs

- `.specify/memory/constitution.md` locked `S-I18N` is it + en, Italian primary.
- `specs/004-ad-grants-landings/spec.md` does not say membership is missing.
- `specs/005-campaign-banners/spec.md` is Satispay QR only; no 5×1000 banner requirement.
- `specs/002`–`006` do not require `/ru`.

## 4. Production unchanged

SSH allowlist only. `php artisan env` still production. Do not run seeders on the VPS.

## 5. Full suite + Pint

```bash
ddev exec php artisan test
ddev exec ./vendor/bin/pint --test
```

## 6. Owner UAT

English list: `checklists/owner-user-tests.md` (created at implement). Same turn: numbered **Russian** script in chat. Wait. Cursor-browser only if the owner agrees that turn.

## Stop

Do not start `001.2` implement until this UAT closes. Tell the owner when `001.2` needs `stripe listen` / sandbox — not before.
