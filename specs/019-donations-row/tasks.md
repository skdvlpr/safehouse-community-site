# Tasks: Donations heading family

**Input**: Design documents from `/specs/019-donations-row/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/heading.md, quickstart.md

**Tests**: Feature tests for heading strings and for the absence of the recurring portal-interrupt sentence. Two-up listing layout and “heading sits outside the card” are owner UAT.

**Organization**: Tasks are grouped by user story. Do not edit Diventa socio, thank-you, or payment-privacy. Do not change Stripe Payment Element wiring. Do not start the next spec in this implement run.

## Format: `[ID] [P?] [Story] Description`

## Phase 1: Setup

**Purpose**: Lock this spec as the only active work

- [x] T001 Confirm `.specify/feature.json` points at `specs/019-donations-row` and that `resources/views/pages/templates/landing.blade.php`, `resources/views/donations/thank-you.blade.php`, and `resources/views/donations/privacy.blade.php` are not edited. (test: no; C1; model: inherit)

---

## Phase 2: Foundational

**Purpose**: Shared heading copy before any view change

**⚠️ CRITICAL**: No user story work until this phase is complete

- [x] T002 [P] In `lang/it/site.php` set listing title/tagline verbatim **Donazioni** and **Sostieni Safe House. 5 x 1000 o pagamenti digitali.**; add one-time title verbatim **Donazione**; add recurring title/tagline verbatim **Donazione ricorrente** and **Sostieni Safe House ogni mese con un contributo ricorrente.**
- [x] T003 [P] In `lang/en/site.php` set listing title/tagline verbatim **Donations** and **Support Safe House. 5 x 1000 or digital payments.**; add one-time title verbatim **Donation**; add recurring title/tagline verbatim **Recurring donation** and **Support Safe House every month with a recurring contribution.**

**Checkpoint**: Both locales have listing, one-time, and recurring heading keys

---

## Phase 3: User Story 1 - Donazioni listing heading and two-up blocks (Priority: P1) 🎯 MVP

**Goal**: Chi siamo heading on the listing; 5 x 1000 and bank transfer on one wide row

**Independent Test**: Italian listing shows the spec heading and no red label; wide layout places the two featured cards on one row

### Tests for User Story 1

- [x] T004 [US1] Extend `tests/Feature/DonationFivePerMilleTest.php` so `/it/donations` and `/en/donations` see the new listing heading strings and `page-hero`. Cite [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C3; model: inherit)

### Implementation for User Story 1

- [x] T005 [US1] In `resources/views/donations/index.blade.php` replace the in-flow `h1`/lead with `pages.partials.page-header` (`prominent` true) and wrap the 5 x 1000 card plus bank-transfer include in a wide two-column grid. Cite [Laravel Blade](https://laravel.com/docs/13.x/blade). (test: yes — T004; C4; model: inherit)
- [x] T006 [US1] Add the listing featured-row rules in `resources/css/app.css` so the two cards are `grid-cols-1` by default and two columns from `lg`, stacked on a phone. Cite [grid-template-columns](https://tailwindcss.com/docs/grid-template-columns). (test: no; C3; model: inherit)

**Checkpoint**: Listing heading matches Chi siamo; featured cards two-up when wide

---

## Phase 4: User Story 2 - 5 x 1000 page heading sits outside the card (Priority: P1)

**Goal**: CMS heading and lead move above the glass; card keeps body, tax code, instructions

**Independent Test**: 5 x 1000 page shows `page-hero` above the glass; large title is not repeated inside the card

### Tests for User Story 2

- [x] T007 [US2] Extend `tests/Feature/DonationFivePerMilleTest.php` so `/it/donations/5-per-thousand` sees `page-hero`, the configured heading and lead, and still sees the tax code. (test: yes; C3; model: inherit)

### Implementation for User Story 2

- [x] T008 [US2] In `resources/views/donations/five-per-mille.blade.php` put `pages.partials.page-header` outside the glass using CMS heading as title and CMS lead as tagline; remove the in-card `h1` and the red menu-label eyebrow; keep body, tax-code panel, and instructions in the card. (test: yes — T007; C3; model: inherit)

**Checkpoint**: 5 x 1000 heading sits above the glass

---

## Phase 5: User Story 3 - One-time campaign payment page heading (Priority: P1)

**Goal**: **Donazione |** campaign name outside the form card; payment widget stays inside

**Independent Test**: A one-time campaign named Dona a Safe House shows that heading above the form and still renders the form

### Tests for User Story 3

- [x] T009 [US3] Extend `tests/Feature/DonationShowRouteTest.php` so a one-time campaign page sees **Donazione**, the campaign name, and `page-hero`, and does not lose the continue-payment control. (test: yes; C3; model: inherit)

### Implementation for User Story 3

- [x] T010 [US3] In `resources/views/donations/show.blade.php` move the heading out of `#donation-form` via `pages.partials.page-header`; one-time pages use title **Donazione** / **Donation** and the campaign name as tagline; keep story, progress, fields, and Payment Element inside the form. Do not change Stripe.js confirm wiring. Cite [Blade](https://laravel.com/docs/13.x/blade) and [Payment Element](https://docs.stripe.com/payments/payment-element). (test: yes — T009; C5; model: inherit)

