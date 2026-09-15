# Tasks: Production snapshot, local content parity, and SITE-queue replan

**Input**: Design documents from `/specs/001.1-prod-parity-replan/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: One secret-hygiene test is requested in spec.md (local-integrations must not contain Stripe key material). No other new PHPUnit rows.

**Organization**: Tasks are grouped by user story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Point the working set at 001.1 and keep official-doc URLs in artifacts

- [x] T001 Confirm `.specify/feature.json` `feature_directory` is `specs/001.1-prod-parity-replan` and append `.specify/progress/008-tasks-001.1.md`
- [x] T002 [P] Keep vendor URLs cited in `specs/001.1-prod-parity-replan/plan.md` and `research.md`: https://caddyserver.com/docs/caddyfile/directives/header · https://laravel.com/docs/13.x/csrf · https://laravel.com/docs/13.x/testing · https://docs.stripe.com/webhooks/signatures · https://docs.stripe.com/payments/checkout · https://filamentphp.com/docs/4.x · https://spatie.be/docs/laravel-translatable/v6/installation-setup

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Implement-time rails — no production writes, no checkout changes

- [x] T003 Add `specs/001.1-prod-parity-replan/checklists/owner-user-tests.md` (English durable list: snapshot, local socio, local demo not public, `/ru` 404, 5×1000 header, seeder has no Stripe keys, S-I18N it+en, 004/005 rewritten). Do not tick for the owner.
- [x] T004 Re-read `specs/001.1-prod-parity-replan/contracts/ssh-allowlist.md` before any SSH; refuse `.env`, `site_settings`, migrate, Caddy reload, `config:cache`

**Checkpoint**: Foundation ready — user story work can begin

---

## Phase 3: User Story 1 - Durable production snapshot (Priority: P1) MVP

**Goal**: Dated production evidence pack is complete and secret-free

**Independent Test**: Open `contracts/production-inspect-2026-09-13.md`; every URL in the spec US1 list has a status; no `sk_` / `whsec_` / `APP_KEY`

### Implementation for User Story 1

- [x] T005 [US1] Refresh or confirm `specs/001.1-prod-parity-replan/contracts/production-inspect-2026-09-13.md` against live HTTPS (membership 200, landing-example 404, `/ru` 404, cms login status, headers) using only the SSH allowlist if SSH is needed
- [x] T006 [P] [US1] Add remap notes for inspect-only items (public CMS login, missing HSTS/CSP, webhook signature ERROR counts) into `specs/001.1-prod-parity-replan/contracts/queue-remap.md` without dumping log bodies
- [x] T007 [US1] Patch `specs/001-stack-compliance-audit/findings.md` header: production **was** inspected 2026-09-13; link to the 001.1 inspect contract

**Checkpoint**: Snapshot is the system of record for “what is live”

---

## Phase 4: User Story 2 - Local preview matches production published content (Priority: P1)

**Goal**: Local published page keys match production; demo landing not public; local Stripe test settings kept

**Independent Test**: DDEV `/it/diventa-socio` 200; `/it/landing-example` not public; CMS integrations still test-mode local

### Implementation for User Story 2

- [x] T008 [US2] Add `app/Console/Commands/ExportCmsPagesCommand.php` (`site:export-cms-pages`) writing `database/seeders/data/deploy-pages.php` with Page fields `key`, `template`, `is_published` (boolean), `title`, `slug`, `body`, `meta` — no `site_settings`, no Stripe keys. Follow `app/Console/Commands/ExportDeployDataCommand.php` (https://laravel.com/docs/13.x)
- [x] T009 [US2] Add `app/Console/Commands/ImportCmsPagesCommand.php` (`site:import-cms-pages`) that upserts from `database/seeders/data/deploy-pages.php` **only** when `APP_ENV=local` (or `--force` local); MUST NOT run against production
- [x] T010 [US2] SSH allowlist: run `php artisan site:export-cms-pages` on production to stdout or `/tmp`, copy into local `database/seeders/data/deploy-pages.php` without secrets; do not rsync `.env`
- [x] T011 [US2] Run `ddev exec php artisan site:import-cms-pages` so local published keys match production (`about`, `services`, `contact`, `privacy`, `cookie`, `trasparenza`, `home`, `faq`, `diventa-socio` per `data-model.md`)
- [x] T012 [US2] Unpublish or delete local public `demo-landing` / slug `landing-example` if still public after import
- [x] T013 [US2] Change `database/seeders/PageSeeder.php` demo-landing `is_published` from `true` to `false` so a later local seed cannot republish it
- [x] T014 [US2] Confirm local `stripe.*` CMS/env test settings were not overwritten (compare Integrations screen / `.env`; do not commit `.env`)

**Checkpoint**: Local published URLs match the snapshot; production untouched

---

## Phase 5: User Story 3 - Product law and public UI Italian + English only (Priority: P1)

**Goal**: S-I18N is it+en; staff locale fallbacks stop offering Russian as a required public language

**Independent Test**: Constitution locked row it+en; `/ru` 404; Filament locale arrays default to config without `ru`

### Implementation for User Story 3

- [x] T015 [US3] Run `/speckit-constitution` to set locked `S-I18N` to UI it + en, Italian primary; keep Principle VIII chat UAT scripts in Russian; bump MAJOR; do **not** change `S-STRIPE`. File: `.specify/memory/constitution.md`
- [x] T016 [P] [US3] Replace fallback `['it', 'ru', 'en']` with `config('locales.available')` only (already `it`,`en` in `config/locales.php`) in `app/Filament/Resources/PageResource.php`, `app/Filament/Resources/PageResource/Support/PageTemplateFormFields.php`, `app/Filament/Resources/DonationCampaignResource.php`, `app/Filament/Resources/ArticleCategoryResource.php`, `app/Filament/Resources/EditorialArticleCategoryResource.php`, `app/Filament/Resources/Concerns/ConfiguresArticleResourceForm.php`
- [x] T017 [P] [US3] Same fallback replacement in `app/Filament/Pages/ManageRecurringDonation.php`, `app/Filament/Pages/ManageDonationsConfig.php`, `app/Filament/Pages/ManageSportelliConfig.php`, `app/Filament/Support/CarouselFormFields.php`, `app/Filament/Support/CarouselBatchUpload.php`, `app/Support/PageCarousel.php`, `app/Services/SiteContentService.php`, `app/Services/RecurringDonationCampaignService.php`
- [x] T018 [US3] Confirm `config/locales.php` `available` is only `it`, `en` and `AGENTS.md` router language line matches constitution (Italian primary, Russian+English **chat/UAT**, not public `/ru`)

**Checkpoint**: Product law and staff locale pickers match owner drop-Russian decision

---

## Phase 6: User Story 4 - Later specs remapped; test secrets leave the working tree (Priority: P1)

**Goal**: 002–006 and 001.2 outline match owner answers; seeder has no Stripe keys

**Independent Test**: `rg sk_test_|pk_test_` on `database/seeders/data/local-integrations.php` is empty; 004 does not say membership is missing; 005 has no 5×1000 banner

### Tests for User Story 4

- [x] T019 [US4] Add failing then passing test `tests/Feature/LocalIntegrationsSeederHasNoStripeKeysTest.php` asserting `database/seeders/data/local-integrations.php` contains none of `sk_test_`, `pk_test_`, `sk_live_`, `pk_live_` (https://laravel.com/docs/13.x/testing)

### Implementation for User Story 4

- [x] T020 [US4] Strip Stripe key/account values from `database/seeders/data/local-integrations.php`; keep non-secret local CRM URL / assigned user placeholders; document `.env` / CMS Integrations as the only key source. Update comments in `database/seeders/DeployIntegrationSeeder.php`, `tests/Feature/StripeSandboxPayoutLifecycleTest.php`, `bin/qa-seed-stripe-prima-nota.php`
- [x] T021 [P] [US4] Remap F-001, F-006, F-017, F-018, F-020 in `specs/001-stack-compliance-audit/findings.md` per `specs/001.1-prod-parity-replan/contracts/queue-remap.md`
- [x] T022 [P] [US4] Rewrite `specs/002-consent-gated-analytics/spec.md` (+ its requirements checklist if locale sentences mention Russian): it+en only
- [x] T023 [P] [US4] Rewrite `specs/003-public-seo-foundation/spec.md` for two locales only
- [x] T024 [P] [US4] Rewrite `specs/004-ad-grants-landings/spec.md`: membership **exists** at `https://safehouse.community/it/diventa-socio`; ads-safe verify/harden; do not create a new page; no ru
- [x] T025 [P] [US4] Rewrite `specs/005-campaign-banners/spec.md`: Satispay = QR in HTML/CSS banner; **no** 5×1000 banner; keep header 5×1000 link
- [x] T026 [P] [US4] Rewrite `specs/006-gdpr-operational-alignment/spec.md`: it+en; still no lawyer DPA (`S-GDPR`)
- [x] T027 [US4] Write `specs/001.1-prod-parity-replan/contracts/001.2-outline.md` listing F-002 (local Stripe first; tell owner when to raise `stripe listen`), F-003–F-005, F-007–F-014, production Turnstile re-check; Checkout Sessions **not** in 001.2

