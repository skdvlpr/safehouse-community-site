# Feature Specification: CMS captcha settings (001.3)

**Feature Branch**: `001.3-cms-captcha-settings`

**Created**: 2026-09-15

**Status**: Draft

**Queue**: Amendment of S01 after `001.2`. Finish this amendment before `002`. Do **not** start `002`–`008` in this feature.

**Depends on**: `001.2` public bot challenge on contact and volunteer (already in preview). Owner will run a **combined** user test of 001.2 challenge behaviour plus this staff screen after implement. This feature does **not** reopen payment-notification or donate-copy work.

**Input**: Staff need a dedicated CMS screen to turn the public bot challenge on or off and to store the provider’s public key and secret, in the same way they already store payment secrets: find it under Settings, save it in the CMS, keep the secret hidden at rest. Captcha today sits on the help-desk (sportelli) screen, which is the wrong mental model. Public forms stay contact and volunteer only. Do not put the live site behind a CDN zone. Do not write production from this feature.

Vendor docs opened this specify (constitution § Tech Stack / security): staff CMS custom screens — https://filamentphp.com/docs/4.x/navigation/custom-pages ; bot-challenge keys and hostname rules — https://developers.cloudflare.com/turnstile/get-started/

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Staff find captcha next to other secrets (Priority: P1)

A staff member who already knows **Impostazioni → Integrazioni** for payment keys opens Settings and finds a **captcha / bot-challenge** screen at the same level (not buried inside help-desk desks and email templates). They see: on/off, public site key, secret. Italian CMS copy is primary; English CMS copy exists. The secret field behaves like other CMS secrets: masked, not shown again after save, blank-on-save does not wipe the stored secret.

**Why this priority**: Owner asked for a settings page like payment integrations. Without it, keys live on the sportelli screen and are easy to forget.

**Independent Test**: Log into local CMS `/cms-safehouse`. From Settings, open the captcha screen in one click. Confirm the three controls. Confirm the sportelli screen no longer hosts those controls.

**Acceptance Scenarios**:

1. **Given** a staff user on local CMS, **When** they open Settings, **Then** they see a captcha/bot-challenge item in that group (same neighbourhood as Integrations), with an Italian label.
2. **Given** they open that screen, **When** the form loads, **Then** they can set enabled/disabled, a public key, and a secret.
3. **Given** a secret is already stored, **When** they save other fields with the secret left blank, **Then** the stored secret remains and public challenge still verifies.
4. **Given** they open help-desk (Sportelli) settings, **When** they look for captcha fields, **Then** those fields are gone (one place only).

---

### User Story 2 - Saving the screen turns public contact and volunteer challenge on or off (Priority: P1)

Staff save the screen. Contact and volunteer public forms (Italian and English) follow that save: when the challenge is on **and** both keys are present, visitors see the widget and cannot store a row without completing it. When the challenge is off, or keys are incomplete, visitors can still submit via today’s honeypot and rate limit (no widget). Donate, cookie banner, and CMS login stay unchanged.

**Why this priority**: The page is useless if it does not drive the two public forms already in 001.2.

**Independent Test**: On local preview, toggle on with valid keys → widget on `/it/contact`, `/en/contact`, `/it/volunteers`, `/en/volunteers`; volunteer/contact submit without completing the widget stores nothing. Toggle off → widget gone; a valid volunteer still stores.

**Acceptance Scenarios**:

1. **Given** challenge enabled with both keys saved, **When** a visitor opens contact or volunteer in it or en, **Then** the bot-challenge widget is visible.
2. **Given** that state, **When** they submit volunteer or contact without completing the challenge, **Then** no new row is stored.
3. **Given** challenge disabled (or enabled but a key missing), **When** a visitor opens those forms, **Then** there is no widget, and a valid human volunteer submit still stores.
4. **Given** donate or cookie-consent, **When** this feature is done, **Then** those flows have no new challenge widget.

---

### User Story 3 - Secret stays off the public site; local save does not change live (Priority: P2)

The public HTML never includes the secret. Preview CMS and live CMS are separate. This feature is proven on local preview. The agent does not paste keys into production and does not onboard `safehouse.community` as a CDN/DNS zone.

**Why this priority**: Secret leak or accidental live DNS change would be worse than a missing nav item.

**Independent Test**: View-source (or equivalent) on local contact/volunteer: public key may appear, secret must not. Confirm no production CMS write and no domain-zone onboard in this feature.

**Acceptance Scenarios**:

