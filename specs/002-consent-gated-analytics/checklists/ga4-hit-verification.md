# Checklist: Prove GA4 is receiving hits (002 / 002.1)

**Purpose:** Owner UAT that **this** property actually collects events. A successful Stripe donation or CRM Prima Nota row is **not** a GA4 hit.

**Do not** paste `GTM-` / `G-` ids here or in chat.

Official: [GTM Preview](https://support.google.com/tagmanager/answer/6107056), [GA4 Realtime](https://support.google.com/analytics/answer/9271392), [GA4 DebugView](https://support.google.com/analytics/answer/7201382), [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [Consent mode](https://developers.google.com/tag-platform/security/guides/consent), [GA4 custom events](https://support.google.com/analytics/answer/12229021).

Screenshots of **Home / last 7 days all zeros** and **Landing page / last 28 days** are **processed reports**. They stay empty for **24–48 hours** (often longer on a new property) even when hits work. Do **not** use them for this checklist.

**Google signals** banner (“how to activate Google signals”): leave **off** (002 lock). Dismiss the card; do not turn Signals on.

## Why a donate can succeed and GA4 still looks empty

| What succeeded | What GA4 needs |
| :--- | :--- |
| Payment + Prima Nota | Unrelated. Ledger is Stripe → site → Espo. |
| Thank-you page | Marker `donate_success` / `donate_recurring_success` is pushed to `dataLayer` **only if** CMS measurement is **on**, container id is a valid `GTM-…`, and cookie choice is **Accetta tutti** (`all`). Essentials / X / no banner choice → **no GTM**, no events. |
| GTM Event tags | Workspace must be **Published**. Preview alone does not send to the live property for other browsers. |
| Home / Explorations | Batch reporting. Use Preview → DebugView → Realtime **today**. |

Local DDEV host is `https://safehouse-community-site.ddev.site`. The GA4 **stream URL** can stay `https://safehouse.community` — that does **not** block DDEV hits. GTM Preview **Connect** URL must be the **DDEV** origin, not production, when testing locally.

## A — Local prerequisites (do these first)

- [ ] Ad blockers / Brave shields / uBlock **off** for DDEV (or a clean Chrome profile).
- [ ] CMS `/cms-safehouse` → Impostazioni → Integrazioni → Analytics: **enable** + paste the **web container** id only (`GTM-…`). Save. Hard-refresh a public page.
- [ ] Public HTML contains `id="measurement-boot"` with `data-measurement-enabled="true"`. Cookie banner note is the **bootable** text (not “non attualmente attivi”).
- [ ] Cookie/privacy status line (legal template) says measurement is configured and loads after consent (`site.measurement.status_on`).
- [ ] Tag Manager workspace that contains the Google tag + four event tags is **Published** ([publish](https://support.google.com/tagmanager/answer/6107163)).
- [ ] Do **not** paste GTM snippets into Laravel.

If the boot node is missing or `enabled` is not true, **stop**. Nothing will reach GA4.

## B — Minimal local proof (no donate required)

Use this path **before** conversions. One visit is enough.

1. Tag Manager → **Preview** → Connect to  
   `https://safehouse-community-site.ddev.site/it`  
   ([Preview](https://support.google.com/tagmanager/answer/6107056)).
2. On the DDEV tab: **Accetta tutti** (not Solo necessari, not X).
3. Tag Assistant: `gtm.js` / Container loaded. Google tag (GA4) **fired**. Consent: `analytics_storage` **granted**; ads keys **denied**.
4. Network (that DDEV tab): `googletagmanager.com/gtm.js` **200**; a collect request to `google-analytics.com` or `analytics.google.com` (`/g/collect` or `/g/s/collect`).
5. Keep Preview connected. GA4 → Admin → DebugView ([DebugView](https://support.google.com/analytics/answer/7201382)): pick **this** debug device. Expect `page_view` (and usually `session_start`) within a minute.
6. Reports → **Realtime** ([Realtime](https://support.google.com/analytics/answer/9271392)): 1 user in last 30 minutes **or** event `page_view`. Realtime is best-effort; if DebugView has events and Network has collect, Realtime zeros for a few minutes are not a fail. Home zeros **are** expected.

**Pass local minimum:** B3 + B4 + (B5 **or** B6).

**Fail:** Accetta tutti but no `gtm.js`, or Tag Assistant never Connected to DDEV, or collect never appears. Then GTM id / publish / consent / blocker / CMS toggle — not “donations failed”.

## C — Local conversions (after B passes)

Consent must already be **all** on that browser (or accept again). GTM Preview still connected.

| Step | URL | Expected `dataLayer` / GA4 event | Fake test data (do not use real donors) |
| :--- | :--- | :--- | :--- |
| 1 | Home `/it` after Accetta tutti | `page_view` | — |
| 2 | One-time donate thank-you | **`donate_success` only** | Card `4242…4242`; name `UAT Ga4 Uno`; comment `UAT GA4 one-time`; amount €1–10 test |
| 3 | Recurring-campaign thank-you | **`donate_recurring_success` only** | Same card; name `UAT Ga4 Recurring` |
| 4 | `/it/volunteers` real submit | **`volunteer_success`** | Nome `Uat`, Cognome `Volontario`, email `uat.volontario@example.com`, phone `+390000000000`, message `UAT GA4 volunteer` |
| 5 | `/it/volunteers` honeypot filled | thank-you flash, **no** `volunteer_success` | Same as 4 + hidden website field if shown |
| 6 | Contact real submit | **`contact_success`** | Analogous fake `uat.contatto@example.com` |

Thank-you address bar: no `donor_name` / `phone` / `email` after JS. `payment_intent` / `payment_intent_client_secret` may still be present (known follow-up, not this checklist).

GTM: Custom Event trigger name must **exactly** match `donate_success` etc. (case-sensitive).

## D — Production (only after local B passes **and** the owner asks to turn measurement on)

Prod CMS is a **different** database. Git deploy does **not** enable the toggle.

- [ ] Live CMS Integrations: enable + same `GTM-…`. Do not put ids in git.
- [ ] Confirm live `.env` does not need `MEASUREMENT_ENABLED=true` if the CMS row is on (`'1'`). Leave env false as fallback.
- [ ] Owner/root: apply Caddy public CSP from `deploy/Caddyfile.snippet` ([Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header)). Without it, the browser may block `googletagmanager.com`.
- [ ] Optional: `php artisan site:sync-legal-pages --force` so cookie/privacy name GA4. **Do not** sync while measurement is off if you want the old “not active” copy.
- [ ] Repeat **B** against `https://safehouse.community/it` (Preview Connect = production URL).
- [ ] One homepage visit + Accetta tutti is enough for the first live `page_view`. A live €1 donate is **optional** for GA4 (it is for CRM UAT).

Until D is done, production Realtime **must** stay empty. That is the kill-switch working.

## Owner ticks

- [ ] Local B pass (date: ______)
- [ ] Local C: donate_success (date: ______)
- [ ] Local C: donate_recurring_success (optional same day)
- [ ] Local C: volunteer_success (optional)
- [ ] Production D not started until asked
