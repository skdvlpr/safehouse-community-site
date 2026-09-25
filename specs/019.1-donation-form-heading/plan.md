# Implementation Plan: Donation form width and one-time heading

**Branch**: `019.1-donation-form-heading` | **Date**: 2026-09-25 | **Spec**: [spec.md](./spec.md)

## Summary

UAT repair of `019`. Keep the Chi siamo heading outside the payment card. Cap `#donation-form` at `max-w-2xl` and center it ([max-width](https://tailwindcss.com/docs/max-width)). One-time heading becomes **campaign name | short tagline**. Recurring heading stays. Pass `align => 'center'` on donation `page-header` includes so the tagline is vertically centered ([align-items](https://tailwindcss.com/docs/align-items), [Blade](https://laravel.com/docs/13.x/blade)). Payment Element stays inside the form ([Payment Element](https://docs.stripe.com/payments/payment-element)). Stop for owner UAT. Do not start `022` in the same implement run.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 ([Laravel 13](https://laravel.com/docs/13.x))

**Primary Dependencies**: Blade includes, existing `page-header` / `page-hero`, Tailwind `max-w-2xl` + `mx-auto` + `items-center`

**Storage**: No new tables

**Testing**: PHPUnit feature assertions for one-time heading strings, form class, and `page-hero__headline--center` ([Laravel testing](https://laravel.com/docs/13.x/testing))

**Target Platform**: Local DDEV (`https://safehouse-community-site.ddev.site`)

**Project Type**: web application

**Performance Goals**: No new queries

**Constraints**: Italian primary, English second. Do not change Diventa socio, thank-you, or payment-privacy. Do not migrate payments to Checkout Sessions. Never `php artisan config:cache` in DDEV.

**Scale/Scope**: `show.blade.php`, donation `page-header` includes, lang keys, `DonationShowRouteTest`, optional CSS `.donation-form` max-width

## Constitution Check

- One active spec: `.specify/feature.json` → `specs/019.1-donation-form-heading`. Pass.
- Dotted UAT repair before `NNN+1`. Pass.
- No CRM edits. Pass.
- `S-STRIPE` unchanged. Pass.
- Official docs opened this plan turn (Blade, max-width, align-items, testing, Payment Element). Pass.

## Project Structure

```text
resources/views/donations/show.blade.php
resources/views/donations/index.blade.php
resources/views/donations/five-per-mille.blade.php
resources/css/app.css
lang/it/site.php
lang/en/site.php
tests/Feature/DonationShowRouteTest.php
tests/Feature/DonationFivePerMilleTest.php
```

## Complexity Tracking

No constitution violations. Width is layout-only; Payment Element is not replaced.
