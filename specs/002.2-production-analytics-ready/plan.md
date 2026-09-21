# Implementation Plan: Production analytics ready (002.2)

**Branch**: `002.2-production-analytics-ready` (spec directory) | **Date**: 2026-09-21 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002.2-production-analytics-ready/spec.md`

**Note**: This template is filled in by `/speckit-plan`. Implement is **not** this command.

## Summary

Unblock **live** consented GA4 collection on safehouse.community by applying the public CSP (GTM + Analytics + Preview hosts, CMS excluded) with the existing one-shot Caddy script. Split contact conversions into three desk names. Combined owner UAT uses Realtime/DebugView, not Home. Owner publishes GTM tags **before** implement UAT.

Official this plan: [Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header), [GTM CSP](https://developers.google.com/tag-platform/security/guides/csp), [GA4 Realtime](https://support.google.com/analytics/answer/9271392), [GTM publish](https://support.google.com/tagmanager/answer/6107163), [custom events](https://support.google.com/analytics/answer/12229021), [Laravel testing](https://laravel.com/docs/13.x/testing), [Blade](https://laravel.com/docs/13.x/blade), [Pint](https://laravel.com/docs/13.x/pint).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Caddy v2 `header` CSP; existing `deploy/apply-caddy-site-once.sh`; GTM web container (owner); first-party `measurement.js`

**Storage**: No new tables. Session flag `measurement_conversion` values change.

**Testing**: PHPUnit Feature — desk → event name; honeypot; allowlist of names in JS ([testing](https://laravel.com/docs/13.x/testing))

**Target Platform**: Production VPS edge (`safehouse.community`) + same Laravel app locally

**Project Type**: Web application (single Laravel tree)

**Performance Goals**: Consented live homepage visible in Realtime within 5 minutes (Google best-effort)

**Constraints**: `S-CSP` Caddy only; no Ads hosts; one-shot self-delete; no CRM writes; no DPA; no `config:cache`

**Scale/Scope**: One Caddy snippet update; contact controller + Blade + `measurement.js` allowlist; docs/UAT; SSH apply

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Result |
| :--- | :--- |
| I Spec-driven | PASS — 002.2 directory; implement later from tasks.md |
| II SoR | PASS — specs/002.2 + progress |
| III Vendor docs | PASS — Caddy, GTM CSP, Realtime, custom events, testing, Blade, Pint opened this turn |
| IV Persistence | PASS — Extends 002; lineage 002.2 |
| V Tests | PASS — propose desk-mapping tests only |
| VI–VII i18n | PASS — no new public copy required; event names are ASCII |
| VIII UAT | PASS — combined checklist; Cursor-browser offer/wait |
| IX Ask | PASS — Realtime vs Home delay stated; no fork |
| X Write site only | PASS |
| XI Models | deferred to `/speckit-tasks` table |
| XII Logs | PASS — unknown desk: no silent `contact_success` |
| S-CSP | PASS — Caddy snippet + one-shot apply |
| S-GDPR | PASS — operational legal sync optional; no DPA |
| S-SITE-QUEUE | PASS — owner interrupt of 002 UAT, not 003 |

**Post-design re-check**: PASS. Design is CSP host list + event-name map + owner GTM contract + SSH apply. No nonce migration. No Ads CSP.

## Project Structure

### Documentation (this feature)

```text
specs/002.2-production-analytics-ready/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── caddy-csp.md
│   ├── contact-events.md
│   ├── gtm-owner.md
│   └── owner-ops.md
├── checklists/
│   ├── requirements.md
│   └── owner-user-tests.md
└── tasks.md
```

### Source Code (repository root)

```text
deploy/Caddyfile.snippet
deploy/apply-caddy-site-once.sh
app/Http/Controllers/ContactSubmissionController.php
resources/views/pages/partials/contact-form-shell.blade.php
resources/js/measurement.js
tests/Feature/MeasurementConversionTest.php
```

**Structure Decision**: Single Laravel app. Edge config in `deploy/`. No new packages.

## Complexity Tracking

> None — no constitution violations to justify.
