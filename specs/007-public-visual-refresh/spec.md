# Feature Specification: Public visual refresh

**Feature Branch**: `007-public-visual-refresh`

**Created**: 2026-09-14

**Status**: Superseded for public motion and layout by `010-ads-ready-refresh` (2026-09-24). Do not implement `007` separately. The public typeface change already shipped in `002.4`.

**Queue**: After S04 (`006-gdpr-operational-alignment`). Do **not** implement while `001.2` or `002`–`006` are open.

**Depends on**: S01 owner UAT. Visual work is last in the SITE queue so ads/SEO landings are not redesigned twice.

**Input**: Owner 2026-09-14: the public site accidentally uses a programmer-editor typeface (JetBrains family). Replace it with a similar contemporary sans for the **public** site only. Leave CMS appearance as it is. Modernize the public look: more animation and a more three-dimensional feel. Exact motion inventory is chosen at plan time. Local-vs-production distinction is a **separate** queue row (`008`).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Public typeface is appropriate for an association site (Priority: P1)

A visitor on Italian or English public pages reads headings and body in a contemporary humanist or neo-grotesk sans that feels like a civic/nonprofit site, not a code editor. The mistaken programmer-editor family is gone from the public look. Staff using `/cms-safehouse` see the same CMS look as today.

**Why this priority**: The current public typeface was called out as a mistake. It is the first thing a visitor reads.

**Independent Test**: Open home, a landing, donate, and an article on a phone and a laptop. The public typeface is not the programmer-editor family. CMS login and a CMS list still look unchanged.

**Acceptance Scenarios**:

1. **Given** any public page in Italian or English, **When** a visitor reads titles and body, **Then** the typeface is a contemporary sans similar in role (neutral, readable) to what is there now, and is **not** a programmer-editor family.
2. **Given** staff open the CMS, **When** they browse lists and forms, **Then** CMS appearance (including its own admin typeface) is unchanged by this feature.
3. **Given** print-style email from the site (if any still uses a generic system stack), **When** that mail is opened, **Then** it remains readable; this feature does not require branded webfonts inside email.

---

### User Story 2 - Public pages feel more alive and dimensional (Priority: P1)

A visitor moving through home, landings, donate, news, and contact feels depth (layers, light, elevation) and restrained motion (entrances, hover, scroll) so the site feels contemporary rather than static. Color identity stays Aurora: dark glass, red accent, existing photo/aurora backgrounds. Layout structure (header, donate CTA, 5×1000 link) stays recognizable.

**Why this priority**: Owner asked to modernize and add dynamics, not a new brand. Plan time will list which surfaces move.

**Independent Test**: Same journeys as today, on a narrow phone and a wide screen, dark and light theme. Pages feel more dimensional and animated than before, without blocking reading or checkout.

**Acceptance Scenarios**:

1. **Given** home and a landing, **When** a visitor scrolls and uses primary buttons, **Then** they notice depth and motion (layers, hover, gentle entrance) while still finding Donate / 5×1000 / Contact in the same places.
2. **Given** the donate or volunteer form, **When** they fill and submit, **Then** motion does not hide labels, shift fields under the finger, or delay the primary action.
3. **Given** the visitor prefers reduced motion (OS/browser setting), **When** they browse, **Then** decorative motion is off or minimal; the site remains fully usable.
4. **Given** light and dark theme, **When** they switch, **Then** the refreshed look works in both; this feature does not invent a third theme.

---

### Edge Cases

- Italian is primary; English pages get the same look even when English copy is still thin.
- Very long CMS-authored HTML in page bodies must stay readable (no motion that clips or overlaps body text).
- Cookie banner and language/theme control remain usable above any decorative layers.
- If a webfont file fails to load, text still renders with a sane fallback stack.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Public pages MUST NOT present a programmer-editor typeface family as the site sans. The replacement MUST be a contemporary humanist or neo-grotesk sans of similar neutrality and weight range. The exact licensed family is chosen at plan time from a shortlist (owner may veto).
- **FR-002**: CMS (`/cms-safehouse`) appearance MUST remain as it is (no CMS visual refresh in this feature).
- **FR-003**: Public visual identity MUST keep the existing Aurora colors, glass panels, and background options. This feature MUST NOT invent a new brand palette.
- **FR-004**: Public pages MUST gain a more dimensional feel (layering, elevation, light) and more animation than today, on representative templates (home, landing, donate, listing, article, forms).
- **FR-005**: Motion MUST be ignorable: honor reduced-motion preference; MUST NOT block reading, tapping, or checkout.
- **FR-006**: Header, donate CTA, 5×1000 header link, and footer MUST remain findable without a scavenger hunt.
- **FR-007**: Both public locales (Italian primary, English) MUST share the same visual language. No public Russian locale.
- **FR-008**: Plan time MUST produce a short inventory of which surfaces move (owner reviews that list before implement). The spec does not freeze pixel-level animation recipes.

### Key Entities

- **Public typeface**: The sans used for public headings and body; not the CMS admin typeface.
- **Motion preference**: Visitor/OS request to reduce decorative motion.
- **Aurora identity**: Existing public color, glass, and background language to preserve.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On a 10-minute walkthrough of home, become-member, donate, and one article, a reviewer who knew the old look can point to (a) a non-editor typeface and (b) at least three places that feel more dimensional or animated — without using a developer tool.
- **SC-002**: A first-time visitor can still start a donation or open 5×1000 from the header in under 30 seconds on a phone-width screen.
- **SC-003**: With reduced-motion on, the same walkthrough completes with no essential control hidden or delayed.
- **SC-004**: A staff member opening CMS lists sees no intentional visual change from this feature.
- **SC-005**: Owner UAT (Italian script in chat) Pass/Fail on typeface, liveliness, and “still the same association,” not a new brand.

## Assumptions

- Replacement typeface is chosen at `/speckit-plan` from a short licensed/open shortlist that resembles the current public sans (neutral, wide weight range) without being a programmer-editor family. Owner veto is enough; they do not have to supply a file unless they want a house font.
- “More 3D” means perceived depth (layers, shadow, light), not a 3D engine or WebGL requirement.
- Donation campaigns, integrations, `.env`, and production writes are out of scope.
- Local-vs-production chrome is `008-local-prod-distinction`, not this feature.
- Further motion detail is a plan-time conversation, as the owner asked.

## Out of scope

- Filament CMS restyle
- New public locale
- Changing live production during this spec’s implement without a later deploy decision
- Satispay QR banner (`005`) and GDPR cookie legal text (`006`) except not breaking their future placement
