# Implementation Plan: Ads-ready public refresh

**Branch**: `010-ads-ready-refresh` | **Date**: 2026-09-24 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/010-ads-ready-refresh/spec.md`. Replaces `010-home-action-menu`. Absorbs the remaining public motion of `007`.

## Summary

Shorten the header, keep membership as the home primary action, show the latest news and articles on home, put a 5×1000 banner on every public page except Diventa socio, widen the volunteer form and the reading column on large screens, and add membership-style entrance motion that turns off when reduced motion is requested. Refresh every public template except Diventa socio. Cookie-policy tables scroll inside the page on a phone. Implement only after `/speckit-tasks` and an owner yes. Local only until a later push.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Blade includes and stacks ([Blade](https://laravel.com/docs/13.x/blade)), Tailwind `max-w-6xl` ([max-width](https://tailwindcss.com/docs/max-width)), Tailwind `motion-safe` / `motion-reduce` ([states](https://tailwindcss.com/docs/hover-focus-and-other-states)), existing `Article` query and `config/navigation.php`

**Storage**: None. Stories are queried. Menu stays in config.

**Testing**: PHPUnit Feature ([testing](https://laravel.com/docs/13.x/testing)) for header items, banner presence, and the home strip. Width and motion are owner UAT.

**Target Platform**: DDEV preview; production https://safehouse.community only after a later owner push

**Project Type**: web application

**Performance Goals**: Home strip is one query, at most four rows. No new third-party animation library.

**Constraints**: Public locales `it` and `en` only. No president video. No CMS restyle. No Google Ads account. Membership page keeps its address and full-width layout. Reading pages cap at `max-w-6xl` on large screens.

**Scale/Scope**: Header config, one home partial, one banner partial, volunteer and shared page width, entrance classes in `resources/css/app.css`

Vendor docs this plan turn: [Laravel Blade](https://laravel.com/docs/13.x/blade); [Laravel testing](https://laravel.com/docs/13.x/testing); [Tailwind max-width](https://tailwindcss.com/docs/max-width); [Tailwind states](https://tailwindcss.com/docs/hover-focus-and-other-states).

## Constitution Check

- One active spec: `010` after `003` is on production. Pass.
- `007` is superseded here, not implemented beside this. Pass.
- `004` is not glued in. Pass.
- No CRM schema edits. Pass.
- Implement from `tasks.md` only after owner yes. This command stops at design. Pass.
- No push in this command. Pass.
- Official docs opened this turn and cited. Pass.
- S-I18N it+en. Pass.

Post-design re-check: same gates. The banner exception and the four-item cap are in [research.md](./research.md).

## Project Structure

### Documentation (this feature)

```text
specs/010-ads-ready-refresh/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── home-and-chrome.md
├── checklists/requirements.md
└── tasks.md              # /speckit-tasks — not created here
```

### Source Code (this feature)

```text
config/navigation.php
app/Http/Controllers/HomeController.php
resources/views/pages/templates/home.blade.php
resources/views/pages/partials/latest-stories.blade.php
resources/views/layouts/partials/five-per-mille-banner.blade.php
resources/views/layouts/app.blade.php
resources/views/pages/volunteer.blade.php
resources/css/app.css
tests/Feature/NavigationMenuTest.php
tests/Feature/HomeStoriesTest.php
```

## Complexity Tracking

No constitution violations.
