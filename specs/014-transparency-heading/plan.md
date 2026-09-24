# Implementation Plan: Transparency heading

**Branch**: `014-transparency-heading` | **Date**: 2026-09-25 | **Spec**: [spec.md](./spec.md)

## Summary

One public page. Heading matches Chi siamo: large title, vertical bar, tagline, no red label above the title. Layout changes named in the spec stay on that page. Diventa socio is not edited. Implement only this spec, then stop for the owner.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Blade ([Blade](https://laravel.com/docs/13.x/blade)), Tailwind width utilities ([max-width](https://tailwindcss.com/docs/max-width))

**Storage**: None

**Testing**: PHPUnit feature assertion for the heading text. Layout width is owner UAT.

**Target Platform**: Local DDEV only until the owner asks to push

**Project Type**: web application

**Performance Goals**: No new queries

**Constraints**: Italian primary, English second. Do not change Diventa socio. Do not start the next spec in the same implement run.

**Scale/Scope**: `resources/views/pages/templates/legal.blade.php`, `resources/css/app.css`, `lang/it/site.php`

## Constitution Check

- One spec implemented at a time. Pass.
- No CRM edits. Pass.
- Stop for owner test before the next spec. Pass.
- Docs cited this plan turn. Pass.

## Project Structure

```text
resources/views/pages/templates/legal.blade.php
resources/css/app.css
lang/it/site.php
```

## Complexity Tracking

No constitution violations.
