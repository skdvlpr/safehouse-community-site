# Tasks: Consent-gated audience measurement (002)

**Input**: Design documents from `/specs/002-consent-gated-analytics/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Propose only real logic ([Laravel testing](https://laravel.com/docs/13.x/testing)). Owner may **drop** any `test: yes` row at the post-tasks wait. No coverage %. No i18n snapshots of whole policies. No Dusk. GA4 Realtime / network = owner UAT.

**Organization**: Tasks grouped by user story. Each item: `test: yes/no`; complexity `C1`–`C10`; proposed model.

**Models**: Default **inherit** (this session). Mechanical copy/config: Composer 2.5 Fast. **No** Opus / GPT / Gemini / Fable unless the owner later says Launch vs Replace for that ID. This file is **not** a launch order.

**Sequence**: US1–US4 (gate, kill-switch, conversions, reopen) **before** US5 policy copy. Do not author a DPA (`S-GDPR`). Do not paste GTM snippets in Blade. Do not commit `GTM-` / `G-` ids. Do not apply production Caddy unless the owner asks that turn.

Official pages for implement: [configuration](https://laravel.com/docs/13.x/configuration), [Blade](https://laravel.com/docs/13.x/blade), [localization](https://laravel.com/docs/13.x/localization), [testing](https://laravel.com/docs/13.x/testing), [Pint](https://laravel.com/docs/13.x/pint), [Vite](https://vite.dev/guide/), [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [Consent mode](https://developers.google.com/tag-platform/security/guides/consent), [GA4 custom events](https://support.google.com/analytics/answer/12229021), [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header), [Filament resources](https://filamentphp.com/docs/4.x/resources/overview).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Parallelizable (different files, no incomplete dependency)
- **[Story]**: US1–US5 from spec.md
- Exact file paths required

---

## Phase 1: Setup

**Purpose**: Env + config rails. No new Composer/npm packages.

- [X] T001 Add empty `MEASUREMENT_ENABLED=false` and `GTM_CONTAINER_ID=` to `.env.example` only (never real ids). https://laravel.com/docs/13.x/configuration (test: no; C1; model: Composer 2.5 Fast — mechanical)
- [X] T002 Create `config/measurement.php`: `enabled` boolean from `MEASUREMENT_ENABLED` default **false**; `container_id` string from `GTM_CONTAINER_ID`; `is_bootable` only when enabled AND id matches `^GTM-[A-Z0-9]+$`. Missing/invalid id ⇒ not bootable, no exception. Do **not** put a `GTM-` value in `phpunit.xml` ([data-model.md](./data-model.md)) (test: yes covered by T006; C3; model: inherit — kill-switch)

**Checkpoint**: Config exists; testing env stays measurement off

---

## Phase 2: Foundational (boot payload)

**Purpose**: Public layout can expose boot JSON without injecting GTM. BLOCKS all stories.

- [X] T003 Add `app/Services/MeasurementBootService.php`: `isBootable(): bool`, `containerId(): ?string` (null when not bootable). Never log the id. Preview/CMS: not bootable. Constructor-inject config; no `new` in controllers ([measurement-boot.md](./contracts/measurement-boot.md)) (test: yes covered by T006; C4; model: inherit)
- [X] T004 In `resources/views/layouts/app.blade.php` add a boot node (`data-measurement-enabled`, optional container for JS). When not bootable: **no** `googletagmanager.com` and **no** `GTM-` in HTML. Filament `/cms-safehouse` MUST NOT include this layout. Preview routes already flagged in this file stay not bootable. https://laravel.com/docs/13.x/blade (test: yes covered by T006; C4; model: inherit)

**Checkpoint**: Home HTML in default tests has no GTM hosts

---

## Phase 3: User Story 1 — Necessary tools run; measurement waits for explicit choice (P1) 🎯 MVP

**Goal**: Dismiss/X = essential. GTM injects only after stored consent `all` **and** `is_bootable`. Basic consent: no noscript iframe; ads keys stay denied ([Consent mode](https://developers.google.com/tag-platform/security/guides/consent)).

**Independent Test**: No stored choice / essentials / dismiss → no `googletagmanager.com`. Accept analytics + bootable → JS may inject. Donate/volunteer still work on essentials-only.

### Tests for User Story 1

- [X] T005 [US1] Add `tests/Feature/MeasurementGateTest.php`: (1) default home HTML has no `googletagmanager.com` / `GTM-`; (2) `Config::set` enabled + `GTM-TEST1` → boot node present, still **no** official snippet in Blade; (3) enabled + `not-a-container` → treat as off; (4) preview route never bootable. https://laravel.com/docs/13.x/testing (test: yes — kill-switch and no pre-consent snippet; C6; model: inherit). Owner may **drop**.

### Implementation for User Story 1

- [X] T006 [US1] Add dismiss/X on `resources/views/layouts/partials/cookie-banner.blade.php` (`data-cookie-dismiss`) plus `__()` strings in `lang/it/site.php` and `lang/en/site.php`. https://laravel.com/docs/13.x/localization (test: yes covered by T016 HTML; C3; model: inherit)
- [X] T007 [US1] In `resources/js/cookie-consent.js`: dismiss/X stores `essential` (default deny); scroll does not store; close preferences without Save does not accept analytics ([consent-ui.md](./contracts/consent-ui.md)) (test: no — JS UAT; C4; model: inherit)
- [X] T008 [US1] Add `resources/js/measurement.js` and import from `resources/js/app.js`: after consent `all` + bootable, `dataLayer`, consent default denied for `ad_storage` / `ad_user_data` / `ad_personalization` / `analytics_storage`, then grant **only** `analytics_storage`, load official `gtm.js` ([GTM web](https://developers.google.com/tag-platform/tag-manager/web)). MUST NOT inject noscript iframe. On `cookie-consent:changed` to essential: deny four keys, stop events. Strip `donor_name` / `phone` / `email` from the measurement URL. https://vite.dev/guide/ (test: no — network UAT; C8; model: inherit — consent-sensitive JS)

**Checkpoint**: MVP gate on DDEV with measurement off by default

---

## Phase 4: User Story 2 — Staff dashboard is GA4, not Filament (P1)

**Goal**: No in-app analytics UI. Kill-switch already in T002–T005. Staff use analytics.google.com after consented hits ([owner-ops.md](./contracts/owner-ops.md)).

**Independent Test**: Config off → no scripts even if cookie is `all`. Consent audit still hashed only (`CookieConsentTest`). Dashboard = owner UAT.

- [X] T009 [US2] Write `specs/002-consent-gated-analytics/checklists/owner-user-tests.md` (English): Realtime/Pages after consented hits; kill-switch; no raw IP in CMS; Skip until local `.env` ids exist. Do **not** build a Filament dashboard (test: no; C2; model: Composer 2.5 Fast — checklist)

**Checkpoint**: US2 is ops + existing audit tests; no new CMS path

---

## Phase 5: User Story 3 — Four conversion events, no PII (P1)

**Goal**: Markers `donate_success` / `donate_recurring_success` / `volunteer_success` / `contact_success`. One thank-you emits exactly one donate name (`allows_recurring`). Honeypot success flash without measurement marker. JS copies **event name only** onto `dataLayer` when consent is `all` and bootable ([conversion-events.md](./contracts/conversion-events.md), [custom events](https://support.google.com/analytics/answer/12229021)).

**Independent Test**: PHPUnit markers; GA4 names = owner UAT after implement.

### Tests for User Story 3

- [X] T010 [US3] Add `tests/Feature/MeasurementConversionTest.php`: one-time thank-you with `donor_name=Mario Rossi` → `data-measurement-event="donate_success"`, not recurring, marker node has no `Mario`; recurring thank-you → `donate_recurring_success` only; volunteer honeypot → `volunteer_success` flash **without** measurement marker; real volunteer success → marker; same pair for contact; `tests/Feature/HomePageTest.php` impact cards still pass (SC-005 — do not change CRM counters). (test: yes — PII on marker / honeypot false conversion; C7; model: inherit). Owner may **drop**.

### Implementation for User Story 3

- [X] T011 [P] [US3] On `resources/views/donations/thank-you.blade.php` set `data-measurement-event` from `$isRecurring` / campaign `allows_recurring`; do not put `donor_name` on that node (test: yes covered by T010; C3; model: inherit)
- [X] T012 [P] [US3] `app/Http/Controllers/VolunteerController.php`: honeypot keeps `volunteer_success` flash; set a separate session flag (e.g. `measurement_conversion`) **only** on real mail success (test: yes covered by T010; C4; model: inherit)
- [X] T013 [P] [US3] Same split in `app/Http/Controllers/ContactSubmissionController.php` for `contact_success` vs measurement flag (test: yes covered by T010; C4; model: inherit)
- [X] T014 [US3] Render marker from the measurement flag in `resources/views/pages/partials/volunteer-form-shell.blade.php` and `resources/views/pages/partials/contact-form-shell.blade.php` (test: yes covered by T010; C3; model: inherit)
- [X] T015 [US3] In `resources/js/measurement.js` push `{ event: name }` only (no amount, name, email, payment id) when marker present, consent `all`, bootable (test: no — UAT; C5; model: inherit)

**Checkpoint**: Four names in HTML contracts; payloads empty of PII

---

## Phase 6: User Story 4 — Reopen / withdraw (P1)

**Goal**: Footer button reopens the **same** preferences panel. Withdraw to essential stops further measurement in that visit. First-visit banner stays hidden after a stored choice ([consent-ui.md](./contracts/consent-ui.md), [FAQ cookie](https://www.garanteprivacy.it/faq/cookie)).

**Independent Test**: Home has dismiss + footer reopen. Switch `all` → `essential` dispatches `cookie-consent:changed`.

- [X] T016 [US4] Extend `tests/Feature/CookieConsentTest.php` (or add assertions in MeasurementGateTest): home includes dismiss control and footer reopen; POST still hashes IP/UA; missing level rejected; rate-limit unchanged (test: yes — reopen/dismiss present + audit hashes; C4; model: inherit). Owner may **drop** if T005 already covers HTML.
- [X] T017 [US4] Add reopen button in `resources/views/layouts/partials/footer.blade.php` legal row (`data-cookie-reopen`) + `__()` labels it+en (test: yes covered by T016; C3; model: inherit)
- [X] T018 [US4] In `resources/js/cookie-consent.js`: reopen shows preferences (not a second banner); `all` → `essential` fires `cookie-consent:changed` so `measurement.js` stops (test: no — JS UAT; C5; model: inherit)

**Checkpoint**: Withdraw path exists without a CMP

---

## Phase 7: User Story 5 — Operational cookie/privacy copy (P2)

**Goal**: After the gate works, banner + CMS legal pages describe live GA4/GTM. When not bootable, texts say the tool is not loaded. No DPA language. No public `/ru` ([policy-copy.md](./contracts/policy-copy.md)).

**Independent Test**: Enabled → no stale “analytics not in use”; disabled → inactive claim. Pages still must not contain EspoCRM / crm host / `Data Processing Agreement`.

- [X] T019 [US5] Update `lang/it/site.php` and `lang/en/site.php` cookie banner/analytics notes from bootable vs off ([localization](https://laravel.com/docs/13.x/localization)) (test: no — strings; C3; model: inherit)
- [X] T020 [P] [US5] Update Italian and English bodies in `database/seeders/Data/LegalPagesContent.php` per [policy-copy.md](./contracts/policy-copy.md). Do **not** publish unused ru bodies. Do **not** add DPA/SCC text. Keep Stripe card-data sentence. https://filamentphp.com/docs/4.x/resources/overview (test: yes covered by T022; C6; model: inherit — operational legal)
- [X] T021 [US5] Add `resources/views/layouts/partials/measurement-status.blade.php` and include it from `resources/views/pages/templates/legal.blade.php` so SC-004 follows config, not a stale CMS paragraph (test: yes covered by T022; C3; model: inherit)
- [X] T022 [US5] Update `tests/Feature/CmsPagesTest.php`: default (measurement off) may still see Italian inactive analytics wording; with `Config::set` bootable, `/it/cookie-policy` and `/en/privacy-policy` contain Google Analytics 4 / Tag Manager (or Italian equivalent), do **not** claim analytics unused, do **not** contain `EspoCRM` / `crm.safehouse.community` / `Data Processing Agreement` (test: yes — stale copy is a privacy defect; C5; model: inherit). Owner may **drop**.
- [X] T023 [US5] Preview only: `ddev exec php artisan site:sync-legal-pages --force` after LegalPagesContent edits. Do **not** overwrite production CMS unless the owner asks ([DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/)) (test: no; C2; model: inherit)

**Checkpoint**: Copy matches live gate; counsel review waits for owner UAT

---

## Phase 8: Polish & cross-cutting

- [X] T024 Document GTM/GA hosts on the **public** CSP matcher in `deploy/Caddyfile.snippet` (`script-src` / `img-src` / `connect-src` per [owner-ops.md](./contracts/owner-ops.md)). Do **not** reload production Caddy. https://caddyserver.com/docs/caddyfile/directives/header (test: no; C3; model: inherit)
- [X] T025 `ddev exec ./vendor/bin/pint` and `ddev exec php artisan test` ([Pint](https://laravel.com/docs/13.x/pint), [testing](https://laravel.com/docs/13.x/testing)). Rebuild frontend if JS/CSS changed: `bash bin/dev-rebuild-frontend.sh` (test: no; C2; model: Composer 2.5 Fast)
- [X] T026 Append `.specify/progress/` handoff; fill remaining ticks on `checklists/ga4-gtm-owner-setup.md` that are still owner-side (Signals, DPT, second admin, Publish if unpublished). Do **not** git commit/push unless asked (test: no; C1; model: Composer 2.5 Fast)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Start immediately
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS stories
- **US1 (Phase 3)**: Depends on Foundational — MVP
- **US2 (Phase 4)**: Depends on US1 kill-switch tests
- **US3 (Phase 5)**: Depends on US1 inject (`measurement.js`)
- **US4 (Phase 6)**: Depends on US1 banner JS
- **US5 (Phase 7)**: Depends on US1–US4 so copy describes reality
- **Polish**: Depends on US5

### User Story Dependencies

- **US1**: After Phase 2
- **US2**: After T005 (kill-switch)
- **US3**: After T008 (`measurement.js` exists)
- **US4**: After T007 (banner JS)
- **US5**: After US1–US4

### Parallel Opportunities

- T011, T012, T013 after T010 is written (or with T010 if TDD)
- T012 and T013 different controllers
- T019 and T020 different files after US5 starts

### Parallel Example: User Story 3

```bash
Task: "thank-you.blade.php donate markers"
Task: "VolunteerController measurement flag"
Task: "ContactSubmissionController measurement flag"
```

---

## Implementation Strategy

### MVP (US1)

1. Phase 1–2
2. Phase 3 (gate + dismiss + inject)
3. Stop: MeasurementGateTest + essentials-only network check

### Incremental

1. US2 checklist (no Filament charts)
2. US3 four events
3. US4 reopen
4. US5 copy + sync
5. Caddy snippet documented; production apply only if owner asks

### Notes

- [P] = different files, no incomplete dependency
- Suggested models are proposals, never a launch order
- Owner UAT script in **Russian** is pasted after implement (Principle VIII), not during tasks
- Cursor-browser: offer and wait; do not drive DDEV unless agreed that turn
