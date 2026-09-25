# Tasks: Donation form width and one-time heading

**Input**: Design documents from `/specs/019.1-donation-form-heading/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/heading.md, quickstart.md

**Tests**: Feature tests for one-time heading strings, form `max-w-2xl`, and `page-hero__headline--center`. Visual “narrow card” is owner UAT.

**Organization**: Tasks grouped by user story. Do not edit Diventa socio, thank-you, or payment-privacy. Do not change Stripe Payment Element wiring. Do not start `022` in this implement run.

## Format: `[ID] [P?] [Story] Description`

## Phase 1: Setup

- [x] T001 Confirm `.specify/feature.json` points at `specs/019.1-donation-form-heading` and that `resources/views/pages/templates/landing.blade.php` is not edited. (test: no; C1; model: inherit)

---

## Phase 2: Foundational

- [x] T002 [P] In `lang/it/site.php` add `campaign_tagline` verbatim **Sostieni Safe House con un dono.**
- [x] T003 [P] In `lang/en/site.php` add `campaign_tagline` verbatim **Support Safe House with a gift.**

**Checkpoint**: Both locales have the one-time tagline

---

## Phase 3: User Story 1 - Narrow payment form card (Priority: P1) 🎯 MVP

**Goal**: `#donation-form` is a centered `max-w-2xl` card

### Tests for User Story 1

- [x] T004 [US1] Extend `tests/Feature/DonationShowRouteTest.php` so one-time and recurring `show` responses see `donation-form` and `max-w-2xl`. Cite [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C3; model: inherit)

### Implementation for User Story 1

- [x] T005 [US1] In `resources/views/donations/show.blade.php` add `donation-form mx-auto w-full max-w-2xl` on `#donation-form`. In `resources/css/app.css` add the same cap to `.donation-form`. Cite [max-width](https://tailwindcss.com/docs/max-width). (test: yes — T004; C2; model: inherit)

**Checkpoint**: Payment card is narrow; listing stays wide

---

## Phase 4: User Story 2 - One-time heading is campaign name then tagline (Priority: P1)

**Goal**: `{campaign} | Sostieni Safe House con un dono.`

### Tests for User Story 2

- [x] T006 [US2] Update `test_one_time_campaign_hides_recurring_cancel_ux` so the page sees **Dona a Safe House**, `campaign_tagline`, and `page-hero`, and no longer requires standalone **Donazione** as the H1. Recurring heading assertions stay. (test: yes; C3; model: inherit)

### Implementation for User Story 2

- [x] T007 [US2] In `resources/views/donations/show.blade.php` set one-time `$headingTitle` to `$title` and `$headingLead` to `__('site.donations.campaign_tagline')`. Recurring mapping unchanged. Cite [Blade](https://laravel.com/docs/13.x/blade). (test: yes — T006; C2; model: inherit)

**Checkpoint**: One-time H1 is the campaign name

---

## Phase 5: User Story 3 - Tagline vertically centered on donation headings (Priority: P1)

**Goal**: Donation page-headers use `align=center`

### Tests for User Story 3

- [x] T008 [US3] Assert `page-hero__headline--center` on one-time show, recurring show, Donazioni listing, and 5 x 1000. (test: yes; C3; model: inherit)

### Implementation for User Story 3

- [x] T009 [US3] Pass `'align' => 'center'` in `resources/views/donations/show.blade.php`, `index.blade.php`, and `five-per-mille.blade.php` page-header includes. Cite [align-items](https://tailwindcss.com/docs/align-items). (test: yes — T008; C2; model: inherit)

**Checkpoint**: Donation headings match contact/news optical alignment

---

## Phase 6: Polish

- [x] T010 Run `ddev exec -- php artisan test --filter=DonationShowRouteTest`, `ddev exec -- php artisan test --filter=DonationFivePerMilleTest`, `ddev exec -- ./vendor/bin/pint`, and `bash bin/dev-rebuild-frontend.sh`. (test: yes; C3; model: inherit)

## Dependencies

- T001 before all
- T002 / T003 before T007
- T005 and T007 may run after lang keys
- T009 can run with T007 (same show file)

## Parallel opportunities

- T002 ∥ T003
- T004 / T006 / T008 can be one test-file edit
- T005 / T007 / T009 can be one show.blade.php edit

## Implementation strategy

MVP: T001–T009 then T010. Stop for owner UAT. Do not start `022` until the owner commits this repair and asks for the next spec.
