# Feature Specification: Site chrome, motion, and ETS naming

**Feature Branch**: `022-site-chrome-motion`

**Created**: 2026-09-25

**Status**: Draft. Specified after `019.1` commit `7c3dd71`. Do not implement until the owner accepts the task table. Diventa socio is **out of scope** (do not edit `landing.blade.php` or membership card motion). Payment collection does not change. Banner red tint is the **last** story and ships only after a git commit of everything else in this spec.

**Input**: Owner 2026-09-25, after donation UAT. Mobile drawer only: add **Home**; do not add Home to the desktop header nav. Replace the drawer title **Menu** with **Safe House ETS**. Document titles: `page_name — Safe House ETS` (was `— Safe House`). Vertically center the copy to the right of `|` on **all** pages. Card/block entrance animations like membership (except Diventa socio). News and articles listing pages are named **Notizie** / **Articoli** (English **News** / **Articles**), not “Tutte le notizie” / “Tutti gli articoli”; Home buttons keep the current All labels. Home CRM counters count from 0 up to the current value in about two seconds. A very light red tint on the 5 x 1000 banner — commit the rest first so the tint can be reverted alone.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Mobile drawer Home and ETS title (Priority: P1)

A visitor on a phone opens the left drawer. The panel title is **Safe House ETS**, not Menu. Home is **not** in the drawer (owner reversed that on implement, 2026-09-25). Desktop header nav does **not** gain a Home item. The hamburger control may still use Menu as its accessible name.

**Why this priority**: Owner named this first after the donation commit.

**Independent Test**: Phone: open drawer, see Safe House ETS and Home. Desktop: header nav items unchanged, no extra Home.

**Acceptance Scenarios**:

1. **Given** a phone-width window on any public page, **When** the drawer opens, **Then** the panel title is **Safe House ETS** and there is no Home item.
2. **Given** a desktop-width window, **When** the header is in view, **Then** Home is not added to the desktop nav list (`config/navigation.php` `header` stays without Home).
3. **Given** the drawer, **When** a screen reader hits the hamburger, **Then** the control still has an accessible name of Menu / Chiudi menu as today.

---

### User Story 2 - Document title suffix Safe House ETS (Priority: P1)

Every public HTML document title ends with **— Safe House ETS** instead of **— Safe House**. Italian and English share that suffix. Open Graph `og:title` stays the page name without the suffix, as today.

**Why this priority**: Owner asked for the legal ETS name in the browser tab on every page.

**Independent Test**: View source or the tab on Home, Chi siamo, Donazioni, Notizie. Suffix is `— Safe House ETS`.

**Acceptance Scenarios**:

1. **Given** Italian Home, **When** the document head is read, **Then** `<title>` ends with `— Safe House ETS`.
2. **Given** English Donations, **When** the document head is read, **Then** the same suffix appears.
3. **Given** any public page, **When** `og:title` is read, **Then** it is still the page name only (no suffix), matching today.

---

### User Story 3 - Vertical center of the line after `|` on every page (Priority: P1)

On every page that uses the Chi siamo heading (large title, bar, tagline), the tagline is vertically centered with the title on wide screens. Donation pages already pass `align=center` from `019.1`. This story makes that the **default** for all remaining pages, including volunteer, about, services, legal, FAQ, news listings.

**Why this priority**: Owner asked for this optical alignment site-wide, not only on donations.

**Independent Test**: Open Chi siamo, Servizi, Contatti, Volunteer, Trasparenza, Notizie on a wide window. Tagline sits mid-height next to the title.

**Acceptance Scenarios**:

1. **Given** any prominent `page-hero` heading, **When** viewed on a wide window, **Then** the headline uses vertical center (`items-center`), not baseline-end with extra tagline padding.
2. **Given** Diventa socio, **When** this feature ships, **Then** that page is unchanged.

---

### User Story 4 - Block entrance motion like membership, except Diventa socio (Priority: P1)

On public pages that show glass or card blocks, those blocks enter with the same scroll reveal already used on Servizi and Diventa socio (`.landing-reveal` / IntersectionObserver). **Diventa socio itself is not edited.** Pages in scope include Home (hero, banners, story cards, stats), Chi siamo, Contatti, Donazioni listing and 5 x 1000 card, campaign form card, Notizie/Articoli listing cards, article body, legal/privacy/cookie/trasparenza, FAQ/default, volunteer form panel. Reduced motion shows blocks in place with no slide ([prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion)).

**Why this priority**: Owner asked to reuse the membership card motion everywhere except that membership page.

**Independent Test**: Open Chi siamo and Home with motion allowed: blocks slide in as they enter the viewport. Open Diventa socio: layout and motion files for that template are untouched. Enable reduced motion: no slide.

**Acceptance Scenarios**:

1. **Given** Chi siamo with motion allowed, **When** the intro and values blocks enter the viewport, **Then** they use the same reveal as Servizi cards.
2. **Given** Home, **When** stats and story cards enter, **Then** they reveal the same way.
3. **Given** Diventa socio, **When** this feature ships, **Then** `resources/views/pages/templates/landing.blade.php` is not modified.
4. **Given** `prefers-reduced-motion: reduce`, **When** any of those pages load, **Then** blocks are visible immediately with no translation.

---

### User Story 5 - Notizie and Articoli names (Priority: P1)

The news listing page is named **Notizie** (EN **News**). The editorial listing is **Articoli** (EN **Articles**). Altre Pagine on desktop and the matching drawer group use those names, not **Tutte le notizie** / **Tutti gli articoli**. Home story-slider buttons keep the current All labels (`Tutte le notizie` / `Tutti gli articoli` / `All news` / `All articles`).

**Why this priority**: Owner separated page names from Home CTAs.

