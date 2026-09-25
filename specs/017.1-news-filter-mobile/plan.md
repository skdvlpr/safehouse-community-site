# Implementation Plan: News/articles filter bar on a phone

**Branch**: `017.1-news-filter-mobile` | **Date**: 2026-09-25 | **Spec**: [spec.md](./spec.md)

## Summary

The shared listing toolbar’s date row is `flex-nowrap` + `shrink-0`, with the label **Data** in the same row as two native date inputs and **Applica**. On a phone that row is wider than the screen, so **Data** clips to **A** on the left and **Applica** clips on the right. Stack the group on small screens: label above, dates in a two-column grid with `min-w-0`, **Applica** full width under them. Desktop row unchanged. Cite [min-width](https://tailwindcss.com/docs/min-width), [grid-template-columns](https://tailwindcss.com/docs/grid-template-columns).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Blade ([Blade](https://laravel.com/docs/13.x/blade)), Tailwind min-width and grid ([min-width](https://tailwindcss.com/docs/min-width), [grid-template-columns](https://tailwindcss.com/docs/grid-template-columns))

**Storage**: None

**Testing**: PHPUnit asserts the toolbar classes used for the phone stack. Overflow is owner UAT.

**Target Platform**: Local DDEV; production already shows the bug

**Project Type**: web application

**Constraints**: Italian primary. Do not edit Diventa socio. Same toolbar file for news and articles.

**Scale/Scope**: `listing-toolbar.blade.php` only if a class is needed; mainly `resources/css/app.css` `.news-toolbar*` / `.news-date-filters*`.

## Constitution Check

- UAT repair of 017 uses `017.1`. Pass.
- Vendor docs opened this turn. Pass.
- No CRM. Pass.

## Project Structure

```text
specs/017.1-news-filter-mobile/
resources/css/app.css
resources/views/pages/articles/partials/listing-toolbar.blade.php
tests/Feature/NewsHeadingTest.php
```
