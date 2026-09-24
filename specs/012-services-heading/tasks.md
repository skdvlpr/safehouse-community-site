# Tasks: Services heading

**Input**: Design documents from `/specs/012-services-heading/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/heading.md, quickstart.md

**Tests**: One feature test for the heading text.

**Organization**: One user story. Do not start the next spec in this implement run.

## Format: `[ID] [P?] [Story] Description`

## Phase 1: Setup

- [x] T001 Confirm `resources/views/pages/templates/services.blade.php` is the page to change and that `resources/views/pages/templates/landing.blade.php` is not edited. (test: no; C1; model: inherit)

## Phase 2: Foundational

- [x] T002 Add or reuse a Chi siamo style heading partial in `resources/views/pages/partials/page-hero.blade.php` with title, bar, and tagline. Cite [Laravel Blade](https://laravel.com/docs/13.x/blade). (test: no; C3; model: inherit)

## Phase 3: User Story 1 - Page matches the agreed heading and layout (Priority: P1)

**Goal**: Services heading

**Independent Test**: Italian page shows the spec heading and no red label above it.

### Tests

- [x] T003 [US1] Add a feature test under `tests/Feature/` that opens the page and sees the new Italian heading and does not see the old red eyebrow key on that page. Cite [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C3; model: inherit)

### Implementation

- [x] T004 [US1] Apply the heading and the spec layout in `resources/views/pages/templates/services.blade.php`, `resources/views/pages/partials/page-header.blade.php`, and `resources/css/app.css`. On a wide screen use half-width fields or side-by-side blocks only where the spec asks. Phone stays stacked. (test: yes — T003; C4; model: inherit)

## Phase 4: Polish

- [x] T005 Run `ddev exec ./vendor/bin/pint --dirty` and the new feature test. Stop. Do not implement the next spec. (test: yes; C2; model: inherit)

## Phase 5: Card entrance

- [x] T006 [US1] Wrap each service card in `resources/views/pages/templates/services.blade.php` with the existing side-slide entrance (`landing-reveal`, alternating left and right). Do not edit `resources/views/pages/templates/landing.blade.php`. Cite [Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API) and [prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion). (test: yes — T003; C4; model: inherit)
- [x] T007 [US1] Let that entrance run on the services grid in `resources/css/app.css`: clip the off-screen start, keep the card colors, and keep reduced motion showing the cards in place. Cite [Tailwind prefers-reduced-motion](https://tailwindcss.com/docs/hover-focus-and-other-states#prefers-reduced-motion). (test: yes — T003; C3; model: inherit)

## Dependencies

T002 before T004. T003 before T004. T005 last.

## Notes

Do not implement until this spec is the one the owner is testing. `tasks.md` is not a launch order for the other page specs.
