# Feature Specification: Stack and compliance audit

**Feature Branch**: `001-stack-compliance-audit`

**Created**: 2026-09-13

**Status**: Draft

**Queue**: S01 (constitution SITE queue)

**Owner permission**: Batch specify 2026-09-13; implement remains serial. This is the only feature that may be implemented first.

**Input**: Owner requested an audit of the public site for vulnerabilities, bugs, hardcoded visitor-facing text, and anti-patterns versus the project's locked stack documentation and architecture rules, then later remediation. This specification covers the **audit and ranked findings only**. Remediations are follow-up features (`001.K` or later `NNN`) after owner UAT of the register.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Ranked findings register (Priority: P1)

The association owner and a future implementer need a single, durable register of problems on the live public site and its staff content workspace: security issues that could expose donor or volunteer data, broken public journeys, visitor-facing text that is not translatable, and designs that contradict the project's own architecture and stack rules.

**Why this priority**: Without a shared register, later work (analytics, ads landings, banners, privacy) would guess at quality and might paper over defects.

**Independent Test**: Open the register in the repository. Confirm every in-scope area below has either findings or an explicit "none found" line, and every finding has severity, evidence, and a follow-up bucket.

**Acceptance Scenarios**:

1. **Given** the current public site and staff workspace, **When** the audit is delivered, **Then** the owner can read a ranked list in English in the repo and a short Russian summary in chat.
2. **Given** a finding, **When** the owner picks it for repair, **Then** they can see whether it belongs in a repair of this audit (`001.K`) or in a later already-specified feature (analytics, SEO, ads landings, banners, privacy) so work is not duplicated.

---

### User Story 2 - Security and data-handling pass (Priority: P1)

Staff need to know whether donation checkout, volunteer and contact forms, cookie-choice storage, and staff login are failing closed: no secrets in the repo, no raw card data on association servers, no unhashed visitor identifiers, webhook and form abuse handled, staff path not the obvious `/admin`.

**Why this priority**: The site already collects donations and personal forms; a silent hole here is worse than missing analytics.

**Independent Test**: Walk the register's security section. Each control in scope is marked pass / fail / not applicable with evidence.

**Acceptance Scenarios**:

1. **Given** donation success and failure paths, **When** audited, **Then** the register states whether card data stays with the payment provider and whether invalid payment notifications fail closed.
2. **Given** volunteer, contact, and cookie-choice submissions, **When** audited, **Then** the register states whether stored identifiers are hashed where required and whether abuse throttling exists.

---

### User Story 3 - Public-journey and i18n pass (Priority: P2)

Visitors in Italian, English, and Russian should not hit dead ends, mixed languages, or unpublished demo content that looks official.

**Why this priority**: Ad Grants and SEO later assume working, honest pages. The audit must name broken journeys before those features rewrite copy.

**Independent Test**: From the register, a reviewer can reproduce each public-journey finding in a browser without reading application code.

**Acceptance Scenarios**:

1. **Given** donate, 5 x 1000, volunteer, contact, about, news, and home, **When** audited in all three locales, **Then** broken links, empty locales, and demo/unpublished content exposed as official are listed or marked none found.
2. **Given** visitor-facing strings, **When** audited, **Then** hardcoded (non-translatable) copy is listed with location.

---

### User Story 4 - Architecture-drift pass (Priority: P2)

Maintainers need to know where the codebase fights its own rules: fat controllers, hidden business rules, empty error swallowing, drive-by complexity, or stack choices that were never approved.

**Why this priority**: Later features must extend a known shape, not copy anti-patterns.

**Independent Test**: Each architecture finding cites a constitution principle or an official stack-doc rule and a concrete location.

**Acceptance Scenarios**:

1. **Given** the constitution architecture rules, **When** the audit finishes, **Then** drift is listed with principle ID and a suggested repair bucket.
2. **Given** an anti-pattern that a later specified feature will already replace (for example missing search descriptions), **When** recorded, **Then** the finding points to that later feature instead of inventing a parallel repair spec.