**Checkpoint**: Queue after 001.1 is readable without contradicting production or owner UAT

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Verify and hand off

- [x] T028 Run `specs/001.1-prod-parity-replan/quickstart.md` locally (`ddev exec php artisan test` and `ddev exec ./vendor/bin/pint --test` — https://laravel.com/docs/13.x/testing · https://laravel.com/docs/13.x/pint)
- [x] T029 [P] Append `.specify/progress/` implement handoff after owner UAT script (Russian numbered list in chat; wait)
- [x] T030 Offer Cursor-browser for local socio / demo / 5×1000 header and **wait** (do not drive DDEV or production unless the owner agrees that turn)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS stories
- **US1**: Snapshot first (MVP)
- **US2**: Needs US1 snapshot keys; local import
- **US3**: Constitution then code fallbacks; can overlap US2 on different files
- **US4**: Spec rewrites can start after T003; seeder test T019 before T020
- **Polish**: After US1–US4

### User Story Dependencies

- **User Story 1 (P1)**: After Phase 2
- **User Story 2 (P1)**: After US1 snapshot (needs production key list)
- **User Story 3 (P1)**: After Phase 2; independent of US2 except shared `PageSeeder.php` — do T013 before large seeder locale edits
- **User Story 4 (P1)**: Spec rewrites `[P]` across different spec files; seeder file after T019

### Parallel Opportunities

- T002, T006, T016, T017, T021–T026 are `[P]` on different files
- Do not parallel T010 (SSH) with any production write (there must be none)

### Parallel Example: User Story 4 spec rewrites

```bash
Task: "Rewrite specs/002-consent-gated-analytics/spec.md"
Task: "Rewrite specs/003-public-seo-foundation/spec.md"
Task: "Rewrite specs/004-ad-grants-landings/spec.md"
Task: "Rewrite specs/005-campaign-banners/spec.md"
Task: "Rewrite specs/006-gdpr-operational-alignment/spec.md"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Phase 1–2
2. Persist/confirm production snapshot
3. **STOP** — owner can already plan 004/005 from evidence

### Incremental Delivery

1. US1 snapshot
2. US2 local parity
3. US3 constitution + locale fallbacks
4. US4 seeder + spec rewrites
5. Quickstart + Russian UAT; do not start 001.2 implement

### Suggested MVP

User Story 1 (snapshot) is already largely drafted in `contracts/production-inspect-2026-09-13.md`; implement should confirm it, then do US2–US4.

---

## Notes

- [P] tasks = different files, no dependencies
- Do not implement webhook, Turnstile, Checkout Sessions, or live Caddy in this feature
- Commit only if the owner asks
- `stripe listen` is **001.2**, announced in chat at that time — not now
