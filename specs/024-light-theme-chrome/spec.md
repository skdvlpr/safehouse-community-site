# Feature Specification: Light-theme chrome, 5×1000 mark, slower Home count

**Feature Branch**: `024-light-theme-chrome`

**Created**: 2026-09-25

**Status**: Draft. Owner-UAT repair after `023`. Light theme currently drops the red outlines on two Chi siamo blocks, hides the 5× inscription on 5×1000 banner hover, and paints the Notizie/Articoli **Categorie** control unlike the date fields. Owner also asked to slow the Home impact count a little after this chrome work.

**Input**: Owner 2026-09-25 screenshots (Chi siamo dark vs light closing/values; 5×1000 rest vs hover on light; news toolbar Categorie vs dates) plus follow-up: make Categorie match the other filter fields on light, then make the Home counter a bit slower.

017.1 phone filter stack stays as already implemented. Diventa socio is out of scope. No CRM change.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Chi siamo red outlines stay on light theme (Priority: P1)

A visitor on Chi siamo in **Aurora Light** still sees a clear red outline on the two blocks that already have it in dark: the values panel (right) and the closing quote. Those outlines must not collapse to the same grey glass border as intro/header/footer.

**Why this priority**: Owner’s first screenshot pair — the red outline disappears on light.

**Independent Test**: Open `/it/about-us`, switch to light. Values panel and closing quote show a red (primary) outline. Intro glass stays the ordinary light glass border.

**Acceptance Scenarios**:

1. **Given** Chi siamo in dark theme, **When** values and closing are in view, **Then** they keep a red/primary outline as today.
2. **Given** Chi siamo in light theme, **When** values and closing are in view, **Then** they still have a red/primary outline, not the generic grey glass border.
3. **Given** the intro article on the same page, **When** light theme is on, **Then** it keeps the ordinary glass border (not forced red).

---

### User Story 2 - 5×1000 mark is a red-outlined rectangle; hover keeps “5×” (Priority: P1)

A visitor sees the 5×1000 strip logo as a **rectangle** (not a filled circle), **transparent** fill, **red outline**, with the same **5×** letters. Hovering the banner changes the outline/fill in a way that is visible on **light** as well as dark. The **5×** letters stay readable on hover (they must not vanish into the fill).

**Why this priority**: Owner: light hover highlight is weak; the 5× inscription disappears; make the logo rectangular, keep 5×, transparent with red outline.

**Independent Test**: Home (or any page with the strip) in light: rest state shows outlined rectangle + 5×. Hover: visible change, 5× still readable. Dark still works.

**Acceptance Scenarios**:

1. **Given** the 5×1000 strip at rest, **When** the mark is in view, **Then** it is rectangular, transparent, red-outlined, and shows **5×**.
2. **Given** light theme, **When** the visitor hovers the banner link, **Then** the highlight is clearly stronger than a faint grey wash, and **5×** remains readable.
3. **Given** dark theme, **When** the visitor hovers the same link, **Then** the mark still shows **5×** and the hover is visible.
4. **Given** reduced motion, **When** the strip is shown, **Then** rest geometry is the same (hover may still recolor).

---

### User Story 3 - News Categorie matches other filter fields on light (Priority: P1)

A visitor on Notizie or Articoli in light theme sees the **Categorie** control with the same surface as the date inputs: same height, same light fill, same border, not a greyer/washed box.

**Why this priority**: Owner screenshot of the light toolbar plus explicit add-on: categories differ from the other fields on light — make them the same.

**Independent Test**: Open `/it/news` and `/it/articles` in light. Categorie, both dates, and the apply control sit on matching light fields. Dark toolbar is unchanged in intent.

**Acceptance Scenarios**:

1. **Given** Notizie in light theme, **When** the filter bar is in view, **Then** Categorie uses the same light fill and border as the date inputs.
2. **Given** Articoli in light theme (including empty categories), **When** the bar is in view, **Then** the same holds.
3. **Given** dark theme, **When** the bar is in view, **Then** Categorie and dates still share the dark glass control look.

---

### User Story 4 - Home impact count is a bit slower (Priority: P2)

A visitor on Home with motion allowed sees the three CRM numbers count up from 0 a little more slowly than the current ~two-second run, so the animation is easier to watch. Reduced-motion visitors still see the final number immediately.

**Why this priority**: Owner asked for this after the chrome correction in the same turn.

**Independent Test**: Open `/it` with motion allowed; the count takes noticeably longer than two seconds, still finishes, still starts from 0.

**Acceptance Scenarios**:

1. **Given** Home with motion allowed and CRM totals present, **When** the stats are on screen, **Then** numbers ease from 0 to the final value over a duration clearly longer than two seconds (about three and a half seconds).
2. **Given** `prefers-reduced-motion: reduce`, **When** Home loads, **Then** the formatted final value is shown with no count animation.

---

### Edge Cases

- Light theme must not restyle Diventa socio cards, donation Payment Element, or legal pages as part of this work.
- 5×1000 strip is hidden on the dedicated 5×1000 page as today; other pages keep the strip.
- English news labels (Categories / Date / Apply) use the same matching surfaces.
- Phone filter stack from `017.1` stays: no sideways clip of **Data** / **Applica**.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: On Aurora Light, Chi siamo values panel and closing quote MUST keep a red/primary outline. The light-theme glass-border remap MUST NOT overwrite those two borders.
- **FR-002**: The 5×1000 strip mark MUST be rectangular, transparent, red-outlined, and contain the **5×** text.
- **FR-003**: Hovering the 5×1000 banner link MUST change fill or outline visibly on light and dark, and MUST keep **5×** readable (no red-on-red text).
- **FR-004**: On Aurora Light, the news/articles **Categorie** summary MUST use the same fill, text color, and border as the date inputs.
- **FR-005**: Home impact count duration MUST be longer than the current 2000 ms (target ~3500 ms). Reduced motion still skips the animation ([prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion)).
- **FR-006**: `017.1` phone toolbar stack MUST remain. Diventa socio MUST NOT change. Stripe Payment Element MUST NOT change.

### Key Entities

- **Chi siamo values panel**: `.template-about-values`
- **Chi siamo closing quote**: `.template-about-closing`
- **5×1000 strip mark**: `.site-five__mark` inside `.site-five__link`
- **News Categorie summary**: `.news-cat-menu__summary`
- **Home impact counters**: `[data-count-to]` via `impact-count.js`

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On light Chi siamo, a visitor can see a red outline on values and closing, matching the dark-theme intent.
- **SC-002**: On light Home, hovering the 5×1000 strip does not hide **5×**; the hover is stronger than the current faint wash.
- **SC-003**: On light Notizie, Categorie is visually the same family as the date fields (fill + border).
- **SC-004**: Home count-up with motion allowed takes about 3.5 seconds from 0 to the CRM total.

## Assumptions

- The two Chi siamo elements in the owner screenshots are the values panel and the closing quote (not the intro).
- “A bit slower” means ~3500 ms, not a new easing curve.
- 017.1 CSS already in the tree stays; this spec does not revert it.
- No copy change. No new translation keys.
