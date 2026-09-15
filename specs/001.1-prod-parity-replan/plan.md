# Implementation Plan: Production snapshot, local content parity, and SITE-queue replan

**Branch**: `001.1-prod-parity-replan` (spec directory; git remains `main` until the owner asks) | **Date**: 2026-09-13 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001.1-prod-parity-replan/spec.md`

## Summary

Persist the 2026-09-13 **read-only production inspect**, make **local published CMS content** match production (membership on, demo landing not public, no production writes), amend **S-I18N to it+en**, strip Stripe **test** keys from `database/seeders/data/local-integrations.php` without rewriting git history, remap F-001/F-006/F-017/F-018/F-020, and rewrite specs `002`–`006` plus a `001.2` outline. **No** webhook, checkout, Turnstile, or live Caddy changes in this feature.

Official docs opened this turn: [Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header), [Laravel CSRF](https://laravel.com/docs/13.x/csrf), [Laravel testing](https://laravel.com/docs/13.x/testing), [Stripe webhook signatures](https://docs.stripe.com/webhooks/signatures), [Stripe Checkout](https://docs.stripe.com/payments/checkout) (deferred), [Filament 4.x](https://filamentphp.com/docs/4.x), [Spatie translatable v6](https://spatie.be/docs/laravel-translatable/v6/installation-setup).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 (locked)

**Primary Dependencies**: Existing Laravel 13, Filament v4 (`/cms-safehouse`), Spatie translatable v6 (locale JSON). No new Composer/npm packages unless a tiny Artisan export/import cannot reuse `ExportDeployDataCommand` patterns — prefer extending that command family over a new library.

**Storage**: MariaDB `pages` / `donation_campaigns` on local DDEV; production **read** only. Markdown in `specs/001.1-prod-parity-replan/`. Constitution file on implement (S-I18N only).

**Testing**: One Feature/Unit test that `database/seeders/data/local-integrations.php` contains no `sk_test_`, `pk_test_`, or `sk_live_` / `pk_live_` material (real secret-hygiene). Existing suite must still pass. No coverage %. No webhook tests here.

**Target Platform**: Local DDEV `https://safehouse-community-site.ddev.site`. Production `https://safehouse.community` inspect-only (SSH `deploy@77.81.234.138`).

**Project Type**: Existing Laravel web app; this feature is ops/content/governance, not a new product surface.

**Performance Goals**: Owner compares local vs production published URLs in one sitting (SC-001, SC-005).

**Constraints**: FR-005 no production writes. No `cat .env`. No `ddev stop`. No git history rewrite. No Checkout Sessions. S-STRIPE stays Payment Element until a later spec. S-CSP: record that production community vhost currently has **no** HSTS/CSP; do not apply Caddy here.

**Scale/Scope**: ~9 production pages, 3 donation campaigns, 5 published articles, specs `002`–`006` rewrites, constitution S-I18N, one seeder file.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
| :--- | :--- | :--- |
| I SDD lock-in | PASS | 001.1 only; 001.2/002–006 rewritten as text, not implemented |
| II Repo SoR | PASS | Snapshot + remap in `specs/001.1-…/` |
| III Official docs | PASS | URLs opened this turn; cited in research.md |
| IV Spec persistence | PASS | Dotted `001.1`; finish before `002` implement |
| V Meaningful tests | PASS | One secret-hygiene test; no coverage % |
| VI Security / PII | PASS | SSH allowlist; no `.env`; keys removed from working tree |
| VII Stack freeze | PASS | No new libs; S-I18N amendment is owner-requested MAJOR locked-row change via `/speckit-constitution` on implement |
| VIII Owner UAT | PASS | `checklists/owner-user-tests.md` at implement; Russian script in chat |
| IX Ask on doubt | PASS | F-020 = later Checkout Sessions spec; S-STRIPE **not** changed in 001.1 |
| X Write this repo | PASS | SSH read-only; no CRM writes |
| XI Model table | N/A | Printed at `/speckit-tasks` |
| XII Errors/logs | PASS | Webhook 4xx vs 500 is **001.2**, not this feature |
| S-STRIPE | PASS | PE unchanged; Checkout Sessions deferred ([Stripe Checkout](https://docs.stripe.com/payments/checkout)) |
| S-I18N | AMEND on implement | Owner: drop `ru`. Until then current row is it+ru+en — 001.1 implement runs constitution command first |
| S-CSP | PASS | Do not put CSP in PHP; record prod vhost gap; apply snippet later (owner-ops / 001.2) |
| S-CMS-PATH | PASS | `/cms-safehouse` confirmed live 200 from the public internet |
| S-GDPR | PASS | No DPA text |

**Post-design re-check**: PASS. Design adds export/import Artisan in this repo, seeder placeholder, constitution S-I18N, markdown spec rewrites. No checkout/webhook/Caddy-on-prod. Complexity table empty.

## Project Structure

### Documentation (this feature)

```text
specs/001.1-prod-parity-replan/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── production-inspect-2026-09-13.md
│   ├── ssh-allowlist.md
│   └── queue-remap.md
├── checklists/
│   ├── requirements.md
│   └── owner-user-tests.md   # created at implement
└── tasks.md                  # /speckit-tasks
```

### Source Code (repository root)

Touched **on implement only**:

```text
.specify/memory/constitution.md          # S-I18N via speckit-constitution
.specify/feature.json                    # already 001.1
database/seeders/data/local-integrations.php
database/seeders/PageSeeder.php          # stop publishing demo-landing; ru not required
config/locales.php                       # confirm it+en (already)
app/Console/Commands/                    # page export/import or extend ExportDeployDataCommand
app/Filament/                            # default locale lists if they hardcode ru
specs/001-stack-compliance-audit/findings.md  # remap addendum or inline
specs/002-consent-gated-analytics/spec.md
specs/003-public-seo-foundation/spec.md
specs/004-ad-grants-landings/spec.md
specs/005-campaign-banners/spec.md
specs/006-gdpr-operational-alignment/spec.md
specs/001.2-audit-repairs/               # outline spec.md only if tasks include drafting it
```

**Structure Decision**: Single Laravel app. 001.1 is documentation + content sync + governance. Payment/webhook code stays untouched.

## Complexity Tracking

> No constitution violations that need a workaround. S-I18N change is an explicit owner amendment, not a silent fork.
