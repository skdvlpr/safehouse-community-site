# Feature Specification: Public SEO foundation

**Feature Branch**: `003-public-seo-foundation`

**Created**: 2026-09-13

**Status**: Planned (2026-09-24). Design is in [plan.md](./plan.md). Implement waits for tasks and an owner yes.

**Queue**: S02b (split from constitution S02)

**Depends on**: S01 owner UAT. May implement after 002 or in series after 002 UAT per serial queue — owner implements one spec at a time. This spec MUST NOT install measurement (002) or rewrite ads landing bodies (004).

**Input**: Public pages should be understandable to search engines and social shares: unique titles and descriptions per locale, an index of published URLs, language alternatives, and no indexing of previews. Today the public layout has almost no search descriptions or site index.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Unique title and description per public URL and locale (Priority: P1)

A search user (or a share preview) seeing an Italian donate page or an English about page gets a title and short description that match that page and language — not a generic site name only.

**Why this priority**: Without this, Ad Grants sitelinks and organic search both look unprofessional. Editors need to control copy without engineering.

**Independent Test**: View source or share-debugger of at least home, donate hub, 5 x 1000, volunteer, about, contact, one news item, in Italian and one other locale: title and description unique and in that language.

**Acceptance Scenarios**:

1. **Given** a published page with editor-supplied search text, **When** a visitor opens it, **Then** the document title and description match that locale's text.
2. **Given** an editor leaves search text empty, **When** a visitor opens the page, **Then** a sensible fallback from the visible heading/summary is used — never another locale's leftover sentence.
3. **Given** two different published URLs, **When** compared, **Then** they do not share an identical description unless an editor intentionally copied it.

---

### User Story 2 - Search engines can fetch a list of published URLs (Priority: P1)

Crawlers receive a maintained index of published public URLs for Italian and English, excluding previews and unpublished items.

**Why this priority**: No sitemap/index exists today; organic and ads quality both benefit from a clean URL list.

**Independent Test**: Open the published index; confirm donate, volunteer, 5 x 1000, about, contact appear; confirm a deliberately unpublished page and any preview URL do not.

**Acceptance Scenarios**:

1. **Given** a page unpublished in the staff workspace, **When** the index is fetched, **Then** that URL is absent.
2. **Given** a new published page, **When** the index is refreshed by the site's normal publish action, **Then** the new URL appears without a manual engineering step.

---

### User Story 3 - Locales are linked, previews stay private (Priority: P2)

Italian and English versions of the same content declare themselves as language alternatives. Signed preview links tell crawlers not to index.

**Why this priority**: Duplicate two-language URLs without alternatives confuse search. Previews must never become sitelink targets.

**Independent Test**: On a translated page, language alternatives are present. A preview response instructs crawlers not to index.

**Acceptance Scenarios**:

1. **Given** a page available in two locales, **When** either is opened, **Then** the other locale is declared as an alternative.
2. **Given** a preview URL, **When** fetched, **Then** it is excluded from the public index and marked noindex.

---

### Edge Cases

- Pages that exist in Italian only: do not invent English titles; do not list missing locales as alternatives. There is no public Russian locale.
- Donation campaign URLs and news articles must be in the index when published, gone when unpublished.
- Demo/sample pages that are still published: include them in the index (honest) but 004 MUST NOT use them as ads sitelinks — 003 does not delete them.
- This feature MUST NOT change visible landing copy, forms, or banners. Heading/body edits for ads quality belong to 004.
- Measurement tags belong to 002; 003 only adds discovery metadata.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Every public HTML document MUST have a locale-appropriate title and a short description suitable for search/share snippets.
- **FR-002**: Editors MUST be able to override title and description per locale for CMS pages and for other first-class public types that already have a staff editor (campaigns, news), without editing templates by hand.
- **FR-003**: The site MUST expose an up-to-date index of published public URLs across locales.
- **FR-004**: Unpublished and preview documents MUST be non-indexable.
- **FR-005**: When a document has more than one locale, language alternatives MUST be declared.
- **FR-006**: Share previews (title, description, image when an image already exists for the content) MUST use the same locale as the URL.
- **FR-007**: Visitor-facing labels for new editor fields MUST exist in Italian and English in the staff workspace.

### Key Entities

- **Discovery text**: Per-locale title, description, optional share image.
- **URL index entry**: Locale, canonical public path, last meaningful update.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A reviewer can open 8 representative public URLs × 2 locales and find a unique, same-language title and description on all 16.
- **SC-002**: The public URL index lists 100% of sampled published pages and 0% of sampled unpublished or preview URLs.
- **SC-003**: A preview URL is not present in the index and is marked as non-indexable.
- **SC-004**: Editors can change a description in the staff workspace and see it on the public page after publish, without a developer.
- **SC-005**: Visible volunteer/donate/about body copy is unchanged by this feature (ads landing rewrites are 004).

## Assumptions

- **Compatibility**: Existing translatable titles/bodies and locale-prefixed URLs stay. Do not introduce public user accounts. Do not add advertising networks or AdSense (Ad Grants website policy: no AdSense).
- **Compatibility with 001**: If the audit flags missing metadata, this spec is the bucket — do not also open `001.K` for the same gap.
- **Compatibility with 002**: No measurement scripts here.
- **Compatibility with 004**: 004 may tighten H1 and CTA on donate/volunteer/membership; 003 supplies defaults from those headings if editors have not written search text yet.
- **Compatibility with 005–006**: Banners and legal pages become indexable like other published pages; cookie banner behaviour unchanged.

## Changelog

- 2026-09-13: Initial specification (S02 split; discovery only).
- 2026-09-13: 001.1 remap — public locales it+en only.
- 2026-09-24: Plan. Thank-you stays out of the index; campaign privacy is indexed only while the campaign is active. Search text never falls back to the other locale. See [research.md](./research.md).