---

### Edge Cases

- Production content differs from seed/demo content: audit must say which environment was inspected (local vs production) and must not treat seed-only demo pages as production facts without checking.
- Sibling CRM issues are **out of scope** except how *this* site calls the CRM (wrong field names, silent failures). Changing the CRM is a stop-and-ask, not a finding to "just fix".
- Legal wording of privacy policies is out of scope for this audit's *rewrites*; the audit MAY note "policy text does not match behaviour" and point to S04.
- Missing analytics, SEO metadata, ads landings, campaign banners are **known absences** already specified as S02–S04. The audit MUST record them as "deferred to spec NNN" rather than as P0 defects to glue into this feature.
- Secrets accidentally present in the workspace: report to the owner immediately; do not copy secret values into the register.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The feature MUST produce a findings register stored in this feature's directory (English), covering at least: security and payment notifications; personal-form handling; cookie-choice handling; staff-workspace exposure; public journeys (home, donate, 5 x 1000, volunteer, contact, about, news); translatable visitor text; architecture drift versus the constitution.
- **FR-002**: Every finding MUST include: identifier, severity (blocker / high / medium / low / note), area, evidence (how to reproduce or where it was seen), impact, and follow-up bucket (`001.K` repair vs `002`–`006` vs owner/ops outside the site).
- **FR-003**: Every in-scope area with no finding MUST be listed as "none found" so silence is not ambiguity.
- **FR-004**: This feature MUST NOT change public or staff behaviour, content, or edge configuration except adding the register and any owner-test checklist. No "quick fixes" inside the audit.
- **FR-005**: The audit MUST compare behaviour to the project's constitution (including locked decisions) and to official documentation of the locked stack, citing those sources on each architecture/security finding.
- **FR-006**: The audit MUST NOT reopen locked decisions (native hosted donation checkout, staff path, three locales, no public accounts, cookies/HSTS at the edge, no production process managers) as design questions.
- **FR-007**: CRM may be read only when checking this site's donation or desk integrations. Findings that require editing the CRM repo MUST be labelled "owner decision — other repo" and MUST NOT include a silent site-only workaround that invents field names.
- **FR-008**: After delivery, the owner receives a numbered user-test script in Russian to confirm they can use the register (open it, understand ranking, accept or dispute buckets). Automated code tests passing is not acceptance.

### Key Entities

- **Finding**: One issue or explicit all-clear for an area; severity; evidence; follow-up bucket.
- **Follow-up bucket**: Repair of this audit, a later already-specified product feature, or owner/ops (credentials, Ads account, legal counsel).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Owner can prioritise work from the register in one sitting (under 30 minutes) without asking where a finding lives.
- **SC-002**: 100% of in-scope areas have either findings or "none found".
- **SC-003**: Zero behavioural changes ship with this feature.
- **SC-004**: At least 90% of findings that overlap S02–S04 are bucketed to those specs, not duplicated as audit repairs.
- **SC-005**: Owner UAT of the register completes (Pass / Fail / Skip with reason) before any `001.K` or S02 implementation starts.

## Assumptions

- **Compatibility (existing product)**: Donation checkout, 5 x 1000 page, volunteer form, contact/sportello forms, cookie banner (essential vs analytics categories, analytics scripts not actually loaded), three locales, and staff content workspace already exist. The audit inspects them; it does not replace them.
- **Compatibility (later specs 002–006)**: Known gaps (no measurement, almost no search metadata, no membership landing, no Satispay/5 x 1000 campaign creatives, cookie texts saying analytics are inactive) are specified elsewhere. This audit records them as deferred, not as in-feature repairs.
- Environment inspected will be stated (local project URL and/or production). Local-only defects get a flag.
- Official stack and Ad Grants/privacy *product* work stays in later specs; this audit may cite those official pages only when judging current exposure.
- Remediation specs are **not** created until the owner UAT's the register.

## Changelog

- 2026-09-13: Initial specification (batch specify; S01 report-only).
