# HTTP contracts: Donation ledger only after settlement (001.5)

Cite: [Webhooks](https://docs.stripe.com/webhooks) · [Signatures](https://docs.stripe.com/webhooks/signatures) · [Asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture) · [Laravel testing](https://laravel.com/docs/13.x/testing).

## `POST /api/webhooks/stripe`

Pipeline (unchanged order, new ingest types / statuses):

1. Verify signature → fail **400** `Invalid signature` (`001.2`).
2. `applyFromStripeEvent` → if handled, **200** `OK` (status only).
3. Resolve PaymentIntent id for ingest.
4. If settlement not ready → **200** `Pending settlement` (no Prima Nota create).
5. If ingest succeeds → **200** `OK`.
6. If Espo/CRM ingest throws → **502** `CRM ingest failed`.
7. Unsupported currency → **200** skip (as today).
8. Unknown type / no PI → **200** `Ignored`.

### Ingest-capable types (after step 2)

| Event | Resolve PI from | Persist income |
| :--- | :--- | :--- |
| `payment_intent.succeeded` | `data.object.id` | Only if usable BT |
| `invoice.paid` | existing invoice → PI helper | Only if usable BT |
| `charge.updated` | `data.object.payment_intent` | Only if usable BT **and** site donation metadata |

`charge.succeeded`, `payment_intent.created`, `mandate.updated`: Ignored (200) unless they fall into status handling (they do not today).

### Status-only types (step 2 `handled`)

Must still return 200 and must not enter ingest: `payment_intent.canceled`, `payment_intent.payment_failed`, `invoice.payment_failed`, `charge.refunded`, `charge.dispute.*` (created, updated, closed, funds_withdrawn, funds_reinstated), `customer.subscription.deleted`, `payout.paid`.

Bodies MUST NOT include Stripe exception text or API keys.

## `GET /{locale}/donations/{campaignSlug}/thank-you`

Query `payment_intent` required (else redirect as today).

- Render thank-you **without** waiting on a settlement retry loop.
- Optional one-shot ingest if BT already usable; failure/pending MUST NOT change HTTP 200 HTML success.
- Do not surface CRM/Stripe internals to the donor.

## CSRF

Except path remains `api/webhooks/stripe` (`001.2`).
