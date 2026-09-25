# Research: Donation form width and one-time heading

## Decision 1: Form cap is `max-w-2xl`, not full `site-content`

**Choice**: `#donation-form` gets `mx-auto w-full max-w-2xl` (42rem / 672px). Listing featured cards stay full width.

**Why**: Owner UAT: after the heading moved outside the card, the form stretched across the content column. The earlier payment card was a narrow block. `max-w-2xl` still fits the two-column email/phone row; `max-w-xl` (576px) is tighter than that row needs. Cite [Tailwind max-width](https://tailwindcss.com/docs/max-width).

**Rejected**: Wrapping the whole `show` content in a narrow column (would also shrink the heading). Changing listing cards.

## Decision 2: One-time title is the campaign name

**Choice**: `$headingTitle = $title` (localized campaign title). `$headingLead = __('site.donations.campaign_tagline')`.

Italian tagline: **Sostieni Safe House con un dono.**

English tagline: **Support Safe House with a gift.**

**Why**: Owner reversed `019` US3 (`Donazione | campaign_name`). Recurring already uses a short slogan after `|`. The one-time line should match that pattern. Campaign name as H1 also matches the document `<title>` (already `$title`).

**Rejected**: Asking the owner for exact tagline copy in-chat before shipping; they asked for “какой то текст короткий как в других заголовках”.

## Decision 3: Vertical center via existing `align=center`

**Choice**: Pass `'align' => 'center'` on donation listing, 5 x 1000, and `show` `page-header` includes. That adds `page-hero__headline--center` (`lg:items-center`, tagline `lg:pb-0`). Cite [align-items](https://tailwindcss.com/docs/align-items).

**Why**: Contact and news listings already use this. Default `page-hero` is `lg:items-end` plus tagline `lg:pb-2`, which drops the line after `|`. Changing the **global** default belongs in `022`.

## Decision 4: Payment Element stays

**Choice**: Layout-only. No Checkout Sessions migration. Cite [Payment Element](https://docs.stripe.com/payments/payment-element) and constitution `S-STRIPE`.

## Decision 5: Tests

**Choice**: Update `DonationShowRouteTest::test_one_time_campaign_hides_recurring_cancel_ux` to assert campaign name, `campaign_tagline`, `donation-form`, `max-w-2xl`, and `page-hero__headline--center`. Drop the requirement that the page shows standalone **Donazione** as the H1. Listing/5 x 1000 tests assert `--center`. Cite [Laravel testing](https://laravel.com/docs/13.x/testing).
