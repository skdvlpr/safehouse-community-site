# Implementation Plan: Urgent visual and legal correction

**Branch**: `002.4-urgent-visual-legal` | **Date**: 2026-09-21 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002.4-urgent-visual-legal/spec.md`

## Summary

Local-only hotfix: wide opaque cookie banner; self-hosted [Nunito Sans](https://fonts.google.com/specimen/Nunito+Sans); heavier header; landing two-column hero + cards; legal hero solid panel; Anzio seat; Contatti FAQ button; membership modal from the official Word form (mail required, Espo Lead Associato best-effort). No git push / no production.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Blade, Tailwind v4 `@theme --font-sans`, Vite, Filament CMS pages (read/update bodies locally), Espo REST `POST Lead` ([API overview](https://docs.espocrm.com/development/api/)), Cloudflare Turnstile ([embed widget](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/))

**Storage**: Existing `pages` JSON; no memberships table

**Testing**: PHPUnit Feature/Unit ([Laravel testing](https://laravel.com/docs/13.x/testing))

**Target Platform**: DDEV `https://safehouse-community-site.ddev.site` only

**Project Type**: web application

**Performance Goals**: Self-hosted woff2; no fonts.googleapis.com request

**Constraints**: Owner 2026-09-21 authorised git push + GitHub Actions deploy for this US7 amendment. MUST NOT production `site:sync-legal-pages`. No CRM repo writes; no DPA; no public `/ru`; consent gate unchanged.

**Scale/Scope**: Public chrome + one POST `/membership-application`

Vendor docs this plan turn: [Laravel validation](https://laravel.com/docs/13.x/validation); [Laravel Blade](https://laravel.com/docs/13.x/blade); [Filament repeater](https://filamentphp.com/docs/4.x/forms/repeater); [Tailwind prefers-reduced-motion](https://tailwindcss.com/docs/hover-focus-and-other-states#prefers-reduced-motion); [Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API); [WCAG 2.2.2 Pause, Stop, Hide](https://www.w3.org/WAI/WCAG22/Understanding/pause-stop-hide.html); [Laravel mail](https://laravel.com/docs/13.x/mail); [Laravel routing rate limiting](https://laravel.com/docs/13.x/routing#rate-limiting); [Tailwind font-family](https://tailwindcss.com/docs/font-family); [HTML dialog](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dialog); [Nunito Sans](https://fonts.google.com/specimen/Nunito+Sans); [Turnstile embed](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/); [Espo API](https://docs.espocrm.com/development/api/).

## Constitution Check

- One active spec: `002.4` amendment before `003`. Pass.
- No CRM schema edits in `nonprofit-espocrm`. Pass.
- No DPA. Pass.
- Implement from `tasks.md`. Pass.
- No `git push` unless owner later asks. **Exception 2026-09-21:** owner authorised push/deploy of US7; still no production legal-page sync. Pass.

## Project Structure

### Documentation (this feature)

```text
specs/002.4-urgent-visual-legal/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/membership-http.md
├── contracts/crm-agent-lead-associato.md
└── tasks.md
```

### Source Code

```text
app/Support/LandingContent.php
app/Filament/Resources/PageResource/Support/PageTemplateFormFields.php
resources/js/landing-motion.js
resources/css/app.css
resources/js/app.js
resources/js/socio-dialog.js
public/fonts/NunitoSans[wght].woff2
resources/views/pages/templates/{landing,legal,contact}.blade.php
resources/views/pages/partials/membership-dialog.blade.php
app/Http/Controllers/MembershipApplicationController.php
app/Services/MembershipApplicationService.php
database/seeders/Data/LegalPagesContent.php
```

## Complexity Tracking

Membership POST copies volunteer mail; Lead create is best-effort using existing Espo fields. Native `<dialog>` instead of a JS modal library.
