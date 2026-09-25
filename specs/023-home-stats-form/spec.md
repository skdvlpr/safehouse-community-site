# Feature Specification: Home stats order, side reveals, donation form columns

**Feature Branch**: `023-home-stats-form`

**Created**: 2026-09-25

**Status**: Draft. Specified for this implement run. Owner asked for specify → plan → tasks → implement in one pass. Diventa socio is out of scope. Stripe Payment Element wiring does not change.

**Input**: Owner 2026-09-25 after `022`. Working tree was already committed (`98a53f6`). Put the Home CRM numbers block **above the quote**, **between the first Home block (hero) and the quote**. Chi siamo and Donazioni block entrance must slide in **from opposite sides**, same as Diventa socio (`data-reveal-from` left/right, full viewport offset). Other public pages keep their current reveal. Donation payment forms (every campaign `show`, one-time and recurring) must stop needing a long scroll on desktop: make the **card** wider, but **do not stretch the fields** — pull the lower fields up into **several columns**. On a narrow screen the form stays the current stacked narrow card.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Home numbers sit between hero and quote (Priority: P1)

A visitor on Home sees: (1) the first glass hero block, (2) the three CRM number cards, (3) the manifesto quote. Stories and the independence banner stay below the quote. Count-up behaviour from `022` does not change.

**Why this priority**: Owner named this first and required it in the spec.

**Independent Test**: Open `/it` and `/en`. In the HTML and on screen, “Pasti distribuiti” (or EN equivalent) appears after the hero and before the quote “NESSUN ESSERE UMANO È ILLEGALE”.

**Acceptance Scenarios**:

1. **Given** Italian Home, **When** the page opens, **Then** the order is hero → impact stats → manifesto quote → latest stories → independence.
2. **Given** English Home, **When** the page opens, **Then** the same order holds with English labels.
3. **Given** CRM totals, **When** stats are in view with motion allowed, **Then** they still count from 0 as in `022`.

---

### User Story 2 - Chi siamo and Donazioni slide from opposite sides like socio (Priority: P1)

A visitor on Chi siamo sees the intro, values, and closing blocks enter from **left and right**, alternating, using the same off-screen slide as Diventa socio (not the short `up` rise). A visitor on Donazioni listing sees featured 5 x 1000, bank transfer, recurring, and online campaign cards do the same. The 5 x 1000 page glass and the campaign payment **form card** also enter from a side. Contatti, Home, news, legal, volunteer and other pages **keep** their current reveal. Diventa socio is not edited.

**Why this priority**: Owner said the previous pass did not give them the socio-style side slide on these two sections.

**Independent Test**: Open Chi siamo and Donazioni with motion allowed. Blocks start off-screen left or right and slide in. Open Contatti: still the previous reveal. Open Diventa socio: unchanged.

**Acceptance Scenarios**:

1. **Given** Chi siamo, **When** the intro, values, and closing blocks are in the markup, **Then** each uses `data-reveal-from="left"` or `"right"` (alternating), not `"up"`.
2. **Given** Donazioni listing, **When** featured, recurring, and campaign cards are in the markup, **Then** they alternate left/right the same way.
3. **Given** the 5 x 1000 page and a campaign payment page, **When** the main glass/form card is in the markup, **Then** it uses a side reveal (`left` or `right`).
4. **Given** Contatti / Home stats / volunteer, **When** this ships, **Then** those pages are not restyled to side-slide.
5. **Given** Diventa socio, **When** this ships, **Then** `landing.blade.php` is not modified.

---

### User Story 3 - Desktop donation form is wide via columns, not stretched fields (Priority: P1)

A visitor on a wide window opening any campaign payment page (one-time or recurring) sees a **wider form card**. Donor type, name, email, phone sit in one column; comment, amount presets, custom amount, notices, and acknowledgement sit in a second column, so the lower fields move **up** beside the earlier ones. Individual inputs keep a similar control width to today (they do not grow to the full content column). Stripe Payment Element and the pay button stay full-card width under the columns. On a phone the form stays the current **narrow stacked** card (`max-w-2xl`). Thank-you and payment-privacy are out of scope.

**Why this priority**: Owner asked to remove desktop scrolling by rearranging fields, not by stretching them.

**Independent Test**: Open a one-time and the recurring payment page at ~1280px: two field columns, card wider than `max-w-2xl`, inputs not full-bleed. At ~390px: stacked narrow form as today. Complete a mock payment still works.

**Acceptance Scenarios**:

1. **Given** a wide window on a one-time campaign `show`, **When** the form is in view, **Then** the card uses a desktop max width larger than `max-w-2xl` (Tailwind `max-w-5xl`) and a two-column field grid from `lg` ([grid-template-columns](https://tailwindcss.com/docs/grid-template-columns)).
2. **Given** the same page on a phone-width window, **When** the form is in view, **Then** fields stack in the current order and the card is capped at `max-w-2xl`.
3. **Given** the recurring campaign `show`, **When** the form is in view, **Then** the same column layout applies; the red cancel panel stays above the columns; Payment Element still mounts in `#payment-element`.
4. **Given** Diventa socio, **When** this ships, **Then** that membership form is unchanged.

---

### Edge Cases

- Home with missing CRM values still shows the three cards (em dash) between hero and quote.
- A Donazioni listing with only one featured card still side-slides that card.
- Recurring cancel checkbox stays in the amount column, not lost in the grid.
- Reduced motion: side slides are skipped as today (`landing-motion.js`).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Home content order is hero → impact stats → manifesto quote → latest stories → independence.
- **FR-002**: Chi siamo intro, values, and closing use alternating `left`/`right` reveals. Parent overflow clips like socio (`overflow: clip`).
- **FR-003**: Donazioni listing featured, recurring, and campaign cards use alternating `left`/`right`. 5 x 1000 glass and campaign form card use a side reveal.
- **FR-004**: Other templates keep their current `data-reveal-from` values. `landing.blade.php` is not edited.
- **FR-005**: Campaign `show` form is `max-w-2xl` by default and `lg:max-w-5xl`. From `lg`, identity fields and amount/comment fields sit in two columns. Inputs are not stretched to the full site content width.
- **FR-006**: Payment Element, submit, and errors span the full form card under the columns. Payment confirmation code does not change ([Payment Element](https://docs.stripe.com/payments/payment-element)).
- **FR-007**: Thank-you, payment-privacy, and Diventa socio are not edited.

### Key Entities

- **Home section order**: hero, stats, quote.
- **Side reveal**: `data-reveal-from="left"` | `"right"` with `--reveal-x: ±100vw`.
- **Donation form columns**: identity column + amount column.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In Home HTML, the stats heading appears before the manifesto quote and after the hero `h1`.
- **SC-002**: Chi siamo and Donazioni listing markup contain both `data-reveal-from="left"` and `data-reveal-from="right"` and do not use `up` on those section cards.
- **SC-003**: At 1280px the payment form is a two-column card; at 390px it is a single stacked column capped at 42rem.
- **SC-004**: A visitor can still start a payment without a change to Stripe client/secret flow.

## Assumptions

- “1 блок” on Home is the hero glass. “цитата” is the manifesto.
- “везде где есть она” means every campaign `show` (shared Blade), not membership.
- `max-w-5xl` (64rem) is wide enough for two field columns without stretching a single input across the page.
- Owner asked to implement in the same turn as specify/plan/tasks.
