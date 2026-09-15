# Feature Specification: Campaign banners (Satispay QR)

**Feature Branch**: `005-campaign-banners`

**Created**: 2026-09-13

**Status**: Draft

**Queue**: S03

**Depends on**: S01 UAT and 001.1 remap. Must not start while earlier queue rows are open.

**Input**: Owner wants a **Satispay QR shown as an HTML/CSS banner with a caption** — markup only, not a second payment processor. **5×1000 stays the existing header link**; this feature MUST NOT add a 5×1000 banner.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Satispay QR banner is visible and honest (Priority: P1)

A visitor sees a banner that displays a Satispay QR code (image or equivalent in HTML/CSS) plus a short caption. It does not claim a second checkout on this site and does not replace the existing online donation flow.

**Why this priority**: This is the only campaign creative the owner asked for after 001.1.

**Independent Test**: Enable the banner; see QR + caption; disable; banner gone. 5×1000 header link still works. No extra 5×1000 banner.

**Acceptance Scenarios**:

1. **Given** the Satispay QR banner is on, **When** a visitor opens the agreed placement, **Then** they see a QR and a caption in HTML/CSS — not a new payment API.
2. **Given** the banner is off, **When** they browse home and donate, **Then** the QR banner is absent and the 5×1000 header link remains.
3. **Given** monthly vs one-off checkout differences, **When** the caption is written, **Then** it does not promise Satispay inside hosted checkout unless that is actually true.

---

### User Story 2 - 5×1000 header link is left alone (Priority: P1)

The existing header (or donate-nav) 5×1000 link continues to reach `/it/donations/5-per-thousand`. This feature adds **no** 5×1000 campaign banner.

**Why this priority**: Owner 2026-09-13: keep the link, skip a banner.

**Independent Test**: With Satispay QR on or off, the 5×1000 header link still opens the existing page. No second 5×1000 creative on home.

**Acceptance Scenarios**:

1. **Given** the current header 5×1000 link, **When** this feature ships, **Then** that link still works and no extra 5×1000 banner is introduced.
2. **Given** ads sitelinks in 004, **When** 5×1000 is listed, **Then** the destination is still the existing page, not a banner.

---

### User Story 3 - Home messages stay (Priority: P2)

Enabling the Satispay QR MUST NOT remove existing home independence/manifesto messages.

**Independent Test**: QR on; independence/manifesto still visible if filled.

**Acceptance Scenarios**:

1. **Given** home independence text is filled, **When** the Satispay QR banner is enabled, **Then** independence text is still visible.

---

### Edge Cases

- Missing QR image: do not show an empty frame.
- Accessibility: caption/alt required; do not convey the ask in the image alone.
- No marketing cookies for impressions.
- No Satispay merchant integration beyond showing a QR.
- Locales: Italian primary, English second. No Russian public copy requirement.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Staff MUST be able to show or hide a Satispay QR banner (image or HTML/CSS QR, caption, optional link, on/off).
- **FR-002**: This feature MUST NOT add a 5×1000 banner. The existing header 5×1000 link MUST remain.
- **FR-003**: Claims about paying with Satispay MUST NOT contradict live checkout. A QR for an external Satispay payee is allowed as static markup.
- **FR-004**: Enabling the QR MUST NOT remove existing home independence or manifesto messages.
- **FR-005**: New visitor strings MUST exist in Italian and English.
- **FR-006**: No new payment processor, no public accounts, no measurement pixels in this feature.

### Key Entities

- **Satispay QR banner**: art/QR, caption, optional link, enabled flag.
- **5×1000 header link**: existing navigation; not a creative in this feature.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Reviewer sees a Satispay QR + caption when enabled, and nothing extra for 5×1000 besides the existing header link.
- **SC-002**: Caption does not lie about hosted checkout methods.
- **SC-003**: Home independence/manifesto remain visible when the QR is on (unless an editor emptied that content).
- **SC-004**: With the QR disabled, public pages match pre-feature banner absence except the unchanged 5×1000 header link.

## Assumptions

- **Compatibility**: Hosted checkout remains the only online card/wallet path (`S-STRIPE`) until a later Checkout Sessions spec.
- 004 sitelink for 5×1000 stays the existing page.
- 006: no extra cookies for impressions.
- Images/QR file supplied by the owner if not already in CMS.

## Changelog

- 2026-09-13: Initial specification (S03).
- 2026-09-13: 001.1 remap — Satispay QR HTML/CSS only; no 5×1000 banner.
