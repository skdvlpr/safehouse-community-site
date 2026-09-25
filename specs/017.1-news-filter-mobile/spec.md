# Feature Specification: News/articles filter bar on a phone

**Feature Branch**: `017.1-news-filter-mobile`

**Created**: 2026-09-25

**Status**: Draft. Owner-UAT repair of `017-news-heading` (same toolbar on Articoli from `018`). 017 already required the phone page not to scroll sideways. Production screenshots 2026-09-25 show the date row overflowing: the label **Data** is clipped to a stray **A**, and **Applica** is cut off on the right. Same on `/it/news` and `/it/articles`.

**Input**: Owner 2026-09-25, two phone screenshots of `safehouse.community/it/news` and `/it/articles`. Filter glass is unusable: controls stick out past the screen. Desktop bar stays as it is now.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Phone can use the filter bar without sideways scroll (Priority: P1)

A visitor on a phone opening Notizie or Articoli sees the category control, the two dates, **Applica**, and Feed/Elenco fully inside the glass. The word **Data** is fully visible. **Applica** is fully visible. The page does not scroll sideways because of the bar.

**Why this priority**: Owner sent the broken production screens. 017 SC-002 already required this.

**Independent Test**: Open `/it/news` and `/it/articles` at ~390px. Every control in the bar is fully on screen. Diventa socio unchanged.

**Acceptance Scenarios**:

1. **Given** a phone-width Notizie page, **When** the filter bar is in view, **Then** **Data** and **Applica** are fully readable and no control is clipped.
2. **Given** Articoli on the same width (including “Nessuna categoria al momento”), **When** the bar is in view, **Then** the same holds.
3. **Given** a wide window, **When** the bar is in view, **Then** the current one-row desktop layout remains.
4. **Given** Diventa socio, **When** this ships, **Then** that page is unchanged.

### Edge Cases

- Two native date fields on Android/Chrome stay inside the bar (`min-width` does not force a 200px+ control).
- English labels (Date / Apply) also fit.
- Reduced motion: no change to this layout.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: On a phone, the shared listing toolbar (`listing-toolbar`) MUST keep every control inside the viewport. MUST NOT clip **Data** / **Applica** (or English Date / Apply).
- **FR-002**: On a phone, the date group MUST stack or wrap: label above or on its own line; date fields and **Applica** fit; **Applica** is not pushed off-screen.
- **FR-003**: Desktop (`md` and up) keeps the current compact one-row bar.
- **FR-004**: Notizie and Articoli share the same toolbar. Both MUST be fixed. Diventa socio MUST NOT change.

### Key Entities

- **Listing toolbar**: category dropdown, date from/to, apply, feed/list toggle.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: At 390px width, a visitor can read **Data** and **Applica** in full on Notizie and Articoli.
- **SC-002**: The listing page does not scroll sideways because of the filter bar.

## Assumptions

- Parent spec 017 already asked for no sideways phone scroll; this amendment only repairs the date row that still overflows.
- No copy change. No new filters.
