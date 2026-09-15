# 024 — Owner asked to publish S01 to production

**Date:** 2026-09-15  
**Command:** owner: push `main` (auto-deploy); captcha keys on prod without leaking; do not copy local CMS content

## Deploy path

GitHub Actions `CI` on `main`: test job, then `deploy` rsync + `deploy/post-deploy.sh`.

Post-deploy ([DEPLOY.md](../../deploy/DEPLOY.md), [Laravel migrations](https://laravel.com/docs/13.x/migrations)): `php artisan migrate --force` and `RoleSeeder` only. **Not** `PageSeeder` / campaign / article / site-content seeders. Storage uploads persist (`rsync-excludes` skips `storage/app/public`).

`001.4` drop `volunteers` runs as that migrate. Must **not** inspect live rows ([owner-ops](../../specs/001.4-volunteer-mail-captcha/contracts/owner-ops.md)).

## Captcha keys

Must **not** go in git, chat, or SSH argv. Local encrypted `turnstile.secret_key` cannot be copied to prod (`APP_KEY` differs). Agent does **not** decrypt or paste the secret.

Owner pastes keys on live CMS Captcha (`/cms-safehouse/captcha`) from the existing Turnstile widget (hostnames already include `safehouse.community`) or [Cloudflare Turnstile dashboard](https://dash.cloudflare.com/?to=/:account/turnstile) ([get started](https://developers.cloudflare.com/turnstile/get-started/)).

## CI blocker (this publish)

First `main` push failed `composer audit` (Filament MFA CVEs, Livewire XSS). Patched in-tree: Filament `v4.13.2`, Livewire `v3.8.9` ([Filament 4 install](https://filamentphp.com/docs/4.x/introduction/installation)). Still v4, not v5. Does not seed CMS content.

## Caddy

Do **not** run `apply-caddy-site-once.sh` this publish: the snippet’s CMS IP allowlist would 403 staff CMS. Live Caddy currently has no CSP, so Turnstile `api.js` is not blocked.

## Stop

Did not start `002`. Did not dump production content. Did not write production `.env`.
