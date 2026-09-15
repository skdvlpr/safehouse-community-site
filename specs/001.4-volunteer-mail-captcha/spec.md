# Feature Specification: Volunteer mail + captcha layout (001.4)

**Feature Branch**: `001.4-volunteer-mail-captcha`

**Created**: 2026-09-15

**Status**: Accepted (owner UAT 2026-09-15)

**Queue**: Amendment of S01 after `001.3`. Finish this amendment before `002`. Do **not** start `002`–`008` in this feature.

**Extends**: `specs/001-stack-compliance-audit` (S01). Lineage: `001.4`.

**Depends on**: Owner UAT of `001.3` (captcha Settings screen works; volunteer widget visible). This feature does **not** reopen payment notifications, donate copy, or the captcha Settings page itself.

**Input**: Owner preview of the volunteer form: the bot-challenge box sits on the right of the field column; it is always dark. The volunteer submit path currently keeps applications in a local store that staff do not use. Next pass: mail the application to a named staff inbox, acknowledge the applicant by email, require every visible field (including a new last name), and stop keeping a volunteer applications table. When this change later goes live, drop that live table without inspecting its rows. A later CRM write is wanted but blocked on CRM work — not this feature.

Vendor docs opened this specify (constitution § Tech Stack / mail / public UI / bot challenge): outbound mail — https://laravel.com/docs/13.x/mail ; form validation — https://laravel.com/docs/13.x/validation ; public layout width — https://tailwindcss.com/docs/width ; bot-challenge widget theme, size, and managed-mode behaviour (checkbox / auto-pass, **no** picture puzzles) — https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/ · https://developers.cloudflare.com/turnstile/concepts/widget/

## User Scenarios & Testing *(mandatory)*

### User Story 1 - A volunteer application reaches Matteo by email, not a unused table (Priority: P1)

A visitor on Italian or English volunteering completes first name, last name, email, phone, message, and consent (and the bot challenge when it is on). They submit. Staff **matteo.grossi@safehouse.community** receive one email with a fixed Italian subject and a fixed Italian body listing those fields. The visitor receives a separate email in the language of the page they used: the application was received, someone will contact them soon, they should not reply. Nothing is written to a volunteer-applications store. The on-page thank-you still appears if send succeeded.

**Why this priority**: Owner said the volunteer table is unused generated waste; the real workflow is email to Matteo plus an auto-reply.

**Independent Test**: Submit a complete local volunteer form. Matteo’s inbox (or the local mail catcher equivalent) has the staff email. The applicant address has the acknowledgement. No new volunteer-application record exists. Empty last name / phone / message is rejected.

**Acceptance Scenarios**:

1. **Given** a complete valid volunteer form on `/it/volunteers` (or the live Italian volunteer slug), **When** the visitor submits, **Then** staff receive an email **To** `matteo.grossi@safehouse.community`, **Subject** `Nuova candidatura volontario`, body in Italian with this meaning and labels:

   ```
   Abbiamo ricevuto una nuova candidatura di volontario
   Nome: {first name} Cognome: {last name}
   Indirizzo email: {email}
   Tel.: {phone}
   Messaggio: {message}

   Website | Safe House
   ```

2. **Given** that same submit, **When** mail is sent, **Then** the applicant receives a message in **Italian** saying the candidacy was received, they will be contacted soon, and they should not reply to that message.
3. **Given** a complete valid submit on the **English** volunteer page, **When** mail is sent, **Then** the staff email stays the Italian template above (staff copy is not translated), and the applicant acknowledgement is **English** with the same meaning (received; we will contact you; do not reply).
4. **Given** a successful submit, **When** staff inspect the site’s volunteer-application store, **Then** there is no new row (the store is gone).
5. **Given** first name, last name, email, phone, message, or consent is empty, **When** they submit, **Then** the form is rejected, no staff email is sent, no applicant email is sent.
6. **Given** the bot challenge is on, **When** they submit without completing it, **Then** no emails are sent (same gate as today).

---

