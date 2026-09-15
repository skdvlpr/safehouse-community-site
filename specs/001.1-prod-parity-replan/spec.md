# Feature Specification: Production snapshot, local content parity, and SITE-queue replan

**Feature Branch**: `001.1-prod-parity-replan`

**Created**: 2026-09-13

**Status**: Draft

**Queue**: S01 amendment (`001.1`). Finish before `001.2` and before implement of `002`–`006`.

**Depends on**: S01 audit register (`specs/001-stack-compliance-audit/findings.md`) and owner answers to F-001…F-020 (2026-09-13).

**Input**: The audit was local-only. The owner then decided: drop Russian from the public site and from product law; keep working donation/CRM status flow and test webhook changes locally first; make visitor strings translation-ready for Italian and English; fix the listed medium/low findings in a later repair spec; align local published pages with production (local copies of production pages are intentional; production demo pages are unpublished; membership lives at `/it/diventa-socio`); Satispay promotion is a QR banner only; 5×1000 stays a header link with no extra banner; migrate hosted checkout later, not in this amendment. This feature records a production snapshot, brings the local preview’s **published content** in line with production, aligns product law to Italian+English, removes Stripe test secrets from the working tree, and rewrites the later specs so they match those decisions. It does **not** change donation checkout behaviour, webhook handling, or the live server configuration.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Durable production snapshot (Priority: P1)

The owner and a later implementer can open one dated snapshot of the live public site: which URLs answer, which pages are published, whether demo landings are public, whether membership exists, what visitors see for languages, cookie/privacy, 5×1000, and whether the live edge headers match what the project claims. The snapshot is evidence, not a repair.

**Why this priority**: Without a production picture, later specs still assume “missing socio”, “add Russian”, or “unpublished demo on production”. Those assumptions are already wrong.

**Independent Test**: Open the snapshot in this feature directory. Every public journey named in S01 has a production status. Membership URL is recorded as live. Demo landing URL is recorded as not public. Edge headers (transport security, framing, staff-path exposure) are recorded. No live secrets appear in the snapshot.

**Acceptance Scenarios**:

1. **Given** the live site, **When** a reader opens the snapshot, **Then** they see HTTP outcomes for Italian and English home, membership, donate hub, 5×1000, volunteer, contact, about, news, privacy, cookie, a demo landing path, `/ru`, and the staff login path.
2. **Given** the snapshot, **When** they look up membership, **Then** Italian `/it/diventa-socio` is recorded as a published page, not as a gap to invent later.
3. **Given** the snapshot, **When** they look up demo landings, **Then** `/it/landing-example` is recorded as not publicly available on production.
4. **Given** the snapshot, **When** they look up languages, **Then** public `/ru` is recorded as absent, matching the owner decision to drop Russian rather than add it.

---

### User Story 2 - Local preview matches production published content (Priority: P1)

A staff member opening the local preview sees the **same published pages and campaigns** as production (keys, slugs, published flags, membership present, demo landings not public). Local payment test settings stay local. Production is not overwritten by seeders.

**Why this priority**: The owner already copied production pages into local to judge them. Local still publishes demo landings that production does not. Later ads and banner work would test the wrong site.

**Independent Test**: Compare the published page keys and public URLs on local preview vs the production snapshot. Membership is published locally. Demo example landing is not publicly reachable locally. Local donation test configuration still works without production keys.

**Acceptance Scenarios**:

1. **Given** production has published membership and no public demo landing, **When** local preview is aligned, **Then** local membership is published and `/it/landing-example` is not a public official page.
2. **Given** local Stripe test configuration, **When** content is aligned, **Then** those test payment settings are not replaced by production payment settings.
3. **Given** production, **When** this feature is done, **Then** no production content seeder has been run against the live site.

---

### User Story 3 - Product law and public UI are Italian + English only (Priority: P1)

The written product law no longer requires a Russian public locale. Public UI, staff locale pickers, and seed content stop advertising Russian. Owner chat and numbered user-test scripts in chat stay Russian. Specs, constitution, and checklists stay English.

**Why this priority**: The audit treated missing `/ru` as a defect against product law. The owner reversed that: Russian must leave both the site and the law, or every later spec will keep promising a third locale.

**Independent Test**: Product law locale row is Italian + English, Italian primary. Public `/ru` remains absent by design. Seed/default locale lists do not offer Russian. Chat UAT instructions still say to paste a Russian script.

**Acceptance Scenarios**:

1. **Given** the locked locale decision, **When** this amendment is done, **Then** product law states Italian + English only, Italian primary.
2. **Given** a visitor, **When** they request `/ru`, **Then** they do not get a public Russian locale (same as today).
3. **Given** editors, **When** they open locale fields, **Then** they are not asked to fill Russian as a required public language.

---

### User Story 4 - Later work matches owner answers; test secrets leave the working tree (Priority: P1)

The owner can read an updated queue: what 001.2 will repair, how 002–006 change, and that hosted-checkout migration waits for its own spec plus a product-law change at that moment. Stripe test secret values are no longer sitting in a committed local-defaults file (history is left as-is).

**Why this priority**: Implementing 002–006 as written would add Russian, invent a membership page, and build a 5×1000 banner the owner rejected.

**Independent Test**: Open 002–006 specs (and the 001.2 outline). None require `/ru`. Membership work is “verify/harden the existing socio URL”, not “create a missing page”. Satispay is a QR banner; 5×1000 is header link only. Checkout Sessions is named as a later spec, not a silent Payment Element rewrite. The local-defaults file contains no `sk_test_` / `pk_test_` secret material.

**Acceptance Scenarios**:

