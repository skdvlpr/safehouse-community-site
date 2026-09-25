# Tasks: Site chrome, motion, and ETS naming

**Input**: Design documents from `/specs/022-site-chrome-motion/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/chrome.md, quickstart.md

**Tests**: Feature tests for drawer Home, title suffix, menu labels, `data-count-to`, and `landing-reveal` on a non-socio page. Visual motion, vertical center, and banner tint are owner UAT.

**Organization**: Do not edit `resources/views/pages/templates/landing.blade.php`. Do not add Home to `config/navigation.php` `header`. Banner tint is last and needs its own commit after US1–US6.

## Format: `[ID] [P?] [Story] Description`

Each task lists **C** (1–10) and **model** for the owner Launch vs Replace table in chat.

## Phase 1: Setup

- [x] T001 Confirm `.specify/feature.json` points at `specs/022-site-chrome-motion` and that `resources/views/pages/templates/landing.blade.php` will not be edited. (test: no; C1; model: inherit)

---

## Phase 2: Foundational

- [x] T002 [P] In `lang/it/site.php` set `layout.title_suffix` to **— Safe House ETS** and add `nav.drawer_title` **Safe House ETS**.
- [x] T003 [P] In `lang/en/site.php` set the same suffix and `drawer_title`.

**Checkpoint**: Lang ready for drawer title and tab suffix

---

## Phase 3: User Story 1 - Drawer ETS title (Priority: P1) 🎯 MVP

**Goal**: Phone drawer branded Safe House ETS. Owner 2026-09-25: **do not add Home** to the drawer.

### Tests for User Story 1

- [x] T004 [US1] Extend `tests/Feature/SiteLayoutTest.php`: Italian Home HTML contains drawer title **Safe House ETS**, the drawer has **no** Home link, hamburger accessible name stays Menu, desktop nav has no Home item. Cite [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C4; model: inherit)

### Implementation for User Story 1

- [x] T005 [US1] In `resources/views/layouts/partials/header.blade.php` set the visible drawer title to `__('site.nav.drawer_title')`. Do **not** add a Home link. Leave the desktop `<nav class="hidden … md:flex">` loop unchanged. Cite [Blade](https://laravel.com/docs/13.x/blade). (test: yes — T004; C3; model: inherit)

**Checkpoint**: Phone drawer has Home + ETS; desktop nav item set unchanged

---

## Phase 4: User Story 2 - Document title suffix Safe House ETS (Priority: P1)

**Goal**: `<title>` ends with `— Safe House ETS`

### Tests for User Story 2

- [x] T006 [US2] Assert `/it` and `/en/donations` document titles contain `— Safe House ETS` and that `og:title` on Home does not include that suffix. (test: yes; C3; model: inherit)

### Implementation for User Story 2

- [x] T007 [US2] Rely on T002/T003 `title_suffix`. Confirm `resources/views/layouts/partials/discovery-head.blade.php` still concatenates suffix only on `<title>`, not `og:title`. (test: yes — T006; C1; model: inherit)

**Checkpoint**: Tabs say Safe House ETS

---

## Phase 5: User Story 3 - Vertical center on every page-hero (Priority: P1)

**Goal**: Tagline after `|` is vertically centered site-wide

### Tests for User Story 3

- [x] T008 [US3] Assert `page-hero__headline--center` (or the new default class) on Chi siamo, Servizi, Volunteer, and a news listing. (test: yes; C3; model: inherit)

### Implementation for User Story 3

- [x] T009 [US3] Default `align` to `center` in `resources/views/pages/partials/page-header.blade.php` and `page-hero.blade.php`. In `resources/css/app.css` change `.page-hero__headline` to `lg:items-center` and remove tagline `lg:pb-2`. Cite [align-items](https://tailwindcss.com/docs/align-items). (test: yes — T008; C3; model: inherit)

**Checkpoint**: Optical center is the heading default

---

## Phase 6: User Story 4 - Block reveal except Diventa socio (Priority: P1)

**Goal**: Glass/card blocks use `.landing-reveal`; membership landing frozen

### Tests for User Story 4

- [x] T010 [US4] Assert `landing-reveal` on Chi siamo and Home stats or story cards. Assert Diventa socio still renders its existing membership cards (no requirement to add new classes there). (test: yes; C4; model: inherit)

### Implementation for User Story 4

- [x] T011 [US4] Wrap glass/card roots in `.landing-reveal` + `.landing-reveal__target` on: Home (hero, manifesto, stories, independence, stats), `about.blade.php`, `contact.blade.php`, donations index/featured/5 x 1000/form card, news feed/list items, `article.blade.php` body, `legal.blade.php`, `default.blade.php`, `volunteer.blade.php` panel. Do **not** edit `landing.blade.php`. Servizi already has reveal. Cite [Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API) and [prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion). (test: yes — T010; C7; model: inherit)

**Checkpoint**: Blocks enter like socio; socio template untouched

---

## Phase 7: User Story 5 - Notizie and Articoli names (Priority: P1)

**Goal**: Menu uses Notizie/Articoli; Home buttons keep All

### Tests for User Story 5

- [x] T012 [US5] Assert Altre Pagine / drawer see `Notizie` and `Articoli` (EN News / Articles). Assert Home still sees `Tutte le notizie` and `Tutti gli articoli`. (test: yes; C3; model: inherit)

### Implementation for User Story 5

- [x] T013 [US5] In `resources/views/layouts/partials/nav-item.blade.php` and `nav-item-mobile.blade.php` switch news/editorial labels from `news_all` / `editorial_all` to `news_title` / `editorial_title`. Leave `resources/views/pages/partials/latest-stories.blade.php` on the All keys. (test: yes — T012; C2; model: inherit)

**Checkpoint**: Pages named Notizie/Articoli; Home CTAs unchanged

---

## Phase 8: User Story 6 - CRM counters count up (Priority: P1)

**Goal**: Numeric Home stats ease 0 → value in ~2s

### Tests for User Story 6

- [x] T014 [US6] Extend `tests/Feature/HomePageTest.php` so CRM totals still appear formatted (`3.149`) and numeric cards expose `data-count-to`. Em dash fallback has no `data-count-to`. (test: yes; C3; model: inherit)

### Implementation for User Story 6

- [x] T015 [US6] Update `resources/views/pages/partials/home-impact-stats.blade.php` and `HomeImpactStatsSnapshot` (or the partial only) so integer values get `data-count-to`. Add `resources/js/impact-count.js`, init from `resources/js/app.js`: 2000ms ease-out via `requestAnimationFrame`, locale grouping, skip `—`, honor reduced motion. Cite [rAF](https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame). (test: yes — T014; C5; model: inherit)

**Checkpoint**: Digits visibly tick; tests still see final HTML values

---

## Phase 9: Polish before banner

- [x] T016 Run donation/layout/home tests as needed, `ddev exec -- ./vendor/bin/pint`, `bash bin/dev-rebuild-frontend.sh`. (test: yes; C3; model: inherit)
- [ ] T017 **Git commit US1–US6 only** (owner already asked for a safety commit before the tint). Do not include banner CSS. (test: no; C2; model: inherit)

---

## Phase 10: User Story 7 - Light red banner tint (Priority: P2)

**Goal**: Very light red wash on `.site-five`, isolated commit

### Implementation for User Story 7

- [ ] T018 [US7] After T017, add a light `color-mix` of `--color-safehouse-primary` (~8%) into `.site-five` for dark and light theme in `resources/css/app.css`. Rebuild frontend. Commit this tint alone. (test: no; C2; model: inherit)

---

## Dependencies

- T001 before all
- T002 / T003 before T005 and T007
- T005 before T013 (same header area, different files)
- T011 must not touch `landing.blade.php`
- T017 before T018
- T018 is the only banner change

## Parallel opportunities

- T002 ∥ T003
- T009 ∥ T013 ∥ T015 after foundation
- T011 is sequential across templates but can be one implement pass

## Implementation strategy

MVP: T001–T016 (drawer, suffix, center, motion, names, counters), then T017 commit, then T018 tint. Stop for owner UAT after T018 (or after T017 if they want to judge motion before tint).
