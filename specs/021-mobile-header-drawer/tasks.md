# Tasks: Mobile header drawer

**Input**: Design documents from `/specs/021-mobile-header-drawer/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/header-drawer.md, quickstart.md

**Tests**: Feature tests for hamburger markup, donate strings, and Contatti heading. Drawer animation and 5 x 1000 centering are owner UAT.

**Organization**: Tasks are grouped by user story. Do not start `019-donations-row` in the implement run.

## Format: `[ID] [P?] [Story] Description`

## Phase 1: Setup

**Purpose**: Lock this spec as the only active work

- [x] T001 Confirm `.specify/feature.json` points at `specs/021-mobile-header-drawer` and that `specs/019-donations-row/` and Diventa socio views are not edited

---

## Phase 2: Foundational

**Purpose**: Shared copy keys before header markup changes

**⚠️ CRITICAL**: No user story work until this phase is complete

- [x] T002 [P] Add `site.nav.donate_short` in `lang/it/site.php` with the verbatim constraint **Dona ora**; keep `site.nav.donate` as verbatim **Tutti i modi per donare**
- [x] T003 [P] Add `site.nav.donate_short` in `lang/en/site.php` with the verbatim constraint **Donate now**; keep `site.nav.donate` as verbatim **All ways to donate**

**Checkpoint**: Both locales have long and short donate keys

---

## Phase 3: User Story 1 - Phone can open the site menu (Priority: P1) 🎯 MVP

**Goal**: Visible hamburger, left-sliding transparent blurred drawer, same destinations as the wide-screen header

**Independent Test**: Phone-width home shows a hamburger; opening it lists header destinations; wide window still shows the row menu

### Tests for User Story 1

- [x] T004 [US1] Extend `tests/Feature/SiteLayoutTest.php` so `/it` and `/en` assert a phone hamburger (`aria-expanded`, `site.nav.menu` accessible name) and drawer markup that includes the same header destinations. Cite [Laravel testing](https://laravel.com/docs/13.x/testing)

### Implementation for User Story 1

- [x] T005 [US1] Replace `details.site-header__menu` in `resources/views/layouts/partials/header.blade.php` with a left-side hamburger (`md:hidden`) plus overlay and left panel with a visible close control; reuse `resources/views/layouts/partials/nav-item-mobile.blade.php` so Altre Pagine children stay. Cite [Laravel Blade](https://laravel.com/docs/13.x/blade)
- [x] T006 [US1] Add drawer, overlay, and hamburger rules in `resources/css/app.css`: panel `w-[min(20rem,85vw)]`, slide via `-translate-x-full` / `translate-x-0`, `transition-transform`, `motion-reduce:transition-none`, transparent fill plus `backdrop-blur-[96px]`, z-index above `.site-top`. Cite [translate](https://tailwindcss.com/docs/translate), [transition](https://tailwindcss.com/docs/transition-property), [backdrop-blur](https://tailwindcss.com/docs/backdrop-blur), [z-index](https://tailwindcss.com/docs/z-index), [width](https://tailwindcss.com/docs/width)
- [x] T007 [US1] Add `resources/js/header-drawer.js` and import it from `resources/js/app.js` to toggle open state, `aria-expanded`, visible close control, backdrop click, Escape, and close on drawer link click

**Checkpoint**: Phone menu works; desktop row menu unchanged

---

## Phase 4: User Story 2 - Donate label fits the phone header (Priority: P1)

**Goal**: Phone donate button reads **Dona ora** / **Donate now**; wide screen keeps the long labels

**Independent Test**: HTML contains both labels; phone CSS shows the short one

### Tests for User Story 2

- [x] T008 [US2] Update `tests/Feature/HomePageTest.php` and `tests/Feature/SiteLayoutTest.php` so they still see `site.nav.donate` and also see `site.nav.donate_short` on `/it` and `/en`

### Implementation for User Story 2

- [x] T009 [US2] In `resources/views/layouts/partials/header.blade.php` keep one donate link to `donations.index` with two spans: `md:hidden` shows `site.nav.donate_short` (verbatim **Dona ora** / **Donate now**); `hidden md:inline` shows `site.nav.donate` (verbatim **Tutti i modi per donare** / **All ways to donate**)

**Checkpoint**: Short and long donate labels coexist in one header link

---

## Phase 5: User Story 3 - 5 x 1000 strip is centered (Priority: P2)

**Goal**: Strip content centered under the header menu on a wide screen and centered on a phone

**Independent Test**: Owner UAT of `resources/views/layouts/partials/five-per-mille-banner.blade.php` on wide and phone. Copy C.F. still works

- [x] T010 [US3] Center `.site-five__inner`, `.site-five__link`, and `.site-five__text` in `resources/css/app.css` (and banner markup in `resources/views/layouts/partials/five-per-mille-banner.blade.php` only if needed). Do not change header color or donate chrome

**Checkpoint**: Strip is visually centered; link and copy still work

---

## Phase 6: User Story 4 - Contatti heading fits a phone (Priority: P2)

**Goal**: Shorter line after the Contatti bar; desks stay in the form; Diventa socio unchanged

**Independent Test**: `/it/contact` and `/en/contact` show the short heading sentences

### Tests for User Story 4

- [x] T011 [US4] Add `tests/Feature/ContactHeadingTest.php` asserting Italian verbatim **Per contattarci compila il modulo e scegli lo sportello più adatto.** and English verbatim **Fill in the form and choose the most suitable desk.** and that the old desk-list heading is gone. Cite [Laravel testing](https://laravel.com/docs/13.x/testing)

### Implementation for User Story 4

- [x] T012 [P] [US4] Set `site.pages.contact_lead` in `lang/it/site.php` to verbatim **Per contattarci compila il modulo e scegli lo sportello più adatto.** Do not change `site.membership.contact_lead`
- [x] T013 [P] [US4] Set `site.pages.contact_lead` in `lang/en/site.php` to verbatim **Fill in the form and choose the most suitable desk.** Do not change `site.membership.contact_lead`

**Checkpoint**: Contatti heading is one short sentence; membership lead unchanged

---

## Phase 7: User Story 5 - Contatti columns and surname (Priority: P2)

**Goal**: Stretch the left column, tighten the form, collect Cognome, send Lead `lastName`

**Independent Test**: Wide Contatti columns share a bottom edge. Form has Cognome. CRM Lead create uses `lastName`

- [x] T015 [US5] Add `site.pages.contact_desks_blurb` in `lang/it/site.php` and `lang/en/site.php` and render it in the FAQ-button block in `resources/views/pages/templates/contact.blade.php`
- [x] T016 [US5] Stretch `.template-page--contact .template-contact-info` to the form column height and tighten `.template-contact-form` vertical spacing in `resources/css/app.css` (contact page only)
- [x] T017 [US5] Add required `last_name` on `contact_submissions` via a new migration under `database/migrations/`, `app/Models/ContactSubmission.php`, `app/Http/Requests/StoreContactSubmissionRequest.php`, `app/Services/ContactSubmissionService.php`, and a Cognome field in `resources/views/pages/partials/contact-form-shell.blade.php`. Constraints: required string, max 255
- [x] T018 [US5] Map `last_name` to EspoCRM Lead `lastName` and `name` to Lead `firstName` in `app/Services/EspoCrm/EspoCrmContactIntakeService.php`. Do not edit the CRM repo. Cite membership/volunteer `lastName` mapping
- [x] T019 [US5] Extend `tests/Feature/ContactFormTest.php` and `tests/Feature/ContactFormMailTest.php` so payloads include `last_name` and a missing surname is rejected. Assert Lead create payload `lastName` where a CRM test exists

**Checkpoint**: Surname is required and reaches Lead `lastName`

---

## Phase 8: User Story 6 - Home news on a phone (Priority: P2)

**Goal**: Wider preview, no arrows or listing buttons on a phone, swipe between stories

**Independent Test**: Phone home news card fills the content width; swipe works; wide home still shows arrows and listing links

- [x] T020 [US6] In `resources/views/pages/partials/latest-stories.blade.php` and `resources/css/app.css` hide `.story-slider__arrow` and `.story-slider__links` below `md`, and enlarge `.story-card` on a phone
- [x] T021 [US6] Add swipe previous/next in `resources/js/story-slider.js`, import it from `resources/js/app.js`, and remove the inline slider script from `resources/views/pages/partials/latest-stories.blade.php`

---

## Phase 9: User Story 7 - Cookie table (Priority: P2)

**Goal**: Full cookie table on a wide screen; sideways scroll only on a phone

**Independent Test**: Cookie-policy wide shows all columns; phone scrolls `.prose-table-scroll` only

- [x] T022 [US7] Change `.prose-table-scroll` in `resources/css/app.css` so overflow-x is auto only below `md` and the table is `w-full` from `md` up. Cite [overflow](https://tailwindcss.com/docs/overflow)

---

## Phase 10: Polish

- [x] T014 Run `bash bin/dev-rebuild-frontend.sh`, `ddev exec php artisan migrate`, `ddev exec ./vendor/bin/pint --dirty`, and the feature tests in `specs/021-mobile-header-drawer/quickstart.md`. Stop. Do not implement `019-donations-row`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: T001 first
- **Foundational (Phase 2)**: T002 and T003 after T001; they may run in parallel
- **US1 (Phase 3)**: T004 before T005–T007; T005 before T007 (markup hooks); T006 may overlap T005
- **US2 (Phase 4)**: After US1 header rewrite; T008 before or with T009
- **US3 (Phase 5)**: Independent of US4; after foundational
- **US4 (Phase 6)**: T012/T013 parallel; T011 can be written first
- **US5 (Phase 7)**: After US4 copy keys; T015–T019 before later stories
- **US6 (Phase 8)**: Independent of Contatti
- **US7 (Phase 9)**: Independent CSS
- **Polish**: T014 last

### User Story Dependencies

- **User Story 1 (P1)**: After Phase 2
- **User Story 2 (P1)**: After US1 header markup (same `header.blade.php`)
- **User Story 3 (P2)**: After Phase 2; different files from US4
- **User Story 4 (P2)**: After Phase 2; different files from US1/US2 except tests
- **User Story 5 (P2)**: After US4 heading copy; same Contatti templates

### Parallel Opportunities

- T002 and T003
- T012 and T013
- US3 can run beside US4
- T006 beside T005 once class names are agreed in T005

### Parallel Example: Foundational copy

```bash
Task: "Add donate_short in lang/it/site.php"
Task: "Add donate_short in lang/en/site.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 + 2)

1. Setup + donate_short keys
2. Hamburger and drawer
3. Short donate spans
4. Owner can already use the phone menu

### Incremental Delivery

5. Center 5 x 1000
6. Shorten Contatti heading; move sportelli copy to the FAQ block
7. Stretch left column, tighten form, add Cognome → Lead `lastName`
8. Home news swipe; cookie table overflow
9. Pint, migrate, frontend rebuild, stop for owner UAT

Do not implement until the owner approves this task list and the model column in chat. `tasks.md` is not a launch order.
