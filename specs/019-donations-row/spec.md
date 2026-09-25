# Feature Specification: Donations heading family

**Feature Branch**: `019-donations-row`

**Created**: 2026-09-25

**Updated**: 2026-09-25

**Status**: Draft. Specified for this implement run. Owner accepted `021-mobile-header-drawer` UAT the same day. Stop after this spec for owner testing. Diventa socio is out of scope. Thank-you and payment-privacy pages are out of scope. Payment collection itself does not change.

**Input**: Owner 2026-09-25. Donazioni listing uses the Chi siamo heading: **Donazioni | Sostieni Safe House. 5 x 1000 o pagamenti digitali.** On a wide screen the 5 x 1000 block and the bank-transfer block sit on one row. On a narrow screen they stay stacked. The red label above the title is removed. Same day, after 021 UAT: bring the 5 x 1000 page and every campaign page that holds the payment form to the same heading treatment as the earlier public pages, with the title **outside** the glass or form card. Any one-time campaign page heading is **Donazione |** then the campaign name (example: Dona a Safe House). Recurring campaign pages heading is **Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente.** The body sentence *Puoi interrompere in qualsiasi momento tramite il portale Stripe dedicato ai donatori.* is redundant because the red cancel panel already explains cancellation.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Donazioni listing heading and two-up blocks (Priority: P1)

A visitor on the donations listing sees the Chi siamo heading: large title **Donazioni**, a vertical bar, then the tagline **Sostieni Safe House. 5 x 1000 o pagamenti digitali.** There is no red label above the title. On a wide screen the 5 x 1000 card and the bank-transfer card sit side by side. On a phone they stack. Online campaign cards below stay as they are.

**Why this priority**: This is the section home. The owner asked for this heading and this row first.

**Independent Test**: Open `/it/donations` and `/en/donations` on a wide window and on a phone. Compare the heading with Chi siamo. Confirm the two featured cards sit on one row when wide.

**Acceptance Scenarios**:

1. **Given** Italian Donazioni, **When** the listing opens, **Then** the heading is **Donazioni | Sostieni Safe House. 5 x 1000 o pagamenti digitali.** and no red label sits above the title.
2. **Given** English Donations, **When** the listing opens, **Then** the heading is **Donations | Support Safe House. 5 x 1000 or digital payments.**
3. **Given** a wide window with both 5 x 1000 and bank transfer enabled, **When** the listing is in view, **Then** those two cards sit on one row.
4. **Given** a phone-width window, **When** the listing opens, **Then** those two cards stack and the page does not scroll sideways.
5. **Given** Diventa socio, **When** this feature ships, **Then** that page is unchanged.

---

### User Story 2 - 5 x 1000 page heading sits outside the card (Priority: P1)

A visitor on the 5 x 1000 page sees the same heading pattern as Chi siamo, **outside** the glass card. The large title is the page heading already used on that page (example: **Dona 5 x 1000**). The line after the bar is that page’s lead sentence. The red uppercase label above the old in-card title is gone. Body copy, tax-code panel, and instructions stay inside the card.

**Why this priority**: The owner asked to bring this page to the same look as the pages already shipped, and to pull the heading out of the card.

**Independent Test**: Open the 5 x 1000 page in Italian and English. The title-and-tagline row is above the glass. The card still holds the long copy and the tax code.

**Acceptance Scenarios**:

1. **Given** the Italian 5 x 1000 page, **When** it opens, **Then** the visitor sees a Chi siamo-style heading outside the glass, using that page’s heading as the title and that page’s lead as the tagline, with no red label above the title.
2. **Given** the English 5 x 1000 page, **When** it opens, **Then** the same structure uses the English heading and lead.
3. **Given** that page, **When** the glass card is in view, **Then** it does not repeat the large title, and it still shows the body, tax-code block, and instructions that were already there.
4. **Given** a phone-width window, **When** the page opens, **Then** the heading may wrap and the page does not scroll sideways.

---

### User Story 3 - One-time campaign payment page heading (Priority: P1)

A visitor on any one-time campaign payment page sees **Donazione** as the large title, a vertical bar, then the campaign name as the tagline (example: **Dona a Safe House**). That heading sits **outside** the payment-form card. The form card still holds the campaign story, progress if any, donor fields, and the payment widget. Payment steps do not change.

**Why this priority**: The owner asked every donation page with the payment form to match the earlier pages and to name the collection after the bar.

**Independent Test**: Open a one-time campaign such as Dona a Safe House. Heading is outside the form. The form still collects a gift.

**Acceptance Scenarios**:

1. **Given** an Italian one-time campaign named **Dona a Safe House**, **When** the payment page opens, **Then** the heading is **Donazione | Dona a Safe House** and sits outside the form card.
2. **Given** an English one-time campaign named **Donate to Safe House**, **When** the payment page opens, **Then** the heading is **Donation | Donate to Safe House**.
3. **Given** any other one-time campaign, **When** its payment page opens, **Then** the title is still **Donazione** / **Donation** and the tagline is that campaign’s own name.
4. **Given** that page, **When** the form card is in view, **Then** it does not repeat the large page title, and a visitor can still complete a payment as today.
5. **Given** Diventa socio, **When** this feature ships, **Then** that page is unchanged.

---

### User Story 4 - Recurring campaign heading and duplicate cancel copy (Priority: P1)

