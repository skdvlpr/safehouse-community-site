# Implementation Plan: Home stats order, side reveals, donation form columns

**Branch**: `023-home-stats-form` | **Date**: 2026-09-25 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/023-home-stats-form/spec.md`

## Summary

Home: move the CRM numbers block so the public order is hero → stats → manifesto quote. Chi siamo and Donazioni: make glass/form cards slide in from opposite sides the same way Diventa socio does (`data-reveal-from` left/right, `--reveal-x: ±100vw`, parent `overflow: clip`). Root cause of the missed socio feel: `--landing-ease-out` is only set on `.template-page--landing` and `.template-page--services`, so the transform transition on other pages is invalid; Chi siamo closing and Donazioni listing/form still use `up`. Donation campaign `show` (one-time and recurring): wider card from `lg` (`max-w-5xl`) with two field columns; inputs stay column-sized, not page-stretched. Payment Element mount and confirm stay. Diventa socio is not edited.

Vendor pages opened this turn: [Blade](https://laravel.com/docs/13.x/blade), [grid-template-columns](https://tailwindcss.com/docs/grid-template-columns), [max-width](https://tailwindcss.com/docs/max-width), [Laravel testing](https://laravel.com/docs/13.x/testing), [Payment Element](https://docs.stripe.com/payments/payment-element), [Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Blade ([Blade](https://laravel.com/docs/13.x/blade)), Tailwind grid and max-width ([grid-template-columns](https://tailwindcss.com/docs/grid-template-columns), [max-width](https://tailwindcss.com/docs/max-width)), existing `landing-motion.js` IntersectionObserver ([Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API)), Stripe Payment Element unchanged ([Payment Element](https://docs.stripe.com/payments/payment-element))

**Storage**: None

**Testing**: PHPUnit HTML order and class assertions ([testing](https://laravel.com/docs/13.x/testing)). Side-slide feel and desktop form height are owner UAT.

**Target Platform**: Local DDEV (`https://safehouse-community-site.ddev.site`)

**Project Type**: web application

**Performance Goals**: No new queries. Same reveal observer.

**Constraints**: Italian primary, English second. No public `/ru`. Do not edit `landing.blade.php`. Do not change Payment Element wiring (`S-STRIPE`). Do not edit the CRM repo. CSP stays Caddy-only.

**Scale/Scope**: Home section order, Chi siamo + Donazioni (listing, 5 x 1000, campaign `show`) reveals, one form layout, CSS clip/easing, existing feature tests.

## Constitution Check

- Principle I: specify → plan → tasks → implement in this turn because the owner ordered the full cycle including implement. Pass.
- One active spec: `.specify/feature.json` → `specs/023-home-stats-form`. Pass.
- `S-STRIPE`: Payment Element still mounts on `#payment-element`; no Checkout Sessions switch. Pass.
- Principle III: vendor URLs opened and cited this turn. Pass.
- Principle X: site repo only. Pass.
- Principle XI: complexity table printed; owner waived wait (“делай сразу”). Inherit for every row. Pass.
- Principle VIII: Russian UAT after implement; Cursor-browser offered, not driven. Pass.

Post-design re-check: same gates. Pass.

## Project Structure

### Documentation (this feature)

```text
specs/023-home-stats-form/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── layout.md
└── tasks.md
```

### Source Code (repository root)

```text
resources/views/pages/templates/home.blade.php
resources/views/pages/templates/about.blade.php
resources/views/donations/index.blade.php
resources/views/donations/show.blade.php
resources/views/donations/five-per-mille.blade.php
resources/css/app.css
tests/Feature/HomePageTest.php
tests/Feature/CmsPagesTest.php
tests/Feature/DonationCampaignRoutesTest.php
tests/Feature/DonationShowRouteTest.php
tests/Feature/DonationFivePerMilleTest.php
```

**Structure Decision**: Existing Laravel app. Home order is a Blade include move. Side slides reuse `.landing-reveal` + `landing-motion.js`; CSS must define `--landing-ease-out` on `.landing-reveal` and clip Chi siamo / Donazioni parents with `overflow: clip` like `.template-landing-cards`. Form columns are Blade + Tailwind `lg:grid-cols-2` inside `.donation-form`.

## Complexity Tracking

No constitution violations. Owner asked to implement in the same turn.
