# Feature Specification: Operational GDPR and cookie alignment

**Feature Branch**: `006-gdpr-operational-alignment`

**Created**: 2026-09-13

**Status**: Draft

**Queue**: S04

**Depends on**: S01 UAT. Should follow 002. **002 (2026-09-16) now owns** first-visit cookie-banner behaviour, reopen/withdraw, and measurement-related cookie/privacy sentences for the locked GA4/Tag Manager product. This feature MUST reconcile leftover operational GDPR (other processors, later marketing category if Ads tags appear, inventory vs live tools) and MUST NOT reintroduce "analytics inactive" while 002 measurement is on. MUST NOT author a lawyer DPA or replace legal counsel (`S-GDPR`).

**Input**: Owner wants cookie, privacy, and consent behaviour aligned with current EU/Italian operational rules for a public association site. The site already has a first-visit banner (accept all / essentials / preferences), hashed consent audit, and legal pages that currently say analytics are not active.

Policy sources informing operational requirements (not a legal opinion): [Garante cookie guidelines 10 June 2021](https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9677876), [Garante cookie FAQ](https://garanteprivacy.it/faq/cookie).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Choice is real, refuse works, change of mind works (Priority: P1)

A visitor can accept all, accept essentials only, or pick categories. Refusing analytics still allows reading the site and donating/applying. They can reopen preferences later and change the choice (control delivered by 002). The association keeps an audit of the choice without storing raw IP or user-agent.

**Why this priority**: This is the heart of leftover operational compliance after 002. Prove the 002 banner still matches the law-shaped UX, and fill gaps 002 did not take (remaining processors, later marketing category).

**Independent Test**: Essentials-only user completes donate or volunteer. Analytics-accepted user sees measurement (if 002 live). User changes choice; behaviour follows. Audit record exists without raw network identifiers.

**Acceptance Scenarios**:

1. **Given** essentials only, **When** the visitor uses donate and volunteer, **Then** those journeys work and measurement/ads tags do not load.
2. **Given** a previous choice, **When** the visitor opens preferences again, **Then** they can switch between essentials and analytics and the new choice is stored.
3. **Given** a saved choice, **When** staff inspect the audit, **Then** they see type and time with hashed identifiers only.

---

### User Story 2 - Policies match what the site actually does (Priority: P1)

Cookie and privacy pages in Italian and English list: controller identity already used, what is essential, what analytics does (002 already named the live product), that card data does not land on association servers, remaining processors, how to contact the association, and that texts are operational not a DPA. Stale "analytics not active" claims cannot survive while measurement is on.

**Why this priority**: Garante requires an extended notice. 002 updates measurement-related sentences; this feature owns the leftover operational rewrite and must reconcile rather than conflict.

**Independent Test**: Read Italian and English cookie and privacy pages against a checklist of actual tools (session, consent cookie, measurement product, payment provider, CRM mentioned as internal systems). No tool is live but undisclosed; no disclosed tool is absent.

**Acceptance Scenarios**:

1. **Given** measurement on, **When** a visitor reads cookie information, **Then** the analytics tool is named, purpose and retention class are stated, and loading is described as consent-gated.
2. **Given** measurement off, **When** they read the same pages, **Then** analytics is described as not loaded.
3. **Given** any page, **When** looking for DPA/processor-agreement legal drafting, **Then** this feature has not added it; a "ask counsel" note is enough.

---

### User Story 3 - No marketing/ads tags without a matching category (Priority: P2)

If a future ads conversion tag would set marketing/profiling cookies, it MUST NOT run under the analytics toggle. This feature either (a) documents that no marketing tags exist, or (b) adds a distinct marketing category default-off. It does **not** implement Google Ads tags (out of scope unless already present).

**Why this priority**: Prevents 002/004 from silently treating Ads pixels as "analytics".

**Independent Test**: With analytics accepted and marketing refused (or absent category), no ads/remarketing requests fire. Inventory of third-party scripts is listed in cookie information.

**Acceptance Scenarios**:

1. **Given** no marketing tags on the site, **When** this feature ships, **Then** cookie information does not invent a fake marketing vendor, and analytics consent does not load ads pixels.
2. **Given** the owner later wants Ads tags, **When** they ask, **Then** that is a new spec (`006.1` or new NNN) that must add a separate category — not a silent extension of 002.

---

### Edge Cases

- Scroll-to-consent is not used; the site already uses explicit buttons — keep explicit buttons (stricter, allowed).
- Legal counsel documents, DPIA, DPA with processors: STOP; owner + counsel.
- Do not store form message bodies in cookie audit.
- Do not weaken hashing of IP/UA.
- If 001 found consent endpoint issues, they are `001.K` (security) not copy edits here — unless the finding is "text ≠ behaviour", which is this spec.
- Children-directed features: site is not a children's service; no extra under-age flow in this spec.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Visitors MUST be able to accept all, essentials only, or granular analytics; essentials MUST keep the site usable including donate and volunteer.
- **FR-002**: Visitors MUST be able to change or withdraw analytics consent after the first choice without contacting staff.
- **FR-003**: Cookie and privacy pages MUST match live tools in Italian and English, including payment provider, consent storage, measurement product state, and internal CRM mentioned at the current level of detail — no new secret processors.
- **FR-004**: Consent audit MUST continue to store only hashed network identifiers plus choice type and time.
- **FR-005**: This feature MUST NOT produce lawyer-grade DPA/DPIA text.
- **FR-006**: Analytics consent MUST NOT be used as a proxy for marketing/profiling tags. Either no such tags, or a separate default-off category (tags themselves are a later spec).
- **FR-007**: Banner and policies MUST remain translatable; no hardcoded legal Italian-only on English URLs. There is no public Russian locale.
- **FR-008**: If 002 already updated "inactive" sentences, 006 MUST reconcile rather than duplicate conflicting paragraphs.

### Key Entities

- **Consent record**: Choice, time, hashed identifiers (existing).
- **Policy page**: Cookie and privacy operational texts per locale.
- **Script category**: Essential vs analytics vs (optional future) marketing.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An essentials-only visitor can complete donate and volunteer with zero measurement/ads third-party loads.
- **SC-002**: A visitor who accepted analytics can switch to essentials and stop further measurement loads without staff help.
- **SC-003**: A reviewer comparing live third parties to cookie/privacy pages finds zero undisclosed live trackers and zero listed-but-absent trackers.
- **SC-004**: Audit samples contain no raw IP or raw user-agent.
- **SC-005**: Owner UAT includes reading Italian cookie + privacy pages and the English locale; Fail if they still say the opposite of live behaviour.

## Assumptions

- **Compatibility**: Keep existing banner UX (three actions, preferences panel). Keep hashed consent API. Keep legal pages as CMS-synced operational texts.
- **Compatibility with 002**: 002 (updated 2026-09-16) owns banner behaviour (necessary automatic; analytics after explicit consent; reopen/withdraw) and the measurement-related cookie/privacy operational texts for the locked GA4 product. 006 is source of truth for leftover operational policy (Stripe, Turnstile, CRM mentions, any later marketing category). Reconcile rather than duplicate. Do not operate two unrelated cookie UIs.
- **Compatibility with 003–005**: SEO and banners add no extra cookies. Ads landings add no pixels here.
- Constitution `S-GDPR` remains closed: operational only.
- Garante: third-party analytics generally need consent; first-party strictly aggregated stats can be closer to technical — the site already asks consent for analytics and will keep asking (stricter). 002 locked Google Analytics 4 after consent (not an exemption claim). This feature does not reopen that product choice.

## Changelog

- 2026-09-13: Initial specification (S04 operational; no DPA).
- 2026-09-13: 001.1 remap — public locales it+en only; `S-GDPR` unchanged.
- 2026-09-16: Boundary with 002 — banner + measurement policy sentences moved into living spec 002; 006 keeps leftover GDPR operational alignment.
