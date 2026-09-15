# Tasks: CMS captcha settings (001.3)

**Input**: Design documents from `/specs/001.3-cms-captcha-settings/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: One Feature row (blank secret must not wipe; incomplete keys ⇒ verifier off). Keep `TurnstileVerifierTest` / `VolunteerFormTest`. No coverage %. Owner UAT is separate (combined 001.2 + 001.3 at polish).

**Organization**: Tasks grouped by user story. Each item: `test: yes/no`; complexity `C1`–`C10`; proposed model.

**Models**: Default **inherit** (this session). No Opus/GPT/Gemini/Fable unless the owner later says Launch vs Replace. This file is **not** a launch order.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Parallelizable (different files, no incomplete dependency)
- **[Story]**: US1–US3 from spec.md
- Exact file paths required

---

## Phase 1: Setup

**Purpose**: Rails; no new packages

- [X] T001 Confirm `.specify/feature.json` is `specs/001.3-cms-captcha-settings`; do not add Composer/npm packages (test: no; C1; model: inherit — mechanical)

---

## Phase 2: Foundational (CMS copy)

**Purpose**: Labels the new page and Sportelli helper need. BLOCKS US1 nav.

- [X] T002 [P] Add `cms.nav.captcha` = `Captcha`, `cms.notifications.captcha_saved` = `Impostazioni captcha salvate`; change `cms.helpers.sportelli_config_link` so it does **not** say captcha lives on Sportelli (point to Impostazioni → Captcha) in `lang/it/cms.php` (test: no; C2; model: inherit — copy)
- [X] T003 [P] Same keys in `lang/en/cms.php`: nav `Captcha`; notification `Captcha settings saved`; helper must not send staff to Help desks for captcha (test: no; C2; model: inherit — copy)

**Checkpoint**: i18n keys exist before the page class

---

## Phase 3: User Story 1 — Staff find captcha next to other secrets (P1) 🎯 MVP

**Goal**: Settings item **Captcha** (`/cms-safehouse/captcha`); three controls; Sportelli has no captcha editor.

**Independent Test**: Super-admin opens Impostazioni → Captcha; toggle + site key + secret. Sportelli has no captcha tab. Desks/mail still save.

### Implementation for User Story 1

- [X] T004 [US1] Add `app/Filament/Pages/ManageCaptchaSettings.php` (panel already `discoverPages`): group Settings, `navigationSort` 98, slug `captcha`, `canAccess` super-admin; form toggle `turnstile.enabled`, site key maxLength **255**, secret password+revealable `dehydrated` only when filled; `save` calls `SiteSettingsService::updateMany` **only** for `turnstile.enabled`, `turnstile.site_key`, `turnstile.secret_key` (do not dump the full settings bag); match `ManageIntegrations` Schema/`content()` pattern, no extra Blade; https://filamentphp.com/docs/4.x/navigation/custom-pages · https://filamentphp.com/docs/4.x/forms/text-input (test: no — UI; C5; model: inherit — Filament page, same house pattern)
- [X] T005 [US1] Remove captcha tab and all `turnstile.*` mount/save from `app/Filament/Pages/ManageSportelliConfig.php`; Sportelli `save` MUST NOT persist `turnstile.enabled` / `turnstile.site_key` / `turnstile.secret_key` (strip leftover Livewire state if `nestedFormValues()` still hydrates the whole bag). Desks, email templates, CRM case type unchanged. Optional one-line helper pointing to Impostazioni → Captcha (test: no; C4; model: inherit — two writers is the bug)

**Checkpoint**: One CMS editor; local keys from 2026-09-15 still present after first save with blank secret (manual)

---

## Phase 4: User Story 2 — Saving turns public contact/volunteer on or off (P1)

**Goal**: Completeness rules in [data-model.md](./data-model.md): widget iff toggle on **and** both keys; incomplete ⇒ no public lockout. Do **not** re-wire Blade/Form Requests unless T004 cannot drive `TurnstileVerifier`.

**Independent Test**: Complete save → widget on `/it|en/contact` and `/it|en/volunteers`; submit without token stores 0 rows. Toggle off or empty site key → no widget; valid volunteer still stores. Donate/cookie/CMS login unchanged.

### Tests for User Story 2

- [X] T006 [US2] Add `tests/Feature/CaptchaSettingsPageTest.php`: acting as super-admin, Livewire-test `ManageCaptchaSettings`: (1) stored secret survives save with blank secret field; (2) `turnstile.enabled=1` and empty site key ⇒ `TurnstileVerifier::enabled()` is false. https://laravel.com/docs/13.x/testing · https://filamentphp.com/docs/4.x/testing/overview (test: yes — FR-004 wipe + half-config lockout; C5; model: inherit — real save logic). Owner may **drop** this row.

### Implementation for User Story 2

- [X] T007 [US2] If T006 fails for reasons other than missing page: fix only `ManageCaptchaSettings.php` save/`updateMany` (do not change `app/Services/TurnstileVerifier.php`, contact/volunteer Blade, or Form Requests unless a proven bug). Confirm `tests/Feature/VolunteerFormTest.php` and `tests/Unit/TurnstileVerifierTest.php` still pass (test: yes covered by T006 + existing; C3; model: inherit — YAGNI public forms)

**Checkpoint**: Verifier follows the new screen; public forms unchanged unless broken

---

## Phase 5: User Story 3 — Secret off public HTML; no live writes (P2)

**Goal**: Secret never in public HTML; no production CMS/Caddy/DNS in this feature.

**Independent Test**: Local contact/volunteer HTML has site key, not secret. Change set has no prod writes.

- [X] T008 [US3] Inspect-only `https://safehouse.community/it/contact` for `cf-turnstile` / `challenges.cloudflare.com`; record yes/no in progress + owner-user-tests notes. No production CMS/`.env`, no Caddy reload, no Cloudflare zone onboard (`specs/001.3-cms-captcha-settings/contracts/owner-ops.md`). SSH only via `specs/001.1-prod-parity-replan/contracts/ssh-allowlist.md` if HTML is insufficient (test: no; C3; model: inherit — FR-010)
- [X] T009 [P] [US3] Grep public Blade (`resources/views/pages/partials/contact-form-shell.blade.php`, `volunteer-form-shell.blade.php`) so only `siteKey()` is printed, never `secret_key` / `secretKey()` (test: no; C2; model: inherit — leak check)

