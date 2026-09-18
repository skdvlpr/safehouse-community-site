# Feature Specification: Ad Grants sitelink landings

**Feature Branch**: `004-ad-grants-landings`

**Created**: 2026-09-13

**Status**: Draft

**Queue**: S02c (split from constitution S02)

**Depends on**: S01 UAT. Should follow 003 so search titles exist, but MUST NOT wait on Google's approval of an Ads account. Google Ads campaign structure, keywords, and CTR hygiene are **owner/ops outside this repository**.

**Input**: Owner wants Google Ad Grants sitelinks for Donazioni, Diventa volontario, Diventa socio (plus reserves: Contatti, 5 x 1000, Chi siamo). This feature makes those **site destinations** honest, complete, and ads-safe. It does not operate the Ads account.

Official policy sources (opened for this specification): [Ad Grants policy compliance](https://support.google.com/nonprofits/answer/9314402), sitelink asset behaviour as documented by Google Ads help (account-level sitelinks; destinations must match link text and load). Third-party summaries (GrantMax 2026 checklists) were used only to name the well-known minimum of **at least two live sitelink destinations**; the site still follows Google's own pages at plan/implement time.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Donate and volunteer landings are ads-safe (Priority: P1)

A person arriving from an ad sitelink "Donazioni" or "Diventa volontario" understands within a few seconds where they are, what the association asks, and what to do next. The page is HTTPS, mobile-usable, in the URL's language, with a working primary action (give or apply). No broken links, no demo content, no third-party display ads.

**Why this priority**: These two journeys already exist. If they are weak, sitelinks get disapproved or waste grant traffic.

**Independent Test**: Open the Italian donate hub and volunteer page on a narrow screen. Time-to-understand and complete the primary action without staff help. Click every in-page link.

**Acceptance Scenarios**:

1. **Given** the donate hub, **When** a new visitor lands, **Then** they see a clear heading, why to give, and a path to 5 x 1000, bank transfer if offered, and online campaigns — without dead links.
2. **Given** the volunteer page, **When** a new visitor lands, **Then** they see why volunteers are needed, what happens after apply, privacy acknowledgement, and a working success state.
3. **Given** either page, **When** checked for ads safety, **Then** there is no AdSense/affiliate clutter and no unpublished preview chrome.

---

### User Story 2 - Membership landing is honest (existing URL) (Priority: P1)

There is already a unique public URL whose heading is the membership invitation: `https://safehouse.community/it/diventa-socio` (English uses the same slug). This feature **verifies and hardens** that page for ads (clear heading, steps to join, one primary action). It does **not** create a new page and does **not** create public member accounts.

**Why this priority**: The sitelink destination exists on production (001.1 inspect). Inventing a CRM membership module would be a different, larger product.

**Independent Test**: Resolve the existing slug in Italian and English; page is published; CTA works; no `/ru`.

**Acceptance Scenarios**:

1. **Given** production already publishes `/it/diventa-socio`, **When** this feature is done, **Then** that URL remains the membership landing in Italian and English — not a newly invented slug.
2. **Given** that landing, **When** the visitor uses the primary action, **Then** they reach an existing contact/desk or email path — not a new public login.
3. **Given** the association later wants a full membership CRM workflow, **When** that needs the other repository, **Then** this feature has not invented fake CRM fields; that work is a new spec and a stop-and-ask.

---

### User Story 3 - Sitelink pack for the Ads operator (Priority: P1)

The owner (or ads contractor) can copy a table of sitelink labels and final URLs for Italian (and notes for English) covering at least: Donazioni, Volontario, Socio, 5 x 1000, Contatti, Chi siamo — each URL live, unique, and matching the label.

**Why this priority**: Google requires at least two account-level sitelinks; the owner asked for three named ones plus reserves. The Ads UI is outside the site; the site must supply destinations that will not be disapproved for mismatch or 404.

**Independent Test**: From the delivered table, open every Italian URL; heading matches the sitelink idea; no demo URLs listed.

**Acceptance Scenarios**:

1. **Given** the sitelink table, **When** each Italian URL is opened, **Then** it returns a published page whose heading matches the asset text intent.
2. **Given** demo or sample pages, **When** the table is produced, **Then** they are absent from the sitelink list.
3. **Given** 5 x 1000 and contact and about already exist, **When** listed as reserves, **Then** this feature does not rebuild them unless the audit/UAT showed they are not ads-safe (then fix those pages, do not create duplicates).

---

### User Story 4 - Conversion URLs stay stable (Priority: P2)

Donate thank-you and volunteer success remain reachable, locale-stable URLs so measurement (002) and future Ads conversion actions can point at them. This feature does not add advertising pixels. Conversion **events** are owned by 002 (GA4 key events). Importing those events in the Google Ads UI is owner/ops, not a second on-site tracker.

**Why this priority**: Grants conversion tracking is often required; pixels wait for privacy categories (006). Stable URLs are the site's part.

**Independent Test**: Complete donate (test mode) and volunteer; success URLs match the sitelink pack notes.

**Acceptance Scenarios**:

1. **Given** a successful donation in test, **When** thank-you loads, **Then** the URL is documented in the sitelink/conversion notes.
2. **Given** 002 not yet live, **When** this feature ships, **Then** pages still work with no advertising tag.

---

### Edge Cases

- Weak membership content: still publish an honest short landing rather than pointing ads at a generic "other pages" demo.
- Recurring donation checkout may not offer the same payment methods as one-off checkout; landings must not promise methods that checkout does not show (Satispay claims belong with 005).
- Do not send ads to preview URLs or unpublished CMS drafts.
- Do not add public comments or member logins.
- Google for Nonprofits application, geo Italy, ad-group structure, CTR 5% are owner/ops — listed in Assumptions as out of repo.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Donate hub and volunteer landing MUST each present: mission-relevant heading, one primary action, trust identifiers already used by the association (name, fiscal code where already shown), working links, mobile-readable layout.
- **FR-002**: The published membership landing MUST remain `https://safehouse.community/it/diventa-socio` (and the English locale of the same page). Heading, steps, and a CTA into an existing contact channel. No public accounts. MUST NOT create a duplicate membership URL.
- **FR-003**: The feature MUST deliver a sitelink pack (labels + final URLs) with at least six Italian destinations: donate, volunteer, membership, 5 x 1000, contact, about — omitting any that the owner marks Skip at UAT.
- **FR-004**: Demo/sample pages MUST NOT appear in the sitelink pack.
- **FR-005**: Landings MUST NOT add display advertising, affiliate ads, or a second donation processor. Existing hosted checkout stays the only online card/wallet path.
- **FR-006**: Copy MUST be translatable; no new hardcoded visitor strings.
- **FR-007**: If 001 bucketed ads-safety defects to 004, those defects MUST be closed here rather than in a parallel `001.K`, unless they are true security holes (those stay `001.K`).
- **FR-008**: Advertising/remarketing tags are out of scope. Conversion *URLs* are in scope.

### Key Entities

- **Sitelink destination**: Label, locale, final URL, page purpose.
- **Membership landing**: Unique published URL; not a user account.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Six (or owner-skipped subset) Italian sitelink URLs load as published pages; 0 of them are demo/preview.
- **SC-002**: A new volunteer can explain the ask and submit the form on a phone-width screen without staff help.
- **SC-003**: A new donor can start giving or open 5 x 1000 from the donate hub in under two minutes.
- **SC-004**: Membership URL exists and does not create a login.
- **SC-005**: Owner can paste sitelink labels and URLs into Google Ads without discovering 404s in a spot-check of all Italian links.

## Assumptions

- **Compatibility**: Keep existing donate, 5 x 1000, volunteer, contact, about routes. Do not replace hosted checkout. Do not write the CRM repo; membership CTA uses current contact/desk.
- **Compatibility with 002**: Use 002 conversion events (locked GA4 product). Do not install a second on-site tracker or remarketing pixel. Ads-UI import of key events is owner/ops.
- **Compatibility with 003**: May rely on search titles; may tighten visible H1/CTA. Coordinate so editors are not given two competing "page title" concepts without explanation.
- **Compatibility with 005**: 5 x 1000 promotional *creatives* are 005; this spec only ensures the 5 x 1000 *page* is sitelink-grade. Do not duplicate banners.
- **Compatibility with 006**: No new marketing cookies.
- Default for membership: strong landing + existing contact CTA (not a full membership CRM). Owner may later specify a CRM workflow.
- Ads account work is out of repository scope.

## Changelog

- 2026-09-13: Initial specification (S02 split; landings + sitelink pack, not Ads UI).
- 2026-09-13: 001.1 remap — socio exists on production; locales it+en only.
- 2026-09-16: Point conversions at 002 GA4 events; still no Ads pixels in this spec.
