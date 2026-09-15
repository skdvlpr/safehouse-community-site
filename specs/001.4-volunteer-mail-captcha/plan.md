# Implementation Plan: Volunteer mail + captcha layout (001.4)

**Branch**: `001.4-volunteer-mail-captcha` (spec directory; git remains current until the owner asks) | **Date**: 2026-09-15 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001.4-volunteer-mail-captcha/spec.md`

## Summary

Replace the unused volunteer-application table with two outbound emails (Italian staff notice to Matteo; localized applicant acknowledgement). Require last name, phone, and message. Fix the public bot-challenge box so it is centred or field-width on contact and volunteer, and follow site light/dark when the provider allows. Local migrate drops `volunteers`; production drop waits for the owner’s later publish ([owner-ops](./contracts/owner-ops.md)). No CRM write. No `002`.

Official docs opened this turn: [Laravel mail](https://laravel.com/docs/13.x/mail#sending-mail), [mail testing](https://laravel.com/docs/13.x/mail#testing-mailables), [Form Requests](https://laravel.com/docs/13.x/validation#form-request-validation), [Laravel testing](https://laravel.com/docs/13.x/testing), [migrations drop](https://laravel.com/docs/13.x/migrations#dropping-tables), [Turnstile embed/theme/size](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configuration-options), [Tailwind width](https://tailwindcss.com/docs/width), [justify-content](https://tailwindcss.com/docs/justify-content), [Vite](https://vite.dev/guide/), [DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/), [Laravel Pint](https://laravel.com/docs/13.x/pint).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 (locked)

**Primary Dependencies**: Existing Mail + `OutboundMailConfigurator` (sportelli From/SMTP). Blade + Tailwind for widget layout. Existing `TurnstileVerifier` (no key/CMS changes). Vite-bundled `theme.js` for copying `html[data-theme]` onto widgets. No new Composer/npm packages.

**Storage**: Drop `volunteers` on preview via migration. No new tables. Mail is the store.

**Testing**: Rewrite `VolunteerFormTest` with `Mail::fake` (staff + applicant; validation; honeypot; Turnstile). Remove model/factory persistence tests. No CSS snapshots. No coverage %. Owner UAT for layout/theme.

**Target Platform**: Local DDEV `https://safehouse-community-site.ddev.site`. Production inspect-only / no live migrate this session.

**Project Type**: Existing Laravel web app; S01 amendment `001.4`.

**Performance Goals**: Owner confirms widget alignment in under 30 seconds (SC-003). One complete submit produces two emails (SC-001).

**Constraints**: Do not `ddev stop`. Do not `config:cache`. Do not write CRM or production. Do not inspect live volunteer rows. S-CSP unchanged. Keep honeypot + throttle. Captcha Settings page untouched.

**Scale/Scope**: Volunteer POST path + two Mailables + drop migration + form copy it+en + widget CSS/theme on two public forms.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
| :--- | :--- | :--- |
| I SDD lock-in | PASS | 001.4 only; implement later from `tasks.md`; do not start 002–008 |
| II Repo SoR | PASS | Plan, research, contracts, progress in this tree |
| III Official docs | PASS | URLs opened this turn; cited above and in research.md |
| IV Spec persistence | PASS | Dotted `001.4`; finish before `002` |
| V Meaningful tests | PASS | Propose mail + validation + no-store; layout = owner UAT |
| VI Security / PII | PASS | No volunteer PII table; fail closed on mail; no secrets in artifacts |
| VII Stack freeze | PASS | No new libs; it+en public copy; staff mail Italian |
| VIII Owner UAT | PASS | Checklist at implement; Russian script; browser offer + wait |
| IX Ask on doubt | PASS | Inbox hardcoded as specified; CRM deferred |
| X Write this repo | PASS | No CRM writes; CRM follow-up recorded |
| XI Model table | N/A | Printed at `/speckit-tasks` |
| XII Errors/logs | PASS | SMTP failure logs warning without secrets; visitor sees error not fake success |
| S-STRIPE | PASS | Untouched |
| S-I18N | PASS | Form it+en; staff mail Italian; no `/ru` |
| S-CSP | PASS | No CSP/HSTS in PHP; widget still `challenges.cloudflare.com` |
| S-CMS-PATH | PASS | No new CMS path |
| S-GDPR | PASS | Consent checkbox stays; no DPA wording; no new consent legal text |
| S-NO-OCTANE | PASS | `Mail::send`, not queued |

**Post-design re-check**: PASS. Design is mailables + Form Request + drop migration + widget CSS/theme copy. Complexity table empty.

## Project Structure

### Documentation (this feature)

```text
specs/001.4-volunteer-mail-captcha/
├── spec.md
├── plan.md              # This file
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── volunteer-http.md
│   ├── captcha-ui.md
│   ├── copy.md
│   └── owner-ops.md
├── checklists/
│   ├── requirements.md
│   └── owner-user-tests.md   # created at implement
└── tasks.md                  # /speckit-tasks (not this command)
```

### Source Code (repository root)

```text
app/Http/Controllers/VolunteerController.php
app/Http/Requests/StoreVolunteerRequest.php
app/Services/VolunteerService.php          # mail, not Eloquent create
app/Mail/VolunteerStaffMail.php            # new
app/Mail/VolunteerApplicantMail.php        # new
config/volunteer.php                       # staff inbox
database/migrations/…_drop_volunteers_table.php
lang/it/site.php
lang/en/site.php
resources/views/pages/partials/volunteer-form-shell.blade.php
resources/views/pages/partials/contact-form-shell.blade.php
resources/css/app.css                      # widget alignment
resources/js/theme.js                      # copy data-theme onto widgets
resources/views/pages/volunteer.blade.php  # turnstile script stack
resources/views/pages/contact.blade.php    # same if script is pushed there
tests/Feature/VolunteerFormTest.php
# remove: app/Models/Volunteer.php, database/factories/VolunteerFactory.php,
#         tests/Feature/VolunteerModelTest.php (or reduce to “table absent”)
```

**Structure Decision**: Single Laravel app. No Filament page. No Caddy. No CRM.

## Complexity Tracking

> No constitution violations that need a workaround.
