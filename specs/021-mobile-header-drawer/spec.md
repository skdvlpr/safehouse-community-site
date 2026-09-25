# Feature Specification: Mobile header drawer

**Feature Branch**: `021-mobile-header-drawer`

**Created**: 2026-09-25

**Status**: Draft. Specified only. Implement one spec at a time and stop for owner testing. Donations heading (`019-donations-row`) waits until this ships and the owner tests it. Diventa socio is out of scope.

**Input**: Owner 2026-09-25. On a phone the page menu is gone because the donate label *Tutti i modi per donare* fills the header. Try a left-sliding menu with a hamburger in the header, animated, transparent with a strong blur. On a phone the donate button may use a short label such as *Dona ora*. Center the 5 x 1000 strip under the header menu on a wide screen, and center it on a phone. Shorten the Contatti line after the heading bar. Amendment same day: the drawer must have a visible close control; move the sportelli descriptions from that heading line into the left Contatti block that holds the FAQ button; stretch that left block so it does not end far above the form; tighten the form vertically; add a surname field and send it to the CRM last-name field. Further amendment: on the home phone news block, remove the extra buttons, enlarge the preview, and move between stories by swipe. Cookie policy table is full on a wide screen and scrolls only on a narrow screen.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Phone can open the site menu (Priority: P1)

A visitor on a phone sees a hamburger in the header at all times. Tapping it opens a panel that slides in from the left with a visible animation. The panel lists the same destinations as the wide-screen header menu. The panel is see-through with a strong blur over the page. The visitor can close it with a visible close control on the panel, the hamburger, a tap outside, Escape, or by following a link. The donate button stays visible on the phone and uses the short label. On a wide screen the existing horizontal menu and the long donate label stay as they are.

**Why this priority**: Without a visible menu a phone visitor cannot reach Chi siamo, Servizi, Notizie, or Contatti.

**Independent Test**: Open the home page at phone width. Confirm the hamburger is visible next to the short donate label. Open and close the left panel. Confirm a wide window still shows the row of links and the long donate label.

**Acceptance Scenarios**:

1. **Given** a phone-width window, **When** any public page loads, **Then** a hamburger control is visible in the header and is not covered by the donate button.
2. **Given** that hamburger, **When** the visitor opens it, **Then** a panel slides in from the left and lists the same destinations as the wide-screen header, including items that currently sit under Altre Pagine.
3. **Given** the open panel, **When** the visitor uses the visible close control, taps outside it, presses Escape, follows a link, or toggles the hamburger again, **Then** the panel closes.
4. **Given** a wide window, **When** the page loads, **Then** the horizontal header links remain and the phone drawer is not shown.
5. **Given** a visitor who prefers reduced motion, **When** the panel opens or closes, **Then** it still appears or disappears without a sliding animation.

---

### User Story 2 - Donate label fits the phone header (Priority: P1)

A visitor on a phone still sees a donate button in the header. The label is short so the hamburger, the logo, the language switch, and the button all fit on one row. On a wide screen the donate button keeps the current long label.

**Why this priority**: The long Italian donate sentence is what hid the menu. Shortening it on a phone is part of making the hamburger usable; the donate path must remain one tap away.

**Independent Test**: Compare the header donate label at phone width and at a wide width in Italian and in English.

**Acceptance Scenarios**:

1. **Given** Italian and a phone-width window, **When** the header loads, **Then** the donate button reads **Dona ora**.
2. **Given** English and a phone-width window, **When** the header loads, **Then** the donate button reads **Donate now**.
3. **Given** a wide window, **When** the header loads, **Then** the donate button still reads **Tutti i modi per donare** in Italian and **All ways to donate** in English.
4. **Given** that donate button, **When** the visitor taps it, **Then** they still reach the donations page.

---

### User Story 3 - 5 x 1000 strip is centered (Priority: P2)

A visitor sees the 5 x 1000 strip under the header. On a wide screen the strip content sits centered under the header navigation, not tucked under the logo. On a phone the strip content is centered in the viewport.

**Why this priority**: The owner asked for this alignment in the same pass as the menu, but a working phone menu is more urgent.

**Independent Test**: Open any public page on a wide window and on a phone. Check that the 5 x 1000 line is centered as described.

**Acceptance Scenarios**:

