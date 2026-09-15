# Implementation Plan: CMS captcha settings (001.3)

**Branch**: `001.3-cms-captcha-settings` (spec directory; git remains current until the owner asks) | **Date**: 2026-09-15 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001.3-cms-captcha-settings/spec.md`

## Summary

Give staff a **Settings-level CMS screen** for the public bot challenge (on/off, public key, secret), next to Integrations, using the same encrypted-secret pattern as Stripe. Remove captcha controls from Sportelli so there is one editor. Public contact and volunteer already consume `TurnstileVerifier`; this feature must not re-wire those forms, wipe local keys, write production, reload Caddy, or onboard a Cloudflare DNS zone.

Official docs opened this turn: [Filament v4 custom pages](https://filamentphp.com/docs/4.x/navigation/custom-pages), [Filament v4 text input (password / revealable)](https://filamentphp.com/docs/4.x/forms/text-input), [Filament v4 testing](https://filamentphp.com/docs/4.x/testing/overview), [Laravel encryption](https://laravel.com/docs/13.x/encryption), [Laravel testing](https://laravel.com/docs/13.x/testing), [Cloudflare Turnstile get started](https://developers.cloudflare.com/turnstile/get-started/), [Laravel Pint](https://laravel.com/docs/13.x/pint).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 (locked)

**Primary Dependencies**: Filament v4 custom page + form schema (already used by `ManageIntegrations` / `ManageSportelliConfig`). Existing `SiteSettingsService` + `TurnstileVerifier`. No new Composer/npm packages.

**Storage**: Existing `site_settings` rows `turnstile.enabled`, `turnstile.site_key`, `turnstile.secret_key` (`encrypted` already true for the secret). **No migration.**

**Testing**: Propose one Feature test: CMS save with blank secret does not wipe stored secret; incomplete keys ⇒ `TurnstileVerifier::enabled()` false. Existing `VolunteerFormTest` / `TurnstileVerifierTest` stay. No coverage %. Owner combined UAT of 001.2 + 001.3 after implement.

**Target Platform**: Local DDEV `https://safehouse-community-site.ddev.site/cms-safehouse`. Production inspect-only for public contact widget; no production CMS writes.

**Project Type**: Existing Laravel web app; S01 amendment `001.3`.

**Performance Goals**: Staff open the captcha item from Settings in under 60 seconds (SC-001). Combined owner UAT in one sitting after implement.

**Constraints**: Super-admin only (`canAccess` same as Integrations/Sportelli). Do not `ddev stop`. Do not `php artisan config:cache` in DDEV. Do not write CRM. Do not onboard Cloudflare zone. Do not paste keys to production. S-CSP unchanged. Implement must not clear local keys already stored 2026-09-15.

**Scale/Scope**: One new CMS page, Sportelli captcha tab removed, copy it+en, helper text that currently points captcha at Sportelli updated. No public Blade/Form Request changes unless a bug is found that the screen cannot save.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
| :--- | :--- | :--- |
| I SDD lock-in | PASS | 001.3 only; implement later from `tasks.md`; do not start 002–008 |
| II Repo SoR | PASS | Plan, research, contracts, progress in this tree |
| III Official docs | PASS | URLs opened this turn; cited above and in research.md |
| IV Spec persistence | PASS | Dotted `001.3`; finish before `002` implement |
| V Meaningful tests | PASS | Propose only blank-secret / incomplete-keys logic; owner may drop at tasks |
| VI Security / PII | PASS | Secret encrypted at rest; never in public HTML; no secrets in git/chat |
| VII Stack freeze | PASS | No new libs; Filament v4 page; it+en CMS copy |
| VIII Owner UAT | PASS | Combined 001.2+001.3 checklist at implement; Russian script; browser offer + wait |
| IX Ask on doubt | PASS | Dedicated Settings page (not a tab inside Stripe). Sportelli loses captcha fields |
| X Write this repo | PASS | No CRM writes |
| XI Model table | N/A | Printed at `/speckit-tasks` |
| XII Errors/logs | PASS | No new swallow; verifier already fail-closed on incomplete keys |
| S-STRIPE | PASS | Payment Element / Integrations Stripe tab untouched |
| S-I18N | PASS | CMS labels it+en; no public `/ru` |
| S-CSP | PASS | No CSP/HSTS in PHP; no live Caddy |
| S-CMS-PATH | PASS | `/cms-safehouse` |
| S-GDPR | PASS | No DPA wording |

**Post-design re-check**: PASS. Design is a Filament page + copy + Sportelli field removal on existing `site_settings` keys. Complexity table empty.

## Project Structure

### Documentation (this feature)

```text
specs/001.3-cms-captcha-settings/
├── spec.md
├── plan.md              # This file
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── cms-ui.md
│   └── owner-ops.md
├── checklists/
│   ├── requirements.md
│   └── owner-user-tests.md   # created at implement
└── tasks.md                  # /speckit-tasks (not this command)
```

### Source Code (repository root)

```text
app/Filament/Pages/ManageCaptchaSettings.php   # new; discoverPages
app/Filament/Pages/ManageSportelliConfig.php   # remove turnstile tab/mount/save
lang/it/cms.php
lang/en/cms.php
tests/Feature/…CaptchaSettings…php             # proposed
tests/Unit/TurnstileVerifierTest.php           # keep
tests/Feature/VolunteerFormTest.php            # keep
```

Public contact/volunteer Blade and Form Requests: **no change** unless implement finds the new page cannot drive `TurnstileVerifier`.

**Structure Decision**: Single Laravel app. Panel already `discoverPages` in `AdminPanelProvider`. No Caddy, no migration, no CRM.

## Complexity Tracking

> No constitution violations that need a workaround.