**Checkpoint**: US3 inspect notes exist; no secret in views

---

## Phase 6: Polish

- [X] T010 Write `specs/001.3-cms-captcha-settings/checklists/owner-user-tests.md` (English; combined 001.2 challenge + new Captcha screen; do not tick for the owner) (test: no; C2; model: inherit)
- [X] T011 [P] `ddev exec php artisan test` and `ddev exec ./vendor/bin/pint` (https://laravel.com/docs/13.x/testing · https://laravel.com/docs/13.x/pint). Do not `php artisan config:cache` (test: no; C3; model: inherit — verify)
- [X] T012 Append `.specify/progress/` implement note; do **not** start spec `002`; Russian UAT script in chat; Cursor-browser only if owner agrees that turn (test: no; C2; model: inherit)

---

## Dependencies & Execution Order

- Phase 1 → Phase 2 (T002 ∥ T003) → US1 T004 then T005 → US2 T006 then T007 → US3 T008 ∥ T009 → Polish
- T006 may be written to fail before T004 exists if TDD; otherwise write after T004 and keep as regression
- Do not start 002
- Commit only if the owner asks

### User Story independence

- US1 CMS nav/editor without public-form code changes
- US2 save/verifier contract (existing volunteer tests)
- US3 inspect + leak grep; no live writes

### Parallel opportunities

- T002 ∥ T003 (it vs en lang)
- T008 ∥ T009 after US1
- T011 after tests exist

---

## Parallel Example: User Story 1

```bash
# After T002/T003:
# T004 page class, then T005 Sportelli (same mental model, sequential — both touch captcha ownership)
```

---

## Implementation Strategy

1. Setup + CMS strings
2. US1 page + remove Sportelli captcha (MVP)
3. US2 Feature test + no public-form churn
4. US3 inspect / leak grep
5. Pint, PHPUnit, owner UAT script; **stop** (no 002)

No `ddev stop`. No production Caddy. No Cloudflare Connect-your-domain. Do not wipe local `turnstile.*` rows.

## Notes

- Suggested models are **not** a launch order for advanced models.
- Wait for owner keep / change / drop (especially T006) before `/speckit-implement`.
