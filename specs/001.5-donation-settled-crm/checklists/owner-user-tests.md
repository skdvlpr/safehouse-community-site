# Owner user tests: Donation ledger only after settlement (001.5)

**Purpose**: Owner accepts 001.5. PHPUnit does not close this handshake.  
**Created**: 2026-09-18  
**Feature**: [spec.md](../spec.md) · [quickstart.md](../quickstart.md)

**Marker semantics**: `[x]` = Pass (owner). Agent must not tick these.

Local automatic CRM UAT **requires** `stripe listen --forward-to https://safehouse-community-site.ddev.site/api/webhooks/stripe` ([CLI listen](https://docs.stripe.com/cli/listen)). CLI `whsec_` must match **local** CMS Integrations. Do not change the live Dashboard URL.

## Happy path (listen on)

- [ ] UAT001 Complete a one-time sandbox donate. Thank-you heading appears **without** a multi-second wait (no settlement spinner/blank).
- [ ] UAT002 Do **not** click Import. When Stripe shows fee/net, CRM Prima Nota has **one** new income row for that PaymentIntent (same sitting, not next day).
- [ ] UAT003 Fee on that row matches Stripe (not invented `0` when Stripe later shows a non-zero fee).
- [ ] UAT004 Listen CLI: `payment_intent.succeeded` may be **200 Pending settlement**; a later `charge.updated` is **200 OK** (not Ignored) once the fee exists.

## Status notices

- [ ] UAT005 Dashboard/CLI resend `charge.refunded` (or a dispute type) for that test payment → Prima Nota payment status updates; **no** second income row.

## Safety / ops

- [ ] UAT006 Live Stripe Dashboard webhook URL was **not** changed by the agent this feature.
- [ ] UAT007 Before going live: enable **`charge.updated`** on the production endpoint (see [owner-ops.md](../contracts/owner-ops.md) and `docs/STRIPE-SETUP.md`). Agent does not PATCH `we_…` unless asked.
- [ ] UAT008 Invalid/unsigned webhook still **400** (optional; PHPUnit covers this).

## After this UAT

Return to `002` / `002.1` analytics UAT. Do **not** start `003`.