**Checkpoint**: One-time payment page heading matches the spec; form still works

---

## Phase 6: User Story 4 - Recurring campaign heading and duplicate cancel copy (Priority: P1)

**Goal**: Fixed recurring heading; drop portal-interrupt body sentence; keep the red cancel panel

**Independent Test**: Recurring page shows the exact heading, does not show the portal-interrupt sentence in the body, still shows the red cancel panel

### Tests for User Story 4

- [x] T011 [US4] Extend `tests/Feature/DonationShowRouteTest.php` so the recurring page sees **Donazione ricorrente** and **Sostieni Safe House ogni mese con un contributo ricorrente.**, still sees `cancel_notice_title`, and does not see *Puoi interrompere in qualsiasi momento tramite il portale Stripe dedicato ai donatori.* or `recurring_frequency_badge`. (test: yes; C3; model: inherit)

### Implementation for User Story 4

- [x] T012 [US4] In `resources/views/donations/show.blade.php` use the recurring heading keys on recurring campaigns, omit the red monthly badge, and do not render campaign body that only repeats the tagline. Keep the red cancel panel and acknowledgement. (test: yes — T011; C4; model: inherit)
- [x] T013 [P] [US4] Shorten recurring default description in `app/Services/RecurringDonationCampaignService.php` and `database/seeders/DonationCampaignSeeder.php` so it MUST NOT contain *Puoi interrompere in qualsiasi momento tramite il portale Stripe dedicato ai donatori.* or the English portal-cancel equivalent, and MUST NOT repeat the heading tagline. If nothing remains, use an empty description. (test: yes — T011; C3; model: inherit)

**Checkpoint**: Recurring heading matches; cancel info lives only in the red panel on this page

---

## Phase 7: Polish

- [x] T014 Run `ddev exec ./vendor/bin/pint --dirty`, `ddev exec php artisan test --filter=DonationFivePerMilleTest`, `ddev exec php artisan test --filter=DonationShowRouteTest`, and `bash bin/dev-rebuild-frontend.sh`. Stop. Do not implement the next spec. (test: yes; C2; model: inherit)

## Dependencies

- T001 then T002 and T003 (parallel).
- T004 before T005. T006 with or after T005.
- T007 before T008. US2 after Phase 2; independent of US1 except shared lang file already done.
- T009 before T010. T010 before T012 (same `show.blade.php`).
- T011 before T012. T013 parallel with T012 after T011.
- T014 last.

## Parallel opportunities

- T002 ∥ T003
- After Phase 2: US1 and US2 can proceed in parallel (different views)
- T013 ∥ T012 after T010

## MVP

US1 listing heading plus two-up featured row. Owner asked the whole family in one spec, so implement US1–US4 in this run, then stop for UAT.

## Notes

`tasks.md` is not a launch order for other page specs. Do not commit unless the owner asks.
