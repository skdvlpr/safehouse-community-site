# Implementation Plan: Donations heading family

**Branch**: `019-donations-row` | **Date**: 2026-09-25 | **Spec**: [spec.md](./spec.md)

## Summary

Reuse the Chi siamo heading partial already on public pages. Apply it to the donations listing, the 5 x 1000 page, and every campaign payment page, **outside** the glass or form card. Listing also puts the 5 x 1000 card and the bank-transfer card on one wide-screen row. Recurring body copy drops the portal-interrupt sentence and does not repeat the new tagline. Stripe Payment Element stays inside the form; this spec does not change how a payment is taken ([Payment Element](https://docs.stripe.com/payments/payment-element), constitution `S-STRIPE`). Diventa socio, thank-you, and payment-privacy are not edited. Stop for owner UAT.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 ([Laravel 13](https://laravel.com/docs/13.x))

**Primary Dependencies**: Blade includes ([Blade](https://laravel.com/docs/13.x/blade)), Tailwind grid for the listing row ([grid-template-columns](https://tailwindcss.com/docs/grid-template-columns)), existing `pages.partials.page-header` / `page-hero`

**Storage**: No new tables. Recurring campaign default description text is shortened so the live body no longer duplicates the heading or the red cancel panel.

**Testing**: PHPUnit feature assertions for heading strings and for the absence of the portal-interrupt sentence on the recurring page ([Laravel testing](https://laravel.com/docs/13.x/testing)). Two-up layout and “heading sits outside the card” are owner UAT plus class/markup assertions where cheap.

**Target Platform**: Local DDEV (`https://safehouse-community-site.ddev.site`) until the owner asks to push

**Project Type**: web application

**Performance Goals**: No new queries

**Constraints**: Italian primary, English second. Do not change Diventa socio. Do not change thank-you or payment-privacy. Do not migrate payments to Checkout Sessions. Do not start the next spec in the same implement run. Never `php artisan config:cache` in DDEV.

**Scale/Scope**: Donations listing, 5 x 1000 page, campaign `show` page, recurring default copy, `lang/it/site.php`, `lang/en/site.php`, `resources/css/app.css`

## Constitution Check

- One active spec: `.specify/feature.json` → `specs/019-donations-row`. Pass.
- `021` owner UAT accepted before this implement. Pass.
- No CRM edits. Pass.
- `S-STRIPE` unchanged: Payment Element remains; layout only. Pass.
- Official docs opened this plan turn (Blade, grid, testing, Payment Element). Pass.
- Stop for owner test before the next spec. Pass.

## Project Structure

```text
resources/views/pages/partials/page-header.blade.php
resources/views/pages/partials/page-hero.blade.php
resources/views/donations/index.blade.php
resources/views/donations/five-per-mille.blade.php
resources/views/donations/show.blade.php
resources/css/app.css
lang/it/site.php
lang/en/site.php
app/Services/RecurringDonationCampaignService.php
database/seeders/DonationCampaignSeeder.php
tests/Feature/DonationFivePerMilleTest.php
tests/Feature/DonationShowRouteTest.php
```

## Complexity Tracking

No constitution violations. Payment Element is not replaced despite Stripe docs preferring Checkout Sessions; `S-STRIPE` wins.
