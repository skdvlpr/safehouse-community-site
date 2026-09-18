# Owner UAT — 002 + 002.1 (combined)

Staff dashboard is **analytics.google.com**, not Filament. Container id is entered in **CMS → Impostazioni → Integrazioni → Analytics**, not in git.

Official: [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [Consent mode](https://developers.google.com/tag-platform/security/guides/consent), [GA4 custom events](https://support.google.com/analytics/answer/12229021), [Filament custom pages](https://filamentphp.com/docs/4.x/navigation/custom-pages).

## 002.1 CMS kill-switch

- [x] Super-admin sees Analytics tab on Integrations (Italian label). Stripe/CRM/mail still there. *(owner, prior turn)*
- [x] Toggle **off** (or empty/invalid `GTM-`): public `/it` has no `googletagmanager.com` even after Accetta tutti. *(owner UAT A)*
- [x] Toggle **on** + valid container id → save → hard-refresh: boot node enabled; still no official snippet on first HTML; `gtm.js` only after Accetta tutti. *(2026-09-18 local: `gtm.js` 200 after Accetta tutti)*
- [x] Do not paste Google’s head/noscript snippet. Do not enter a `G-` Measurement ID on this screen.

## 002 Consent / events / copy

Reuse [002 owner-user-tests](../../002-consent-gated-analytics/checklists/owner-user-tests.md) after CMS is on:

- [ ] Essentials / X / close preferences without Save: no GTM; donate/volunteer/contact work.
- [x] Accetta tutti: `gtm.js`; ads consent keys stay denied; no noscript iframe. *(gtm.js 200 observed; ads keys not re-checked this turn)*
- [x] Thank-you `donor_name` stripped from the address bar; DebugView `page_location` clean. *(donor_name gone 2026-09-18; Stripe `payment_intent_client_secret` still present — DebugView not done; follow-up after CRM interrupt)*
- [ ] `donate_success` vs `donate_recurring_success`; volunteer/contact real vs honeypot.
- [ ] Footer cookie preferences reopen; withdraw to essential stops events.
- [ ] Cookie/privacy pages name GA4/Tag Manager; marketing unused; status line matches on/off.

UAT pause 2026-09-18: see [notes/2026-09-18-local-uat.md](../notes/2026-09-18-local-uat.md). Do not treat GA4 Home zeros as a gate failure.

## Still owner-side in Google Admin

See [ga4-gtm-owner-setup.md](../../002-consent-gated-analytics/checklists/ga4-gtm-owner-setup.md): Publish if unpublished; Signals off; ads personalization off; DPT; second admin; do-not (snippets, CMP, Ads tags).

## Production (only when asked)

- [ ] Repeat the CMS save on live Integrations (preview DB ≠ live DB).
- [ ] Live Caddy CSP apply from `deploy/Caddyfile.snippet` (002). This amendment does not reload Caddy.
