# Tasks: CMS measurement settings (002.1)

**Input**: Design documents from `/specs/002.1-cms-measurement-settings/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Propose only CMS-vs-env and invalid-id logic ([Laravel testing](https://laravel.com/docs/13.x/testing), [Filament testing](https://filamentphp.com/docs/4.x/testing/overview)). No coverage %. Owner combined UAT of 002+002.1 after implement.

**Organization**: Tasks grouped by user story. Each item: `test: yes/no`; complexity `C1`–`C10`; proposed model.

**Models**: Default **inherit** (this session). Owner ordered the full specify→plan→tasks→implement cycle this turn. This file is **not** a launch of Opus/GPT.

Official pages: [custom pages](https://filamentphp.com/docs/4.x/navigation/custom-pages), [text input](https://filamentphp.com/docs/4.x/forms/text-input), [encryption](https://laravel.com/docs/13.x/encryption), [configuration](https://laravel.com/docs/13.x/configuration), [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [Pint](https://laravel.com/docs/13.x/pint).

## Format: `[ID] [P?] [Story] Description`

---

## Phase 1: Setup

- [X] T001 Confirm `.specify/feature.json` is `specs/002.1-cms-measurement-settings`. Comment in `.env.example` that CMS Integrations is the staff entry; env is fallback only. Do not put a live id. https://laravel.com/docs/13.x/configuration (test: no; C1; model: inherit)

---

## Phase 2: Foundational

- [X] T002 Add `measurement.enabled` and `measurement.container_id` to `config/site_settings.php` (`encrypted` false; `config` keys on `measurement.*`). https://laravel.com/docs/13.x/encryption (test: yes covered by T006; C2; model: inherit)
- [X] T003 [P] CMS copy it+en in `lang/it/cms.php` and `lang/en/cms.php`: tab label, section, field labels, helpers (no snippets, no `G-` field) (test: no; C2; model: inherit)

**Checkpoint**: Catalog exists before the tab

---

## Phase 3: User Story 1 — Staff find measurement on Integrations (P1)

- [X] T004 [US1] Add measurement tab to `app/Filament/Pages/ManageIntegrations.php`: toggle + container id; `canAccess` unchanged; Stripe/CRM/mail untouched. https://filamentphp.com/docs/4.x/navigation/custom-pages · https://filamentphp.com/docs/4.x/forms/text-input (test: yes covered by T006; C4; model: inherit)

---

## Phase 4: User Story 2 — Save drives public boot (P1)

- [X] T005 [US2] `app/Services/MeasurementBootService.php` reads CMS via `SiteSettingsService` / `IntegrationConfig` (CMS row wins; env fallback). Preview/CMS still never bootable. Invalid id ⇒ off. https://laravel.com/docs/13.x/configuration (test: yes covered by T006; C5; model: inherit)
- [X] T006 [US2] Add `tests/Feature/MeasurementSettingsTest.php`: super-admin Livewire save enabled+`GTM-TEST1` ⇒ public home boot node enabled; CMS enabled=`0` overrides `config(['measurement.enabled'=>true])`; invalid id ⇒ not bootable. Keep env-fallback cases in `MeasurementGateTest`. https://laravel.com/docs/13.x/testing · https://filamentphp.com/docs/4.x/testing/overview (test: yes; C6; model: inherit)

---

## Phase 5: User Story 3 — No live ids in git

- [X] T007 [US3] Grep tree so seeders/specs/`.env.example` have no live container id; do not save a real id into local CMS this turn ([owner-ops.md](./contracts/owner-ops.md)) (test: no; C1; model: inherit)

---

## Phase 6: Polish

- [X] T008 `ddev exec ./vendor/bin/pint` and `ddev exec php artisan test` ([Pint](https://laravel.com/docs/13.x/pint)). No frontend rebuild unless JS/CSS change (they should not).
- [X] T009 Combined owner UAT checklist `checklists/owner-user-tests.md` + progress; do not git commit/push; do not production Caddy/CMS.

---

## Dependencies

Setup → catalog/copy → tab → boot service + tests → polish.

Owner asked **full cycle this turn** (no wait after the model table).
