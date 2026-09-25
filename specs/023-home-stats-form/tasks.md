# Tasks: Home stats order, side reveals, donation form columns

**Input**: Design documents from `/specs/023-home-stats-form/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/layout.md, quickstart.md

**Tests**: Feature tests for Home HTML order, Chi siamo/Donazioni `left`/`right`, and donation form column classes. Motion feel and desktop scroll are owner UAT.

**Organization**: Tasks are grouped by user story. Do not edit `resources/views/pages/templates/landing.blade.php`.

## Format: `[ID] [P?] [Story] Description`

Complexity and proposed model are in the chat table. Owner ordered implement in the same turn; inherit for every row.

## Phase 1: Setup

**Purpose**: Lock this spec as the only active work

- [x] T001 Confirm `.specify/feature.json` points at `specs/023-home-stats-form`

---

## Phase 2: Foundational

**Purpose**: Make side-slide CSS valid on every page that uses `.landing-reveal`

**⚠️ CRITICAL**: No user story work until this phase is complete

- [x] T002 Set `--landing-ease-out: cubic-bezier(0.16, 1, 0.3, 1)` on `.landing-reveal` in `resources/css/app.css` so the transform transition is valid outside Diventa socio and Servizi. Cite the existing socio comment and [Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API)

**Checkpoint**: Easing is inherited by Chi siamo and Donazioni targets

---

## Phase 3: User Story 1 - Home numbers sit between hero and quote (Priority: P1) 🎯 MVP

**Goal**: Public Home order is hero → impact stats → manifesto quote → stories → independence

**Independent Test**: `/it` HTML has `Pasti distribuiti` after the hero and before `NESSUN ESSERE UMANO È ILLEGALE`

### Tests for User Story 1

- [x] T003 [US1] Extend `tests/Feature/HomePageTest.php` so `/it` asserts stats heading position is after the hero `h1` and before the manifesto quote. Cite [Laravel testing](https://laravel.com/docs/13.x/testing)

### Implementation for User Story 1

- [x] T004 [US1] In `resources/views/pages/templates/home.blade.php` move the impact stats `<section>` to immediately after the hero `landing-reveal` and before `@include('pages.partials.home-manifesto-banner')`. Cite [Blade](https://laravel.com/docs/13.x/blade)

**Checkpoint**: Home order matches FR-001

---

## Phase 4: User Story 2 - Chi siamo and Donazioni slide from opposite sides (Priority: P1)

**Goal**: Those two sections use socio left/right + `overflow: clip`. Other templates keep current reveals. Diventa socio not edited.

**Independent Test**: About and Donazioni markup have `left` and `right` and those cards do not use `up`

### Tests for User Story 2

- [x] T005 [P] [US2] Extend `tests/Feature/CmsPagesTest.php` so `/it/about-us` sees `data-reveal-from="left"` and `"right"` and the about closing is not `up`
- [x] T006 [P] [US2] Extend `tests/Feature/DonationCampaignRoutesTest.php` and `tests/Feature/DonationFivePerMilleTest.php` so listing/recurring/campaign/5 x 1000 cards use `left` or `right`, not `up`

### Implementation for User Story 2

- [x] T007 [US2] In `resources/views/pages/templates/about.blade.php` set intro/values/closing to alternating `left`/`right`. In `resources/css/app.css` set `.template-page--about` and `.template-about-grid` to `overflow: clip` (not mixed `overflow-x-clip`)
- [x] T008 [US2] In `resources/views/donations/index.blade.php` set recurring and campaign cards to alternating `left`/`right`; add a clipped campaigns list class. Featured already left/right. Change `.donations-index__featured` to `overflow: clip`
- [x] T009 [P] [US2] In `resources/views/donations/five-per-mille.blade.php` and `resources/views/donations/show.blade.php` set the main glass/form wrapper to `left` or `right` (not `up`) and clip the wrapper

**Checkpoint**: Chi siamo and Donazioni match socio side entrance; `landing.blade.php` untouched

---

## Phase 5: User Story 3 - Desktop donation form uses columns (Priority: P1)

**Goal**: Wide card from `lg`, two field columns, unstretched inputs, stacked `max-w-2xl` on small screens. Payment Element unchanged.

**Independent Test**: Campaign `show` HTML has `donation-form__columns`, `max-w-2xl`, and `lg:max-w-5xl`

### Tests for User Story 3

- [x] T010 [US3] Extend `tests/Feature/DonationShowRouteTest.php` for one-time and recurring: `donation-form__columns`, `lg:max-w-5xl`, still `max-w-2xl`, still `#payment-element`

### Implementation for User Story 3

- [x] T011 [US3] In `resources/views/donations/show.blade.php` wrap identity fields and comment/amount/notices in `.donation-form__columns` / two `.donation-form__col` groups; keep cancel panel, Payment Element, and submit outside the grid. Cite [Blade](https://laravel.com/docs/13.x/blade)
- [x] T012 [US3] In `resources/css/app.css` set `.donation-form` to `max-w-2xl lg:max-w-5xl` and `.donation-form__columns` to `grid grid-cols-1 lg:grid-cols-2`. Cite [grid-template-columns](https://tailwindcss.com/docs/grid-template-columns) and [max-width](https://tailwindcss.com/docs/max-width)

**Checkpoint**: Desktop form is wide via columns; Stripe JS still mounts `#payment-element`

---

## Phase 6: Polish

- [x] T013 Run `ddev exec php artisan test` and `ddev exec ./vendor/bin/pint`; rebuild frontend with `bash bin/dev-rebuild-frontend.sh` if CSS changed
- [x] T014 Mark this file complete; append `.specify/progress/` handoff; paste Russian UAT in chat and wait (Cursor-browser offer only)

## Dependencies

- T001 → T002 → US1 / US2 / US3
- T011 before T012 may swap; both before T013

## Implementation strategy

MVP is T001–T004 (Home order). Then US2 (the animation the owner missed). Then US3 (form columns). Single implement pass as ordered.
