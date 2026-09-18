# Implementation Plan: Consent-gated audience measurement

**Branch**: `002-consent-gated-analytics` (spec directory; git remains current until the owner asks) | **Date**: 2026-09-16 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002-consent-gated-analytics/spec.md`

## Summary

Load **Google Analytics 4 through Google Tag Manager** on the public site **only after** an explicit analytics consent. Necessary cookies stay automatic. Advertising/remarketing tags stay off. Association servers keep hashed cookie-audit identifiers only (no raw IP for measurement). Conversion events `donate_success`, `donate_recurring_success`, `volunteer_success`, and `contact_success` carry no personal fields.

**Cookie and privacy adaptation is in this plan, not a later spec.** Sequence is mandatory: (1) consent gate + Tag Manager inject + events + reopen/withdraw, (2) **then** operational banner notes, cookie policy, and privacy policy in Italian and English so they describe the live tool. After owner UAT the owner shows those pages to counsel; this feature MUST NOT author a DPA (`S-GDPR`). Counsel edits later are `002.K` or `006`, not silent legal drafting in implement.

Closes audit notes F-015 (no measurement) and the measurement-sentence part of F-019. Leftover processor inventory stays `006`.

Owner Google Admin remaining ticks (revisit after implement): [checklists/ga4-gtm-owner-setup.md](./checklists/ga4-gtm-owner-setup.md).

Official docs opened this turn: [Laravel configuration](https://laravel.com/docs/13.x/configuration), [Blade](https://laravel.com/docs/13.x/blade), [localization](https://laravel.com/docs/13.x/localization), [testing](https://laravel.com/docs/13.x/testing), [Pint](https://laravel.com/docs/13.x/pint), [Filament resources](https://filamentphp.com/docs/4.x/resources/overview), [Vite](https://vite.dev/guide/), [Tailwind width](https://tailwindcss.com/docs/width), [DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/), [Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header), [GTM account/container](https://support.google.com/tagmanager/answer/6103696), [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [Consent mode](https://developers.google.com/tag-platform/security/guides/consent), [GA4 cookies](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage), [GA4 custom events](https://support.google.com/analytics/answer/12229021), [GA4 EU IP](https://support.google.com/analytics/answer/12017362), [Ads Data Processing Terms](https://support.google.com/analytics/answer/3379636), [Ad Grants conversions](https://support.google.com/grants/answer/9841491), [Garante cookie guidelines](https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9677876), [Garante FAQ cookie](https://www.garanteprivacy.it/faq/cookie).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 (locked)

**Primary Dependencies**: Existing Blade + Vite-bundled public JS (`cookie-consent.js`). No new Composer/npm packages. Google Tag Manager snippet injected by our JS after consent ([GTM web](https://developers.google.com/tag-platform/tag-manager/web)). GA4 is configured **inside** the owner’s Tag Manager container, not as a second on-page gtag product.

**Storage**: Existing `gdpr_consents` (hashed IP/UA). No new measurement tables. No raw visitor IP for analytics.

**Testing**: PHPUnit Feature tests for config kill-switch, boot payload, no GTM hosts in HTML until enabled+consented path is described, conversion markers without PII, legal-page operational strings, footer reopen control. JS network gating and GA4 dashboard = owner UAT. No coverage %. No i18n snapshots of whole policies.

**Target Platform**: Local DDEV `https://safehouse-community-site.ddev.site`. Production `.env` / Caddy apply / CMS overwrite only when the owner later asks to publish.

**Project Type**: Existing Laravel web app; SITE queue S02a.

**Performance Goals**: Essentials-only sessions send zero measurement-product requests (SC-001). Staff can see 20 consented test page views in the GA4 dashboard (SC-002, owner/ops delay).

**Constraints**: Do not `ddev stop`. Do not `config:cache` in DDEV. Do not write CRM. Do not apply production Caddy unless the owner asks that turn. Do not author DPA/DPIA. Do not install Ads/remarketing tags. Do not commit GTM/GA IDs. Public locales it+en only.