### User Story 2 - The challenge box sits with the form, and follows light/dark (Priority: P2)

A visitor on contact or volunteer sees the bot-challenge box aligned with the form fields (centred in the field column, or stretched to the field width — not hugging the right edge). If the site theme is light, the box is light; if dark, the box is dark. If the provider cannot change theme, the layout still must be fixed.

**Why this priority**: Owner UAT of the working widget; layout is wrong today; theme is nice-to-have if the provider allows it.

**Independent Test**: Open volunteer and contact in Italian (and spot-check English). Challenge on. Box is not right-aligned. Toggle site theme light vs dark: box appearance follows, or document that the provider ignored theme but layout is still correct.

**Acceptance Scenarios**:

1. **Given** the challenge is on, **When** a visitor opens volunteer or contact, **Then** the challenge box is centred in the form column **or** spans the same width as the text fields — not flush right with unused space on the left.
2. **Given** the site appearance is **light**, **When** the widget can follow theme, **Then** the box is light; **Given** appearance is **dark**, **Then** it is dark. If the provider does not honour theme, layout (scenario 1) still passes.
3. **Given** donate, cookie banner, or CMS login, **When** this feature is done, **Then** those surfaces still have no challenge widget.

---

### User Story 3 - Live volunteer store is removed only when this ships, without reading it (Priority: P2)

Preview can already stop using the volunteer-application store. Production still has whatever rows it has. The owner does **not** want a content review of those live rows. When they later ask to publish this feature, the live volunteer-application store is removed outright.

**Why this priority**: Owner asked to remember the live drop for push/deploy, and forbade doing it now.

**Independent Test**: This feature’s local change set removes the volunteer-application store from preview. The change set does **not** include a live production write. Owner-ops notes say: on the first live deploy of this feature, drop that store without inspecting rows.

**Acceptance Scenarios**:

1. **Given** local preview after this feature, **When** someone looks for a volunteer-applications table/list, **Then** it is gone and submit still only emails.
2. **Given** production **before** the owner asks to publish, **When** this specify/plan/implement runs, **Then** production is not written and the live store is not dropped yet.
3. **Given** the owner later asks to push/deploy this feature, **When** live is updated, **Then** operators drop the live volunteer-application store without opening or exporting its rows.

---

### Edge Cases

- Mail cannot be sent (SMTP down): visitor sees a safe error; they are **not** told the candidacy was received; no partial “stored locally instead”.
- Honeypot still silently accepts without mailing staff or the applicant (same anti-bot behaviour as today).
- Rate limit still applies; over-limit visitors are not mailed.
- Staff email Reply-To is the applicant’s address so Matteo can answer them from his client; the applicant acknowledgement must not invite a thread (no “reply to Matteo” on that auto-reply).
- First name stays the existing name field conceptually; last name is new and required. English and Italian labels exist.
- Phone placeholder must no longer say optional.
- Existing unused volunteer-application rows on preview may be discarded with the store; no migration of those rows into CRM or mail.
- Bot challenge remains the existing site-wide on/off + keys (001.3). This feature does not move keys.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Volunteer submit MUST send one staff email to `matteo.grossi@safehouse.community` with subject `Nuova candidatura volontario` and the Italian body structure in User Story 1 (grammar: *Abbiamo ricevuto*, *Messaggio* — owner draft had agreement/spelling slips).
- **FR-002**: Volunteer submit MUST send one acknowledgement to the applicant email: candidacy received; they will be contacted soon; do not reply. Language MUST match the public page locale (`it` or `en`).
- **FR-003**: Volunteer submit MUST NOT create or update a volunteer-application record. The volunteer-application store MUST be removed from preview in this feature.
- **FR-004**: Visible volunteer fields MUST all be required: first name, last name (new), email, phone, message, consent. Bot challenge remains required when enabled.
- **FR-005**: Staff email MUST be Italian regardless of public locale. Applicant acknowledgement MUST follow the public locale.
- **FR-006**: Challenge widgets on **contact and volunteer** MUST sit centred or field-width in the form column, not right-aligned.
- **FR-007**: Challenge widgets SHOULD follow the site’s current light/dark appearance. Layout (FR-006) is mandatory even if theme cannot change.
- **FR-008**: This feature MUST NOT write production, MUST NOT drop the live volunteer store until the owner asks to publish, and MUST NOT onboard a CDN zone or reload live Caddy.
- **FR-009**: When the owner later publishes this feature, live volunteer-application storage MUST be removed without inspecting row contents (owner-ops; not done in this specify/implement session).
- **FR-010**: Donate, cookie consent, CMS login, contact sportelli routing, and captcha Settings keys MUST stay out of this feature’s behaviour change (contact only shares the widget layout/theme).
- **FR-011**: A later CRM write of volunteer candidacies is **out of scope** and MUST be recorded as a follow-up (CRM changes required first). MUST NOT invent CRM fields or write the sibling CRM repo.

