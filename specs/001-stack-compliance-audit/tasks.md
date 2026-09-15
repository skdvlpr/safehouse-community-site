# Tasks: Stack and compliance audit

**Input**: Design documents from `/specs/001-stack-compliance-audit/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: **None.** Plan and Principle V: no new PHPUnit. Every task is `test: no` (inspection/docs only). Existing suite is a polish regression gate only.

**Organization**: User stories from spec.md. App/PHP/Blade/routes/database trees are **read-only**. Writes only under `specs/001-stack-compliance-audit/` and `.specify/progress/`.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Parallel (different files, no write conflict on `findings.md`)
- **[Story]**: US1–US4 map to spec user stories
- Each task: `test: no`, complexity `C1`–`C10`, proposed model (constitution XI)

## Path Conventions

Feature dir: `specs/001-stack-compliance-audit/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Register file exists with required header fields from data-model EnvironmentNote.

- [x] T001 Create `specs/001-stack-compliance-audit/findings.md` with header fields `local_url`, `production_url` (`https://safehouse.community` or “not inspected”), `production_permission` (`yes` / `no` / `not-asked`), `crm_repo_read` (`yes` / `no`) per `specs/001-stack-compliance-audit/data-model.md` and `specs/001-stack-compliance-audit/contracts/findings-register.md` (test: no — markdown only; C2; model: Composer 2.5 Fast)
- [x] T002 Record in `specs/001-stack-compliance-audit/findings.md` that implement MUST NOT modify `app/`, `resources/`, `routes/`, `database/`, `bootstrap/`, Caddy, or DDEV config (test: no; C1; model: Composer 2.5 Fast)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Coverage stubs and environment rules before story inspections. **BLOCKS** US1–US4 writes that assume stubs exist.

**⚠️ CRITICAL**: Do not append Finding records until this phase is complete.

