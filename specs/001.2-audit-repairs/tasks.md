# Tasks: Audit repairs (001.2)

**Input**: Design documents from `/specs/001.2-audit-repairs/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Only real-logic rows (webhook 4xx, donate locale + safe JSON, volunteer Turnstile, HMAC/UA consent). No coverage %. Owner UAT is separate.

**Organization**: Tasks grouped by user story. Each item: `test: yes/no`; complexity `C1`–`C10`; proposed model.

**Models**: Default **inherit** (this session). No Opus/GPT/Gemini/Fable unless the owner later says Launch vs Replace.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Parallelizable (different files, no incomplete dependency)
- **[Story]**: US1–US5 from spec.md
- Exact file paths required

---

## Phase 1: Setup

**Purpose**: Rails; no new packages

- [x] T001 Confirm `.specify/feature.json` points at `specs/001.2-audit-repairs`; do not add Composer/npm packages (test: no; C1; model: inherit — mechanical)

---

## Phase 2: Foundational (`bootstrap/app.php`)

**Purpose**: CSRF path, trusted proxies, CMS dump — shared file, sequential

- [x] T002 In `bootstrap/app.php` set `preventRequestForgery(except: ['api/webhooks/stripe'])` per https://laravel.com/docs/13.x/csrf (fall back to `validateCsrfTokens` with the same except if that is the installed API); remove dead `webhooks/stripe` (test: no; C3; model: inherit — API group already omits CSRF; this is hygiene)
- [x] T003 In `bootstrap/app.php` replace `trustProxies(at: '*')` with loopback + RFC1918 CIDRs; honor `TRUSTED_PROXIES` env (`*` or comma list) per https://laravel.com/docs/13.x/requests#configuring-trusted-proxies; document in `.env.example` (test: no; C4; model: inherit — review/document finding)
- [x] T004 In `bootstrap/app.php` CMS `cms-safehouse*` last-error writer: class + truncated redacted message + file + line; **no** `getTraceAsString()` (test: no; C3; model: inherit — logging hygiene, not business logic)

**Checkpoint**: Bootstrap hygiene done before story code

---

## Phase 3: User Story 1 — Forged payment notifications → 4xx (P1) MVP

**Goal**: Bad Stripe signatures never 500

**Independent Test**: `POST /api/webhooks/stripe` with missing or wrong signature returns 400; no CRM write. Valid mocked ingest still 200.

### Tests for User Story 1

- [x] T005 [US1] Add Feature tests in `tests/Feature/StripeWebhookDonationTest.php`: missing signature and invalid signature against real `Stripe\Webhook::constructEvent` / `StripePaymentService` (not a mock that swallows the throw) assert **400**, never 5xx (test: yes — this is the bug; C6; model: inherit — webhook fail-closed)

### Implementation for User Story 1

- [x] T006 [US1] In `app/Http/Controllers/StripeWebhookController.php` catch `\Stripe\Exception\SignatureVerificationException`, `\UnexpectedValueException`, and `RuntimeException` around `constructWebhookEvent`; return 400 generic `Invalid signature`; `report()` the exception; keep post-verify 502 for CRM `RuntimeException` (https://docs.stripe.com/webhooks/signatures) (test: yes covered by T005; C5; model: inherit — typed catch)
- [x] T007 [US1] In owner chat, **announce** before any `stripe listen` / sandbox; do not change live Dashboard webhooks (`specs/001.2-audit-repairs/contracts/owner-ops.md`) (test: no; C2; model: inherit — FR-003 process)

**Checkpoint**: Unsigned POST is 400 on DDEV/PHPUnit

---

## Phase 4: User Story 2 — English donate copy + safe errors (P1)

**Goal**: Locale-correct donate strings; no raw exception JSON

**Independent Test**: English donate validation is English; checkout 422 message ≠ exception text.

### Tests for User Story 2

- [x] T008 [P] [US2] Update `tests/Feature/DonationCheckoutTest.php`: validation missing contact asserts translated `site.donations.contact_required`; Stripe `RuntimeException` 422 asserts `__('site.donations.checkout_failed')` and **not** the raw exception string (test: yes — locale + leak; C4; model: inherit)

### Implementation for User Story 2

- [x] T009 [P] [US2] Add `site.donations.*` keys (contact_required, phone_invalid, contact_help, checkout_failed, campaign_inactive, privacy_*) to `lang/it/site.php` and `lang/en/site.php` (test: no; C2; model: inherit — copy)
- [x] T010 [US2] Wire keys in `app/Http/Requests/CreateDonationIntentRequest.php`, `resources/views/donations/show.blade.php`, `resources/views/donations/privacy.blade.php` (https://laravel.com/docs/13.x/blade) (test: no; C3; model: inherit — i18n wiring)
- [x] T011 [US2] In `app/Http/Controllers/Api/DonationCheckoutController.php` and `app/Http/Controllers/Api/MockDonationCompleteController.php` return translated `checkout_failed` / `campaign_inactive`; `report($exception)` (test: yes covered by T008; C3; model: inherit — FR-005)

**Checkpoint**: English donate URL shows English; JSON errors are generic

---

## Phase 5: User Story 3 — Volunteer Turnstile (P1)

**Goal**: Same bot challenge as contact when enabled

**Independent Test**: Turnstile on → volunteer POST without token stores 0 rows; off → valid volunteer stores.

### Tests for User Story 3

- [x] T012 [US3] Extend `tests/Feature/VolunteerFormTest.php`: with Turnstile enabled and no token, assert errors and `assertDatabaseCount('volunteers', 0)`; with disabled, existing happy path still passes (https://laravel.com/docs/13.x/testing) (test: yes — gate logic; C4; model: inherit)

### Implementation for User Story 3

- [x] T013 [US3] Mirror contact Turnstile rules in `app/Http/Requests/StoreVolunteerRequest.php` via `TurnstileVerifier` (https://laravel.com/docs/13.x/validation#form-request-validation) (test: yes covered by T012; C4; model: inherit)
- [x] T014 [P] [US3] Add widget + script when enabled in `resources/views/pages/partials/volunteer-form-shell.blade.php` and `resources/views/pages/volunteer.blade.php` (same pattern as contact) (test: no; C3; model: inherit — markup)
- [x] T015 [US3] Inspect-only production contact for Turnstile widget; record yes/no in progress + owner-user-tests notes; SSH only via `specs/001.1-prod-parity-replan/contracts/ssh-allowlist.md` (test: no; C3; model: inherit — FR-007)

**Checkpoint**: Volunteer matches contact challenge policy

---

## Phase 6: User Story 4 — Framing policy in repo (P2)

**Goal**: PHP and snippet both `DENY`; live Caddy untouched

**Independent Test**: Grep `deploy/Caddyfile.snippet` `X-Frame-Options DENY`; `SecurityHeaders` still `DENY`; no production reload.

- [x] T016 [US4] Set `X-Frame-Options DENY` in `deploy/Caddyfile.snippet`; comment that live apply is owner-ops; do **not** run `deploy/apply-caddy-site-once.sh` (https://caddyserver.com/docs/caddyfile/directives/header) (test: no; C2; model: inherit — snippet only)

**Checkpoint**: Repo framing agrees; production Caddy unchanged

---

## Phase 7: User Story 5 — Hygiene (P2)

**Goal**: Injection, logging, hashes, title suffix

**Independent Test**: HomeController has constructor `PageService`; consent rows have UA hash; title uses `__()`.

### Tests for User Story 5

- [x] T017 [P] [US5] Add `tests/Unit/ContactSubmissionHashTest.php`: `hashIp` / `hashUserAgent` equal `hash_hmac('sha256', …, config('app.key'))` and **not** unsalted `hash('sha256', …)` (test: yes — PII hashing; C3; model: inherit)
- [x] T018 [P] [US5] Update `tests/Feature/CookieConsentTest.php` and `tests/Feature/GdprConsentModelTest.php`: `user_agent_hash` column exists; stored hash matches helper for the test UA (test: yes — consent audit; C3; model: inherit)

### Implementation for User Story 5

- [x] T019 [P] [US5] Constructor-inject `PageService` in `app/Http/Controllers/HomeController.php` (test: no; C2; model: inherit — architecture, reviewable)
- [x] T020 [P] [US5] Log empty `catch (\Throwable)` in `app/Services/Donations/PrimaNotaPaymentStatusService.php` (`refreshFromPrimaNotaId` reload + settlement expand) via `Log::warning`; keep fallback behaviour (https://laravel.com/docs/13.x/logging) (test: no; C3; model: inherit — logging, CRM enum unchanged)
- [x] T021 [US5] HMAC helpers in `app/Services/ContactSubmissionService.php`; add nullable `user_agent_hash` string(64) migration + `GdprConsent` fillable + `GdprConsentService` + factory; `ddev exec php artisan migrate` (https://laravel.com/docs/13.x/migrations) (test: yes covered by T017/T018; C5; model: inherit)
- [x] T022 [P] [US5] Title suffix `__('site.layout.title_suffix')` in `resources/views/layouts/app.blade.php` + it/en lang keys (test: no; C2; model: inherit — i18n wiring)

**Checkpoint**: US5 checklist items observable

---

## Phase 8: Polish

- [x] T023 Write `specs/001.2-audit-repairs/checklists/owner-user-tests.md` (English; do not tick for the owner) (test: no; C2; model: inherit)
- [x] T024 [P] `ddev exec php artisan test` and `ddev exec ./vendor/bin/pint` (https://laravel.com/docs/13.x/testing · https://laravel.com/docs/13.x/pint) (test: no; C3; model: inherit — verify)
- [x] T025 Append `.specify/progress/` implement note; do **not** start spec `002` (test: no; C1; model: inherit)

---

## Dependencies & Execution Order

- Phase 1 → Phase 2 (T002–T004 same file, sequential) → US1–US5
- US1 T005 may be written to fail, then T006
- T009 can parallel T008; T010 after T009
- T014 parallel T013
- T017/T018/T019/T020/T022 parallel; T021 before T018 can pass
- T015 anytime after volunteer UI exists
- T007 is a chat announcement, not a code gate for PHPUnit
- Polish after all stories

### User Story independence

- US1 webhook without donate/Turnstile
- US2 donate without webhook listen
- US3 volunteer without Stripe
- US4 snippet-only
- US5 mostly independent; hashing touches volunteer/contact tests via shared helper

### Parallel opportunities

- Lang files (T009) || checkout tests (T008)
- HomeController (T019) || PrimaNota log (T020) || title (T022) || hash unit test (T017)
- Volunteer markup (T014) || framing snippet (T016)

---

## Parallel Example: User Story 2

```bash
# After T009 keys exist:
# T008 tests and T010/T011 wiring on different files
```

---

## Implementation Strategy

1. Setup + bootstrap
2. US1 webhook 4xx (MVP)
3. US2 donate i18n + safe JSON
4. US3 volunteer Turnstile + prod inspect
5. US4 snippet
6. US5 hygiene + migrate
7. Pint, PHPUnit, owner UAT script; **stop** (no 002)

Commit only if the owner asks. No `ddev stop`. No production Caddy. No live webhook rewrite.

## Notes

- Suggested models in this file are **not** a launch order for advanced models.
- Owner ordered this cycle through implement; do not wait for a second Launch after this table.
