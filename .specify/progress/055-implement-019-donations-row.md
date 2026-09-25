# 055 — Implement `019-donations-row`

**Date**: 2026-09-25
**Agent**: Cursor Grok 4.6
**Active spec**: `specs/019-donations-row/`

## Current state

T001–T014 complete. Listing, 5 x 1000, one-time campaign, and recurring campaign headings use the Chi siamo row outside the card. Featured 5 x 1000 + bank transfer sit on one wide row. Recurring body no longer repeats the portal-interrupt sentence; the red cancel panel stays. Payment Element wiring unchanged.

Waiting on owner UAT. Do not start the next spec. Do not commit unless asked.

## Files

- `resources/views/donations/index.blade.php`
- `resources/views/donations/five-per-mille.blade.php`
- `resources/views/donations/show.blade.php`
- `resources/views/donations/partials/bank-transfer.blade.php`
- `resources/css/app.css`
- `lang/it/site.php`
- `lang/en/site.php`
- `app/Services/RecurringDonationCampaignService.php`
- `app/Support/DiscoveryText.php`
- `database/seeders/DonationCampaignSeeder.php`
- `tests/Feature/DonationFivePerMilleTest.php`
- `tests/Feature/DonationShowRouteTest.php`

## Verification

- `DonationFivePerMilleTest` 3 passed
- `DonationShowRouteTest` 5 passed
- `RecurringDonationCampaignServiceTest` 3 passed
- `DonationCampaignRoutesTest` 5 passed
- Pint dirty: pass
- `bash bin/dev-rebuild-frontend.sh`: `app-Dq3sU7Xj.css`

Docs this turn: https://laravel.com/docs/13.x/blade · https://tailwindcss.com/docs/grid-template-columns · https://laravel.com/docs/13.x/testing · https://docs.stripe.com/payments/payment-element

## Next steps

Owner UAT on listing, 5 x 1000, one-time form, recurring form (desktop + phone).
