# Implementation Plan: Audit repairs (001.2)

**Branch**: `001.2-audit-repairs` (spec directory; git remains current until the owner asks) | **Date**: 2026-09-15 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001.2-audit-repairs/spec.md`

## Summary

Close remaining S01 repair items **on local DDEV** before `002`. Invalid Stripe webhook signatures fail closed with **4xx** (not 500). Donor donate copy exists in Italian and English. Volunteer submit reuses the contact Turnstile challenge when it is enabled. PHP and the repo Caddy snippet agree on `X-Frame-Options: DENY`; **live Caddy is owner-ops**. Hygiene: constructor injection on home, log swallowed PrimaNota reloads, CSRF except matches `/api/webhooks/stripe`, cookie-consent HMAC + user-agent hash, trusted-proxy CIDRs, CMS last-error without stacks, translated title suffix.

Payment product stays Stripe Payment Element ([Payment Element](https://docs.stripe.com/payments/payment-element)). No Checkout Sessions. No production webhook rewrite. No HSTS/CSP apply.

Official docs opened this turn: [Stripe webhook signatures](https://docs.stripe.com/webhooks/signatures), [Laravel CSRF](https://laravel.com/docs/13.x/csrf), [Laravel trusted proxies](https://laravel.com/docs/13.x/requests#configuring-trusted-proxies), [Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header), [Laravel Form Requests](https://laravel.com/docs/13.x/validation#form-request-validation), [Laravel Blade](https://laravel.com/docs/13.x/blade), [Laravel migrations](https://laravel.com/docs/13.x/migrations), [Laravel logging](https://laravel.com/docs/13.x/logging), [Laravel testing](https://laravel.com/docs/13.x/testing), [Laravel Pint](https://laravel.com/docs/13.x/pint).

CRM sibling read (no writes): `nonprofit-espocrm` `PrimaNota.paymentStatus` enum is already Planned / Inviato / Cancelled / Refunded / Disputed / Problematic. This feature only logs existing sync failures.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 (locked)

**Primary Dependencies**: Existing Laravel 13, Stripe PHP `Webhook::constructEvent`, Cloudflare Turnstile (already on contact), Filament `/cms-safehouse`. No new Composer/npm packages.

**Storage**: MariaDB `gdpr_consents` gains nullable `user_agent_hash` (64). Volunteer/contact hash helpers switch to HMAC-SHA256 (hex still 64). Local DDEV migrate. Production schema only when the owner later deploys.

**Testing**: PHPUnit Feature tests for: unsigned/bad-signature webhook → 4xx; English donate validation copy; checkout JSON never echoes raw `RuntimeException` text; volunteer Turnstile when enabled; consent row stores UA hash via the shared helper. No coverage %. No `stripe listen` required for PHPUnit.

**Target Platform**: Local DDEV `https://safehouse-community-site.ddev.site`. Production `https://safehouse.community` inspect-only for contact Turnstile widget (FR-007).

**Project Type**: Existing Laravel web app; amendment of S01.

**Performance Goals**: Owner UAT of donate English copy, volunteer challenge, title suffix, and “no live Caddy change” in one sitting (SC-002, SC-004).

**Constraints**: FR-003 announce local `stripe listen` / sandbox in chat before using it; do not assume it is running. No production webhook Dashboard change. No live Caddy reload. No `ddev stop`. No `php artisan config:cache` in DDEV. No CRM repo writes. S-CSP: do not put CSP/HSTS in PHP.

**Scale/Scope**: Findings F-002, F-003, F-004, F-005, F-007–F-014. Twelve repair items, one migration, existing tests updated.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
| :--- | :--- | :--- |
| I SDD lock-in | PASS | 001.2 only; implement from `tasks.md`; do not start 002–008 |
| II Repo SoR | PASS | Plan, research, contracts, progress in this tree |
| III Official docs | PASS | URLs opened this turn; cited above and in research.md |
| IV Spec persistence | PASS | Dotted `001.2`; finish before `002` implement |
| V Meaningful tests | PASS | Tests only for signature, locale donate, safe JSON, Turnstile, HMAC/UA hash |
| VI Security / PII | PASS | HMAC hashes; no secrets in logs; no `.env` dump; SSH allowlist for contact inspect |
| VII Stack freeze | PASS | No new libs; HomeController constructor injection; it+en UI |
| VIII Owner UAT | PASS | `checklists/owner-user-tests.md` at implement; Russian script in chat; browser offer + wait |
| IX Ask on doubt | PASS | Framing policy = DENY (stricter, matches PHP + Caddy docs example). HMAC = shared helpers. Not silent forks of S-* |
| X Write this repo | PASS | CRM read only; PrimaNota enum already matches |
| XI Model table | N/A | Printed at `/speckit-tasks`; owner already ordered implement this cycle |
| XII Errors/logs | PASS | Webhook signature → 4xx; empty PrimaNota catch logs; checkout JSON translated |
| S-STRIPE | PASS | Payment Element unchanged |
| S-I18N | PASS | Donate/title/privacy strings it+en; no public `/ru` |
| S-CSP | PASS | PHP still has no CSP/HSTS; snippet X-Frame only aligned; live apply owner-ops |
| S-CMS-PATH | PASS | CMS dump path unchanged (`cms-safehouse*`) |
| S-GDPR | PASS | Operational consent hashes only; no DPA wording |

**Post-design re-check**: PASS. Design stays in this repo: webhook catch, Form Requests, lang files, one migration, Caddy **snippet** alignment, bootstrap middleware. No live edge apply. Complexity table empty.

## Project Structure

### Documentation (this feature)

```text
specs/001.2-audit-repairs/
├── spec.md
├── plan.md              # This file
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── http.md
│   └── owner-ops.md
├── checklists/
│   ├── requirements.md
│   └── owner-user-tests.md   # created at implement
└── tasks.md                  # /speckit-tasks
```

### Source Code (repository root)

```text
bootstrap/app.php
app/Http/Controllers/StripeWebhookController.php
app/Http/Controllers/HomeController.php
app/Http/Controllers/Api/DonationCheckoutController.php
app/Http/Controllers/Api/MockDonationCompleteController.php
app/Http/Requests/CreateDonationIntentRequest.php
app/Http/Requests/StoreVolunteerRequest.php
app/Services/ContactSubmissionService.php
app/Services/GdprConsentService.php
app/Services/Donations/PrimaNotaPaymentStatusService.php
app/Models/GdprConsent.php
database/migrations/…_add_user_agent_hash_to_gdpr_consents.php
database/factories/GdprConsentFactory.php
lang/it/site.php
lang/en/site.php
resources/views/layouts/app.blade.php
resources/views/donations/show.blade.php
resources/views/donations/privacy.blade.php
resources/views/pages/volunteer.blade.php
resources/views/pages/partials/volunteer-form-shell.blade.php
deploy/Caddyfile.snippet
.env.example
tests/Feature/StripeWebhookDonationTest.php   # add bad-signature cases
tests/Feature/DonationCheckoutTest.php
tests/Feature/VolunteerFormTest.php
tests/Feature/CookieConsentTest.php
tests/Feature/GdprConsentModelTest.php
tests/Unit/ContactSubmissionHashTest.php      # HMAC contract
```

**Structure Decision**: Single Laravel app. No new packages. Caddy change is git snippet only.

## Complexity Tracking

> No constitution violations that need a workaround.