1. **Given** a wide window, **When** the sticky header and strip are in view, **Then** the 5 x 1000 line is centered under the header menu.
2. **Given** a phone-width window, **When** the strip is in view, **Then** the 5 x 1000 line is centered.
3. **Given** this change, **When** the strip still shows, **Then** copy-codice and the link to the 5 x 1000 page still work.

---

### User Story 4 - Contatti heading fits a phone (Priority: P2)

A visitor on Contatti still sees the title, the vertical bar, and a short line after the bar. The sportelli descriptions that used to sit in that heading line now sit in the left column, in the same block as the Domande frequenti button. The form still lets the visitor pick a desk. Diventa socio is unchanged.

**Why this priority**: The phone heading wrapped too far; the owner asked to keep the desk explanations next to the FAQ button instead of in the heading.

**Independent Test**: Open Contatti on a phone and on a wide window. Heading is the short sentence. Desk descriptions sit with the FAQ button. Form still has the desk control.

**Acceptance Scenarios**:

1. **Given** Italian Contatti, **When** the heading loads, **Then** the line after the bar is **Per contattarci compila il modulo e scegli lo sportello più adatto.**
2. **Given** English Contact, **When** the heading loads, **Then** the line after the bar is **Fill in the form and choose the most suitable desk.**
3. **Given** that page, **When** the left column is in view, **Then** it explains Sportello digitale, Sportello legale, and Richiesta generica in the block that also holds the FAQ button (English equivalent on `/en/contact`).
4. **Given** Diventa socio, **When** this feature ships, **Then** that page is unchanged.

---

### User Story 5 - Contatti columns and surname (Priority: P2)

A visitor on a wide Contatti screen sees the left information column stretch to the same height as the form column, instead of ending far above it. The form is a little tighter vertically. The form asks for first name and surname. Surname is stored and sent to EspoCRM Lead `lastName` (the same field membership and volunteer already use). First name stays Lead `firstName`. No CRM schema change.

**Why this priority**: Owner asked for the height, the tighter form, and a real surname mapped to CRM in the same Contatti pass.

**Independent Test**: Wide Contatti: left glass block matches form column height. Form shows Nome and Cognome. A test submission sends `lastName` to Lead create. Phone still stacks.

**Acceptance Scenarios**:

1. **Given** a wide Contatti layout, **When** both columns are in view, **Then** the left block reaches the same bottom edge as the form block.
2. **Given** the contact form, **When** it is compared with today, **Then** vertical gaps and field padding are tighter and the message box is shorter.
3. **Given** a complete submission with Nome **Luca** and Cognome **Bianchi**, **When** the site creates the CRM Lead, **Then** `firstName` is Luca and `lastName` is Bianchi.
4. **Given** a missing surname, **When** the visitor submits, **Then** the form does not store the message.

---

### User Story 6 - Home news on a phone (Priority: P2)

A visitor on the home page on a phone sees a larger news preview. The side arrows and the Tutte le notizie / Tutti gli articoli buttons are gone on that width. The visitor moves between stories with a horizontal swipe. Tapping a card still opens that story. On a wide screen the arrows and the two listing buttons stay.

**Why this priority**: The owner’s phone screenshot shows a cramped preview squeezed by arrows and two full-width buttons.

**Independent Test**: Phone home: no arrows, no listing buttons, a wider card, swipe changes the story. Wide home still has arrows and both listing links.

**Acceptance Scenarios**:

1. **Given** a phone-width home page with published stories, **When** the news block is in view, **Then** the preview uses the full content width and the side arrows are not shown.
2. **Given** that phone news block, **When** it is in view, **Then** the Tutte le notizie and Tutti gli articoli buttons are not shown.
3. **Given** more than one story, **When** the visitor swipes left or right on the preview, **Then** the next or previous story is shown.
4. **Given** a wide window, **When** home loads, **Then** the arrows and both listing buttons remain.

---

### User Story 7 - Cookie table (Priority: P2)

A visitor on the cookie policy page sees the cookie table in full on a wide screen. On a phone the same table scrolls sideways inside the page, without scrolling the whole site sideways.

**Why this priority**: Owner asked for a full table on wide and scroll only on a narrow screen.

**Independent Test**: Open cookie-policy on a wide window (all columns visible, no inner scroller) and on a phone (inner horizontal scroll).

**Acceptance Scenarios**:

1. **Given** a wide cookie-policy page, **When** the table is in view, **Then** every column is visible without a horizontal scroller on the table.
2. **Given** a phone-width cookie-policy page, **When** the table is wider than the screen, **Then** the visitor can scroll the table sideways and the rest of the page does not scroll sideways.

