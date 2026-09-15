# Research: 001.1 production snapshot and queue replan

## Decision: Production inspect is read-only SSH as `deploy` plus public HTTPS

**Rationale**: Owner asked for SSH with their key. Personal `~/.ssh/id_ed25519` (`semkoksharov.dev@gmail.com`) is **not** in `authorized_keys` for `deploy@77.81.234.138`. Local `~/.ssh/safehouse-deploy` (comment `github-actions-deploy`) **is**. That key is equivalent to GitHub Actions `DEPLOY_SSH_KEY`: full `deploy` user, `www-data` group. Agent SSH = owner trust. Mitigations used 2026-09-13: `BatchMode`, no agent forwarding, no `.env`, no `site_settings` dump, no writes.

**Alternatives considered**: Cursor-browser on production (constitution VIII: wait for that-turn approval). Public curl only (misses published-flag and Caddyfile). Root SSH (unnecessary, more dangerous).

**Safety residual**: High. Acceptable for a dated allowlisted inspect. Do not make this a habit for writes.

Official deploy notes: [deploy/DEPLOY.md](../../../deploy/DEPLOY.md). Edge headers: [Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header). Staff CMS: [Filament 4.x](https://filamentphp.com/docs/4.x) path `/cms-safehouse` (`S-CMS-PATH`).

## Decision: Persist inspect in `contracts/production-inspect-2026-09-13.md`

**Rationale**: Spec US1 needs a durable snapshot. The inspect already ran in the specify turn; implement refreshes only if production drifted, then owner-UAT.

**Findings that change the later queue** (full table in the contract):

- `/it/diventa-socio` **200**, page key `diventa-socio`, template `landing`, published. English URL is also `diventa-socio` (not `become-a-member`).
- `/it/landing-example` **404**. Demo keys **absent** from production DB (not merely unpublished).
- `/ru` **404**. Stored `ru` translations still exist on several pages ([Spatie translatable](https://spatie.be/docs/laravel-translatable/v6/installation-setup)).
- Community Caddy vhost is **minimal** (root, encode, php_fastcgi, try_files, file_server). **No** HSTS, **no** CSP, **no** CMS IP allowlist. `Via: Caddy`. PHP sends `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`. Repository `deploy/Caddyfile.snippet` is **not** the live community block. CRM vhost does set `X-Frame-Options SAMEORIGIN`.
- `https://safehouse.community/cms-safehouse/login` **200** from the public internet (snippet would 403 except localhost).
- Laravel 13.17.0, PHP 8.4.24, `APP_ENV=production`, no `.git` on server.
- `storage/logs/laravel.log` ~6.3 MiB: `SignatureVerificationException` count 270, `No signatures found` 180, 90 ERROR lines mentioning it, 0 `StripeWebhook` ERROR lines, 0 `HTTP 500` string. Aligns with F-002 risk; **do not repair in 001.1**. Docs: [Stripe signatures](https://docs.stripe.com/webhooks/signatures).
- CMS last error 2026-09-01: Blade `touch(): Utime failed: Operation not permitted` (permissions), not a current outage.
- Public HTML: 5×1000 header link present (`/it/donations/5-per-thousand`). No Satispay. Volunteer and contact HTML had **no** Turnstile/cloudflare challenge markers on 2026-09-13 (site key may be unset). `/admin` 404.

**Alternatives considered**: Re-inspect on every implement day (yes, quickstart step 1). Dump production DB (rejected: secrets, PII).

## Decision: Local parity = export/import published pages (and campaign list), never seed production

**Rationale**: [deploy/DEPLOY.md](../../../deploy/DEPLOY.md) forbids running `PageSeeder` on production. `site:export-deploy-data` today exports **articles only**. Implement adds a pages (and optionally campaigns) export that omits integration settings. Import on DDEV only. Keep local Stripe test CMS values.

**Parity rules**:

- Published keys on local = production keys: `about`, `services`, `contact`, `privacy`, `cookie`, `trasparenza`, `home`, `faq`, `diventa-socio`.
- Unpublish or delete public `demo-landing` / `landing-example` on local.
- Do not copy production Stripe/CRM secrets into local.
- `PageSeeder` must stop `is_published => true` for demo landing so a future local seed does not re-publish it.

**Alternatives considered**: Manual CMS clicking (slow, not repeatable). Full mysqldump (secrets). rsync `storage/` (media may help later; out of MVP unless pages reference missing images).

## Decision: S-I18N → it + en on implement via `/speckit-constitution` (MAJOR)

**Rationale**: Owner removed Russian from site **and** constitution. Locked-row redefine is MAJOR. Chat UAT scripts stay Russian (Principle VIII). Public UI it+en already in `config/locales.php`. Seeders/Filament defaults still list `ru`.

**Do not** change `S-STRIPE` in 001.1. Owner wants Checkout Sessions later ([Stripe Checkout](https://docs.stripe.com/payments/checkout)). Changing S-STRIPE now would make current Payment Element non-compliant.

**Alternatives considered**: Leave S-I18N and add `/ru` (rejected). Change S-STRIPE in 001.1 (rejected: current checkout would violate law).

## Decision: Working-tree secret removal, no history rewrite

**Rationale**: Owner choice. File `database/seeders/data/local-integrations.php` committed in `777c237` with real `pk_test_51TfltZ…` / `sk_test_51TfltZ…` / `acct_1TfltZ…`. GitHub secret scanning matches this. Replace with env/CMS-only placeholders. Optional Dashboard rotation is owner-ops (old commits still contain the test secret).

**Alternatives considered**: `git filter-repo` (owner declined). Leave keys (declined).

CSRF note for later 001.2 F-010: Laravel 13 documents `preventRequestForgery(except: …)` for Stripe webhooks — [CSRF excluding URIs](https://laravel.com/docs/13.x/csrf). Not this feature.

## Decision: Queue remap (owner answers)

| ID | After 001.1 |
| :--- | :--- |
| F-001 | Closed as product-law change (drop ru), not “add /ru” |
| F-002 | **001.2**, local Stripe sandbox first; tell owner when to run `stripe listen` |
| F-003 | **001.2** translation-ready it+en |
| F-004 | **001.2** volunteer (and verify contact on prod) Turnstile |
| F-005 | **001.2** code header consistency; **owner-ops** apply Caddy snippet (root) |
| F-006 | Parity in **001.1**; PageSeeder demo not published |
| F-007–F-014 | **001.2** |
| F-015 | **002** (no ru) |
| F-016 | **003** (it+en) |
| F-017 | **004** verify existing socio, do not create |
| F-018 | **005** Satispay QR HTML/CSS banner; 5×1000 header only |
| F-019 | **006** after 002 |
| F-020 | **New later spec** + S-STRIPE amend at that time |
| Prod Caddy gap / public CMS | **owner-ops** (privileged) |

**Alternatives considered**: Glue repairs into 002 (forbidden by S-SITE-QUEUE). Migrate Checkout in 001.2 (too large; law still PE).
