# Feature Specification: Donation ledger only after settlement (001.5)

**Feature Branch**: `001.5-donation-settled-crm`

**Created**: 2026-09-18

**Status**: Draft

**Queue**: Owner **interrupt** of open `002` / `002.1` UAT. Amendment of S01 donation → CRM bookkeeping (after `001.4`). Finish this amendment, then **return** to `002` + `002.1` UAT. Do **not** start `003`.

**Extends**: `specs/001-stack-compliance-audit` donation ledger path (Payment Element checkout, signed payment notifications, Prima Nota in the sibling CRM). Lineage: `001.5`.

**Depends on**: Local payment sandbox + local notification forward (`stripe listen` to the preview site) as today. This feature does **not** migrate checkout to Checkout Sessions (`S-STRIPE`). This feature does **not** change cookie/analytics (`002` / `002.1`). This feature MUST NOT write the CRM git repo (`S-CROSS-REPO`) unless a later stop proves the ledger entity itself cannot receive rows.

**Input**: After a successful sandbox donation, Stripe confirms payment and the donor reaches thank-you, but Prima Nota often appears only if staff run **Import online payments**. Local notification forward is running and signed events reach the site. Success notifications that fire before the provider’s fee/net exist are rejected as server errors; the later notification that carries fee/net is ignored for ledger create. The thank-you page waits on that same fee lookup, so the success screen stalls several seconds (it did not used to). Owner rules for this amendment: **every** succeeded donation MUST become a CRM ledger row; the row MUST NOT be written until fee/net (and the rest of the settlement snapshot) are known; cancel / fail / refund / dispute / payout status notifications MUST keep updating CRM; thank-you MUST be fast again.