A visitor on a recurring campaign payment page sees **Donazione ricorrente** as the large title, a vertical bar, then **Sostieni Safe House ogni mese con un contributo ricorrente.** The heading sits outside the form card. The red monthly label that used to sit above the in-card title is gone. The sentence about stopping anytime through the dedicated donor portal does **not** appear in the campaign body, because the red cancel panel already explains cancellation. The tagline is not repeated as body copy. The red cancel panel, the acknowledgement checkbox, and the payment flow stay.

**Why this priority**: The owner gave this exact heading and called the portal sentence redundant with the red panel.

**Independent Test**: Open the recurring campaign page in Italian and English. Heading matches. Body does not repeat the tagline or the portal-interrupt sentence. The red cancel panel is still there.

**Acceptance Scenarios**:

1. **Given** the Italian recurring payment page, **When** it opens, **Then** the heading is **Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente.** and sits outside the form card.
2. **Given** the English recurring payment page, **When** it opens, **Then** the heading is **Recurring donation | Support Safe House every month with a recurring contribution.**
3. **Given** that page, **When** the visitor reads the campaign body under the heading, **Then** they do not see *Puoi interrompere in qualsiasi momento tramite il portale Stripe dedicato ai donatori.* or the English equivalent, and they do not see the tagline sentence duplicated as body copy.
4. **Given** that page, **When** the form card is in view, **Then** the red cancel panel, how-to-stop copy, portal link, and acknowledgement checkbox remain, and a visitor can still start a monthly payment as today.
5. **Given** a phone-width window, **When** the page opens, **Then** the heading may wrap and the page does not scroll sideways.

### Edge Cases

- English uses the same structure with the English lines in this spec.
- If a 5 x 1000 lead is empty, the heading still shows the title; the bar and tagline may be omitted.
- If a one-time campaign has no name, the page is not invented; the existing campaign name is required for the tagline.
- If stripping the redundant portal sentence and the duplicated tagline leaves the recurring campaign body empty, the empty body is omitted rather than shown as a blank paragraph.
- Thank-you and payment-privacy pages stay as they are.
- Reduced motion does not hide the heading.
- Payment collection, amounts, and donor fields do not change in this spec.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The Donazioni listing MUST use the Chi siamo heading pattern: large title **Donazioni** (EN **Donations**), a vertical bar, then the tagline **Sostieni Safe House. 5 x 1000 o pagamenti digitali.** (EN **Support Safe House. 5 x 1000 or digital payments.**), with no red label above the title.
- **FR-002**: On a wide Donazioni listing, the 5 x 1000 card and the bank-transfer card MUST sit on one row when both are shown. On a phone they MUST stack. The page MUST NOT scroll sideways.
- **FR-003**: The 5 x 1000 page MUST use the Chi siamo heading pattern **outside** the glass card. The large title MUST be that page’s heading. The tagline MUST be that page’s lead. The red uppercase label above the old in-card title MUST be gone. Body, tax-code panel, and instructions MUST stay in the card and MUST NOT repeat the large title.
- **FR-004**: Every one-time campaign payment page MUST use the Chi siamo heading pattern **outside** the form card. Italian title **Donazione**. English title **Donation**. Tagline MUST be the campaign name. The form card MUST NOT repeat that large title. Payment collection MUST keep working as today.
- **FR-005**: Every recurring campaign payment page MUST use the Chi siamo heading pattern **outside** the form card with Italian **Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente.** and English **Recurring donation | Support Safe House every month with a recurring contribution.**
- **FR-006**: Recurring campaign body copy MUST NOT include the portal-interrupt sentence (*Puoi interrompere in qualsiasi momento tramite il portale Stripe dedicato ai donatori.* and the English equivalent). It MUST NOT repeat the heading tagline. The red cancel panel and acknowledgement MUST remain.
- **FR-007**: The red monthly label that sat above the old in-card recurring title MUST NOT appear.
- **FR-008**: Diventa socio MUST NOT change.
- **FR-009**: Thank-you and payment-privacy pages MUST NOT change in this spec.
- **FR-010**: The listing MUST keep sentences and campaign cards the owner did not ask to replace, other than the heading and the two-up featured row.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On Donazioni, a visitor can read **Donazioni | Sostieni Safe House. 5 x 1000 o pagamenti digitali.** on the first screen with no red label above the title.
- **SC-002**: On a wide listing, the 5 x 1000 card and the bank-transfer card share one row; on a phone they stack and the page does not scroll sideways.
- **SC-003**: On 5 x 1000, the title-and-tagline row sits above the glass; the glass no longer holds the large title.
- **SC-004**: On a one-time campaign, a visitor reads **Donazione |** plus that campaign’s name above the form card and can still pay.
- **SC-005**: On the recurring campaign, a visitor reads the exact heading in User Story 4, does not see the portal-interrupt sentence outside the red cancel panel, and still sees that panel.
- **SC-006**: Diventa socio looks the same as before this spec.

## Assumptions

- Italian wording in the owner note is the source text. English is the same meaning.
- The 5 x 1000 title and tagline reuse the heading and lead already configured for that page, moved outside the card. No new 5 x 1000 slogan is invented.
- One-time campaign tagline is the campaign’s public name, not a second slogan.
- Recurring pages use the fixed heading in this spec even if the stored campaign name is the same as the title.
- The payment widget stays inside the form card. This spec does not change how a payment is taken.
- Thank-you still explains cancellation after a successful recurring payment; that is a different page.
- This family of donation pages is one spec. Stop for owner test before the next page spec.
