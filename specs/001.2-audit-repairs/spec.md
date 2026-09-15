# Feature Specification: Audit repairs (001.2)

**Feature Branch**: `001.2-audit-repairs`

**Created**: 2026-09-15

**Status**: Draft

**Queue**: Amendment of S01. Finish before `002`. Do **not** start `002`–`008` in this feature.

**Depends on**: 001.1 owner UAT (pages, locales, queue remap). Source: [001.2-outline.md](../001.1-prod-parity-replan/contracts/001.2-outline.md) and findings F-002, F-003, F-004, F-005, F-007–F-014.

**Input**: Close the remaining S01 repair items on the local preview first. Invalid payment notifications must fail closed. Donors on English pages must see English donate copy and must not see raw provider errors. Volunteer applications get the same bot challenge as contact when that challenge is enabled. Framing headers in the repo must not contradict each other; applying them on the live edge is owner-ops. Smaller hygiene: home-page construction pattern, visible payment-status sync failures, CSRF except path, cookie-consent hashing, trusted proxies, CMS error dump, translatable title suffix. Public locales remain Italian primary and English. Payment checkout product stays as today (no Checkout Sessions migration).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Forged payment notifications are rejected without retry storms (Priority: P1)

A payment provider sends a notification that is missing, forged, or has a bad signature. The site refuses it with a client error so the provider does **not** treat it as a server failure and retry forever. After a **valid** notification, existing CRM bookkeeping still works. Work is proven on the local preview before any live webhook change.

**Why this priority**: Unsigned or malformed notifications that return a server error cause retry storms and are a fail-open risk.

**Independent Test**: On local preview, send a notification with a wrong signature (or none). The site returns a client error, not a server error. A correctly signed test notification still updates CRM as today. Before that test, the implementer tells the owner to raise local listen / sandbox — do not assume it is running.

**Acceptance Scenarios**:

1. **Given** a payment notification with an invalid or missing signature, **When** it hits the site, **Then** the response is a client error (not a server error) and no donation/CRM write is performed for that payload.
2. **Given** a correctly signed test notification on local preview, **When** it is processed, **Then** existing CRM statuses still update as they do today.
3. **Given** live production, **When** this feature ships, **Then** live webhook endpoints are **not** rewritten blindly; any live change is a later owner-ops step.

---

### User Story 2 - English donors see English donate copy, not internals (Priority: P1)

A donor on the English donate journey sees English labels, help, and validation. Italian stays Italian. If checkout cannot start, they see a safe, translated message — not a raw provider or CRM exception string.

**Why this priority**: Constitution: public UI Italian primary + English; no leaking internals to visitors.

**Independent Test**: Open the English donate form; validation and help are English. Force a checkout failure in preview; the on-page message is a generic translated error, not a stack of provider English.

**Acceptance Scenarios**:

1. **Given** the English donate URL, **When** the donor leaves required fields empty or invalid, **Then** messages are English, not leftover Italian keys.
2. **Given** the Italian donate URL, **When** the same happens, **Then** messages stay Italian.
3. **Given** checkout cannot create a payment, **When** the form returns an error, **Then** the donor never sees raw provider/CRM exception text.

---

### User Story 3 - Volunteer form matches contact bot challenge when it is on (Priority: P1)

Staff have enabled the existing bot challenge on contact. Volunteers currently only have a honeypot and a rate limit. After this feature, volunteer submit uses the same challenge when it is enabled. Production contact is re-checked (widget was absent on 2026-09-13).

**Why this priority**: Automated volunteer spam is easier than contact spam.

**Independent Test**: With the challenge enabled in CMS, volunteer submit without a token is rejected. With it disabled, volunteer still works via honeypot/rate limit. Production contact: record whether the widget is present (inspect-only).

**Acceptance Scenarios**:

1. **Given** the bot challenge is enabled, **When** a volunteer submit omits the challenge token, **Then** the application is not stored.
2. **Given** the challenge is disabled, **When** a human volunteer submits a valid form, **Then** it still stores as today.
3. **Given** production contact, **When** this feature is verified, **Then** the owner gets a yes/no whether the challenge widget is on the live contact page (no production writes).

---

### User Story 4 - One clickjacking policy in the repo; live edge is owner-ops (Priority: P2)

The app and the published edge snippet in this repository currently disagree on framing. After this feature they agree. Turning that snippet on the **live** server remains owner-ops (root). This feature does **not** add HSTS/CSP on production.

**Why this priority**: Undefined framing policy if both layers emit different values.

**Independent Test**: Repo PHP headers and `deploy/Caddyfile.snippet` do not contradict on `X-Frame-Options`. No production Caddy reload by the agent.

**Acceptance Scenarios**:

1. **Given** the repository, **When** a reviewer compares app framing and the deploy snippet, **Then** they name one policy, not DENY vs SAMEORIGIN.
2. **Given** production, **When** this feature is done, **Then** live Caddy is unchanged unless the owner applies it themselves.

---

### User Story 5 - Hidden failures and hygiene (Priority: P2)

