# Tasks: Light-theme chrome, 5×1000 mark, slower Home count

**Input**: Design documents from `/specs/024-light-theme-chrome/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/chrome.md, quickstart.md

**Tests**: Feature tests for Chi siamo classes, 5× mark markup, news Categorie class. Light/hover pixels and count duration feel are owner UAT.

**Organization**: Tasks are grouped by user story. Do not edit Diventa socio. Do not revert 017.1 phone toolbar CSS.

## Format: `[ID] [P?] [Story] Description`

Complexity and proposed model are in the chat table. Owner ordered implement in the same turn; inherit for every row.

## Phase 1: Setup

**Purpose**: Lock this spec as the only active work

- [x] T001 Confirm `.specify/feature.json` points at `specs/024-light-theme-chrome`

---

## Phase 2: Foundational

**Purpose**: None — CSS exceptions do not need a shared scaffold

**⚠️ CRITICAL**: No user story work until setup is complete

- [x] T002 No-op foundation: keep `017.1` `.news-toolbar` / `.news-date-filters` stack in `resources/css/app.css`

**Checkpoint**: Active spec is 024; phone filter CSS still present

---

## Phase 3: User Story 1 - Chi siamo red outlines on light (Priority: P1) 🎯 MVP

**Goal**: Values and closing keep a red/primary outline in Aurora Light. Intro stays generic glass.

**Independent Test**: `/it/about-us` HTML has `template-about-values` and `template-about-closing`. Light CSS does not map those two to `--safehouse-glass-border`.

### Tests for User Story 1

- [x] T003 [US1] Extend `tests/Feature/CmsPagesTest.php` so `/it/about-us` asserts `template-about-values` and `template-about-closing`. Cite [Laravel testing](https://laravel.com/docs/13.x/testing)

### Implementation for User Story 1

- [x] T004 [US1] In `resources/css/app.css` remove `.template-page--about .template-about-values` and `.template-about-closing` from the light `border-color: var(--safehouse-glass-border)` list. Add light rules: closing `border-color: var(--color-safehouse-primary)`; values a primary mix stronger than glass. Cite [border-color](https://tailwindcss.com/docs/border-color)

**Checkpoint**: Light Chi siamo matches FR-001

---

## Phase 4: User Story 2 - Rectangular 5× mark with readable hover (Priority: P1)

**Goal**: Mark is a transparent red-outlined rectangle. Hover fills primary with white **5×**. Banner hover wash is visible on light.

**Independent Test**: Home HTML has `site-five__mark` and `5×`. CSS mark is not `rounded-full`.

### Tests for User Story 2

- [x] T005 [P] [US2] Extend `tests/Feature/NavigationMenuTest.php` so `/it` asserts `site-five__mark` and `5×`. Cite [Laravel testing](https://laravel.com/docs/13.x/testing)

### Implementation for User Story 2

- [x] T006 [US2] In `resources/css/app.css` restyle `.site-five__mark` to `rounded-sm`, transparent fill, `border-safehouse-primary`, primary text (not `rounded-full` fill). Hover: primary fill + white text. Replace `hover:bg-white/5` on `.site-five__link` with a primary `color-mix` wash. Keep `5×` in `resources/views/layouts/partials/five-per-mille-banner.blade.php`. Cite [border-radius](https://tailwindcss.com/docs/border-radius)

**Checkpoint**: FR-002 and FR-003

---

## Phase 5: User Story 3 - Categorie matches date fields on light (Priority: P1)

**Goal**: Light `.news-cat-menu__summary` uses the same fill, color, and border as date inputs.

**Independent Test**: `/it/news` and `/it/articles` HTML include `news-cat-menu__summary`

### Tests for User Story 3

- [x] T007 [US3] Extend `tests/Feature/NewsHeadingTest.php` so `/it/news` and `/it/articles` assert `news-cat-menu__summary`. Cite [Laravel testing](https://laravel.com/docs/13.x/testing)

### Implementation for User Story 3

- [x] T008 [US3] In `resources/css/app.css` add `.news-cat-menu__summary` to the light control rule shared with `.news-date-filters__field input` (`rgb(255 255 255 / 92%)` fill, `rgb(0 0 0 / 14%)` border)

**Checkpoint**: FR-004

---

## Phase 6: User Story 4 - Home count a bit slower (Priority: P2)

**Goal**: `COUNT_DURATION_MS` is 3500. Reduced motion still skips.

**Independent Test**: `resources/js/impact-count.js` duration constant is 3500; reduced-motion early return remains.

### Implementation for User Story 4

- [x] T009 [US4] In `resources/js/impact-count.js` set `COUNT_DURATION_MS` to 3500 and keep the [prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion) skip. Cite [requestAnimationFrame](https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame)

**Checkpoint**: FR-005

---

## Phase 7: Polish

- [x] T010 Run `ddev exec php artisan test` and `ddev exec ./vendor/bin/pint --test`; rebuild frontend with `bash bin/dev-rebuild-frontend.sh`
