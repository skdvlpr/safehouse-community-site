# 056 — Specify / plan / tasks / implement `023-home-stats-form`

**Date**: 2026-09-25
**Agent**: Cursor Grok 4.6
**Active spec**: `specs/023-home-stats-form/`
**Feature pointer**: `.specify/feature.json` → `specs/023-home-stats-form`

## Current state

Owner asked to commit first (working tree was already clean at `98a53f6`), then run the full specify → plan → tasks → implement cycle for:

1. Home CRM numbers **above the manifesto quote**, between the hero and the quote
2. Chi siamo and Donazioni socio-style **left/right** block slides (other pages keep current reveals; Diventa socio not edited)
3. Donation campaign form **wide on desktop via columns**, not stretched fields; phone stays the stacked `max-w-2xl` card

Implemented in this turn. Waiting on owner UAT. No git commit unless asked.

## Files

- `specs/023-home-stats-form/` (spec, plan, research, data-model, contracts, tasks, checklists)
- `resources/views/pages/templates/home.blade.php`
- `resources/views/pages/templates/about.blade.php`
- `resources/views/donations/index.blade.php`
- `resources/views/donations/show.blade.php`
- `resources/views/donations/five-per-mille.blade.php`
- `resources/css/app.css` (`--landing-ease-out` on `.landing-reveal`, `overflow: clip` on Chi siamo / Donazioni parents, `.donation-form` `lg:max-w-5xl` + columns)
- `tests/Feature/HomePageTest.php`
- `tests/Feature/CmsPagesTest.php`
- `tests/Feature/DonationCampaignRoutesTest.php`
- `tests/Feature/DonationShowRouteTest.php`
- `tests/Feature/DonationFivePerMilleTest.php`

## Verification

- `ddev exec php artisan test` — 409 passed, 2 skipped
- `ddev exec ./vendor/bin/pint` — PASS
- `bash bin/dev-rebuild-frontend.sh` — Vite build ok

Vendor docs opened this turn: Blade, Tailwind grid-template-columns, Tailwind max-width, Laravel testing, Stripe Payment Element, Intersection Observer.

## Blockers

Owner UAT. Cursor-browser not driven this turn.

## Next steps

Owner runs the Russian UAT script. Fail → hotfix spec. Pass → owner may ask for commit.
