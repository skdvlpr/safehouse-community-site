# 031 — local UAT notes (002/002.1) + Stripe→site→CRM research

**Date**: 2026-09-18

**Active spec (unchanged)**: `specs/002.1-cms-measurement-settings` (analytics UAT paused; CRM interrupt is research-only — no new spec this turn).

**Owner**: started real local UAT; asked to record analytics answers, then investigate why site donations no longer appear in CRM PrimaNota unless Import online payments is used. No code fix, no git, no production apply.

Official pages opened this turn:

- [GTM consent mode](https://developers.google.com/tag-platform/security/guides/consent)
- [GA4 EU IP / location-device](https://support.google.com/analytics/answer/12017362)
- [Google signals](https://support.google.com/analytics/answer/9445345)
- [Garante cookie guidelines 2021](https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9677876)
- [Stripe confirmPayment / return_url](https://docs.stripe.com/js/payment_intents/confirm_payment)
- [PaymentIntent client_secret handling](https://docs.stripe.com/api/payment_intents/object#payment_intent_object-client_secret)
- [Payment Element](https://docs.stripe.com/payments/payment-element)
- [Laravel logging](https://laravel.com/docs/13.x/logging)
- [Turnstile testing](https://developers.cloudflare.com/turnstile/troubleshooting/testing/)

## Analytics UAT (local, measurement ON)

Observed (owner screenshots + code):

- Contact page before Accept: Turnstile widget visible; Network `challenges.cloudflare.com` **401** is the captcha challenge platform, not GA4. Not a GTM failure.
- After Accetta tutti: `gtm.js` **200**. Gate works.
- Thank-you heading named the donor; address bar had Stripe `payment_intent` + `payment_intent_client_secret` + `redirect_status`, **no** `donor_name`. That is expected: Blade reads `donor_name` then `measurement.js` strips PII query keys (`donor_name`, `phone`, `email`). Stripe appends the PI client secret on redirect ([confirmPayment](https://docs.stripe.com/js/payment_intents/confirm_payment)). It is **not** `sk_test` / `sk_live`. Stripe still says the client secret must not be stored or logged ([client_secret](https://docs.stripe.com/api/payment_intents/object#payment_intent_object-client_secret)). Follow-up (not this turn): also strip `payment_intent_client_secret` from `page_location`.
- GA4 **Home / last 7 days all zeros** is not proof of no hits. Home is processed reporting (often 24–48 h). Next check: Reports → Realtime, Admin → DebugView, GTM Preview, Network `google-analytics.com/g/collect` or `analytics.google.com/g/s/collect`.

Banner copy vs what GA4 actually measures:

- The short banner line is a summary. After consent, GA4 (Enhanced measurement + default dimensions) also records engagement time, pages, coarse geo (country/region/city from IP lookup; EU IP not stored — [answer 12017362](https://support.google.com/analytics/answer/12017362)), device/browser. Spec 002 US2 already asked for that. Policy HTML can name it later (US5 still last).
- **Cannot** reclassify geo/device/time-on-page as “necessary” under locked 002 + Garante 2021 cookie guidelines. Those cookies/scripts are audience measurement, not strictly needed to serve the page. Necessary remains session, security, consent memory, checkout.
- Google-account “who visits us” profiling = **Google signals** ([answer 9445345](https://support.google.com/analytics/answer/9445345)). Locked **off** in 002. Not anonymous necessary stats. Later spec + counsel only.

Analytics work **paused** until the Stripe→CRM interrupt is specified and closed.

## Stripe → site → CRM (research only)

Architecture (two directions, two secrets):

| Direction | Auth | What it does |
| :--- | :--- | :--- |
| Site → CRM | Espo **API key** (`espocrm.api_key`) + assigned user | Webhook + thank-you ingest writes PrimaNota |
| CRM → site | Shared **`crm.sync_token`** header `X-Safehouse-Sync-Token` | CRM “Import online payments” / refresh-from-Stripe. CRM holds site URL + token; Stripe secrets stay on the site |

`crm.sync_token` is **not** a Stripe webhook secret and **not** the Espo API user. Import works locally ⇒ CRM `safehouseDonationSiteUrl` + `safehouseCrmSyncToken` already match the site token (CMS row or `CRM_SYNC_TOKEN` env). If the owner forgot the value: Reveal in CMS Integrations, or mint a new random string on **both** sides. Do not paste it in chat.

Local probe 2026-09-18 (no secret values):

- Espo base URL in CMS: `https://nonprofit-espocrm.ddev.site`
- Espo API key present; `App/user` now **200** as API user `site_safehouse.community`
- Assigned user id in CMS is a **stale id** that Espo says does not exist; ingest falls back to the API user (warning spam)
- `crm.sync_token` resolved from **env** (no CMS row)
- Stripe mock **off**; webhook secret present (listen or Dashboard)

Owner sandbox PaymentIntent from the thank-you URL (test mode, account Safe House): **succeeded**; later metadata had a PrimaNota id — created by **bulk pull**, not by the first ingest.

Laravel log (same PI, 08:39 UTC):

1. Thank-you + webhook ingest failed: `EspoCRM API error` **HTTP 401** empty body (`EspoCrmClient::search` PrimaNota). Same 401 pattern all morning on App/user, fundraising, totals.
2. ~08:48 UTC Import online payments used the same ingest path and **created** rows (restore-miss then create). API auth was working by then.
3. A later PI failed webhook with `BalanceTransaction not ready after 20 attempt(s)` — `retrieveSettledPaymentIntent` refuses to persist without Stripe fee SoT. Default capture is `automatic_async`; BT can lag past ~10 s. Import later succeeds because BT exists.

So “donations no longer save unless I import” is **not** a missing sync-token hook. Automatic path died this morning on **401 to Espo**, and can also die on **BT race**. Import is a later pull that retries the same writer once CRM answers 200 and BT exists.

No CRM repo writes. No spec opened. Stripe MCP: sandbox account listed; PI retrieved in **test** mode only.

## Follow-up 2026-09-18 (owner TEST 4 + sync-token UI)

- Thank-you Network document request **does** include `donor_name` before `history.replaceState` (owner screenshot). Strip is working.
- TEST 4 (`pi_3UGyDK…`, €12, comment TEST 4) thank-you ingest failed at 09:38 UTC: **BalanceTransaction not ready** after 20 retries. API 401 was **not** the reason this time. Dashboard webhook `we_…` points at **production** `https://safehouse.community/api/webhooks/stripe` and is **disabled** — MCP resend cannot hit DDEV. No `stripe listen` running.
- Replay of the same thank-you ingest at 09:45 UTC (BT already present, Espo API 200) **created** PrimaNota gross=12. Assigned-user fallback warning remains (stale CMS id).
- Sync token: not on the Role screen. CMS password field is **always empty on load** (encrypted secrets not echoed — Filament `password`/`revealable`). CRM has **no Settings field** for `safehouseCrmSyncToken` (config-only). Import works because the pair already matches. Rotate = new random string in CMS Integrations **and** CRM config; do not paste the value in chat.

Official this follow-up: [Stripe CLI](https://docs.stripe.com/stripe-cli), [events resend](https://docs.stripe.com/cli/events/resend), [webhook endpoints](https://docs.stripe.com/api/webhook_endpoints), [Filament text input password/revealable](https://filamentphp.com/docs/4.x/forms/text-input).

## Follow-up 2026-09-18 12:20 — listen is running; TEST 5 not force-ingested

Owner screenshot: `stripe listen --forward-to https://safehouse-community-site.ddev.site/api/webhooks/stripe` is active. Signature 200s prove the CLI `whsec_` matches the site. Listen is **required** locally and is not the bug.

Listen pattern: `payment_intent.succeeded` → **502** `CRM ingest failed` / `BalanceTransaction not ready`; later `charge.updated` → **200** because the site **ignores** that type (`paymentIntentIdFromWebhookEvent` only maps `payment_intent.succeeded` + `invoice.paid`). Stripe docs: with default `capture_method=automatic_async`, BT is null on PI.succeeded; fee arrives on `charge.updated` ([asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture)).

TEST 5 (`pi_3UGysM…`, comment TEST 5, €10, `automatic_async`): thank-you + webhook both failed BT-not-ready at 10:19 UTC. **Not** force-ingested. No `crm_prima_nota_id` on the PI. BT exists now — natural retry must be **resend `payment_intent.succeeded`**, not `charge.updated`.

## Next

Owner orders `/speckit-specify` for the Stripe→site→CRM interrupt (dotted amendment of donations ingest, not 003). Then plan → tasks → implement. Then return to 002/002.1 UAT (Realtime/DebugView, four events, remaining Google Admin).