Home construction follows the same collaborator pattern as other public pages. If payment-status sync to the ledger fails, the failure is visible to operators (log), not swallowed. CSRF except path matches the real payment-notification URL. Cookie-consent rows hash identifiers the same way volunteer/contact do (HMAC if required). Trusted proxies are reviewed so rate limits and hashes are not trivially spoofed. CMS last-error dump does not keep full secret-bearing traces. The document title suffix is a translation string, not a hardcoded phrase.

**Why this priority**: Remaining S01 items; each is independently testable; none is a new product.

**Independent Test**: Checklist per item below; PHPUnit where there is real logic; owner UAT for donate/volunteer/title.

**Acceptance Scenarios**:

1. **Given** home, **When** it is built, **Then** it does not look up collaborators with a service locator (constructor injection).
2. **Given** ledger payment-status sync fails, **When** it is attempted, **Then** operators can see a failure (not an empty swallow).
3. **Given** cookie consent is recorded, **When** a row is stored, **Then** it includes the same class of hashes as volunteer/contact (including HMAC if that is the house rule).
4. **Given** a CMS exception, **When** the last-error file is written, **Then** it does not dump a full stack that can hold tokens.
5. **Given** any public page, **When** the title is shown, **Then** the suffix is translated (Italian / English).

---

### Edge Cases

- Valid payment notifications that fail later in CRM still return a retryable server error (do not turn those into 4xx).
- Local payment mock mode still works when no live listen is running (existing mock path).
- Reduced public locales: it + en only; leftover `ru` in CMS JSON is not a public locale.
- Trusting proxies: DDEV and production sit behind a known reverse proxy; do not trust arbitrary forwarded-for from the public internet if the app is exposed without that proxy.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Invalid or unsigned payment notifications MUST be rejected with a client error, not a server error.
- **FR-002**: Correctly signed notifications MUST keep today’s CRM bookkeeping behaviour.
- **FR-003**: Implementer MUST tell the owner in chat before local listen / sandbox is required; MUST NOT assume it is running; MUST NOT blindly change live webhooks.
- **FR-004**: Donor-facing donate strings MUST exist in Italian and English and MUST be used on the matching URL locale.
- **FR-005**: Checkout/mock error JSON shown to donors MUST be a safe translated message, never a raw exception string.
- **FR-006**: When the site-wide bot challenge is enabled, volunteer submit MUST require it, same as contact.
- **FR-007**: Production contact MUST be re-inspected read-only for presence of the challenge widget.
- **FR-008**: App framing header and the repo edge snippet MUST agree; live apply is owner-ops; no HSTS/CSP rollout in this feature.
- **FR-009**: Home MUST receive collaborators by constructor injection.
- **FR-010**: Ledger payment-status sync MUST NOT swallow all failures silently.
- **FR-011**: CSRF except configuration MUST name the real payment-notification path (or be removed if the API group already omits CSRF).
- **FR-012**: Cookie-consent audit hashes MUST match volunteer/contact hashing class (IP and user-agent; HMAC if that is the house rule).
- **FR-013**: Trusted-proxy configuration MUST be reviewed and documented; spoofed forwarded-for MUST NOT be the default if avoidable.
- **FR-014**: CMS last-error dump MUST NOT write full stacks likely to contain secrets.
- **FR-015**: Public document title suffix MUST be a translation string (it + en).

### Key Entities

- **Payment notification**: Provider callback about a donation; signature is the trust boundary.
- **Donor-facing message**: Copy a giver sees on donate (labels, validation, checkout errors).
- **Bot challenge**: Existing contact challenge reused on volunteer when enabled.
- **Consent audit row**: Stored cookie choice with hashed network identifiers.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A reviewer sending a bad-signature payment notification on local preview gets a client error in 100% of those attempts (sample ≥ 3), never a server error.
- **SC-002**: An English donor can complete the donate form language check in under 3 minutes: all visible validation/help on that URL is English.
- **SC-003**: With bot challenge on, 100% of volunteer posts without a token fail to store a row (sample ≥ 3).
- **SC-004**: Owner UAT script (Russian in chat) covers: donate English copy, volunteer challenge, title suffix, and “no live Caddy change.” Pass/Fail/Skip — Skip for live listen until credentials exist.
- **SC-005**: Listed preview-only/live-forbidden items (live webhook rewrite, HSTS/CSP apply, Checkout Sessions) are absent from the change set.

## Assumptions

- Payment checkout stays the current hosted card element; Checkout Sessions is a later spec.
- Local CMS already has test payment settings from 001.1; live keys stay on live.
- Framing on the live server remains owner-ops even after the snippet in git is aligned.
- HMAC for consent IP follows the same secret as other form hashes if those already use the application key.
- Tests are proposed only where there is real logic (signature fail-closed, volunteer challenge, donate locale, safe checkout errors).

## Out of scope

- Checkout Sessions / changing the locked payment product
- Production Caddy HSTS, CSP, CMS IP allowlist
- Specs `002`–`008`
- Git history rewrite for old test keys
- Syncing donation campaigns between preview and live
