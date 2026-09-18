# Owner UAT — consent-gated audience measurement (002)

Staff dashboard is **analytics.google.com**, not Filament. Skip this file until local `.env` has `MEASUREMENT_ENABLED=true` and a real `GTM_CONTAINER_ID`. Do not paste container or Measurement IDs into git.

Official references: [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [Consent mode](https://developers.google.com/tag-platform/security/guides/consent), [GA4 custom events](https://support.google.com/analytics/answer/12229021).

## Kill-switch

- [ ] With `MEASUREMENT_ENABLED=false` (or empty/invalid `GTM_CONTAINER_ID`), even after Accept all, the Network panel has no `googletagmanager.com` / `gtm.js`.
- [ ] CMS `/cms-safehouse` never loads GTM.

## First visit / essentials

- [ ] No stored choice: banner visible; no GTM until Accept all (or analytics saved in Preferences).
- [ ] Essential only, dismiss/X, or Close preferences without Save: site works (donate / volunteer / contact); no GTM.
- [ ] Scroll does not store a choice.

## Analytics accepted (bootable)

- [x] After Accept all: `gtm.js` loads; Consent mode grants **only** `analytics_storage`; ads keys stay denied; **no** noscript iframe. *(2026-09-18: `gtm.js` 200; ads keys / noscript not re-dumped)*
- [ ] GA4 Realtime / Pages shows the visit (wait a minute; ad blockers off). *(Home last-7-days zeros are not this check. Resume after CRM interrupt.)*
- [x] Thank-you `?donor_name=` is stripped from the address bar after inject; DebugView `page_location` has no `donor_name` / `phone` / `email`. *(donor_name stripped; `payment_intent_client_secret` still in URL; DebugView pending)*

## Conversions (one name, no PII)

- [ ] One-time campaign thank-you → `donate_success` only.
- [ ] Recurring-campaign thank-you → `donate_recurring_success` only (not later Stripe invoices).
- [ ] Real volunteer mail success → `volunteer_success`; honeypot success message **without** that event.
- [ ] Real contact store → `contact_success`; honeypot without that event.
- [ ] Events have **no** donor name, email, amount, or payment id.

## Reopen / withdraw

- [ ] Footer “cookie preferences” reopens the **same** panel (first-visit card stays hidden until then).
- [ ] Switching all → essential stops further events in that visit.

## Copy / privacy

- [ ] Cookie and privacy pages name Google Analytics 4 / Tag Manager; marketing/ads still unused.
- [ ] Status line matches kill-switch (configured vs not loaded).
- [ ] No raw visitor IP in CMS consent audit (hashes only).

## Still owner-side (not this implement)

- [ ] Signals off; ads personalization off; Google products & services sharing off as in `ga4-gtm-owner-setup.md`.
- [ ] DPT acknowledged; second GA4/GTM admin; Publish if the workspace is unpublished.
- [ ] Production `.env` + Caddy CSP apply only when asked.
- [ ] Counsel review of IT+EN legal HTML after this UAT — not a DPA drafted by implement.
