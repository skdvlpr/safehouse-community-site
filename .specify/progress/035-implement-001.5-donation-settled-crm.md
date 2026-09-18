# 035 — implement 001.5 donation settled CRM

**Date**: 2026-09-18

**Feature**: `specs/001.5-donation-settled-crm`

**Checklists at start**: `requirements.md` 16/16 PASS. `owner-user-tests.md` written at T014 (owner ticks).

## Models

- Fable 5.1 **Launch**: T003 [3acc4068](3acc4068-b0a3-46b7-ab55-aa153646f520), T004 [1a5d1c7b](1a5d1c7b-1ea2-448d-8d3d-673c30ac005f), T006 [b44a154a](b44a154a-2fdf-4d11-b8f1-f69f96ebf063), T007 [dabdae29](dabdae29-67ba-4a60-affb-48ca7dc25019)
- Rest: this session (Grok extra high)

## Code

- Typed `StripeSettlementNotReadyException`; webhook **200 Pending settlement** (not 502)
- Ingest on `charge.updated` when BT usable + `campaign_id` / `stripe_subscription_id`
- Thank-you: `retrievePaymentIntentRecord` one-shot, no retry loop
- Status `applyFromStripeEvent` unchanged; `charge.updated` not handled there
- `docs/STRIPE-SETUP.md` lists `charge.updated` (live Dashboard = owner-ops)

## Verify

- Pint: PASS (367 files `--test`)
- PHPUnit: full `ddev exec php artisan test` PASS (see artisan output this turn)

## Not done

- Git commit/push
- Live Stripe Dashboard PATCH
- CRM repo
- Owner UAT (Russian script in chat; Cursor-browser offer + wait)
- Return to 002/002.1 after UAT; do not start 003

Official: [async capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture), [webhooks](https://docs.stripe.com/webhooks), [signatures](https://docs.stripe.com/webhooks/signatures), [event types](https://docs.stripe.com/api/events/types), [listen](https://docs.stripe.com/cli/listen), [testing](https://laravel.com/docs/13.x/testing), [logging](https://laravel.com/docs/13.x/logging), [Pint](https://laravel.com/docs/13.x/pint)
