# 032 — specify 001.5 donation settled CRM

**Date**: 2026-09-18

**Feature**: `specs/001.5-donation-settled-crm`

**Input**: Owner `/speckit-specify` interrupt of `002`/`002.1`. Every donation must hit Prima Nota; no write until fee/net exist; thank-you must not stall; cancel/dispute/refund/payout notices must keep updating CRM. Local `stripe listen` stays required.

## Why 001.5

S01 donation → CRM repair (not S03, not `002.2`). Last S01 amendment was `001.4`. Owner interrupt (constitution I). After UAT, return to analytics.

## Research folded in

- Listen **is** running; 502 is the site rejecting `payment_intent.succeeded` when BalanceTransaction is missing (`automatic_async`).
- `charge.updated` is forwarded locally but ignored for ingest; that is the notice Stripe documents for fees ([asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture)).
- Thank-you delay: success page currently waits on the same settlement retry (up to ~10 s). Spec forbids that wait (US2).
- Status path (`applyFromStripeEvent` types: cancel, fail, refund, dispute*, payout.paid, subscription deleted) stays in FR-005/006.

Official this specify: [Payment Element](https://docs.stripe.com/payments/payment-element), [Payment Intents](https://docs.stripe.com/payments/payment-intents), [async capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture), [webhooks](https://docs.stripe.com/webhooks), [event types](https://docs.stripe.com/api/events/types), [CLI](https://docs.stripe.com/stripe-cli).

## Not done

- Plan / tasks / implement
- Git commit / production Dashboard event-type edit / CRM repo writes
- Force-ingest of TEST 5
