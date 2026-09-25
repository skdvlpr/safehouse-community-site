# Feature Specification: Donation form width and one-time heading

**Feature Branch**: `019.1-donation-form-heading`

**Created**: 2026-09-25

**Updated**: 2026-09-25

**Status**: Draft. UAT repair of `019-donations-row`. Finish this amendment before `022`. Diventa socio is out of scope. Thank-you and payment-privacy are out of scope. Payment collection itself does not change.

**Input**: Owner 2026-09-25 UAT of the recurring payment page. The payment form card is too wide after the heading moved outside it; restore the earlier **narrow** card. One-time campaign heading is **not** `Donazione | campaign_name`. It is **campaign_name |** a short tagline, same pattern as other public pages. Recurring heading stays **Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente.** On every donations heading changed in `019` / this repair, the line after `|` is vertically centered with the title.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Narrow payment form card (Priority: P1)

A visitor on a one-time or recurring campaign payment page still sees the Chi siamo heading at full content width. The Stripe form card below it is a **narrow centered block** (`max-w-2xl`, 42rem / 672px), not stretched to the full content column. Listing cards on Donazioni stay full width.

**Why this priority**: Owner UAT called the stretched form the first problem.

**Independent Test**: Open a campaign payment page on a wide window. The heading uses the full content column. The form card is a centered column about 672px wide. Open Donazioni listing: featured cards stay wide.

**Acceptance Scenarios**:

1. **Given** a wide window on a one-time campaign payment page, **When** the form is in view, **Then** `#donation-form` is centered and capped at Tailwind `max-w-2xl` ([max-width](https://tailwindcss.com/docs/max-width)).
2. **Given** a wide window on the recurring campaign payment page, **When** the form is in view, **Then** the same narrow cap applies.
3. **Given** a phone-width window, **When** either payment page opens, **Then** the form uses the available width and the page does not scroll sideways.
4. **Given** Donazioni listing, **When** this repair ships, **Then** the featured 5 x 1000 and bank-transfer cards stay full listing width.

---

### User Story 2 - One-time heading is campaign name then a short tagline (Priority: P1)

A visitor on any one-time campaign payment page sees the **campaign name** as the large title, a vertical bar, then a short tagline: Italian **Sostieni Safe House con un dono.** / English **Support Safe House with a gift.** The heading sits outside the form. Recurring pages keep the heading already accepted in `019`.

**Why this priority**: Owner reversed the `019` one-time title/tagline order after seeing it live.

**Independent Test**: Open Dona a Safe House. Title is the campaign name. Tagline is the short gift line. Recurring page still shows Donazione ricorrente | monthly tagline.

**Acceptance Scenarios**:

1. **Given** an Italian one-time campaign named **Dona a Safe House**, **When** the payment page opens, **Then** the heading is **Dona a Safe House | Sostieni Safe House con un dono.**
2. **Given** an English one-time campaign named **Donate to Safe House**, **When** the payment page opens, **Then** the heading is **Donate to Safe House | Support Safe House with a gift.**
3. **Given** the Italian recurring payment page, **When** it opens, **Then** the heading remains **Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente.**
4. **Given** any one-time campaign, **When** the form card is in view, **Then** it does not repeat the large page title, and payment can still be completed as today.

---

### User Story 3 - Tagline vertically centered on donation headings (Priority: P1)

On Donazioni listing, 5 x 1000, one-time campaign, and recurring campaign, the copy to the right of `|` is vertically centered with the large title on wide screens ([align-items: center](https://tailwindcss.com/docs/align-items)). Contact and news listings already do this; donation pages must match.

**Why this priority**: Owner asked for that optical alignment on every donations page changed in this family.

**Independent Test**: Open those four donation screens on a wide window. The tagline sits mid-height next to the title, not dropped to the baseline.

**Acceptance Scenarios**:

1. **Given** Donazioni listing, 5 x 1000, one-time show, or recurring show, **When** the heading is in view on a wide window, **Then** the headline uses the existing `page-hero__headline--center` treatment (`align` center on `page-header`).
2. **Given** Diventa socio, **When** this repair ships, **Then** that page is unchanged.

---

### Edge Cases

- Long campaign names wrap on a phone; the form stays full phone width.
- Recurring interrupt sentence stays stripped from body (`019` US4). This repair does not put it back.
- Unused `site.donations.campaign_heading` (`Donazione` / `Donation`) may remain in lang files; it is no longer the one-time H1.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: `#donation-form` on campaign `show` is `w-full max-w-2xl mx-auto`. Listing and 5 x 1000 cards do not receive that cap.
- **FR-002**: One-time `show` heading title is the localized campaign title. Tagline is `site.donations.campaign_tagline`.
- **FR-003**: Recurring `show` heading stays `recurring_heading` | `recurring_tagline`.
- **FR-004**: Donation listing, 5 x 1000, and both `show` variants pass `align => 'center'` into `pages.partials.page-header`.
- **FR-005**: Stripe Payment Element wiring does not change ([Payment Element](https://docs.stripe.com/payments/payment-element)).
- **FR-006**: Diventa socio, thank-you, and payment-privacy are not edited.

### Key Entities

- **One-time campaign heading**: campaign title + `campaign_tagline`.
- **Payment form card**: existing `#donation-form`, now width-capped.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On a 1280px-wide window the payment card is visually a centered column, not full listing width.
- **SC-002**: One-time Italian heading reads `{campaign} | Sostieni Safe House con un dono.`
- **SC-003**: Recurring Italian heading is unchanged from the accepted `019` line.
- **SC-004**: Donation page-hero headlines include `page-hero__headline--center`.

## Assumptions

- Short one-time tagline is the parallel of the recurring tagline, not a second campaign-name field.
- `max-w-2xl` (672px) is the restored “narrow card”; listing stays the wide exception.
- Global vertical-center for **all** site pages is **out of scope** here; that is the next spec (`022`).
