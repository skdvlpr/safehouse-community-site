# Tasks: Production analytics ready (002.2)

**Input**: Design documents from `/specs/002.2-production-analytics-ready/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/)

**Tests**: Desk → conversion name is real logic ([Laravel testing](https://laravel.com/docs/13.x/testing)). No coverage %. Caddy apply is owner/SSH, not PHPUnit.

**Organization**: US2 (contact names) is independently testable on DDEV. US1 (live CSP) is production-only. US3 is owner combined UAT after both.

## Format: `[ID] [P?] [Story] Description`

Complexity **C1–C10** and proposed model are in the chat table after this file (Principle XI). Suggested models here are **proposals**, not a launch order.

## Phase 1: Setup

- [x] T001 Confirm `.specify/feature.json` points at `specs/002.2-production-analytics-ready` and cite [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header) in any snippet edit this implement (test: no; C1; model: Composer 2.5 Fast)

---

## Phase 2: Foundational

**Purpose**: Failing tests for the new names before code.

- [x] T002 [US2] In `tests/Feature/MeasurementConversionTest.php` change contact cases: honeypot still has flash and **no** `data-measurement-event`; real `generic_desk` → `contact_generic_success`; `legal_desk` → `contact_slegale_success`; `digital_desk` → `contact_sdigitale_success`; assert **no** `contact_success` measurement marker. Tests MUST fail before T003–T005 ([testing](https://laravel.com/docs/13.x/testing)) (test: yes — desk map; C5; model: inherit)

**Checkpoint**: New assertions exist and fail on current `contact_success`

---

## Phase 3: User Story 2 - Three contact conversion names (P1)

**Goal**: Real contact submit emits exactly one desk-specific name.

**Independent Test**: PHPUnit MeasurementConversionTest green; local Accetta tutti + three desks → three dataLayer events.

- [x] T003 [US2] In `resources/js/measurement.js` extend `CONVERSION_EVENTS`: add the three names; **remove** `contact_success`. Keep donate/volunteer names ([custom events](https://support.google.com/analytics/answer/12229021)) (test: yes covered by T002; C3; model: Composer 2.5 Fast)
- [x] T004 [US2] In `app/Http/Controllers/ContactSubmissionController.php` map validated `desk` → session conversion name per [data-model.md](./data-model.md). Honeypot unchanged. Unknown desk → do not set `SESSION_CONVERSION`. Do not keep `contact_success` as fallback ([Blade](https://laravel.com/docs/13.x/blade)) (test: yes covered by T002; C4; model: inherit)
- [x] T005 [US2] In `resources/views/pages/partials/contact-form-shell.blade.php` render `data-measurement-event` from the session conversion value (allowlist the three names only) (test: yes covered by T002; C3; model: Composer 2.5 Fast)

**Checkpoint**: PHPUnit contact markers match desks; no `contact_success` event name

---

## Phase 4: User Story 1 - Live public CSP apply (P1)

**Goal**: Production public pages may load GTM/GA4/Preview; CMS excluded.

**Independent Test**: After apply, Accetta tutti on live `/it` shows `gtm.js` + collect; Realtime within 5 minutes.

- [x] T006 [US1] Update `deploy/Caddyfile.snippet` public CSP per [contracts/caddy-csp.md](./contracts/caddy-csp.md) (Preview + GA4-without-Ads hosts; no doubleclick). Keep `@public_csp not path /cms-safehouse*` and Stripe/Turnstile. Cite [GTM CSP](https://developers.google.com/tag-platform/security/guides/csp) and [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header) (test: no; C5; model: inherit)
- [x] T007 [US1] After the snippet is on the server (git deploy or rsync), run `sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh` via the owner’s SSH if possible. Script MUST self-delete on success and stay on failure. If sudo/SSH fails, print the exact root one-liner and stop — do not disable CMS allowlist IPs (test: no; C7; model: inherit — live edge)

**Checkpoint**: Live collect not CSP-blocked; CMS login still works

---

## Phase 5: User Story 3 - Combined UAT (P2)

**Goal**: One owner round; Home zeros allowed.

**Independent Test**: [checklists/owner-user-tests.md](./checklists/owner-user-tests.md)

- [x] T008 [US3] Optional: `php artisan site:sync-legal-pages --force` on production **only if** live measurement is on ([owner-ops](./contracts/owner-ops.md)). No DPA (test: no; C2; model: Composer 2.5 Fast)
- [x] T009 [US3] Print Russian combined UAT from [owner-user-tests.md](./checklists/owner-user-tests.md). Remind owner GTM must already be published ([gtm-owner.md](./contracts/gtm-owner.md)). Cursor-browser: offer, wait. Do not start `003` (test: no; C2; model: Composer 2.5 Fast)

---

## Phase 6: Polish

- [x] T010 Run `ddev exec ./vendor/bin/pint` on dirty PHP and `ddev exec php artisan test` ([Pint](https://laravel.com/docs/13.x/pint), [testing](https://laravel.com/docs/13.x/testing)) (test: no new cases; C2; model: Composer 2.5 Fast)

---

## Dependencies & Execution Order

- Setup T001 → Foundational T002 → US2 T003–T005 → US1 T006 then T007 (T007 needs snippet on disk **and** preferably deployed) → US3 T008–T009 → T010.
- US2 can ship in git before Caddy apply; combined UAT waits for T007 + owner GTM publish.
- T006 and T003–T005 are different files and may overlap after T002.

### Parallel example (after T002)

```text
T003 measurement.js
T004 ContactSubmissionController.php
T006 Caddyfile.snippet
```

T005 depends on T004 session values.

## Implementation Strategy

MVP: T001–T005 (names work locally). Then T006–T007 (live hits). Then T008–T009 owner round.

Do **not** implement until the owner starts `/speckit-implement` after reviewing this file and the model table.
