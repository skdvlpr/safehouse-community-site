# Feature Specification: Consent-gated audience measurement

**Feature Branch**: `002-consent-gated-analytics`

**Created**: 2026-09-13

**Status**: Draft

**Queue**: S02a (split from constitution S02 — owner allowed split on 2026-09-13)

**Depends on**: S01 owner UAT complete. Must not start implement while `001` UAT is open.

**Input**: Staff need to see visits, pages, devices, geography where available, traffic sources, and a small set of conversion events, using a ready-made measurement product (not a custom analytics engine). Scripts must load only after the visitor accepts the existing analytics cookie category. CRM remains the system for cases and donations accounting, not for public-web traffic.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Measurement only after analytics consent (Priority: P1)

A first-time visitor sees the existing cookie choice. If they accept only essentials, no audience-measurement scripts run. If they accept analytics (or "accept all"), measurement may run. If they later tighten the choice, measurement stops.

**Why this priority**: Italian cookie rules require an informed choice before non-technical tracking. The site already promises that analytics are off until configured and consented.

**Independent Test**: With essentials-only, network/privacy tools show no measurement product requests. After accepting analytics, measurement requests appear. After reverting to essentials, they stop.

**Acceptance Scenarios**:

1. **Given** no stored choice, **When** the visitor picks essentials only, **Then** pages work fully and no measurement product loads.
2. **Given** analytics accepted, **When** the visitor browses, **Then** page views are recorded in the staff dashboard.
3. **Given** analytics previously accepted, **When** the visitor switches to essentials only, **Then** further measurement loads stop within the same visit after the choice is saved.

---

### User Story 2 - Staff can read traffic without building a custom dashboard (Priority: P1)

A designated staff member (and optionally an ads contractor as viewer) can open a dashboard and see visits, unique visitors, pages, devices, country/city if the product provides them, sources, and session/engagement time.

**Why this priority**: This is the reason to add measurement. Building charts in-house is out of scope.

**Independent Test**: After test traffic with consent, staff can point to those numbers in the chosen product's dashboard (or an export).

**Acceptance Scenarios**:

1. **Given** consented test visits to home and donate, **When** staff open the dashboard within the product's normal delay, **Then** those pages appear in page reports.
2. **Given** measurement disabled by configuration, **When** anyone visits, **Then** no measurement scripts load even if cookie consent is "all".

---

### User Story 3 - Conversion events for donate, volunteer, contact (Priority: P1)

Staff can see that a donation thank-you, a successful volunteer application, and a successful contact/desk message occurred, without sending names, emails, or amounts into the measurement product.

**Why this priority**: Later ads work needs conversions. Events must exist here so ads landings (004) do not invent a second tracker.

**Independent Test**: Complete each journey with analytics consent; dashboard shows the three event types and no personal fields in event payloads.

**Acceptance Scenarios**:

1. **Given** analytics consent and a completed donation thank-you, **When** staff check conversions, **Then** a donate-success event exists without donor identity or amount.
2. **Given** essentials-only consent and the same thank-you, **When** staff check, **Then** no donate-success measurement event is sent.

---

### User Story 4 - Public texts stop claiming analytics are inactive (Priority: P2)

Once measurement is actually on, the cookie banner note, cookie policy, and privacy summary must no longer say that analytics cookies are inactive. They name the product, purpose, and that it runs only after consent.

**Why this priority**: Lying policies are a privacy defect. Full legal redraft stays in S04; this story only updates the sentences that become false.

**Independent Test**: With measurement enabled, Italian and English cookie and privacy pages no longer contain the "analytics not active" claim.

**Acceptance Scenarios**:

1. **Given** measurement enabled, **When** a visitor opens cookie information, **Then** the analytics category describes a live tool gated by consent.
2. **Given** measurement disabled by configuration, **When** a visitor opens cookie information, **Then** texts again say the tool is not loaded.

---

### Edge Cases

