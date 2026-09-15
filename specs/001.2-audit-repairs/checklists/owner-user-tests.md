# Owner user tests: Audit repairs (001.2)

**Purpose**: Owner accepts 001.2. PHPUnit does not close this handshake.  
**Created**: 2026-09-15  
**Feature**: [spec.md](../spec.md)

**Marker semantics**: `[x]` = Pass (owner). Agent may note inspect-only facts below; do not treat those as owner UAT.

## Payment notifications

- [ ] UAT001 On local DDEV, a POST to `/api/webhooks/stripe` with a missing or wrong signature returns **400**, not 500 (PHPUnit covers this; optional `stripe listen` is Skip until you ask)
- [ ] UAT002 Live Stripe Dashboard webhook URL was **not** changed in this feature

## Donate copy

- [ ] UAT003 Open an English donate campaign URL (`/en/donations/…`). Empty email+phone: help/validation are English
- [ ] UAT004 Same campaign in Italian (`/it/donations/…`): copy stays Italian
- [ ] UAT005 Force a checkout failure in local mock mode: on-page JSON error is a generic translated sentence, not Stripe/CRM internals

## Volunteer challenge

- [ ] UAT006 CMS Turnstile **on**: volunteer submit without the widget token does not store a row
- [ ] UAT007 CMS Turnstile **off**: a valid volunteer form still stores as today

## Framing and production

- [ ] UAT008 Confirm the agent did **not** reload production Caddy / apply HSTS/CSP
- [ ] UAT009 Public page `<title>` ends with the translated suffix (`— Safe House`)

## Notes from agent inspect (2026-09-15)

- Production `https://safehouse.community/it/contact` HTTP 200. **Turnstile widget: no** (`cf-turnstile` / `challenges.cloudflare.com` absent in HTML). Same as 2026-09-13.
- Production response `X-Frame-Options: DENY` (PHP). No `Content-Security-Policy` / `Strict-Transport-Security` on that response. Repo snippet now also `DENY`; live Caddy still owner-ops.
- PHPUnit: 333 passed, 2 skipped. Pint: PASS.
- `stripe listen` was **not** started during implement. Owner later ran `stripe listen --forward-to https://safehouse-community-site.ddev.site/api/webhooks/stripe` (CLI `whsec_` still needs to be stored in **local** CMS Integrations; production Dashboard webhook URL unchanged).
- 2026-09-15 owner ops (local only): Cloudflare Turnstile widget **Safe House public forms** created via API ([create widget](https://developers.cloudflare.com/api/resources/turnstile/subresources/widgets/methods/create/)). Hostnames: `safehouse.community`, `safehouse-community-site.ddev.site`. Mode: managed. Sitekey (public): `0x4AAAAAAE1r96-62y0XWJ1P`. Secret stored in **local** CMS `turnstile.secret_key` (encrypted), toggle `turnstile.enabled=1`. Production CMS / Caddy / DNS **not** written. Local `/it/contact` and `/it/volunteers` HTML now include the widget. Owner did **not** onboard the domain as a Cloudflare zone (not required for Turnstile). Follow-up after this UAT: optional `001.3` to move the existing Sportelli captcha fields onto an Integrations-style page — not `002`.
