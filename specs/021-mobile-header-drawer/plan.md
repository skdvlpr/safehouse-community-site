# Implementation Plan: Mobile header drawer

**Branch**: `021-mobile-header-drawer` | **Date**: 2026-09-25 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/021-mobile-header-drawer/spec.md`

## Summary

Phone visitors cannot open the site menu because the long donate label fills the header. Replace the `details` dropdown with a left-sliding drawer toggled by a hamburger, keep a short donate label on a phone, center the 5 x 1000 strip, and shorten the Contatti heading line. Wide screens keep the row menu and the long donate label. No CRM, no Diventa socio, no `019-donations-row`.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Blade ([Blade](https://laravel.com/docs/13.x/blade)), Tailwind drawer motion ([translate](https://tailwindcss.com/docs/translate), [transition](https://tailwindcss.com/docs/transition-property), [backdrop-blur](https://tailwindcss.com/docs/backdrop-blur), [z-index](https://tailwindcss.com/docs/z-index), [width](https://tailwindcss.com/docs/width)), Vite module in `resources/js/`

**Storage**: None

**Testing**: PHPUnit feature assertions for hamburger markup, donate strings, and Contatti heading ([testing](https://laravel.com/docs/13.x/testing)). Drawer animation and strip centering are owner UAT.

**Target Platform**: Local DDEV (`https://safehouse-community-site.ddev.site`) until the owner asks to push

**Project Type**: web application

**Performance Goals**: No new queries. Drawer CSS transition only.

**Constraints**: Italian primary, English second. No public `/ru`. Do not change Diventa socio. Do not implement `019-donations-row`. Do not edit the CRM repo. CSP stays Caddy-only. No new JS framework.

**Scale/Scope**: Header drawer, 5 x 1000 strip, Contatti heading + left column + form surname, one small JS module, lang files, `contact_submissions.last_name` migration, Lead `lastName` mapping.

## Constitution Check

- One spec implemented at a time. This spec interrupts the page-heading queue by owner request. Pass.
- `019-donations-row` stays specified-only. Pass.
- No CRM edits. Pass.
- Stop for owner test before returning to Donations. Pass.
- Vendor docs cited this plan turn. Pass.

Post-design re-check: same gates. Pass.

## Project Structure

### Documentation (this feature)

```text
specs/021-mobile-header-drawer/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── header-drawer.md
└── tasks.md
```

### Source Code (repository root)

```text
resources/views/layouts/partials/header.blade.php
resources/views/layouts/partials/nav-item-mobile.blade.php
resources/views/layouts/partials/five-per-mille-banner.blade.php
resources/css/app.css
resources/js/header-drawer.js
resources/js/app.js
lang/it/site.php
lang/en/site.php
tests/Feature/SiteLayoutTest.php
tests/Feature/HomePageTest.php
tests/Feature/ContactHeadingTest.php
```

**Structure Decision**: Existing Laravel app. Drawer markup in the header partial. Motion and blur in `resources/css/app.css`. Open/close, Escape, and backdrop in `resources/js/header-drawer.js`, imported from `resources/js/app.js` like `sportello-select.js`.

## Complexity Tracking

No constitution violations.