- Consent stored but measurement configuration missing: behave as "not loaded"; do not error the page.
- Thank-you or success pages opened without completing the journey: do not count as conversions unless the site already treats them as success.
- Preview/unpublished pages: do not pollute production reports as if they were public (exclude or mark).
- Advertising / remarketing pixels are **out of scope**. They must not ride along inside "analytics consent".
- Google Analytics-class US transfer tools MUST NOT be the default. Italian SA has treated classic Analytics transfers as unlawful; pick a privacy-preserving product at plan unless the owner later brings counsel approval for a specific successor.
- Membership conversion is out of scope until 004 confirms the existing socio landing (`/it/diventa-socio`).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Audience measurement MUST load only after the visitor's existing analytics consent (the same first-visit banner and preferences already on the site). Essential-only MUST be sufficient to use the whole public site.
- **FR-002**: Staff MUST have a ready-made dashboard (SaaS or self-hosted) showing visits, uniques, pages, devices, sources, engagement, and geography if the product offers it. Custom-built analytics is forbidden.
- **FR-003**: The site MUST record at least: page view; donate success (thank-you); volunteer submit success; contact submit success. Event properties MUST NOT include names, emails, phone numbers, messages, or donation amounts.
- **FR-004**: A configuration switch MUST allow turning measurement off globally without deploying a new product decision.
- **FR-005**: When measurement is on, cookie/privacy visitor texts that currently say analytics are inactive MUST be updated in Italian and English. When off, those texts MUST revert to inactive. S04 may later rewrite policies more fully; 002 MUST NOT author lawyer-grade DPA language.
- **FR-006**: Measurement MUST NOT replace or mix into CRM impact counters on the home page.
- **FR-007**: New third parties MUST be listed in cookie information and MUST remain blocked by the site's edge security policy until explicitly allowed for consented loads.
- **FR-008**: Product choice happens at planning. The product MUST support consent gating and a staff dashboard. Default class: privacy-preserving, EU-compatible measurement — not a US Analytics product unless the owner later records counsel approval.

### Key Entities

- **Consent level**: Essential vs analytics (existing).
- **Measurement event**: Page view or named conversion without personal data.
- **Staff dashboard access**: Who may view reports (ops, not a public account on this site).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In a supervised test, 100% of essentials-only sessions produce zero measurement-product requests.
- **SC-002**: After 20 consented test page views, staff can identify those pages in the dashboard without exporting raw logs.
- **SC-003**: Each of the three conversions can be demonstrated once with consent and once without (with = counted, without = not sent).
- **SC-004**: Cookie information in Italian and English matches whether measurement is currently on or off (no stale "inactive" / "active" mismatch).
- **SC-005**: Home CRM impact numbers remain visible and unchanged in meaning.

## Assumptions

- **Compatibility**: Reuse the existing cookie banner, preference panel, stored choice, and audit of consent. Do not add a second banner. Do not load measurement under a new "marketing" category — that category, if needed for ads tags, belongs to S04 plus a later ads-tag decision.
- **Compatibility with 001**: Implement only after S01 UAT. Adopt any 001 findings bucketed to 002; do not start 001.K security repairs here unless the owner re-buckets.
- **Compatibility with 003–006**: SEO and ads landings consume these events; they do not install another tracker. Privacy spec (006) may deepen policy text; 002 only fixes false "inactive" sentences. Banners (005) are not conversion goals unless they land on existing donate/5 x 1000 success paths.
- Existing hashed IP/UA consent audit stays; measurement product must not store extra raw IP on association servers.
- Official sources informing this spec: [Garante cookie FAQ](https://garanteprivacy.it/faq/cookie), [Garante cookie guidelines 10 June 2021](https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9677876). Legal counsel still required for any DPA.

## Changelog

- 2026-09-13: Initial specification (S02 split; measurement only).
- 2026-09-13: 001.1 remap — public locales it+en only (no Russian).
