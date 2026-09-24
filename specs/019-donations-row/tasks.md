# Tasks: Donations heading row

**Input**: Design documents from `/specs/019-donations-row/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/heading.md, quickstart.md

**Tests**: One feature test for the heading text.

**Organization**: One user story. Do not start the next spec in this implement run.

## Format: `[ID] [P?] [Story] Description`

## Phase 1: Setup

- [ ] T001 Confirm `resources/views/donations/index.blade.php` is the page to change and that `resources/views/pages/templates/landing.blade.php` is not edited. (test: no; C1; model: inherit)

## Phase 2: Foundational

- [ ] T002 Add or reuse a Chi siamo style heading partial in `resources/views/pages/partials/page-hero.blade.php` with title, bar, and tagline. Cite [Laravel Blade](https://laravel.com/docs/13.x/blade). (test: no; C3; model: inherit)

## Phase 3: User Story 1 - Page matches the agreed heading and layout (Priority: P1)

**Goal**: Donations heading row

**Independent Test**: Italian page shows the spec heading and no red label above it.

### Tests

- [ ] T003 [US1] Add a feature test under `tests/Feature/` that opens the page and sees the new Italian heading and does not see the old red eyebrow key on that page. Cite [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C3; model: inherit)

### Implementation

- [ ] T004 [US1] Apply the heading and the spec layout in `resources/views/donations/index.blade.php`, `resources/css/app.css`, and `lang/it/site.php`. On a wide screen use half-width fields or side-by-side blocks only where the spec asks. Phone stays stacked. (test: yes — T003; C4; model: inherit)

## Phase 4: Polish

- [ ] T005 Run `ddev exec ./vendor/bin/pint --dirty` and the new feature test. Stop. Do not implement the next spec. (test: yes; C2; model: inherit)

## Dependencies

T002 before T004. T003 before T004. T005 last.

## Notes

Do not implement until this spec is the one the owner is testing. `tasks.md` is not a launch order for the other page specs.