Vendor docs opened this specify (constitution § Tech Stack): [Payment Element](https://docs.stripe.com/payments/payment-element) · [Payment Intents](https://docs.stripe.com/payments/payment-intents) · [Asynchronous capture / fee timing](https://docs.stripe.com/payments/payment-intents/asynchronous-capture) · [Webhooks](https://docs.stripe.com/webhooks) · [Event types](https://docs.stripe.com/api/events/types) · [Stripe CLI listen](https://docs.stripe.com/stripe-cli)

## User Scenarios & Testing *(mandatory)*

### User Story 1 - A succeeded donation becomes a complete ledger row without Import (Priority: P1)

A visitor completes a real sandbox (or live) donation. Stripe marks the payment succeeded. When the provider has published the processing fee and net amount for that payment, the site writes **one** Prima Nota income row in CRM (same fields as today’s successful ingest: gross, fee, net, reference, donor snapshot). Staff do **not** need Import online payments for that row. If the first “payment succeeded” notice arrives before fee/net exist, the site does **not** invent a zero fee and does **not** create a partial row; it waits for the notice that carries settlement and then writes. The same rule applies to a first recurring charge that the site already books from a paid invoice notice.

**Why this priority**: Owner: every donation MUST be recorded; incomplete commission MUST NOT be stored. Import is catch-up, not the happy path.

**Independent Test**: With local notification forward running, pay with the sandbox card. Do **not** click Import. When fee/net exist at the provider, Prima Nota shows the new row with non-invented fee/net. Repeat once for a recurring first invoice if that campaign is on.

**Acceptance Scenarios**:

1. **Given** local listen is forwarding signed notices to the preview site, **When** a one-time sandbox donation succeeds and the provider has fee/net, **Then** CRM shows a new income row for that payment without Import.
2. **Given** a “payment succeeded” notice arrives before fee/net exist, **When** the site handles it, **Then** no Prima Nota row is created and no zero/guessed commission is stored.
3. **Given** a later notice for the same payment that includes fee/net, **When** the site handles it, **Then** the complete row is created (or completed) exactly once (idempotent).
4. **Given** staff later run Import for that payment, **When** the row already exists, **Then** they get a duplicate/update, not a second donation.

---

### User Story 2 - Thank-you is immediate; it does not wait for the ledger (Priority: P1)

After Stripe confirms the payment to the donor, the thank-you page appears without a multi-second stall. The page may still greet the donor and show the payment reference. Writing Prima Nota MUST NOT block that page on fee/net retries. If settlement is not ready yet, thank-you still succeeds; CRM catches up from notifications (US1).

**Why this priority**: Owner regression: thank-you now waits several seconds; previously it did not. The stall is the success page waiting on settlement.

**Independent Test**: Complete a sandbox donate. Time from Stripe success redirect to visible thank-you heading. It feels immediate (no several-second spinner/blank wait). CRM may lag seconds until settlement; that lag is on the ledger, not on the donor page.

**Acceptance Scenarios**:

1. **Given** Stripe has confirmed the payment and redirected, **When** the thank-you URL loads, **Then** the success heading is shown without waiting for fee/net.
2. **Given** fee/net are not yet available at thank-you time, **When** the page renders, **Then** it still succeeds and does not error at the donor.
3. **Given** the same payment, **When** settlement later exists, **Then** US1 still creates the CRM row.

---

### User Story 3 - Status notices still move existing rows (Priority: P1)

After a row exists (or when a payment never settles into income), Stripe still sends cancel, payment-failed, refund, dispute, and payout-paid notices. Those MUST keep updating Prima Nota payment status as they do today (cancelled / problematic / refunded / disputed / sent-to-bank). Adding the settlement-complete path MUST NOT swallow or skip those notices. A refund or dispute on a payment that was booked MUST flip status even if the original row was created from the later settlement notice rather than from “succeeded” alone.

**Why this priority**: Owner: extra webhooks for disputes, cancellations, and the rest MUST keep feeding CRM.

**Independent Test**: On preview with listen: (a) a succeeded booked payment then a test refund or dispute notice updates that row’s status; (b) a cancel/fail notice still marks the matching row when one exists; (c) payout-paid still marks Planned Stripe income as sent when that flow is used. Do not require a live card dispute; signed test events or Dashboard resend of those types are enough.

**Acceptance Scenarios**:

1. **Given** a Prima Nota row for a Stripe donation, **When** a signed refund notice for that charge arrives, **Then** the row’s payment status becomes refunded (same meaning as today).
2. **Given** that row, **When** a signed dispute-open notice arrives, **Then** status becomes disputed (same meaning as today).
3. **Given** that row in Planned, **When** a signed automatic payout-paid notice includes that charge, **Then** status becomes sent/inviato as today.
4. **Given** a signed cancel or payment-failed notice for a known payment, **When** a matching row exists, **Then** it is cancelled or problematic as today.
5. **Given** a settlement-complete notice and a status notice for the same payment, **When** both are processed, **Then** both outcomes apply (row exists **and** status is not stuck at Planned if a later status notice said otherwise).

---

### User Story 4 - Local listen stays the preview path; live catalog is owner-ops (Priority: P2)

Local UAT keeps using `stripe listen` to the preview webhook URL (required; do not claim tests pass without it). Invalid signatures still fail closed as client errors (`001.2`). The **live** Dashboard endpoint’s event list MUST include whatever notice types this feature needs for settlement-complete **and** the existing status types. The agent does not silently rewrite the live endpoint.

**Why this priority**: Preview already forwards all CLI events; production allowlists types. Missing the settlement-complete type on live would recreate “only Import works”.

**Independent Test**: Document the notice types the site handles after this feature. Compare to the live Dashboard endpoint without changing it unless the owner asks. Local listen + sandbox donate proves US1–US3.

**Acceptance Scenarios**:

1. **Given** local listen is off, **When** someone donates on preview, **Then** this feature’s UAT does not treat that as a pass for automatic CRM write (thank-you still must be fast).
2. **Given** a missing or bad signature, **When** a notice hits the site, **Then** the response is still a client error, not a server error, and no ledger write.
3. **Given** production, **When** this feature is reviewed, **Then** the owner has a checklist of event types to enable on the live endpoint; the agent has not changed that endpoint unless asked.

---

### Edge Cases

- Fee/net never appear within the provider’s published window: do **not** write a partial row; staff Import remains the catch-up; donor thank-you already succeeded.
- Duplicate notices (succeeded, settlement-complete, Import): one logical donation row (idempotent by payment reference).
- Recurring first invoice / later invoice paid: same complete-settlement rule; this feature does not add new recurring products.
- Status notice arrives before any income row (cancel before settle): keep today’s behaviour (update if a row exists; do not invent income).
- Settlement-complete notice that is not a donation (unrelated charge update): MUST NOT create a junk Prima Nota.
- Manual Import while a notice is in flight: lock/idempotency as today; no double income.
- Mock checkout (no live Stripe keys): existing simulate-success path is unchanged.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Every succeeded one-time (and existing recurring-invoice) donation MUST result in a CRM Prima Nota income row once the payment provider has published fee and net for that payment, without requiring staff Import.
- **FR-002**: The site MUST NOT create or update a donation ledger row with missing, zero, or guessed processing fee when the provider has not yet published settlement amounts.
- **FR-003**: The donor thank-you page MUST render as soon as the provider has confirmed payment success to the donor; it MUST NOT delay on settlement/fee lookup.
- **FR-004**: If settlement is not ready when thank-you loads, the page MUST still succeed; CRM write MUST happen from a later signed notice once settlement exists (FR-001).
- **FR-005**: Signed notices for payment cancel, payment failed, invoice payment failed, charge refunded, dispute open/update/close/funds, subscription deleted, and payout paid MUST continue to update Prima Nota payment status with the same meanings as today.
- **FR-006**: Handling settlement-complete notices MUST NOT prevent FR-005 notices from being applied to the same row.
- **FR-007**: Ledger writes from notices MUST stay idempotent on the existing donation payment reference (no duplicate income for retries or Import).
- **FR-008**: Invalid or unsigned payment notices MUST still return a client error (`001.2`); “settlement not ready yet” MUST NOT be treated as a durable server failure that drops the only chance to book the donation.
- **FR-009**: A settlement-complete notice MUST be ignored for Prima Nota create unless it belongs to a Safe House donation payment the site originated (campaign metadata / existing ingest rules).
- **FR-010**: Local preview UAT of automatic booking MUST use notification forward to the preview webhook URL; production Dashboard event-type allowlist changes remain owner-ops and MUST be listed for the owner.
- **FR-011**: Import online payments MUST remain a valid catch-up; it MUST NOT be the only path after this feature.
- **FR-012**: This feature MUST NOT change public donate fields, Payment Element checkout product, cookie/analytics, or the CRM codebase.

### Key Entities

- **Donation payment**: Stripe PaymentIntent (and first/later invoice payment where already booked), with donor/campaign metadata.
- **Settlement snapshot**: Provider fee, net, gross, and related charge/balance reference — required before first income write.
- **Prima Nota income row**: CRM ledger line keyed by donation payment reference; payment status Planned until payout rules say otherwise.
- **Status notice**: Provider event that changes payment status without creating a new donation (cancel, fail, refund, dispute, payout).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: After a sandbox donate with local listen on, staff see the new Prima Nota row without Import once the provider shows fee/net, in the same sitting (under a few minutes in normal sandbox, not “next day”).
- **SC-002**: In that sitting, the donor-facing thank-you heading is visible without a several-second wait after Stripe’s success redirect (subjectively immediate; no settlement spinner).
- **SC-003**: No new donation row is stored with an invented zero fee when the provider later shows a non-zero fee for that payment.
- **SC-004**: A refund or dispute notice on a booked test payment still changes that row’s status without a second Import.
- **SC-005**: Repeating the same success/settlement notices does not create a second income row for one payment.

## Assumptions

- Local `stripe listen --forward-to` the preview webhook remains how preview receives notices; the owner keeps it running during UAT (they already do).
- Stripe’s current default is asynchronous capture: fee/net often missing on “payment succeeded” and present on a later charge update ([asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture)). The site must follow that timing rather than stall the donor.
- Live Dashboard endpoint event list may omit the settlement-complete type today; enabling it is owner-ops when going live, documented in plan/owner-ops — not a silent production write.
- Status mappings already in production (Planned / Inviato / Cancelled / Refunded / Disputed / Problematic) stay; this feature does not redesign the status model.
- Sibling CRM Import and API user stay; this feature only changes **when** the site writes, not the CRM module.
- Analytics (`002` / `002.1`) stays paused until this UAT closes.

## Out of Scope

- Checkout Sessions migration; new payment methods; Satispay banners (`003` / `005`).
- Cookie banner, GA4, GTM (`002` / `002.1`).
- Regenerating CRM sync token UI; assigned-user CMS hygiene (ops, not this ledger timing bug).
- Writing `/home/skoksharov/safehouse/nonprofit-espocrm`.
- Legal DPA wording (`S-GDPR`).
- Production Caddy, production CMS, git push.
