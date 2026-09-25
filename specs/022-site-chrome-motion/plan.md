# Implementation Plan: Site chrome, motion, and ETS naming

**Branch**: `022-site-chrome-motion` | **Date**: 2026-09-25 | **Spec**: [spec.md](./spec.md)

## Summary

Phone drawer: Home link + title **Safe House ETS**. Browser tabs: `page_name — Safe House ETS`. Make `page-hero` taglines vertically centered everywhere. Reuse `.landing-reveal` on glass/card blocks except Diventa socio (`landing.blade.php` frozen). Menu labels for listings become Notizie / Articoli; Home All-buttons stay. Home CRM figures count 0→value in ~2s. Last: a light red wash on `.site-five`, only after a commit of the rest.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 ([Laravel 13](https://laravel.com/docs/13.x))

**Primary Dependencies**: Blade ([Blade](https://laravel.com/docs/13.x/blade)), existing `header-drawer.js`, existing `landing-motion.js` + Intersection Observer ([Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API)), `requestAnimationFrame` count-up ([rAF](https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame)), `prefers-reduced-motion` ([MDN](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion)), Tailwind ([align-items](https://tailwindcss.com/docs/align-items))

**Storage**: No new tables. CRM reads stay as today (`HomeImpactStatsService`).

**Testing**: PHPUnit feature assertions for drawer Home, title suffix, menu labels, `data-count-to`, `landing-reveal` on a non-socio page, and that Home still sees All-news buttons ([Laravel testing](https://laravel.com/docs/13.x/testing)). Motion and banner tint are owner UAT.

**Target Platform**: DDEV `https://safehouse-community-site.ddev.site`

**Project Type**: web application

**Performance Goals**: One extra rAF loop on Home only; reveal observer already boots site-wide.

**Constraints**: Do not edit `resources/views/pages/templates/landing.blade.php`. Do not add Home to `config/navigation.php` `header`. Do not change Stripe. No CRM writes. Banner tint is a second commit. Never `php artisan config:cache` in DDEV.

**Scale/Scope**: Header drawer, lang suffix, page-hero CSS default, several public templates, news/editorial nav labels, home impact partial + small JS, optional `.site-five` background.

## Constitution Check

- One active spec: `.specify/feature.json` → `specs/022-site-chrome-motion`. Pass after `019.1` commit.
- No CRM writes. Pass.
- `S-STRIPE` untouched. Pass.
- Official docs opened this plan turn (Blade, testing, Intersection Observer, rAF, prefers-reduced-motion, align-items). Pass.
- Diventa socio frozen. Pass.
- Stop after tasks table until the owner says implement. Pass.

## Project Structure

```text
resources/views/layouts/partials/header.blade.php
resources/views/layouts/partials/nav-item.blade.php
resources/views/layouts/partials/nav-item-mobile.blade.php
resources/views/pages/partials/page-hero.blade.php
resources/views/pages/partials/page-header.blade.php
resources/views/pages/partials/home-impact-stats.blade.php
resources/css/app.css
resources/js/app.js
resources/js/impact-count.js
lang/it/site.php
lang/en/site.php
tests/Feature/SiteLayoutTest.php
tests/Feature/HomePageTest.php
tests/Feature/CmsPagesTest.php
```

Plus wrap `landing-reveal` around glass blocks in about, contact, home, donations, articles, legal, default, volunteer, article show. Not `landing.blade.php`.

## Complexity Tracking

Banner tint is P2 and commit-gated so a rejected wash does not rewind chrome work.
