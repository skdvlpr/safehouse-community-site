# Owner-ops: Donation ledger only after settlement (001.5)

Cite: [CLI listen](https://docs.stripe.com/cli/listen) · [Event types](https://docs.stripe.com/api/events/types) · [Asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture).

## Local preview (required for automatic CRM UAT)

Keep:

```bash
stripe listen --forward-to https://safehouse-community-site.ddev.site/api/webhooks/stripe
```

CLI signing secret (`whsec_…`) must match **local** CMS Integrations / `.env` webhook secret. Do not paste the secret in chat.

PHPUnit does **not** need listen. Sandbox donate UAT **does**.

Do not change the **live** webhook URL while testing locally.

## Live Dashboard event allowlist (applied 2026-09-18)

Live endpoint `we_1Tokof…` (`https://safehouse.community/api/webhooks/stripe`) is **enabled**. Owner asked the agent to PATCH it: **`charge.updated` added** via [update webhook endpoint](https://docs.stripe.com/api/webhook_endpoints/update). Do **not** change the URL. Test-mode endpoint to the same URL stays **disabled** (live keys on production).

Minimum types (union of ingest + status) — now on live:

**Ingest / settlement**

- `payment_intent.succeeded`
- `invoice.paid`
- `charge.updated` ← add if missing

**Status (keep)**

- `payment_intent.canceled`
- `payment_intent.payment_failed`
- `invoice.payment_failed`
- `customer.subscription.deleted`
- `charge.refunded`
- `charge.dispute.created`
- `charge.dispute.updated`
- `charge.dispute.closed`
- `charge.dispute.funds_withdrawn`
- `charge.dispute.funds_reinstated`
- `payout.paid`

CLI listen already forwards all snapshot events (`[*]`).

## Production Caddy / CMS / CRM repo

Unchanged. No Caddy reload, no CRM git writes, no analytics.

## After implement

Owner UAT in Russian (implementer prints checklist). Cursor-browser: offer, wait. Then return to `002`/`002.1` analytics UAT.
