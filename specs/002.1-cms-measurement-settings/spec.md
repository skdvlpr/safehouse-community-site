# Feature Specification: CMS measurement settings (002.1)

**Feature Branch**: `002.1-cms-measurement-settings`

**Created**: 2026-09-17

**Status**: Draft

**Queue**: Amendment of S02 after `002`. Finish this amendment before `003`. Combined owner UAT of **002 + 002.1** after implement.

**Depends on**: `002` consent-gated audience measurement (gate, events, legal copy already in preview). This feature does **not** change banner UX, conversion event names, legal HTML, or production Caddy.

**Input**: Staff must turn public audience measurement on or off and store the Tag Manager container id **in the CMS**, the same way they already store payment and mail integrations. The public site must follow that save. Environment files are a fallback only, not the staff entry point. Do not put live container ids in git. Do not write production from this feature.

Vendor docs opened this specify (constitution § Tech Stack): staff CMS custom screens — https://filamentphp.com/docs/4.x/navigation/custom-pages ; form text inputs — https://filamentphp.com/docs/4.x/forms/text-input ; encrypted values at rest — https://laravel.com/docs/13.x/encryption ; GTM web container — https://developers.google.com/tag-platform/tag-manager/web

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Staff find measurement next to other integrations (Priority: P1)

A staff member who already knows **Impostazioni → Integrazioni** for Stripe, CRM, and email opens that same screen and finds a **measurement / analytics** tab at the same level. They see: on/off, Tag Manager container id (`GTM-…`). Italian CMS copy is primary; English CMS copy exists. They do not paste Google’s install snippets. They do not enter a Measurement ID (`G-…`) here — that stays inside Tag Manager. The visitor dashboard remains the Google Analytics website, not a chart inside this CMS.

**Why this priority**: Owner asked that every integration is edited from the CMS UI, not from server env files.

**Independent Test**: Log into local CMS `/cms-safehouse`. Open Settings → Integrations. Open the measurement tab. Confirm on/off and container id. Confirm Stripe/CRM/mail tabs still save.

**Acceptance Scenarios**:

1. **Given** a super-admin on local CMS, **When** they open Integrations, **Then** they see a measurement/analytics tab with an Italian label.
2. **Given** they open that tab, **When** the form loads, **Then** they can set enabled/disabled and a container id starting with `GTM-`.
3. **Given** a non-admin staff user, **When** they try Integrations, **Then** they still cannot access it (same rule as today).

---

### User Story 2 - Saving the screen turns public measurement on or off (Priority: P1)

Staff save Integrations. Public pages follow that save using the existing consent gate from 002: when measurement is on **and** the container id is a valid `GTM-` value, visitors who already accepted analytics may load Tag Manager. When measurement is off, or the id is missing/invalid, no Tag Manager hosts appear even after “accept all”. Cookie banner, donate, volunteer, and contact stay otherwise unchanged. Preview routes and the CMS itself still never load Tag Manager.

**Why this priority**: A settings tab is useless if public pages still read only env.

**Independent Test**: On local preview, save on + valid test container → public home boot node is enabled (still no official snippet in first HTML). Save off, or invalid id → boot node disabled, no `GTM-` in HTML. Existing 002 dismiss/essentials behaviour unchanged.

**Acceptance Scenarios**:

1. **Given** measurement enabled with a valid container id saved in CMS, **When** a visitor opens a public page, **Then** the site is allowed to boot measurement after analytics consent (same 002 rule).
2. **Given** measurement disabled in CMS, **When** a visitor accepts all cookies, **Then** Tag Manager still does not load.
3. **Given** enabled but an invalid or empty container id, **When** a visitor opens the site, **Then** measurement stays off and the public page does not error.
4. **Given** a CMS-saved value and a different leftover env fallback, **When** both exist, **Then** the CMS-saved value wins.

---

### User Story 3 - Secrets stay off git; local save does not change live (Priority: P2)

Git and chat do not receive a live container id. Local CMS and live CMS databases are separate. This feature is proven on local preview. The agent does not paste ids into production and does not reload live Caddy.

**Why this priority**: Accidental live enable or id leak would be worse than a missing tab.

**Independent Test**: Repo files (`.env.example`, seeders, specs) contain no live `GTM-` / `G-` id. Change set has no production CMS write.

**Acceptance Scenarios**:

1. **Given** this feature’s files, **When** a reviewer searches for a live container id, **Then** none is committed.
2. **Given** this feature’s change set, **When** it is reviewed, **Then** it does not include production CMS writes or production Caddy reload.

---

### Edge Cases

- Enabling measurement with an empty or invalid container id MUST keep the public site usable without Tag Manager (fail closed, not a 500).
- A CMS-saved off switch MUST win over a leftover env “on”.
- Preview and CMS HTML MUST still never expose the container as a bootable measurement inject.
- Stripe, CRM, and mail tabs MUST keep working after the new tab is added.
- Env keys from 002 remain a **fallback** when CMS has no row, so tests and emergency ops still work; staff entry point is CMS.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Super-admin MUST be able to edit public measurement (on/off + Tag Manager container id) from the existing Integrations screen, without using a server env file as the normal path.
- **FR-002**: Public measurement MUST follow the saved CMS values through the existing 002 consent gate (no inject until analytics accept; no noscript; ads keys stay denied).
- **FR-003**: Invalid or empty container id MUST disable measurement without breaking the public page.
- **FR-004**: CMS-saved values MUST override env fallbacks when a row exists.
- **FR-005**: Actual secrets already on Integrations (Stripe secret, webhook, CRM keys, SMTP password) MUST remain encrypted at rest with the same Laravel encryption already used. The Tag Manager container id is **not** a secret (it appears in the browser after consent, like a publishable payment key) and MUST remain readable in the form after save.
- **FR-006**: The CMS MUST NOT ask for a Google Analytics Measurement ID (`G-…`) or install snippets.
- **FR-007**: CMS and signed preview MUST NOT boot measurement.
- **FR-008**: This feature MUST NOT write production CMS, production env, or reload production Caddy.
- **FR-009**: Live container ids MUST NOT be committed to git.

### Key Entities

- **Measurement setting**: on/off flag and Tag Manager container id stored with other CMS integration settings; public site reads them after save.
- **Env fallback**: empty default off, used only when CMS has no stored row (tests / emergency).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A staff member who already uses Integrations can find and save measurement in under two minutes without opening a server file.
- **SC-002**: After a save, the next public page load reflects on/off (kill-switch) without a deploy.
- **SC-003**: With measurement off in CMS, a reviewer finds no Tag Manager host on the public homepage even after accepting analytics cookies.
- **SC-004**: Combined 002 + 002.1 owner test can be finished in one sitting on local preview.

## Assumptions

- Staff already have a Tag Manager web container from 002 owner-ops; this feature only stores the container id and kill-switch.
- Google Analytics 4 stays configured **inside** Tag Manager; the staff dashboard stays analytics.google.com.
- Captcha remains on its dedicated Settings page (already shipped in 001.3); this feature unifies **measurement** with Stripe/CRM/mail on Integrations, not a third settings item.
- Combined UAT of 002 (consent/events/copy) and 002.1 (CMS save) happens after this implement; counsel still waits.
- Local CMS will **not** be pre-filled with a live container id by the agent (id is not in local env; must not appear in git or chat).
