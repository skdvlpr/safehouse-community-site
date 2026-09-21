# Implementation Plan: Operational cookie and privacy pages

**Branch**: `002.3-operational-cookie-privacy` | **Date**: 2026-09-21 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002.3-operational-cookie-privacy/spec.md`

## Summary

Replace Italian and English (and unpublished Russian seeder) cookie/privacy CMS bodies so they match live processors and the live banner. Preview via `site:sync-legal-pages --force`. Do not overwrite production. Do not change banner JS, Caddy, or measurement events.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Filament v4 CMS pages; `LegalPagesContent` seeder data; `site:sync-legal-pages`

**Storage**: MariaDB `pages` JSON translations (existing)

**Testing**: PHPUnit `CmsPagesTest` (+ forbidden-phrase assertions)

**Target Platform**: Public site it+en; local DDEV preview first

**Project Type**: web application (copy-only)

**Performance Goals**: N/A (static CMS HTML)

**Constraints**: No DPA; no EspoCRM hostname; no public `/ru`; no production CMS overwrite in this feature

**Scale/Scope**: Two public legal pages × two locales

Vendor docs this plan turn: [Laravel localization](https://laravel.com/docs/13.x/localization); [Filament resources](https://filamentphp.com/docs/4.x/resources/overview); [GA4 cookies](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage); [Turnstile widgets](https://developers.cloudflare.com/turnstile/concepts/widget/); [Stripe Privacy Policy](https://stripe.com/privacy); [Google Workspace privacy](https://support.google.com/a/answer/60762).

## Constitution Check

- One active spec: `002.3` amendment of `002`, S04 brought forward. Pass.
- No CRM repo writes. Pass.
- No DPA. Pass.
- Implement from `tasks.md` only. Pass.
- Production legal sync is owner-ops, not default deploy. Pass.

## Project Structure

### Documentation (this feature)

```text
specs/002.3-operational-cookie-privacy/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/policy-copy.md
└── tasks.md
```

### Source Code

```text
database/seeders/Data/LegalPagesContent.php
app/Console/Commands/SyncLegalPagesCommand.php  # existing, no behaviour change
tests/Feature/CmsPagesTest.php
```

## Complexity Tracking

Copy-only; no new tables or endpoints.