### Key Entities

- **Volunteer application (transient)**: First name, last name, email, phone, message, consent. Lives only in the outbound emails for this pass — not in a local applications table.
- **Staff notification**: Fixed Italian mail to Matteo’s inbox.
- **Applicant acknowledgement**: Localized auto-reply; not a conversation.
- **Bot-challenge widget**: Existing public challenge on contact and volunteer; this feature only changes how it is shown.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: After one complete local volunteer submit, staff inbox has exactly one matching Italian notification and the applicant inbox has exactly one acknowledgement; volunteer-application record count stays 0.
- **SC-002**: 100% of empty last-name, phone, or message attempts (sample ≥ 1 each) are rejected with no mail.
- **SC-003**: On volunteer and contact (it, plus at least one en spot-check), the challenge box is not right-aligned; owner can confirm centred or full field width in under 30 seconds.
- **SC-004**: Light vs dark site appearance: widget looks light/dark **or** owner accepts “theme unchanged by provider” **and** layout still meets SC-003.
- **SC-005**: Change set for this feature includes 0 production writes. Live table drop is written as a publish-time owner-ops step only.

## Assumptions

- SMTP already used for sportelli/contact mail is reused (From = existing site sender). No new mail provider.
- Staff copy stays the owner’s Italian template (with *ricevuto* / *Messaggio*). Applicant acknowledgement copy is written in site Italian and English to match FR-002; owner can tweak wording at UAT.
- Staff mail may use the applicant as Reply-To; acknowledgement should look like a no-reply notice.
- Honeypot, throttle, GDPR checkbox, and 001.3 captcha Settings remain.
- Widget managed mode may show a green check without pictures — that is expected (see widget docs). This feature does not switch to a puzzle captcha.
- Preview discard of unused volunteer-application rows is acceptable.
- `002` stays blocked until this amendment’s owner UAT.

## Out of scope

- Writing volunteer data to EspoCRM / PrimaNota (follow-up after CRM is ready)
- CMS screen to change Matteo’s address
- Contact form fields, sportelli desks, donate, cookie banner, CMS login challenge
- Changing captcha keys / Settings page
- Picture-selection captcha
- Dropping the **live** volunteer store during this specify/plan/tasks/implement (only when the owner later asks to publish)
- Specs `002`–`008`

## Follow-up (not this feature)

Recorded 2026-09-15 at owner request:

1. **CRM volunteer ingest** — extended candidacy flow that writes to CRM. Blocked on CRM changes. Do not start until the owner opens a later spec and CRM mapping exists. Read `/home/skoksharov/safehouse/nonprofit-espocrm` in that future spec (`S-CROSS-REPO`).
2. **Live store drop** — on the first production publish of **this** feature (`001.4`), drop the live volunteer-application store **without inspecting or exporting rows**. Not now.

## Changelog

- 2026-09-15: Owner UAT accepted — all `owner-user-tests.md` items Pass. S01 amendments closed; `002` unblocked (not started this turn).
- 2026-09-15: Initial specify from owner UAT (widget layout/theme + volunteer email-only workflow).