1. **Given** local public contact or volunteer HTML, **When** a reviewer searches for the stored secret, **Then** it is absent.
2. **Given** this feature’s change set, **When** it is reviewed, **Then** it does not include production CMS writes, production Caddy reload, or Cloudflare domain onboarding.
3. **Given** live contact (inspect-only), **When** this feature is verified, **Then** the owner is told whether the live widget is still absent until they themselves save keys on live CMS.

---

### Edge Cases

- Enabling the challenge with an empty public key or empty secret MUST keep public forms usable without a widget (fail closed on “half configured,” not lock honest visitors out).
- Saving with a blank secret MUST NOT clear a previously stored secret.
- Preview and live staff databases are independent: a local save MUST NOT appear on live until staff repeat the save there.
- Turning the challenge off after it was on MUST hide the widget on the next page load of contact and volunteer.
- Dummy/testing keys are allowed on preview; they MUST NOT be the recommended live configuration.
- Help-desk desks, email templates, and CRM case-type mapping on the sportelli screen MUST keep working after captcha is removed from that screen.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Staff MUST be able to open a dedicated bot-challenge settings screen from CMS Settings (`/cms-safehouse`), alongside other integration secrets—not inside help-desk desk lists.
- **FR-002**: That screen MUST let staff set: challenge on/off, public site key, secret key.
- **FR-003**: The secret MUST be stored encrypted at rest, masked in the CMS, and omitted from public HTML.
- **FR-004**: Saving with a blank secret MUST leave the existing secret unchanged (same pattern as payment webhook secret).
- **FR-005**: CMS labels and help on this screen MUST exist in Italian (primary) and English.
- **FR-006**: The help-desk (sportelli) settings screen MUST NOT still contain captcha controls after this feature (single source of truth).
- **FR-007**: When the challenge is on and both keys are present, contact and volunteer submit MUST require a successful challenge before storing a row (existing 001.2 behaviour, driven by this screen).
- **FR-008**: When the challenge is off or keys are incomplete, contact and volunteer MUST remain submittable without a widget (honeypot and rate limit unchanged).
- **FR-009**: Donate, cookie consent, and CMS login MUST NOT gain a bot challenge in this feature.
- **FR-010**: This feature MUST NOT onboard the public domain as a CDN/DNS zone, MUST NOT reload production Caddy, and MUST NOT write production CMS/keys.

### Key Entities

- **Bot-challenge setting**: Per-environment staff record: enabled flag, public key, secret. Preview and live each have their own.
- **Public form**: Contact (including help-desk destination) and volunteer only.
- **Staff CMS user**: Someone who can already edit Settings (Integrations / Sportelli). No new public accounts.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A staff member who already uses Integrations can open the captcha screen from Settings in under 60 seconds on local CMS (one navigation group, dedicated item).
- **SC-002**: After a save that enables the challenge with both keys, 100% of the four public URLs (contact/volunteer × it/en) show the widget on the next load (sample all four).
- **SC-003**: With the challenge on, 100% of volunteer (and contact) submits without completing the widget store zero new rows (sample ≥ 2 each).
- **SC-004**: With the challenge off, a valid volunteer submit still stores within one attempt (same as today’s no-widget path).
- **SC-005**: Review of public HTML finds the secret in 0 pages; review of the change set finds 0 production CMS/Caddy/DNS writes.

## Assumptions

- Public challenge widgets on contact and volunteer already exist from 001.2; this feature is the staff screen and the single place to edit the same settings.
- Local preview already has a working widget and keys from owner-ops (2026-09-15). Implement must not wipe those keys unless staff clear them on purpose.
- One provider widget may list both the public hostname and the local preview hostname; preview CMS and live CMS still store keys separately.
- Staff already know how to copy keys from the Cloudflare Turnstile dashboard; this feature does not build a Cloudflare login inside the CMS.
- Owner combined UAT of 001.2 + 001.3 happens after this feature is implemented; specifying 001.3 now does not close 001.2.
- Tests: propose only if save/blank-secret or “incomplete keys ⇒ no public lockout” needs a logic assertion. Owner UAT is not replaced by automated tests.

## Out of scope

- Cloudflare “Connect your domain” / nameserver / reverse-proxy onboarding
- Bot challenge on donate, cookie banner, or CMS login
- Creating or rotating the Cloudflare widget from inside the CMS
- Pasting keys onto production during implement
- Live Caddy HSTS/CSP apply
- Specs `002`–`008`
- Stripe listen / webhook secret work (still 001.2 owner-ops, not this screen)
