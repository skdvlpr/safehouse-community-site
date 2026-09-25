# Implementation Plan: Light-theme chrome, 5×1000 mark, slower Home count

**Branch**: `024-light-theme-chrome` | **Date**: 2026-09-25 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/024-light-theme-chrome/spec.md`

## Summary

Light theme currently remaps `border-color` on `.template-about-values` and `.template-about-closing` to `--safehouse-glass-border`, which wipes the red/primary outline those blocks have in dark. Exclude those two selectors from the remap and restore primary/accent borders. 5×1000 `.site-five__mark` is a filled circle; hover sets red text on a red fill so **5×** disappears on light. Change the mark to a transparent rectangle with a red outline; hover fills primary with **white** text. News `.news-cat-menu__summary` is missing from the light control remap that date inputs already have — add it. Home `COUNT_DURATION_MS` in `impact-count.js` is 2000; raise to 3500.

Vendor pages opened this turn: [border-color](https://tailwindcss.com/docs/border-color), [border-radius](https://tailwindcss.com/docs/border-radius), [requestAnimationFrame](https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame), [prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion), [Laravel testing](https://laravel.com/docs/13.x/testing), [Blade](https://laravel.com/docs/13.x/blade).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Tailwind component CSS ([border-color](https://tailwindcss.com/docs/border-color), [border-radius](https://tailwindcss.com/docs/border-radius)), `impact-count.js` ([requestAnimationFrame](https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame))

**Storage**: None

**Testing**: PHPUnit HTML class assertions ([testing](https://laravel.com/docs/13.x/testing)). Light/hover pixels are owner UAT.

**Target Platform**: Local DDEV (`https://safehouse-community-site.ddev.site`)

**Project Type**: web application

**Performance Goals**: No new queries. Count duration is longer by design.

**Constraints**: Italian primary, English second. No public `/ru`. Do not edit Diventa socio. Do not change Payment Element (`S-STRIPE`). Do not edit the CRM repo. CSP stays Caddy-only. Keep `017.1` phone toolbar CSS.

**Scale/Scope**: Two light-theme border exceptions, 5×1000 mark/hover, one news control remap, one JS duration constant, existing feature tests.

## Constitution Check

- Principle I: specify → plan → tasks → implement in this turn because the owner ordered the full cycle including implement, then added the slower count in the same turn. Pass.
- One active spec: `.specify/feature.json` → `specs/024-light-theme-chrome`. Pass.
- `S-STRIPE`: untouched. Pass.
- Principle III: vendor URLs opened and cited this turn. Pass.
- Principle X: site repo only. Pass.
- Principle XI: complexity table printed; owner waived wait (“делай сразу” / finish the correction then slow the count). Inherit for every row. Pass.
- Principle VIII: Russian UAT after implement; Cursor-browser offered, not driven. Pass.

Post-design re-check: same gates. Pass.

## Project Structure

### Documentation (this feature)

```text
specs/024-light-theme-chrome/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── chrome.md
└── tasks.md
```

### Source Code (repository root)

```text
resources/css/app.css
resources/js/impact-count.js
resources/views/layouts/partials/five-per-mille-banner.blade.php
tests/Feature/CmsPagesTest.php
tests/Feature/NavigationMenuTest.php
tests/Feature/NewsHeadingTest.php
```

**Structure Decision**: Existing Laravel app. All visual work is CSS except the count duration constant. Banner Blade keeps the **5×** text; geometry is CSS (`rounded-sm` not `rounded-full`).

## Complexity Tracking

No constitution violations. Owner asked to implement in the same turn.
