# Feature Specification: Local vs live environment identity

**Feature Branch**: `008-local-prod-distinction`

**Created**: 2026-09-14

**Status**: Draft

**Queue**: After `007-public-visual-refresh`. Do **not** implement while `001.2` or `002`–`007` are open.

**Depends on**: S01 owner UAT. Owner 2026-09-14: distinction MUST be **technical** (configuration / status), not a public visual badge. Public look of local preview may match live; that is acceptable.

**Input**: Operators must always know whether they are on the local preview instance or the live association site, from configuration (or a safe status readout), without relying on page chrome. Local DDEV and live production already use different hostnames and Laravel environment names; this feature makes that identity **explicit, consistent, and enforced** for dangerous local-only actions. Donation campaigns and CMS integration records stay per-instance (different campaigns on local vs live remain OK).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - An operator can read the instance identity (Priority: P1)

A developer or owner working on the local preview can answer “is this live?” from an official environment label (configuration and/or a status command that prints **no secrets**). The same check on the live site answers “live.” The two answers cannot be the same.

**Why this priority**: The owner does not care about on-page banners. They need a technical source of truth.

**Independent Test**: On local preview, run the agreed status check; it reports non-live. On live (read-only), the same kind of check reports live. Neither output contains API keys or `.env` dumps.

**Acceptance Scenarios**:

1. **Given** the local preview, **When** an operator reads the official environment identity, **Then** it is clearly not live (local / preview).
2. **Given** the live site, **When** an operator reads the official environment identity, **Then** it is clearly live.
3. **Given** either instance, **When** they use the status readout, **Then** secrets (payment keys, CRM keys, `APP_KEY`) are not printed.

---

### User Story 2 - Live-only and preview-only actions cannot be mixed (Priority: P1)

Actions that must never run against the live site (content import meant for preview, demo seed shortcuts, payment mock) refuse when the identity is live. The live site cannot be accidentally treated as the local preview just because a command was copied from docs.

**Why this priority**: Mixing instances is how test keys, demo pages, or destructive imports hit the association site.

**Independent Test**: On local preview, allowed preview-only actions still run. A dry explanation or a local test double of “identity = live” refuses those actions. Existing live-safe operations (normal page view, CMS edit on live) are unchanged.

**Acceptance Scenarios**:

1. **Given** identity is live, **When** someone runs a preview-only import or demo seed shortcut, **Then** it refuses and does not change data.
2. **Given** identity is local preview, **When** they run the same preview-only action, **Then** it may run (subject to existing rules).
3. **Given** identity is live, **When** visitors use the public site, **Then** this feature does not add a required on-page “LOCAL” banner (visual chrome is out of scope).

---

### Edge Cases

- Automated tests use their own environment name (`testing`) and must keep working.
- Missing environment name MUST fail closed toward “treat as live” (do not assume preview).
- Copying a live database dump onto local preview does not rename the instance by itself; identity comes from configuration of that runtime, not from CMS page content.
- Different donation campaigns and integration records per instance remain allowed.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The product MUST have one official instance identity with at least two values: local preview and live. It MUST be readable from configuration (not from guessing the URL alone, though URL may agree with it).
- **FR-002**: Local preview and live MUST never report the same official identity.
- **FR-003**: If the identity is unset, the system MUST NOT assume preview (fail closed: treat as live for dangerous actions).
- **FR-004**: Preview-only actions that already exist (page import for local parity, local-only seed shortcuts, local payment mock) MUST refuse when identity is live.
- **FR-005**: A secrets-free status readout MUST exist so an operator can confirm identity in one step.
- **FR-006**: This feature MUST NOT require public-page banners, watermarks, or CMS color badges. Visual distinction is optional and not acceptance.
- **FR-007**: This feature MUST NOT overwrite live integration settings or donation campaigns to match local, or vice versa.
- **FR-008**: Public and CMS pages may look the same on preview and live; identity is not communicated by layout.

### Key Entities

- **Instance identity**: Official label of the running site (preview vs live). Not a CMS page, not a campaign.
- **Preview-only action**: An operation that is forbidden on live (imports/seeds/mocks already in that class).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An operator who has access to the runtime can state preview vs live in under 30 seconds from the official identity (config or status readout), without opening the public homepage.
- **SC-002**: 100% of listed preview-only actions refuse on live identity in a review checklist (no data change).
- **SC-003**: Status readout samples contain none of: payment secret material, CRM API keys, application encryption key.
- **SC-004**: Owner UAT: local preview reports preview; they agree live would report live (live may be checked read-only). No requirement to see a banner on the website.

## Assumptions

- Local preview continues to be the workstation instance the team already uses; live is the public association site.
- The existing environment name on each instance remains part of the story. Plan may add a dedicated instance label so a mis-copied “preview” setting on the live server cannot pass as preview — that is a plan-time choice, not a visual one.
- Campaigns and payment/CRM records stay per-instance data, as the owner already accepted.

## Out of scope

- Public visual refresh (`007`)
- Rewriting live `.env` from this repo during implement
- Syncing donation campaigns between preview and live
- On-page LOCAL banners