1. **Given** F-001, **When** later specs are read, **Then** they do not require adding a Russian public locale.
2. **Given** F-017 and production membership, **When** the ads-landings spec is read, **Then** it treats `/it/diventa-socio` as existing.
3. **Given** F-018, **When** the banners spec is read, **Then** Satispay is a QR in page markup and 5×1000 has no extra banner.
4. **Given** F-020, **When** the queue is read, **Then** Payment Element stays until a dedicated later spec amends product law and migrates to Checkout Sessions.
5. **Given** the local-defaults file, **When** inspected after this feature, **Then** it has no Stripe secret or publishable test key values (settings come from local environment or the staff integrations screen).

---

### Edge Cases

- Production staff login may be reachable from the public internet even if the repository edge snippet would block it. Record it; do not lock the owner out in this feature.
- Production may lack transport-security and content-security headers the repository snippet describes. Record it; do not reload the live edge in this feature.
- Local may still contain unpublished copies of demo templates for editors. That is allowed if they are not publicly published.
- Russian strings already stored in content JSON may remain as leftover data until editors clean them; they MUST NOT be routed or offered as a public locale.
- Git history of the local-defaults file still contains the old test secret. This feature does not rewrite history. Rotation in the Stripe Dashboard is owner-ops, optional.
- Agent SSH to production is read-only evidence gathering. This feature MUST NOT change production files, environment, or services.
- Live donation flow that already posts statuses into the case system MUST NOT be changed here.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: This feature MUST store a dated production snapshot covering public URL outcomes, published page identity (key, template, published flag, slugs/titles per stored locale), campaign list without secrets, edge headers, staff-path reachability, and a redacted note on donation-webhook log noise.
- **FR-002**: The snapshot MUST NOT contain live environment files, integration secrets, or full payment keys.
- **FR-003**: Local preview published pages MUST match production on: which page keys are published, membership present, demo example landing not public.
- **FR-004**: Local payment test settings MUST remain local test settings (not production live keys, not a wipe of the local integrations screen).
- **FR-005**: Production MUST NOT be written to (no content seed, no edge reload, no application deploy) as part of this feature.
- **FR-006**: Product law locale decision MUST become Italian + English, Italian primary; Russian MUST be removed from that locked row and from public UI locale lists.
- **FR-007**: Owner-chat user-test scripts MUST remain Russian; constitution/specs/checklists MUST remain English.
- **FR-008**: Specs `002`–`006` MUST be rewritten so they no longer require Russian, no longer treat membership as missing, describe Satispay as a QR banner only, and leave 5×1000 as the existing header link without a campaign banner.
- **FR-009**: A follow-up repair spec outline (`001.2`) MUST list owner-approved audit repairs (webhook fail-closed, translation-ready donor strings, volunteer abuse control, framing-header alignment, injection and error-handling items F-007–F-014) and MUST state they are tested on local first; the owner is told in chat when to raise the Stripe test listener.
- **FR-010**: Checkout Sessions migration MUST be recorded as a later feature that amends the locked payment decision at that time. This feature MUST NOT change checkout.
- **FR-011**: Working-tree local integration defaults MUST NOT contain Stripe secret or publishable key material. Git history is not rewritten.
- **FR-012**: Findings F-001, F-006, F-017, F-018, F-020 MUST be remapped in the audit register (or an addendum in this directory) so they no longer contradict owner decisions.

### Key Entities

- **ProductionSnapshot**: Dated evidence pack (URL outcomes, published pages, campaigns without secrets, headers, staff-path note, redacted log note).
- **PublishedPage**: CMS page identity used for parity (key, template, published, slugs/titles).
- **DonationCampaignRef**: Public campaign identity (slug, title, active), no processor keys.
- **QueueRemap**: Mapping from audit finding IDs to `001.2` / `002`–`006` / later payment spec / owner-ops.
- **LocalePolicy**: Italian + English public UI; Russian absent by design.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A reader can answer “what is live?” for membership, demo landing, `/ru`, donate, volunteer, 5×1000, privacy/cookie, and staff login in under ten minutes from the snapshot alone.
- **SC-002**: After parity, local and production disagree on **zero** published page keys named in the snapshot (membership on, demo landing not public).
- **SC-003**: 100% of specs `002`–`006` stop requiring a Russian public locale and stop calling membership “missing”.
- **SC-004**: A search of the working-tree local-defaults file finds zero Stripe secret/publishable key values.
- **SC-005**: The owner can complete a Russian numbered user-test of local vs production published URLs without being asked to approve a live-server change.

## Assumptions

- Owner answers F-001…F-020 (2026-09-13) remain in force, including: drop Russian; repair webhook carefully later with local tests; translation-ready strings; fix F-004–F-016 in `001.2`; Satispay QR banner; no 5×1000 banner; Checkout Sessions later.
- Local preview already holds copies of many production pages; parity is published-flag and missing-key alignment, not a blind wipe of local drafts.
- Stripe test keys in git are **test** mode (`sk_test_` / `pk_test_`), introduced in commit `777c237`. Owner chose to remove them from the working tree and not rewrite history.
- Agent SSH used the local `safehouse-deploy` identity as `deploy` (personal `id_ed25519` is not authorised on that account). Inspect 2026-09-13 is the baseline snapshot source.
- Applying the repository edge snippet on the live server is **out of this feature** (needs a privileged edge change and owner presence).
- PHPUnit does not replace owner UAT. One regression test that local-defaults contain no Stripe key material is in scope because that is real secret-hygiene logic.

## Out of scope

- Changing live checkout, webhooks, or CRM ingest.
- Migrating Payment Element to Checkout Sessions.
- Reloading Caddy or editing production `.env`.
- Git history rewrite / BFG.
- Legal DPA wording.
- Implementing `002`–`006` or `001.2` code repairs.