**Scale/Scope**: Public layout boot + consent JS + four conversion markers + footer reopen + dismiss-without-accept + operational cookie/privacy/banner copy (IT/EN) + Caddy CSP *documented* for owner-ops.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
| :--- | :--- | :--- |
| I SDD lock-in | PASS | 002 only; implement later from `tasks.md`; do not start 003–006 |
| II Repo SoR | PASS | Plan, research, contracts, progress in this tree |
| III Official docs | PASS | URLs opened this turn; cited above and in research.md |
| IV Spec persistence | PASS | Living spec 002 (not yet implemented); changelog already on spec |
| V Meaningful tests | PASS | Propose consent-gate + kill-switch + no-PII events + policy-string contracts; dashboard/network = owner UAT |
| VI Security / PII | PASS | No secrets in artifacts; hashed consent audit unchanged; no raw IP store for measurement |
| VII Stack freeze | PASS | No new libs; it+en; CMS legal pages stay Filament-edited |
| VIII Owner UAT | PASS | Checklist at implement; Russian script; browser offer + wait |
| IX Ask on doubt | PASS | Product locked in spec; basic consent (no GTM until accept) chosen here |
| X Write this repo | PASS | No CRM writes |
| XI Model table | N/A | Printed at `/speckit-tasks` |
| XII Errors/logs | PASS | Missing GTM id → no inject, no page error; do not log container IDs in visitor-facing errors |
| S-STRIPE | PASS | Untouched except thank-you event marker (no amount/name in payload) |
| S-I18N | PASS | Banner/policies it+en; no `/ru` |
| S-CSP | PASS | CSP stays in Caddy; snippet updated in repo for owner-ops; **do not apply** unless asked |
| S-CMS-PATH | PASS | No new CMS path; legal pages remain `/cms-safehouse` resources |
| S-GDPR | PASS | Operational cookie/privacy **in scope**; DPA = STOP; counsel review is **after** UAT (owner) |
| S-NO-OCTANE | PASS | No queues for this feature |
| S-SITE-QUEUE | PASS | Cookie/privacy **measurement sentences** pulled into 002 by owner (2026-09-16); 006 leftover inventory not implemented here |

**Post-design re-check**: PASS. Design is config + public JS inject + dataLayer events + banner/footer + LegalPagesContent/sync. Complexity table empty.

## Project Structure

### Documentation (this feature)

```text
specs/002-consent-gated-analytics/
├── spec.md
├── plan.md              # This file
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── measurement-boot.md
│   ├── consent-ui.md
│   ├── conversion-events.md
│   ├── policy-copy.md
│   └── owner-ops.md
├── checklists/
│   ├── requirements.md
│   ├── ga4-gtm-owner-setup.md
│   └── owner-user-tests.md   # written at /speckit-tasks / implement
└── tasks.md
```

### Source Code (repository root)

```text
config/measurement.php                          # new; env-backed kill-switch + container id
.env.example                                    # MEASUREMENT_ENABLED, GTM_CONTAINER_ID (empty)
resources/views/layouts/app.blade.php           # boot JSON; no GTM snippet in HTML
resources/views/layouts/partials/cookie-banner.blade.php
resources/views/layouts/partials/footer.blade.php   # reopen control
resources/views/layouts/partials/measurement-status.blade.php  # legal pages live/off line
resources/views/pages/templates/legal.blade.php
resources/views/donations/thank-you.blade.php
resources/views/pages/partials/volunteer-form-shell.blade.php
resources/views/pages/partials/contact-form-shell.blade.php
resources/js/app.js
resources/js/cookie-consent.js                  # dismiss = essential; reopen; notify measurement
resources/js/measurement.js                     # new; inject GTM after analytics accept
app/Http/Controllers/VolunteerController.php    # real success vs honeypot marker
app/Http/Controllers/ContactSubmissionController.php
lang/it/site.php
lang/en/site.php
database/seeders/Data/LegalPagesContent.php     # operational IT/EN (and unused ru bodies unchanged/minimal)
deploy/Caddyfile.snippet                        # document GA/GTM hosts; do not apply
tests/Feature/CookieConsentTest.php
tests/Feature/MeasurementGateTest.php           # new
tests/Feature/CmsPagesTest.php                  # legal strings after sync
```

**Structure Decision**: Single Laravel app. No Filament analytics dashboard. No Caddy reload from implement. No CRM.

## Complexity Tracking

> No constitution violations that need a workaround.