- [x] T003 Re-open this implement turn the official URLs listed in `specs/001-stack-compliance-audit/research.md` (Laravel CSRF https://laravel.com/docs/13.x/csrf, validation https://laravel.com/docs/13.x/validation, mass assignment https://laravel.com/docs/13.x/eloquent#mass-assignment, Stripe signatures https://docs.stripe.com/webhooks/signatures, Payment Element https://docs.stripe.com/payments/payment-element, Filament v4 path https://filamentphp.com/docs/4.x/panel-configuration, Spatie Permission v8 https://spatie.be/docs/laravel-permission/v8/introduction, Caddy header https://caddyserver.com/docs/caddyfile/directives/header) (test: no; C3; model: inherit)
- [x] T004 Run `ddev start` if the local site is down (never `ddev stop`); set `local_url` to `https://safehouse-community-site.ddev.site` in `specs/001-stack-compliance-audit/findings.md`; leave `production_permission` as `not-asked` unless the owner already approved production browse **this turn** (test: no; C2; model: inherit)
- [x] T005 Insert pending coverage stubs in `specs/001-stack-compliance-audit/findings.md` for every Area enum value: `security-payments`, `personal-forms`, `cookie-consent`, `staff-cms`, `public-journeys`, `i18n`, `architecture`, `secrets-repo`, `crm-integration` (FR-003: silence invalid) per `specs/001-stack-compliance-audit/contracts/area-coverage.md` (test: no; C2; model: Composer 2.5 Fast)

**Checkpoint**: `findings.md` has header + nine area stubs.

---

## Phase 3: User Story 1 - Ranked findings register (Priority: P1) 🎯 MVP

**Goal**: Owner-readable register schema, severity/bucket legend, and pre-bucketed known S02–S04 gaps so later stories only add real defects.

**Independent Test**: Open `specs/001-stack-compliance-audit/findings.md`. Legend quotes data-model enums. Deferred gaps point at `002`–`006`, not `001.K`.

### Implementation for User Story 1

- [x] T006 [US1] Write severity legend in `specs/001-stack-compliance-audit/findings.md` quoting `blocker` \| `high` \| `medium` \| `low` \| `note` and bucket enum `001.K` \| `002` \| `003` \| `004` \| `005` \| `006` \| `owner-ops` \| `other-repo` from `specs/001-stack-compliance-audit/data-model.md` (test: no; C2; model: Composer 2.5 Fast)
- [x] T007 [US1] Add note-severity deferred findings in `specs/001-stack-compliance-audit/findings.md` for known absences: no measurement → bucket `002`; no SEO title/index → `003`; no membership sitelink landing → `004`; no Satispay/5×1000 creatives → `005`; full privacy rewrite → `006` (SC-004; not `001.K`) (test: no; C3; model: inherit)

**Checkpoint**: Register is usable as a ranking document even before US2–US4 fill areas.

---

## Phase 4: User Story 2 - Security and data-handling pass (Priority: P1)

**Goal**: Payments, forms, consent, CMS path, secrets, CRM mapping inspected; each area `open` findings or `none-found`.

**Independent Test**: Coverage rows `security-payments`, `personal-forms`, `cookie-consent`, `staff-cms`, `secrets-repo`, `crm-integration` each have finding ids or none-found. No secret values in the file. `crm_repo_read: yes` if ingest/sportello findings exist.

### Implementation for User Story 2

- [x] T008 [P] [US2] Inspect webhook signature fail-closed in `app/Http/Controllers/StripeWebhookController.php` and `app/Services/Payments/StripePaymentService.php` (`constructWebhookEvent` / `Webhook::constructEvent`); 4xx on bad signature vs 502 after verified ingest — cite https://docs.stripe.com/webhooks/signatures; notes only, write in T017 (test: no; C8; model: inherit — optional security-review subagent only if owner Launch vs Replace)
- [x] T009 [P] [US2] Inspect CSRF exclusion in `bootstrap/app.php` (`validateCsrfTokens` vs `preventRequestForgery`) and `routes/api.php` `POST /api/webhooks/stripe` against https://laravel.com/docs/13.x/csrf — notes for T017 (test: no; C6; model: inherit)
- [x] T010 [P] [US2] Confirm hosted Payment Element remains the checkout (S-STRIPE) in `app/Services/Payments/StripePaymentService.php`; Stripe Checkout Sessions preference in https://docs.stripe.com/payments/payment-element is **severity `note` only**, not `001.K` (test: no; C4; model: inherit)
- [x] T011 [P] [US2] Inspect volunteer/contact validation and PII hashing (`ip_hash` / `user_agent_hash` only) in `app/Http/Requests/`, `app/Services/VolunteerService.php`, `app/Services/GdprConsentService.php`, `app/Models/`, `tests/Feature/VolunteerFormTest.php`, `tests/Feature/ContactFormTest.php` vs https://laravel.com/docs/13.x/validation — notes for T017 (test: no; C7; model: inherit)
- [x] T012 [P] [US2] Inspect cookie choice (essential vs all, no live tracker) in `resources/js/cookie-consent.js`, `app/Http/Controllers/GdprConsentController.php`, `tests/Feature/CookieConsentTest.php` — notes for T017 (test: no; C5; model: inherit)
- [x] T013 [P] [US2] Inspect CMS path `cms-safehouse` not `/admin` in `app/Providers/Filament/AdminPanelProvider.php`, `tests/Feature/FilamentPanelTest.php`, `tests/Feature/RbacTest.php` vs https://filamentphp.com/docs/4.x/panel-configuration and https://spatie.be/docs/laravel-permission/v8/introduction — notes for T017 (test: no; C4; model: inherit)
- [x] T014 [P] [US2] Inspect secrets in git (`.gitignore`, tracked files under repo root); **do not paste secret values** into `specs/001-stack-compliance-audit/findings.md` — notes for T017 (test: no; C6; model: inherit)
- [x] T015 [P] [US2] Inspect `app/Http/Middleware/SecurityHeaders.php` and `tests/Feature/SecurityHeadersTest.php`: CSP/HSTS must stay at Caddy (S-CSP, https://caddyserver.com/docs/caddyfile/directives/header) — notes for T017 (test: no; C5; model: inherit)
- [x] T016 [US2] Read `/home/skoksharov/safehouse/nonprofit-espocrm` while checking `app/Services/Donations/DonationIngestService.php` and `app/Services/EspoCrm/`; set `crm_repo_read` in `specs/001-stack-compliance-audit/findings.md`; mismatches bucket `other-repo` (no invented field names) (test: no; C7; model: inherit)
- [x] T017 [US2] Append Finding records to `specs/001-stack-compliance-audit/findings.md` using the template in `specs/001-stack-compliance-audit/contracts/findings-register.md`; required fields: `id` `F-NNN`, `title`, `severity` (`blocker` \| `high` \| `medium` \| `low` \| `note`), `area`, `status` (`open` \| `none-found`), `env` (`local` \| `production` \| `both` \| `unknown`), `evidence`, `impact`, `constitution`, `docs_url`, `bucket`, `bucket_why`; close each US2 area with none-found if empty (test: no; C6; model: inherit)

**Checkpoint**: US2 areas complete independently of journeys/architecture.

---

## Phase 5: User Story 3 - Public-journey and i18n pass (Priority: P2)

**Goal**: it/en/ru journeys and hardcoded visitor strings listed or none-found.

**Independent Test**: Reviewer can reproduce each public-journey finding from URLs/labels without reading PHP. Demo seed pages not treated as production unless env says so.

### Implementation for User Story 3

- [x] T018 [P] [US3] Static-check public journeys in `routes/web.php` and `resources/views/` for home, donate hub, 5×1000, volunteer, contact, about, news — notes for T022 (test: no; C6; model: inherit)
- [x] T019 [P] [US3] Inspect visitor-string i18n in `lang/it/`, `lang/en/`, `lang/ru/` and hardcoded copy in `resources/views/` (exclude brand names already content) — notes for T022 (test: no; C6; model: inherit)
- [x] T020 [P] [US3] Inspect demo/sample pages in `database/seeders/PageSeeder.php` vs official-looking public content — notes for T022 (test: no; C4; model: inherit)
- [x] T021 [US3] Optional curl against `https://safehouse-community-site.ddev.site` (it/en/ru sample URLs). MUST NOT use Cursor-browser or production unless the owner agreed **this turn**. Record `env` in `specs/001-stack-compliance-audit/findings.md` (test: no; C5; model: inherit)
- [x] T022 [US3] Append `public-journeys` and `i18n` findings or none-found sections to `specs/001-stack-compliance-audit/findings.md` with the same Finding field constraints as T017 (test: no; C4; model: inherit)

**Checkpoint**: US3 coverage rows complete.

---

## Phase 6: User Story 4 - Architecture-drift pass (Priority: P2)

**Goal**: Drift vs constitution VII/XII and official Laravel Form Request / mass-assignment docs, with buckets (later spec vs `001.K`).

**Independent Test**: Each architecture finding cites a principle or docs URL and a file path.

### Implementation for User Story 4

- [x] T023 [P] [US4] Inspect thin-controller / no `new ClassName()` in `app/Http/Controllers/` — notes for T027 (test: no; C6; model: inherit)
- [x] T024 [P] [US4] Inspect Form Request usage for mutations vs https://laravel.com/docs/13.x/validation in `app/Http/Requests/` and controllers — notes for T027 (test: no; C6; model: inherit)
- [x] T025 [P] [US4] Inspect mass assignment: `$fillable` explicit, no `$guarded = []` / `#[Unguarded]` on request-facing models in `app/Models/` vs https://laravel.com/docs/13.x/eloquent#mass-assignment — notes for T027 (test: no; C6; model: inherit)
- [x] T026 [P] [US4] Inspect empty/swallowing `catch (Throwable)` and secret logging in `app/` (constitution XII) — notes for T027 (test: no; C7; model: inherit)
- [x] T027 [US4] Append `architecture` findings or none-found to `specs/001-stack-compliance-audit/findings.md`; S02–S04 overlaps must use buckets `002`–`006` (test: no; C4; model: inherit)

**Checkpoint**: All four stories have independently testable register sections.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Ranking, SC-004, UAT checklist, no-behaviour-change proof.

- [x] T028 Renumber findings sequentially `F-001`… and order by severity (`blocker` first) in `specs/001-stack-compliance-audit/findings.md` (test: no; C3; model: Composer 2.5 Fast)
- [x] T029 Verify ≥90% of analytics/SEO/sitelink/banner/privacy-copy findings use buckets `002`–`006` in `specs/001-stack-compliance-audit/findings.md` (SC-004) (test: no; C3; model: inherit)
- [x] T030 Create `specs/001-stack-compliance-audit/checklists/owner-user-tests.md` (English Pass/Fail/Skip: open register, understand ranking, dispute buckets, confirm no site behaviour change) (test: no; C3; model: Composer 2.5 Fast)
- [x] T031 Run `git status`, `ddev exec php artisan test`, `ddev exec ./vendor/bin/pint --test` per `specs/001-stack-compliance-audit/quickstart.md`; revert accidental app edits (test: no new tests — regression only; C3; model: inherit)
- [x] T032 Append handoff in `.specify/progress/` pointing at `specs/001-stack-compliance-audit/findings.md` (test: no; C2; model: Composer 2.5 Fast)
- [x] T033 Stop for owner UAT using `specs/001-stack-compliance-audit/checklists/owner-user-tests.md`: do not start `001.K` or spec `002`; paste numbered Russian script in chat; offer Cursor-browser and **wait** (constitution VIII) (test: no; C2; model: inherit)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Immediate
- **Foundational (Phase 2)**: After T001–T002; blocks story appends
- **US1 (Phase 3)**: After Phase 2 — MVP register
- **US2 (Phase 4)**: After Phase 2 (can overlap US1 legend). T008–T015 parallel; T016 then T017 sequential writes
- **US3 (Phase 5)**: After Phase 2. T018–T020 parallel; T021 then T022
- **US4 (Phase 6)**: After Phase 2. T023–T026 parallel; T027 sequential
- **Polish**: After US1–US4 appends (T028 needs all findings)

### User Story Dependencies

- **US1 (P1)**: After Foundational — no dependency on US2–US4
- **US2 (P1)**: After Foundational — independently fills security areas
- **US3 (P2)**: After Foundational — independently fills journeys/i18n
- **US4 (P2)**: After Foundational — independently fills architecture
- Serial implement still: one agent; recommended order T001→…→T033

### Parallel Opportunities

- T008–T015 (reads, different files)
- T018–T020
- T023–T026
- Do **not** parallelize T017, T022, T027, T028 (same `findings.md`)

---

## Parallel Example: User Story 2

```text
T008 StripeWebhookController.php + StripePaymentService.php
T009 bootstrap/app.php + routes/api.php
T010 StripePaymentService.php (PE / S-STRIPE)
T011 Form Requests + Volunteer/Gdpr services
T012 cookie-consent.js + GdprConsentController
T013 AdminPanelProvider.php + Filament/Rbac tests
T014 secrets grep (no values in register)
T015 SecurityHeaders.php
# then T016 CRM read, T017 write findings.md
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Phase 1–2
2. Phase 3 (legend + deferred 002–006 notes)
3. Validate: owner can already see buckets for known gaps
4. Then US2 (highest risk) before US3/US4

### Incremental Delivery

1. Setup + Foundational
2. US1 MVP
3. US2 security
4. US3 journeys
5. US4 architecture
6. Polish + owner UAT wait

### Parallel Team Strategy

Constitution `S-SPEC-SERIAL`: one implementer. [P] means parallel *reads* for that agent, not two implements.

---

## Notes

- `test: no` on every row — do not add `tests/Feature/` files
- Commit only if the owner asks (not after each task)
- Stripe Checkout Sessions vs PE = `note`, not repair
- Legal DPA = stop (`S-GDPR`)
