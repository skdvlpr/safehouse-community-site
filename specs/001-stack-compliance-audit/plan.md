# Implementation Plan: Stack and compliance audit

**Branch**: `001-stack-compliance-audit` | **Date**: 2026-09-13 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-stack-compliance-audit/spec.md`

## Summary

S01 delivers a **ranked findings register** in this feature directory (English) plus an owner UAT checklist. The public site, staff CMS, donation checkout, and consent flows are **inspected, not changed**. Remediations are later `001.K` or specs `002`–`006`. Method: static review against the constitution and official stack docs (opened this plan), evidence from existing Feature tests, and optional local HTTP probes. Production browsing only if the owner agrees that turn.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 (locked)

**Primary Dependencies**: Laravel 13, Filament v4, Stripe PHP + Payment Element (existing), Spatie Permission v8, Spatie Translatable/Media Library (inspect only). No new Composer/npm packages.

**Storage**: Markdown files in `specs/001-stack-compliance-audit/` (not a database). MariaDB is inspected only as the live app store; no migrations.

**Testing**: Existing PHPUnit Feature suite as a **no-behaviour-change** gate (`ddev exec php artisan test`). **No new tests** in this feature (no new product logic; Principle V). Pint `--test` only to confirm implementers did not reformat the app.

**Target Platform**: Local DDEV `https://safehouse-community-site.ddev.site`. Production `https://safehouse.community` optional, owner-approved that turn.

**Project Type**: Existing Laravel web application; this feature is a documentation/audit deliverable.

**Performance Goals**: Owner can prioritise from the register in one sitting (< 30 minutes) — SC-001.

**Constraints**: FR-004 zero behavioural change. No secrets in the register. No `ddev stop`. No git push. No CRM writes. Cursor-browser wait. Legal DPA out of scope.

**Scale/Scope**: Security/payments, personal forms, cookie choice, staff path, public journeys (home, donate, 5×1000, volunteer, contact, about, news), i18n hardcoding, architecture drift. Known S02–S04 gaps deferred, not P0-fixed here.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
| :--- | :--- | :--- |
| I SDD lock-in | PASS | Plan for 001 only; implement later from `tasks.md`; no mega-spec with 002–006 |
| II Repo SoR | PASS | Register lives in `specs/001-…/`, not Notion |
| III Official docs | PASS | URLs opened this turn; cited in research.md |
| IV Spec persistence | PASS | Living 001 artifacts; remediations are new dirs after UAT |
| V Meaningful tests | PASS | No new PHPUnit rows (no new logic) |
| VI Security / PII | PASS | Secrets never copied into findings; card data expected at Stripe; no Caddy/DDEV mutation |
| VII Stack freeze | PASS | No new libs; no drive-by refactors |
| VIII Owner UAT | PASS | `checklists/owner-user-tests.md` at implement; Russian script in chat |
| IX Ask on doubt | PASS | Production probe and Stripe-Checkout-Sessions vs locked PE: decided in research (note, not fork) |
| X Write this repo | PASS | CRM **read** only when checking ingest/sportello mapping |
| XI Model table | N/A | Printed at `/speckit-tasks`, not this command |
| XII Errors/logs | PASS | Empty-catch / 500 webhook storms are **findings**, not fixes |
| S-SITE-QUEUE | PASS | S01 first |
| S-STRIPE / S-CMS-PATH / S-CSP / S-GDPR | PASS | Audit must not reopen as design questions (FR-006) |

**Post-design re-check**: PASS. Design adds only markdown contracts and a Finding model. No app tree changes. Complexity table empty.

## Project Structure

### Documentation (this feature)

```text
specs/001-stack-compliance-audit/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── findings-register.md
│   └── area-coverage.md
├── checklists/
│   ├── requirements.md
│   └── owner-user-tests.md   # created at implement
├── findings.md               # created at implement (the register)
└── tasks.md                  # /speckit-tasks — not this command
```

### Source Code (repository root)

Inspect-only. Implement MUST NOT edit these except if an accidental edit is reverted.

```text
app/
├── Http/Controllers/          # thin-controller check; StripeWebhookController
├── Http/Requests/             # Form Request presence
├── Http/Middleware/           # SecurityHeaders (non-CSP), locale, CRM token
├── Services/                  # Donations, Payments, EspoCrm, GdprConsent
├── Models/                    # $fillable vs unguarded
├── Filament/                  # CMS resources; path cms-safehouse
└── Providers/Filament/AdminPanelProvider.php
bootstrap/app.php              # CSRF except, trustProxies, cms error dump
routes/web.php
routes/api.php                 # POST /api/webhooks/stripe
resources/views/               # hardcoded strings
resources/js/cookie-consent.js
lang/{it,en,ru}/
database/migrations/           # PII columns: ip_hash / user_agent_hash
tests/Feature/                 # evidence, not new tests
Caddy / DDEV config (read)     # CSP/HSTS at edge — do not edit
```

**Structure Decision**: Single existing Laravel app. Audit outputs stay under `specs/001-stack-compliance-audit/`. Application layout is unchanged.

## Complexity Tracking

> No constitution violations.