### Edge Cases

- The drawer must not sit under the sticky header or the 5 x 1000 strip.
- Opening the drawer must not scroll the page sideways.
- Language switch stays in the header on a phone; it is not required inside the drawer.
- English uses the same header structure and the English labels in this spec.
- Reduced motion does not hide the hamburger or the drawer, only the slide.
- `019-donations-row` is not started in this spec.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: On a phone-width screen the header MUST show a hamburger that a visitor can tap to open a left-sliding menu.
- **FR-002**: That menu MUST list the same destinations as the wide-screen header, including items currently grouped under Altre Pagine.
- **FR-003**: The sliding menu MUST be transparent with a strong blur over the page behind it, and MUST animate open and closed unless the visitor prefers reduced motion.
- **FR-004**: The visitor MUST be able to close the menu with a visible close control on the open panel, the hamburger, a tap outside, Escape, or by following a link.
- **FR-005**: On a phone the header donate button MUST use **Dona ora** (IT) and **Donate now** (EN). On a wide screen it MUST keep **Tutti i modi per donare** (IT) and **All ways to donate** (EN).
- **FR-006**: On a wide screen the horizontal header menu MUST remain; the sliding menu MUST NOT replace it.
- **FR-007**: The 5 x 1000 strip MUST be centered under the header menu on a wide screen and centered on a phone.
- **FR-008**: The Contatti heading line after the bar MUST be the shorter Italian and English sentences in User Story 4.
- **FR-009**: Diventa socio MUST NOT change.
- **FR-010**: The donations heading spec MUST remain untouched in this work.
- **FR-011**: The sportelli descriptions MUST appear in the left Contatti column in the block that holds the FAQ button, not in the heading line after the bar.
- **FR-012**: On a wide Contatti screen the left information column MUST stretch to the height of the form column.
- **FR-013**: The contact form MUST be tighter vertically than today (smaller field gaps, shorter message box) without hiding required fields.
- **FR-014**: The contact form MUST collect surname (`last_name` / Cognome). The site MUST send that value to EspoCRM Lead `lastName` and the first-name field to Lead `firstName`. CRM entity definitions MUST NOT be edited.
- **FR-015**: On a phone-width home page the news preview MUST be wider, MUST hide the side arrows and the two listing buttons, and MUST change story on a horizontal swipe. Wide home MUST keep arrows and listing buttons.
- **FR-016**: The cookie-policy table MUST display in full on a wide screen and MUST scroll horizontally only on a narrow screen.

### Key Entities

- **Header chrome**: Logo, hamburger or row menu, language switch, donate button, 5 x 1000 strip.
- **Phone drawer**: Left-sliding overlay listing public destinations, with a visible close control.
- **Contact submission**: First name, surname, email, desk, message; surname maps to Lead `lastName`.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On a phone a visitor can see the hamburger on the first screen without scrolling sideways and can open every header destination from the drawer.
- **SC-002**: On a phone the donate button and the hamburger are both visible in the header at the same time.
- **SC-003**: On a wide screen a visitor still sees the current long donate label and the row of links.
- **SC-004**: The Contatti line after the bar is one short sentence and does not list the three desks; those desk lines sit with the FAQ button.
- **SC-005**: On a wide Contatti screen the left column and the form column share one bottom edge.
- **SC-006**: A contact Lead in CRM has surname in `lastName`, not guessed from a single name string.
- **SC-007**: On a phone home, a visitor can swipe through news in a wide preview without the listing buttons.
- **SC-008**: On a wide cookie-policy page the table is fully visible; on a phone only the table scrolls sideways.

## Assumptions

- Owner chose the left drawer plus hamburger, not a dropdown under a Menu label.
- Short donate labels **Dona ora** / **Donate now** are the chosen phone wording. Long labels stay on a wide screen.
- Contatti still lists the desks in the form select. The long desk explanations move to the FAQ-button block.
- Surname maps to existing EspoCRM Lead `lastName`. No CRM write in the CRM repo.
- This spec interrupts the page-heading queue. After owner test, work returns to `019-donations-row`.
- Italian is primary. English is the same meaning. No public `/ru`.
- Feature tests may assert the Italian and English strings and that the hamburger markup is present. Drawer animation and strip centering are owner UAT.
