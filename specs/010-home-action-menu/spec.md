# Feature Specification: Home first screen and shorter menu

**Feature Branch**: `010-home-action-menu`

**Created**: 2026-09-24

**Status**: Draft. Specified only. Do not implement until the owner asks, and not before `003` is on production and checked.

**Queue**: Owner follow-up after `003-public-seo-foundation`. Does not start `004-ad-grants-landings` and does not include the president video.

**Input**: Owner 2026-09-24: the public site should make the next action obvious. Do not replace the home address with the membership page. Put a short mix of home and membership on the first screen. Shorten the header. Video of the president's speech is a later decision, after this page order is accepted.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - First screen shows who we are and what to do (Priority: P1)

A new visitor on the home page understands, without scrolling, that this is Safe House and sees one primary action, Become a member, plus Donate and Become a volunteer.

**Why this priority**: Ads and search will send people to the home address. That address must orient them.

**Independent Test**: Open the home page in Italian and English on a desktop width and a phone width. The membership button, donate, and volunteer are visible before any long text.

**Acceptance Scenarios**:

1. **Given** the home page, **When** it loads, **Then** the address stays the home address, not the membership page.
2. **Given** the first screen, **When** the visitor looks for an action, **Then** Become a member is the primary button and Donate and Become a volunteer are secondary.
3. **Given** the visitor chooses Become a member, **When** the full form and statute are needed, **Then** those stay on the existing membership page.

---

### User Story 2 - Header lists the real choices (Priority: P1)

The header no longer lists nine competing items. Membership and volunteering are in the header. News and articles move out of that row. Donate stays a button.

**Why this priority**: The current header hides the two actions the owner wants people to find.

**Independent Test**: On any public page, read the header in Italian and English. It shows Who we are, Services, Become a member, Volunteering, Contact, and a Donate button. The logo goes home. There is no separate Home item.

**Acceptance Scenarios**:

1. **Given** any public page, **When** the visitor uses the header, **Then** they can open Who we are, Services, Become a member, Volunteering, and Contact.
2. **Given** the header, **When** the visitor looks for money, **Then** Donate is a button, and 5 per mille is reached from the donations page and the footer, not as its own header item.
3. **Given** news and articles, **When** the visitor looks in the header, **Then** they are under Other pages, not in the main row.

---

### Edge Cases

- The membership page URL does not change. Ads and the future sitelink keep that address.
- 5 per mille, news, and articles remain published. They move in the menu. They are not deleted.
- The president video is not placed in this feature. Its slot, if accepted later, is directly under the first screen. Source and autoplay are undecided.
- Italian and English both get the same menu order. There is no public Russian.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The home address MUST remain the home page.
- **FR-002**: The membership page MUST remain its own address with the existing application form.
- **FR-003**: The home first screen MUST state who the association is and offer Become a member as the primary action, with Donate and Become a volunteer beside it.
- **FR-004**: The header MUST list Who we are, Services, Become a member, Volunteering, and Contact, plus a Donate button.
- **FR-005**: The logo MUST open the home page. The header MUST NOT also contain a Home item.
- **FR-006**: 5 per mille MUST stay available from donations and the footer, not as a top-level header item.
- **FR-007**: News and articles MUST remain available under Other pages.
- **FR-008**: This feature MUST NOT add or embed the president video.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A first-time visitor can name the primary action on the home page within a few seconds, without scrolling, on phone and desktop.
- **SC-002**: Membership, volunteering, and donate are each one header action away from any public page.
- **SC-003**: The membership form still opens on the membership page, and the home address is unchanged.

## Assumptions

- Visible copy of the membership page stays. This feature rearranges entry points.
- `004` sitelink work waits until the owner has checked production after `003`.
- Video storage and playback are out of scope until the owner chooses them.
