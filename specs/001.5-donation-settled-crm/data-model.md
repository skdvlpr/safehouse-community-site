# Data model: Donation ledger only after settlement (001.5)

No new MariaDB tables. Ledger remains Espo **PrimaNota** via the existing site API client.

## Donation payment (Stripe)

| Field | Role |
| :--- | :--- |
| PaymentIntent id | Idempotency key (`donationPaymentReference` `#pi_…`) |
| `metadata.campaign_id` | Site-originated one-time donation |
| `metadata.stripe_subscription_id` / invoice PI | Existing recurring path |
| Charge id | Status + payout matching |
| BalanceTransaction fee/net | **Required** before first income write |

## Settlement snapshot

Must be present before `DonationIngestService::ingest` create:

- gross (amount received)
- fee (BT `fee`, not invented 0)
- net (BT `net`)
- currency
- BT id / charge id for enrichment (existing fields)

State: **missing** → no row; **present** → create or idempotent update.

## Prima Nota income (unchanged shape)

Existing create payload: Income, Donation classification, gross/fee/net, donor snapshot, Planned until payout-paid.

Payment status enum (CRM, no schema change): Planned · Inviato · Cancelled · Refunded · Disputed · Problematic.

## Status notices (unchanged mapping)

| Stripe type | Status |
| :--- | :--- |
| `payment_intent.canceled`, `customer.subscription.deleted` | Cancelled |
| `payment_intent.payment_failed`, `invoice.payment_failed` | Problematic |
| `charge.refunded` | Refunded |
| `charge.dispute.created` / `updated` / `funds_withdrawn` | Disputed |
| `charge.dispute.closed` (won / warning_closed) | Planned |
| `charge.dispute.closed` (lost) / default | Disputed |
| `charge.dispute.funds_reinstated` | Planned |
| `payout.paid` (automatic) | Inviato when charge/BT matches Planned Stripe rows |

These run **before** settlement ingest. `charge.updated` is **not** a status-handled type.

## Validation / invariants

- One PI → at most one income row (existing lock + reference lookup).
- No create when BT missing.
- No create when PI is not a site donation (no campaign/subscription metadata).
- Import online payments remains the same ingest with `bulkSkipForceResync` as today.
