# Feature Specification: Production analytics ready (Caddy + sportello events)

**Feature Branch**: `002.2-production-analytics-ready` (spec directory; git remains current until the owner asks)

**Created**: 2026-09-21

**Status**: Implemented (live Caddy apply pending owner git deploy + root one-liner)

**Extends**: `specs/002-consent-gated-analytics`

**Lineage**: `002.2`

**Queue**: Owner interrupt of 002 / 002.1 UAT. Not `003`. Not membership/volunteer CRM leads (progress 039).

**Input**: Owner will not continue analytics UAT until production is fully configured so one combined test round can see **live** activity. CMS measurement toggle already exists and may already be on. Production edge must allow the consented measurement product on public pages. Contact conversions must be distinguishable by sportello: `contact_generic_success`, `contact_slegale_success`, `contact_sdigitale_success`. Owner updates Tag Manager tags before implement; site code maps desks at implement. Production apply via SSH; any one-shot server script MUST self-delete after success; leftover root commands MUST be printed if SSH cannot finish.

Official pages opened this specify turn: [Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header), [GTM CSP](https://developers.google.com/tag-platform/security/guides/csp), [GA4 Realtime](https://support.google.com/analytics/answer/9271392), [GA4 custom events](https://support.google.com/analytics/answer/12229021), [GTM publish](https://support.google.com/tagmanager/answer/6107163), [Laravel testing](https://laravel.com/docs/13.x/testing).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Consented live visits appear in the live activity view (Priority: P1)

A visitor on **https://safehouse.community** who accepts analytics is allowed to load the association’s audience-measurement product. Staff open the product’s **live** activity view (last minutes, not the delayed Home snapshot) and see that visit. The staff CMS path stays protected: measurement hosts are not required there and must not break the CMS.

**Why this priority**: Without this, every donate/contact UAT is wasted — hits never reach the property. Local DDEV is not the live site.

**Independent Test**: Measurement on + Accetta tutti on the live Italian homepage (ad blockers off). Within a few minutes, staff see a visit and a page view in the live activity view. CMS login still works. Essentials-only still loads no measurement product.

**Acceptance Scenarios**:

1. **Given** measurement is enabled in live CMS and the visitor accepts analytics, **When** they open `/it`, **Then** the live activity view shows a user / page view without waiting for the next-day Home report.
2. **Given** essentials only (or analytics withdrawn), **When** they browse public pages, **Then** the live activity view does not gain a new consented session from that choice.
3. **Given** a staff member opens `/cms-safehouse`, **When** the public measurement allowlist is applied, **Then** the CMS still loads (no new measurement requirement on that path).

---

### User Story 2 - Contact desks are three named conversions (Priority: P1)

A real contact submit (not honeypot) records **exactly one** conversion name matching the chosen sportello. Staff can tell legale, digitale, and generica apart in the same live activity / events list. The old single `contact_success` name MUST NOT be emitted.

**Why this priority**: Owner forbade further UAT until desks are distinguishable.

**Independent Test**: Three real submits (one desk each) with analytics accepted → three different conversion names; honeypot still shows thank-you with **no** conversion name.

**Acceptance Scenarios**:

1. **Given** desk Richiesta generica (`generic_desk`), **When** a real submit succeeds with analytics accepted, **Then** the conversion name is `contact_generic_success` and not the other two, and not `contact_success`.
2. **Given** desk Sportello legale (`legal_desk`), **When** a real submit succeeds with analytics accepted, **Then** the name is `contact_slegale_success`.
3. **Given** desk Sportello digitale (`digital_desk`), **When** a real submit succeeds with analytics accepted, **Then** the name is `contact_sdigitale_success`.
4. **Given** honeypot filled, **When** the fake success flash shows, **Then** no contact conversion name is present.

---

### User Story 3 - One combined owner UAT round (Priority: P2)

After US1+US2 are live, the owner runs **one** checklist covering: live page view, one-time donate, recurring donate, volunteer, three contact desks, essentials-off, CMS intact. They do not repeat ten partial analytics loops.

**Why this priority**: Owner’s explicit stop condition.

**Independent Test**: Owner ticks the combined checklist in this feature folder. PHPUnit does not close UAT.

**Acceptance Scenarios**:

1. **Given** production apply finished and Tag Manager published with the three contact names, **When** the owner follows the combined checklist, **Then** each named conversion can be demonstrated once in the live activity or debug view.
2. **Given** Home / last-7-days still zeros, **When** live activity already showed the visits, **Then** UAT still **passes** (batch reports are out of this feature’s success gate).

---

### Edge Cases

- Measurement globally off: public pages MUST NOT load the measurement product even after Accetta tutti; live activity stays empty for that site.
- Unknown or extra desk key: MUST NOT emit `contact_success`; MUST NOT invent a fourth public name in this feature.
- Google Tag Manager Preview on the live origin: the public allowlist MUST include Preview hosts from Google’s CSP guide so debug/live verification is possible.
- SSH as deploy cannot reload the edge: implement prints exact **root** commands; does not leave a failed one-shot script deleted.
- `www.safehouse.community` MUST receive the same public allowlist as the apex.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Production public pages MUST be allowed to load the locked 002 measurement product (GA4 via Tag Manager) after analytics consent. The staff CMS path MUST remain excluded from that public allowlist.
- **FR-002**: Apply on the live server MUST use a **one-shot** script that **self-deletes after success**. On failure the script MUST remain. If the agent cannot finish as the SSH user, they MUST print the remaining **root** commands and stop.
- **FR-003**: This feature MUST NOT claim to remove Google’s **batch** delay on Home / standard reports. Immediate proof is the product’s **live activity** and **debug** views ([Realtime](https://support.google.com/analytics/answer/9271392)).
- **FR-004**: Advertising / remarketing hosts MUST NOT be added. Ads consent keys stay denied (002).
- **FR-005**: Real contact success MUST set conversion names: `generic_desk` → `contact_generic_success`; `legal_desk` → `contact_slegale_success`; `digital_desk` → `contact_sdigitale_success`. Payload remains the event name only (no PII).
- **FR-006**: The name `contact_success` MUST NOT be pushed after this feature. Honeypot MUST NOT set any contact conversion.
- **FR-007**: Donate and volunteer conversion names from 002 stay unchanged.
- **FR-008**: Cookie/privacy **operational** copy on production MAY be synced so it no longer says measurement is inactive **if** live measurement is on. No DPA (`S-GDPR`).
- **FR-009**: Owner Tag Manager work (three Event tags + triggers, retire `contact_success`, Publish) is required before combined UAT. Details live in this feature’s GTM contract; the agent does not log into the owner’s Google account.
- **FR-010**: No CRM git writes. No socio/volunteer Lead ingest. No `003`.

### Key Entities

- **Public measurement allowlist**: Hosts the browser may contact on public pages after consent (Tag Manager, Analytics collect, Preview). Not applied to CMS.
- **Contact conversion name**: One of three strings tied to a sportello desk key.
- **One-shot server apply**: Script that updates the live edge from git, validates, reloads, then deletes itself.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: After apply, a consented live homepage visit is visible in the audience product’s live activity view within **5 minutes** (ad blockers off). Home zeros do not fail this.
- **SC-002**: CMS login still returns a usable staff screen after apply.
- **SC-003**: Three real contact submits (one per desk) produce three **different** conversion names in live activity or debug; none is `contact_success`.
- **SC-004**: Essentials-only visit does not load the measurement product.
- **SC-005**: Owner completes the combined checklist in one sitting after implement (not ten separate analytics loops).

## Assumptions

- Live CMS measurement toggle is already available; owner may already have turned it on. This feature does not invent a second toggle.
- Git deploy of code may land before or with the edge apply; combined UAT waits for **both** plus published GTM tags.
- Local DDEV has no Caddy CSP; US1 is production-only. US2 is testable in PHPUnit + local preview.
- Existing `deploy/apply-caddy-site-once.sh` pattern (backup, validate, reload, self-delete) is the apply mechanism (`S-CSP`).
- `unsafe-inline` stays acceptable for this interrupt (002 injects Tag Manager from first-party JS; nonce is a later spec).
- Google Signals stay off.

## Changelog

- 2026-09-21: Implemented T001–T010 locally; production CSP apply blocked (SSH publickey). Combined UAT waits for git deploy + `apply-caddy-site-once.sh`.
- 2026-09-21: Initial 002.2 specification (production allowlist + three contact conversion names + combined UAT).
