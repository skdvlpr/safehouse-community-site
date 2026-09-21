# Research: Operational cookie and privacy pages

**Date**: 2026-09-21

## Decision 1 — Source of truth

**Decision**: Keep `database/seeders/Data/LegalPagesContent.php` as the authored source. Apply locally with `ddev exec php artisan site:sync-legal-pages --force` ([Artisan](https://laravel.com/docs/13.x/artisan); [Filament resources](https://filamentphp.com/docs/4.x/resources/overview) remain the CMS editor).

**Rationale**: Same path as 002 US5. Deploy does not auto-overwrite CMS HTML.

## Decision 2 — Processor names on the public page

**Decision**: Name Aruba Cloud (hosting, Italy), Google Workspace for Nonprofits (mail), Stripe, Cloudflare Turnstile, Google Analytics 4 / Tag Manager, Google Calendar/Drive (staff). Describe internal accounting and desk files without the CRM product name.

**Rationale**: Owner asked for current configuration. Tests already forbid EspoCRM / CRM hostname.

## Decision 3 — Analytics event names

**Decision**: Prefer ordinary language on the public pages (donazione, volontariato, contatto / sportelli). Do not list `contact_success`. Optional machine names only if they match 002.2.

**Rationale**: Owner asked not to put unnecessary internals in the documents.

## Decision 4 — Banner UX in the cookie document

**Decision**: Document the shipped UX: analytics proposed selected; uncheck allowed; no load until Accetta tutti or Salva; reopen from footer and cookie-page button.

**Rationale**: Matches `0cdb4e0` and owner answers.

## Alternatives considered

- Edit only in Filament and skip the seeder: rejected; git would drift.
- Auto-sync on production deploy: rejected; owner reviews locally first.
