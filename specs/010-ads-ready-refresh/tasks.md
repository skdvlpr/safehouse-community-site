# Tasks: Ads-ready public refresh

**Input**: Design documents from `/specs/010-ads-ready-refresh/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/home-and-chrome.md, quickstart.md

**Tests**: Feature tests for the home strip, the header, the 5×1000 banner, and the cookie-banner language control. Width and motion are owner UAT.

**Organization**: Tasks are grouped by user story. Implement only after the owner says yes. `tasks.md` is not a launch order.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1, US2, US3

## Phase 1: Setup

**Purpose**: Shared motion that every story uses

- [x] T001 Add entrance utilities in `resources/css/app.css` using Tailwind `motion-safe:` for the animation and `motion-reduce:` so the final state shows with no animation. Cite [Tailwind states](https://tailwindcss.com/docs/hover-focus-and-other-states). (test: no; C3; model: inherit)

---

## Phase 2: Foundational (Blocking)

**Purpose**: Large-screen reading cap shared by US3 and kept off the membership page

**⚠️ CRITICAL**: No user story work until this phase is complete

- [x] T002 Add a shared reading-width class capped at Tailwind `max-w-6xl` (72rem) in `resources/css/app.css`. Cite [Tailwind max-width](https://tailwindcss.com/docs/max-width). Do not apply it to the Diventa socio template. (test: no; C2; model: inherit)

**Checkpoint**: Motion and width classes exist and are unused

---

## Phase 3: User Story 1 - Home actions and latest stories (Priority: P1) MVP

**Goal**: Home keeps its address, leads with Become a member, and shows up to four newest news and articles with links to both sections.

**Independent Test**: Open `/it` and `/en` with published stories. Primary button is membership. At most four windows, newest first, mixed. Both section links work. No stories means no strip.

### Tests for User Story 1

- [x] T003 [US1] Write failing feature tests in `tests/Feature/HomeStoriesTest.php`: mixed newest-first limit of 4, locale with no title is skipped, empty list hides the strip, membership stays the primary link. Cite [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C4; model: inherit)

### Implementation for User Story 1

- [x] T004 [US1] In `app/Http/Controllers/HomeController.php`, query published news and editorial articles that have a title in the current locale, order by `published_at` then `updated_at` newest first, limit 4, and pass them to the home view. (test: yes — T003; C5; model: inherit)
- [x] T005 [US1] Render the windows, the all-news link, and the all-articles link in `resources/views/pages/partials/latest-stories.blade.php`, included from `resources/views/pages/templates/home.blade.php`. Primary action Become a member; secondary Donate and Become a volunteer. Windows use the T001 entrance class. Cite [Laravel Blade](https://laravel.com/docs/13.x/blade). (test: yes — T003; C4; model: inherit)

**Checkpoint**: US1 passes `HomeStoriesTest` without the new header

---

## Phase 4: User Story 2 - Short header, 5×1000 banner, local banner language (Priority: P1)

**Goal**: Header is five items plus Donate. 5×1000 is a banner on every public page except Diventa socio. The cookie banner's IT/EN control changes only that banner.

**Independent Test**: Header has no Home, news, or 5×1000. Banner is on `/it/about-us` and absent on `/it/diventa-socio`. Choosing English inside the cookie banner on an Italian page does not change the address.

### Tests for User Story 2

- [x] T006 [P] [US2] Extend `tests/Feature/NavigationMenuTest.php` for the new header order and for the 5×1000 banner present on a normal page and absent on Diventa socio. (test: yes; C4; model: inherit)
- [x] T007 [P] [US2] Write failing feature tests in `tests/Feature/CookieBannerLocaleTest.php`: the banner language control is not a link to the other locale; the footer language control still points at `LocalizedUrl` for the other locale. (test: yes; C3; model: inherit)

### Implementation for User Story 2

- [x] T008 [P] [US2] Replace the header list in `config/navigation.php` with Who we are (`about`), Services (`services`), Become a member (`diventa-socio`), Volunteering (`volunteers.show`), and Contact (`contact`). Add `diventa-socio` to `standard_page_keys`. Leave the Donate button in `resources/views/layouts/partials/header.blade.php`. (test: yes — T006; C3; model: inherit)
- [x] T009 [P] [US2] Add `resources/views/layouts/partials/five-per-mille-banner.blade.php` and include it from `resources/views/layouts/app.blade.php` on every public page except when the current page key is `diventa-socio`. The banner links to `donations.five-per-mille`. (test: yes — T006; C3; model: inherit)
- [x] T010 [US2] In `resources/views/layouts/partials/locale-switch.blade.php` and `resources/views/layouts/partials/cookie-banner.blade.php`, make the banner variant a control that swaps only the banner copy between `it` and `en`. It MUST NOT use `App\Support\LocalizedUrl`. The footer variant MUST keep `LocalizedUrl::forLocale`. (test: yes — T007; C5; model: inherit)

**Checkpoint**: US2 tests pass. Footer still switches the whole page.

---

## Phase 5: User Story 3 - Wider reading column and membership-style motion (Priority: P2)

**Goal**: Volunteer form and reading pages use the `max-w-6xl` cap on large screens. Entrance motion uses T001 and does not run under reduced motion. Diventa socio stays full width.

**Independent Test**: Volunteer form markup uses the wide class. A news article uses the reading cap. Diventa socio template does not. Entrance classes are `motion-safe` only.

### Tests for User Story 3

- [x] T011 [US3] Add assertions in `tests/Feature/HomeStoriesTest.php` or a new `tests/Feature/PublicWidthTest.php` that the volunteer page includes the wide form class and `/it/diventa-socio` does not include the reading-cap class. (test: yes; C3; model: inherit)

### Implementation for User Story 3

- [x] T012 [P] [US3] Apply the wide form layout on large screens in `resources/views/pages/volunteer.blade.php` using the T002 cap. Phone layout stays stacked. (test: yes — T011; C3; model: inherit)
- [x] T013 [P] [US3] Apply the T002 reading cap and T001 entrance classes to `resources/views/pages/articles/index.blade.php`, `resources/views/pages/articles/show.blade.php`, `resources/views/pages/editorial-articles/index.blade.php`, `resources/views/pages/editorial-articles/show.blade.php`, `resources/views/pages/templates/about.blade.php`, `resources/views/pages/templates/services.blade.php`, `resources/views/pages/templates/contact.blade.php`, and `resources/views/donations/index.blade.php`. Do not add the cap to `resources/views/pages/templates/landing.blade.php`. (test: yes — T011; C4; model: inherit)

**Checkpoint**: US3 width assertions pass. Reduced-motion behavior is in the CSS from T001.

---

## Phase 6: Polish

- [x] T015 Wrap CMS tables in a sideways-scroll container in `app/Support/CmsHtml.php` and style `.prose-table-scroll` in `resources/css/app.css` so the cookie-policy table stays on screen on a phone. Assert the wrapper in `tests/Unit/CmsHtmlTest.php`. (test: yes; C3; model: inherit)
- [x] T014 Run `ddev exec ./vendor/bin/pint --dirty` and `ddev exec php artisan test`. Cite [Laravel Pint](https://laravel.com/docs/13.x/pint) and [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C2; model: inherit)

---

## Dependencies & Execution Order

### Phase Dependencies

- Setup (T001) has no dependency.
- Foundational (T002) blocks US3. US1 and US2 can start after T001.
- US1: T003 before T004 and T005. T005 after T001 and T004.
- US2: T006 before T008 and T009. T007 before T010. T008, T009, and T010 touch different files and can run together after their tests.
- US3 after T001 and T002. T011 before T012 and T013. T012 and T013 can run together.
- Polish last.

### User Story Dependencies

- **US1 (P1)**: after T001. No dependency on US2 or US3.
- **US2 (P1)**: after T001. Independently testable. Does not require the home strip.
- **US3 (P2)**: after T001 and T002.

### Parallel Opportunities

- T006 and T007 are different test files.
- T008, T009, and T010 are different files.
- T012 and T013 are different views.

### Parallel Example: User Story 2

```text
T008 config/navigation.php
T009 five-per-mille banner partial
T010 cookie banner language control
```

---

## Implementation Strategy

### MVP First (User Story 1)

1. T001
2. T003–T005
3. Stop and check home actions and the story windows before the header change.

### Incremental Delivery

1. US1: home orients and shows the latest stories.
2. US2: short header, 5×1000 banner, banner language stays local.
3. US3: wider volunteer form and reading pages, membership-style motion.
4. Owner UAT from `quickstart.md`, then a separate push decision.

### Out of scope

No president video, no CMS restyle, no Google Ads account, no full-bleed layout on reading pages, no change to the footer language control.

## Notes

- [P] tasks use different files.
- Tests in T003, T006, T007, and T011 are written to fail before their implementation tasks.
- Do not implement from this file until the owner says yes.