**Independent Test**: Open `/it/news` (or the news index route): H1 and menu entry are Notizie. Home buttons still say Tutte le notizie.

**Acceptance Scenarios**:

1. **Given** the Italian news listing, **When** it opens, **Then** the heading is **Notizie** (already the page title key) and Altre Pagine / drawer use **Notizie**, not Tutte le notizie.
2. **Given** the Italian articles listing, **When** it opens, **Then** the heading and menu entry are **Articoli**.
3. **Given** English listings, **When** they open, **Then** the names are **News** and **Articles**, not All news / All articles.
4. **Given** Home with stories, **When** the slider buttons are in view, **Then** they still say Tutte le notizie / Tutti gli articoli (EN All news / All articles).

---

### User Story 6 - CRM counters count up from zero (Priority: P1)

On Home, the three impact figures from CRM (meals, interventions, partners) start at 0 and ease to the live value in about two seconds, so the change is visible. Em dash fallback (`—`) when CRM is missing does not animate. Reduced motion shows the final value immediately. Server HTML still contains the formatted final value so no-JS and PHPUnit keep seeing it.

**Why this priority**: Owner asked for a visible count-up of a couple of seconds.

**Independent Test**: Load Home with CRM numbers. Figures run from 0 to the current totals in ~2s. Disable CRM: dashes stay. Reduced motion: final number from the first paint.

**Acceptance Scenarios**:

1. **Given** Home with CRM totals and motion allowed, **When** the stats are in view, **Then** each numeric counter animates from 0 to its value over about two seconds ([requestAnimationFrame](https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame)).
2. **Given** a missing CRM value shown as `—`, **When** the page loads, **Then** that card does not count.
3. **Given** `prefers-reduced-motion: reduce`, **When** Home loads, **Then** the formatted final values show at once.
4. **Given** PHPUnit Home CRM tests, **When** they run, **Then** they still see the formatted totals in the HTML (e.g. `3.149`).

---

### User Story 7 - Light red tint on the 5 x 1000 banner (Priority: P2)

The sticky 5 x 1000 strip gets a **very light** red wash using the brand primary, without changing type size, copy, or the copy-tax-code control. **This story is implemented only after a git commit of US1–US6**, so the owner can revert the tint in isolation if it is not okay.

**Why this priority**: Owner asked for the tint last, behind a commit, because it may be rejected.

**Independent Test**: After the pre-tint commit, add the wash. Compare Home before/after. If rejected, revert only that commit.

**Acceptance Scenarios**:

1. **Given** US1–US6 are committed, **When** this story is implemented, **Then** `.site-five` has a light red tint (on the order of ~8% brand red mixed into the existing strip), still readable in light and dark theme.
2. **Given** the 5 x 1000 page, **When** the strip is in view, **Then** copy and the copy-code button still work.
3. **Given** the owner rejects the tint, **When** they revert that commit, **Then** US1–US6 remain.

---

### Edge Cases

- Desktop nav must not gain Home even if the drawer link is implemented as a shared partial.
- `og:title` does not pick up the ETS suffix.
- Counters with thousands separators (Italian `3.149`) animate as integers and re-apply locale grouping at each frame.
- Diventa socio English/Italian card split from earlier work stays as shipped.
- Banner tint must remain subtle; not a solid red bar.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Mobile drawer panel title is **Safe House ETS**. Hamburger `aria-label` stays Menu.
- **FR-002**: Drawer includes Home → locale home. Desktop `navigation.header` does not include Home.
- **FR-003**: `site.layout.title_suffix` is `— Safe House ETS` in IT and EN. Discovery `<title>` uses it. `og:title` stays the page name.
- **FR-004**: `page-hero` default alignment is vertical center for the tagline on all prominent headings.
- **FR-005**: Glass/card blocks on in-scope pages use existing `.landing-reveal` motion. `landing.blade.php` is not edited.
- **FR-006**: Altre Pagine and drawer news/editorial labels use `site.pages.news_title` / `site.pages.editorial_title` (or the equivalent `site.nav.news` / `site.nav.editorial`). Home latest-stories buttons keep `news_all` / `editorial_all`.
- **FR-007**: Numeric Home impact stats count from 0 to the CRM value in ~2 seconds. `—` does not count. Reduced motion and no-JS show the final formatted value.
- **FR-008**: Banner tint is a separate last commit after US1–US6.
- **FR-009**: Do not edit Diventa socio. Do not change Stripe Payment Element. No CRM schema writes.

### Key Entities

- **Drawer chrome**: title + Home link, phone-only.
- **Document title suffix**: shared IT/EN legal name.
- **Impact counter**: integer CRM total with locale grouping.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Phone drawer shows Safe House ETS and Home; desktop header item count for the main nav is unchanged.
- **SC-002**: A sample of public `<title>` values end with `— Safe House ETS`.
- **SC-003**: On a 1280px window, taglines sit vertically centered next to titles on Chi siamo, Servizi, Volunteer, and Donazioni.
- **SC-004**: Chi siamo blocks reveal on scroll; Diventa socio template file is untouched.
- **SC-005**: Altre Pagine says Notizie / Articoli; Home buttons still say Tutte le notizie / Tutti gli articoli.
- **SC-006**: With CRM numbers, a visitor can see the digits change for about two seconds.
- **SC-007**: Banner tint can be removed with a single git revert without undoing US1–US6.

## Assumptions

- Home label stays the word **Home** in both locales (already in `site.nav.home`).
- ETS stays in the English title suffix (legal name, not translated).
- News listing H1 already uses `news_title`; this spec mainly fixes menu labels that still say “all”.
- Count-up duration is 2000ms ease-out, not a CMS setting.
- Banner mix is about 8% brand `#dc2626` into the existing strip; owner UAT decides if it is too strong.
- Cursor-browser is not driven unless the owner agrees that turn.
