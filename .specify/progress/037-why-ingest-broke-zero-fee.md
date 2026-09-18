# 037 — why CRM ingest broke; 0-fee vs missing charge.updated

**Date**: 2026-09-18

Owner UAT TEST 7 reached CRM; refund status applied. Questions: historical breakage, 0 commission, “only charge.updated”.

## Not “only charge.updated”

Ingest when **BalanceTransaction exists** (`fee` field set, including **0**). Triggers: `payment_intent.succeeded`, `invoice.paid`, **and** `charge.updated`. Status events (refund/dispute/…) already apply immediately.

## Why it used to “just work”

Latest Stripe API default `capture_method=automatic_async` ([async capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture)): BT is **null** on `payment_intent.succeeded` / `charge.succeeded`. Fee arrives when BT is created (`charge.updated`, SLA up to 1 hour).

Old site: ingest **only** on succeeded/invoice.paid; ignore `charge.updated`; retry retrieve ~10 s then **502**. Dashboard retries 5xx for days ([webhooks](https://docs.stripe.com/webhooks)) → later succeeded retry saw BT → CRM with real fee. CLI listen does **not** retry like Dashboard. Thank-you also blocked on the same 10 s loop.

When BT lagged past 10 s (Link + async): listen 502 + Ignored `charge.updated` → only Import. TEST 6 extra 502: two Espo Contacts, not missing fee.

## 0 fee vs missing BT

Stripe BT `fee` is an integer and **can be 0** ([BT object](https://docs.stripe.com/api/balance_transactions/object)). `isset(fee)` is true for 0 → we **do** write. Missing object (`balance_transaction: null`) is not “0%”. TEST 7 still had fee 58 / net 942 on €10.

## If charge.updated never arrives

Stripe’s documented path is still to emit it within the SLA. If our **live** endpoint omits the type, listen-off preview, or CRM 502: Import (Stripe API later) remains catch-up. Do not invent fee.

Official this note: async capture, BT object, webhooks.
