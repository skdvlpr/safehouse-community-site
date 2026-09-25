# Feature Specification: Articles heading

**Feature Branch**: `018-articles-heading`

**Created**: 2026-09-25

**Status**: Draft. Specified only. Implement one spec at a time and stop for owner testing. Diventa socio is out of scope.

**Input**: Owner 2026-09-25. Articoli uses the same listing template as Notizie. Only the words change: Articoli | Approfondimenti, testimonianze e contenuti editoriali. The red label above the title is removed. The filter bar and list cards match Notizie, including the category name on the right edge of each list card.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Page matches the agreed heading and layout (Priority: P1)

A visitor on the Italian page, and the English page when it exists, sees the heading treatment described above and no red label above the title. Layout changes in the input are visible on a wide screen and still readable on a phone.

**Why this priority**: This is the whole change for this page.

**Independent Test**: Open the page on a wide screen and on a phone. Compare the heading with Chi siamo. Confirm Diventa socio is unchanged.

**Acceptance Scenarios**:

1. **Given** the page, **When** it opens on a wide screen, **Then** the heading matches the wording in the input and the red label above the title is gone.
2. **Given** a phone-width screen, **When** the page opens, **Then** the heading and any two-column layout stack or shrink without a horizontal page scroll.
3. **Given** Diventa socio, **When** this feature ships, **Then** that page is unchanged.

### Edge Cases

- English uses the same structure with an English tagline of the same meaning.
- Sentences the owner did not mention stay on the page.
- Reduced motion does not hide the heading.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The page MUST use the Chi siamo heading pattern: large title, a vertical bar, then the tagline, with no red label above the title.
- **FR-002**: The page MUST keep every sentence the owner did not ask to replace.
- **FR-003**: Any layout change in the input MUST apply on a wide screen and MUST remain usable on a phone.
- **FR-004**: Diventa socio MUST NOT change.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A visitor can read the new heading on the first screen without seeing a red label above it.
- **SC-002**: On a phone, the page does not scroll sideways.

## Assumptions

- Italian wording in the owner note is the source text. English is the same meaning.
- This spec is one page only. The next page waits until the owner tests this one.
