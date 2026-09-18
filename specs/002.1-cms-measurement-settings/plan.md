# Implementation Plan: CMS measurement settings (002.1)

**Branch**: `002.1-cms-measurement-settings` (spec directory; git remains current until the owner asks) | **Date**: 2026-09-17 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002.1-cms-measurement-settings/spec.md`

## Summary

Add a **measurement tab** on the existing Filament Integrations page (on/off + `GTM-` container id). Public `MeasurementBootService` reads `SiteSettingsService` / `IntegrationConfig` first (CMS wins), then env/`config/measurement.php` fallback. No new packages, no migration, no G- Measurement ID field, no production writes.

Official docs opened this turn: [Filament v4 custom pages](https://filamentphp.com/docs/4.x/navigation/custom-pages), [Filament v4 text input](https://filamentphp.com/docs/4.x/forms/text-input), [Filament v4 testing](https://filamentphp.com/docs/4.x/testing/overview), [Laravel encryption](https://laravel.com/docs/13.x/encryption), [Laravel configuration](https://laravel.com/docs/13.x/configuration), [Laravel testing](https://laravel.com/docs/13.x/testing), [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [Laravel Pint](https://laravel.com/docs/13.x/pint).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 (locked)

**Primary Dependencies**: Existing `ManageIntegrations`, `SiteSettingsService`, `IntegrationConfig`, `MeasurementBootService`. Filament v4 tabs/toggle/text input. No new Composer/npm packages.

**Storage**: Existing `site_settings` table. New keys `measurement.enabled` (plain) and `measurement.container_id` (plain, like Stripe publishable). Secrets already on that page stay `Crypt::encryptString`. **No migration.**

**Testing**: Feature: CMS save drives boot; CMS off wins over env on; invalid id ⇒ not bootable. Keep `MeasurementGateTest` env-fallback cases. No coverage %. Combined 002+002.1 owner UAT after implement.

**Target Platform**: Local DDEV `https://safehouse-community-site.ddev.site/cms-safehouse`. No production CMS writes.

**Project Type**: Existing Laravel web app; S02 amendment `002.1`.

**Performance Goals**: Staff save in under two minutes (SC-001). Combined UAT in one sitting.

**Constraints**: Super-admin only. Do not `ddev stop`. Do not `config:cache` in DDEV. Do not write CRM. Do not commit live GTM/G- ids. Do not apply production Caddy. S-CSP unchanged. Do not paste GTM snippets.

**Scale/Scope**: One Integrations tab + boot service reads CMS + lang it+en + tests. No banner/JS/legal copy changes unless a proven regression.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
| :--- | :--- | :--- |
| I SDD lock-in | PASS | 002.1 only; implement from `tasks.md`; do not start 003 |
| II Repo SoR | PASS | Plan, research, contracts, progress in this tree |
| III Official docs | PASS | URLs opened this turn; cited above |
| IV Spec persistence | PASS | Dotted `002.1`; finish before `003` |
| V Meaningful tests | PASS | Propose CMS-vs-env and invalid-id logic; owner asked full cycle |
| VI Security / PII | PASS | No live ids in git; container id is public-after-consent; existing secrets stay encrypted |
| VII Stack freeze | PASS | No new libs; Filament tab; it+en CMS copy |
| VIII Owner UAT | PASS | Combined 002+002.1 checklist at implement; Russian script; browser offer + wait |
| IX Ask on doubt | PASS | Tab on Integrations (not a third Settings page); container id not encrypted (readable like publishable) |
| X Write this repo | PASS | No CRM writes |
| XI Model table | PASS | Printed in tasks; owner said run full cycle |
| XII Errors/logs | PASS | Invalid id ⇒ off, no page error; do not log ids in visitor errors |
| S-STRIPE | PASS | Stripe tab untouched except sibling tab |
| S-I18N | PASS | CMS labels it+en; no public `/ru` |
| S-CSP | PASS | No CSP/HSTS in PHP; no live Caddy |
| S-CMS-PATH | PASS | `/cms-safehouse` |
| S-GDPR | PASS | No DPA wording |

**Post-design re-check**: PASS. Design is catalog keys + Integrations tab + `MeasurementBootService` via `IntegrationConfig`.

## Project Structure

### Documentation (this feature)

```text
specs/002.1-cms-measurement-settings/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── cms-ui.md
│   └── owner-ops.md
├── checklists/
│   ├── requirements.md
│   └── owner-user-tests.md
└── tasks.md
```

### Source Code (repository root)

```text
config/site_settings.php
config/measurement.php
app/Support/IntegrationConfig.php          # existing
app/Services/MeasurementBootService.php
app/Filament/Pages/ManageIntegrations.php
lang/it/cms.php
lang/en/cms.php
.env.example                               # comment: CMS primary, env fallback
tests/Feature/MeasurementSettingsTest.php
tests/Feature/MeasurementGateTest.php      # CMS override case
```

## Complexity Tracking

None — no constitution violations.
