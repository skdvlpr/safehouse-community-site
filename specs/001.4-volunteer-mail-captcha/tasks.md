# Tasks: Volunteer mail + captcha layout (001.4)

**Input**: Design documents from `/specs/001.4-volunteer-mail-captcha/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: One Feature rewrite (`VolunteerFormTest` + drop persistence tests). No CSS snapshots. No coverage %. Owner UAT covers widget layout/theme. Owner may **drop** the Feature rewrite row.

**Organization**: Tasks grouped by user story. Each item: `test: yes/no`; complexity `C1`–`C10`; proposed model.

**Models**: Default **inherit** (this session). No Opus/GPT/Gemini/Fable unless the owner later says Launch vs Replace. This file is **not** a launch order.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Parallelizable (different files, no incomplete dependency)
- **[Story]**: US1–US3 from spec.md
- Exact file paths required

---

## Phase 1: Setup

**Purpose**: Rails; no new packages

- [X] T001 Confirm `.specify/feature.json` is `specs/001.4-volunteer-mail-captcha`; do not add Composer/npm packages (test: no; C1; model: inherit — mechanical)

---

## Phase 2: Foundational (copy + inbox config)

**Purpose**: Strings and staff To address before mailables. BLOCKS US1.

- [X] T002 [P] In `lang/it/site.php`: add `last_name` / placeholder; phone placeholder MUST NOT say optional; add staff-mail body labels matching [copy.md](./contracts/copy.md); applicant acknowledgement (received / we will contact you / do not reply); SMTP-fail visitor error. Keep Italian primary (test: no; C2; model: inherit — copy)
- [X] T003 [P] Same keys in `lang/en/site.php` (first name / last name / phone not optional; English acknowledgement; English SMTP-fail). Staff **subject/body stay Italian** — do not translate `Nuova candidatura volontario` (test: no; C2; model: inherit — copy)
- [X] T004 Add `config/volunteer.php` with staff inbox `matteo.grossi@safehouse.community` (not a CMS field) ([data-model.md](./data-model.md)) (test: no; C1; model: inherit — config)

**Checkpoint**: it+en form/mail strings and inbox config exist

---

## Phase 3: User Story 1 — Email Matteo + applicant; no volunteer table (P1) 🎯 MVP

**Goal**: Complete volunteer POST sends two mails, stores zero rows, requires `name` max 255, `last_name` max 255, `email` max 255, `phone` max 50, `message` max 5000, consent; honeypot and Turnstile gates unchanged.

**Independent Test**: Local complete submit → staff Italian mail to Matteo + applicant ack in page locale; `volunteers` table gone; empty last name/phone/message send nothing.

### Tests for User Story 1

- [X] T005 [US1] Rewrite `tests/Feature/VolunteerFormTest.php` with `Mail::fake()` like `ContactFormMailTest`: (1) complete it POST → `VolunteerStaffMail` To Matteo, subject `Nuova candidatura volontario`, Italian body fragments, Reply-To applicant; `VolunteerApplicantMail` To applicant in it; (2) complete en POST → staff still Italian, applicant English; (3) missing `last_name` / `phone` / `message` → errors + `assertNothingSent`; (4) honeypot → success flash, nothing sent; (5) Turnstile enabled without token → errors, nothing sent; (6) `Schema::hasTable('volunteers')` is false after migrate. https://laravel.com/docs/13.x/mail#testing-mailables · https://laravel.com/docs/13.x/testing · https://laravel.com/docs/13.x/validation#form-request-validation (test: yes — mail routing, locale split, no-store, required fields; C6; model: inherit). Owner may **drop** this row.

### Implementation for User Story 1

- [X] T006 [US1] Update `app/Http/Requests/StoreVolunteerRequest.php`: `name` required string max **255**; `last_name` required string max **255**; `email` required email max **255**; `phone` required string max **50** (not nullable); `message` required string max **5000** (not nullable); keep `gdpr_consent` accepted and Turnstile required-if enabled ([data-model.md](./data-model.md)) (test: yes covered by T005; C4; model: inherit — Form Request)
- [X] T007 [US1] Add last-name field; mark phone and message required in `resources/views/pages/partials/volunteer-form-shell.blade.php` (test: no — UI; C3; model: inherit)
- [X] T008 [P] [US1] Add `app/Mail/VolunteerStaffMail.php` (+ Blade/text view if needed under `resources/views/mail/`): To config inbox, Italian subject/body per [copy.md](./contracts/copy.md), Reply-To applicant `name last_name`. https://laravel.com/docs/13.x/mail#sending-mail (test: no — covered by T005; C4; model: inherit)
- [X] T009 [P] [US1] Add `app/Mail/VolunteerApplicantMail.php` (+ view): To applicant, locale of the request, no Reply-To Matteo (test: no — covered by T005; C4; model: inherit)
- [X] T010 [US1] Change `app/Services/VolunteerService.php`: `Mail::send` staff then applicant via `OutboundMailConfigurator::applyForSportello()` / `canSendSportelloNotifications()`; **do not** `Volunteer::create`. If SMTP missing or send throws: `Log::warning` without secrets/full bodies; throw or return failure so the controller does **not** flash success (test: yes covered by T005; C5; model: inherit — fail closed)
- [X] T011 [US1] Update `app/Http/Controllers/VolunteerController.php`: honeypot still success + **zero** mail; on mail failure redirect **back** with translated error, no `volunteer_success`; throttle name `volunteers` unchanged (test: yes covered by T005; C3; model: inherit)
- [X] T012 [US1] Add migration `Schema::dropIfExists('volunteers')` under `database/migrations/`; delete `app/Models/Volunteer.php`, `database/factories/VolunteerFactory.php`; replace `tests/Feature/VolunteerModelTest.php` with “table absent” (or delete). https://laravel.com/docs/13.x/migrations#dropping-tables (test: yes — schema; C4; model: inherit)
- [X] T013 [US1] `ddev exec php artisan migrate` on **preview only** ([DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/)). Do **not** migrate production ([owner-ops](./contracts/owner-ops.md)) (test: no; C2; model: inherit)

**Checkpoint**: MVP mail-only volunteer on DDEV; local `volunteers` table gone

---

## Phase 4: User Story 2 — Challenge box aligned; follows light/dark (P2)

**Goal**: Contact + volunteer widget centred or field-width; theme from `html[data-theme]`, not hardcoded dark.

**Independent Test**: Visual matrix in [captcha-ui.md](./contracts/captcha-ui.md). Donate/cookie/CMS login still have no widget.

### Implementation for User Story 2

- [X] T014 [US2] In `resources/css/app.css`, `.template-contact-form .cf-turnstile` (or field wrapper) `w-full` + flex `justify-center` so the box is not right-aligned. https://tailwindcss.com/docs/width · https://tailwindcss.com/docs/justify-content (test: no — visual UAT; C3; model: inherit)
- [X] T015 [P] [US2] On `resources/views/pages/partials/contact-form-shell.blade.php` and `volunteer-form-shell.blade.php`: `data-size="flexible"`; remove hardcoded `data-theme="dark"`; do **not** use Turnstile `auto`. https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configuration-options (test: no; C3; model: inherit)
- [X] T016 [US2] Copy `html[data-theme]` (`light`|`dark`) onto `.cf-turnstile` in `resources/js/theme.js` (`applyTheme`); move `api.js` from `@push('head')` to `@stack('scripts')` **after** that copy in `resources/views/pages/volunteer.blade.php` and `resources/views/pages/templates/contact.blade.php`. On theme toggle, reset widget if `window.turnstile` exists (best-effort). https://vite.dev/guide/ (test: no; C5; model: inherit)
- [X] T017 [P] [US2] Grep donate / cookie / CMS login views: still no `cf-turnstile` (test: no; C2; model: inherit — leak of widget)

**Checkpoint**: Layout/theme on both public forms; no new widgets elsewhere

---

## Phase 5: User Story 3 — Live table drop only at publish (P2)

**Goal**: This session does not touch production `volunteers`. Publish-time drop is written down.

**Independent Test**: Change set has no production migrate/SSH drop. Owner-ops says drop live without inspecting rows when they later ask to publish.

- [X] T018 [US3] Do **not** run production migrate, dump, or `DROP TABLE` on live. Record in progress + owner-user-tests notes: first owner-asked publish of 001.4 runs the same migration and MUST NOT inspect rows ([owner-ops](./contracts/owner-ops.md)) (test: no; C2; model: inherit — FR-008/FR-009)

**Checkpoint**: Live store still exists until owner publish; preview already dropped in T013

---

## Phase 6: Polish

- [X] T019 Write `specs/001.4-volunteer-mail-captcha/checklists/owner-user-tests.md` (English; volunteer mail + captcha layout; include live-drop-later; do not tick for the owner) (test: no; C2; model: inherit)
- [X] T020 `ddev exec php artisan test` and `ddev exec ./vendor/bin/pint` (https://laravel.com/docs/13.x/testing · https://laravel.com/docs/13.x/pint). Do not `php artisan config:cache` (test: no; C3; model: inherit)
- [X] T021 Append `.specify/progress/` implement note; do **not** start spec `002`; Russian UAT script in chat; Cursor-browser only if owner agrees that turn (test: no; C2; model: inherit)

---

## Dependencies & Execution Order

- Phase 1 → Phase 2 (T002 ∥ T003, then T004) → US1 T005 then T006–T013 → US2 T014–T017 (T015 ∥ T017 after T014/T016 as needed) → US3 T018 → Polish
- T008 ∥ T009 after T002–T004 (different mail classes)
- US2 files are mostly independent of US1 mail; sequential after US1 to keep one implementer and avoid Blade conflicts on `volunteer-form-shell.blade.php` (T007 then T015)
- Do not start 002
- Commit only if the owner asks

### User Story independence

- US1 mail + drop table without widget CSS
- US2 layout/theme without changing mail
- US3 owner-ops only (no code if T013 already dropped preview)

### Parallel opportunities

- T002 ∥ T003
- T008 ∥ T009
- T015 ∥ T017 after widget markup task started

---

## Parallel Example: User Story 1

```bash
# After T002–T004:
# T008 VolunteerStaffMail.php and T009 VolunteerApplicantMail.php in parallel
# then T010 VolunteerService (needs both)
```

---

## Implementation Strategy

1. Setup + copy + inbox config
2. US1 tests rewrite + Form Request + mailables + drop table + local migrate (MVP)
3. US2 widget CSS/theme
4. US3 live-drop reminder
5. Pint, PHPUnit, owner UAT script; **stop** (no 002)

No `ddev stop`. No production migrate. No CRM writes. Do not wipe local `turnstile.*` rows.

## Notes

- Suggested models are **not** a launch order for advanced models.
- Wait for owner keep / change / drop (especially T005) before `/speckit-implement`.
