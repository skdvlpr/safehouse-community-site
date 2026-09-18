# Research: Donation ledger only after settlement (001.5)

**Date**: 2026-09-18

## R1 — When Stripe publishes fee/net

- **Decision**: Treat Stripe **BalanceTransaction** (fee + net on the Charge) as the settlement SoT. Do not persist Prima Nota until `retrieve`/`expand` shows a usable BT (`fee` present). Follow Stripe’s asynchronous capture: BT is often **null** on `payment_intent.succeeded` and `charge.succeeded`; it appears on **`charge.updated`**. Cite: [asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture), [charge.updated](https://docs.stripe.com/api/events/types).
- **Rationale**: Owner forbids guessed/zero commission. Local listen already shows PI.succeeded **502** then charge.updated **200 Ignored**. Import works later because BT exists.
- **Alternatives considered**: `capture_method=automatic` on PaymentIntent create so BT is on succeeded immediately. Rejected as the **only** fix: Stripe still documents possible lag; thank-you would still stall if we keep 20 retries; live API default remains async. **Owner micro-patch 2026-09-18**: one-time `createDonationIntent` now sets `capture_method=automatic` ([create PaymentIntent](https://docs.stripe.com/api/payment_intents/create#create_payment_intent-capture_method)). Webhook still ingests on `charge.updated` if BT lags. Recurring invoice PaymentIntents stay Stripe Billing defaults.

## R2 — Which webhook types create income vs update status

- **Decision**: Keep `PrimaNotaPaymentStatusService::applyFromStripeEvent` **first**. If `handled === true`, return 200 and **do not** ingest (cancel, payment_failed, invoice.payment_failed, charge.refunded, dispute.*, customer.subscription.deleted, payout.paid — unchanged).
  Extend **ingest** PI resolution to:
  - `payment_intent.succeeded` (as today)
  - `invoice.paid` (as today)
  - **`charge.updated`** via `data.object.payment_intent` when the Charge belongs to a site donation (metadata `campaign_id` or `stripe_subscription_id` after PI retrieve)
  Do **not** ingest on `charge.succeeded` (BT typically still null).
- **Rationale**: Status events must not be swallowed (US3). `charge.updated` is the documented settlement notice. Local CLI already forwards it; production Dashboard allowlist likely omits it (owner-ops).
- **Alternatives considered**: Ingest only on PI.succeeded with long retries (current; stalls + 502). Queue/Horizon job (forbidden `S-NO-OCTANE` spirit / extra service). Rejected.

## R3 — HTTP status when settlement is not ready

- **Decision**: Introduce a typed settlement-not-ready failure (not a generic CRM 502). Webhook returns **200** with a short body (`Pending settlement` or similar). Log `info`/`warning` with PI id, no secrets. True Espo/API ingest failures stay **502** so Stripe retries those. Invalid signature stays **400** ([signatures](https://docs.stripe.com/webhooks/signatures), `001.2`).
- **Rationale**: CLI listen does not usefully retry 502 the way Dashboard does; 502 on “BT missing” drops the only PI.succeeded delivery and **ignores** the later charge.updated. FR-008: not-ready must not be a durable server failure.
- **Alternatives considered**: Keep 502 to force Stripe retries of PI.succeeded (works on Dashboard, fails on listen). Sleep 10 s in the request (thank-you stall). Rejected.

## R4 — Thank-you must not wait

- **Decision**: Thank-you may **attempt one** settled retrieve (no multi-second retry loop). If BT is ready, ingest (idempotent). If not, log and **still render** thank-you. Webhook/`charge.updated` is the SoT for booking. Recurring customer-portal lookup on thank-you must keep using the non-settlement retrieve (already separate).
- **Rationale**: Current `retrieveSettledPaymentIntent` default 20 × 500 ms ≈ 10 s on the HTTP request — matches owner “several seconds” regression. SC-002.
- **Alternatives considered**: Remove thank-you ingest entirely (also valid; one-shot is a cheap win when BT is already there). Background worker (out of stack).

## R5 — Junk `charge.updated`

- **Decision**: After resolving PI, skip ingest unless donation metadata contains `campaign_id` (one-time) or subscription id (existing recurring path). Do not create Prima Nota with the generic “Donazioni online” fallback for unrelated charges.
- **Rationale**: FR-009. `charge.updated` is noisy.
- **Alternatives considered**: Ingest any succeeded charge in the account (too wide).

## R6 — Production event catalog

- **Decision**: Document required Dashboard types in `contracts/owner-ops.md`. Agent does not PATCH the live `we_…` unless the owner asks. Local `stripe listen` already uses `[*]` ([listen](https://docs.stripe.com/cli/listen)).
- **Rationale**: FR-010. Observed sandbox endpoint listed status types but **not** `charge.updated`. **Owner 2026-09-18**: agent must PATCH live `we_…` to add `charge.updated` ([update webhook endpoint](https://docs.stripe.com/api/webhook_endpoints/update)).

## R7 — Tests vs listen

- **Decision**: PHPUnit mocks Stripe Event + retrieve; no CLI. Owner UAT: announce listen is required (already running). [Laravel testing](https://laravel.com/docs/13.x/testing).
- **Rationale**: Same as 001.2 FR-003.
