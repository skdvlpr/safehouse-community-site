# Owner user tests: CMS captcha settings (001.3) + leftover 001.2 challenge

**Purpose**: Owner accepts combined 001.2 public challenge + 001.3 staff Captcha screen. PHPUnit does not close this handshake.  
**Created**: 2026-09-15  
**Feature**: [spec.md](../spec.md)

**Marker semantics**: `[x]` = Pass (owner). Agent inspect notes below are not owner UAT.

**Closed**: 2026-09-15 — leftover CMS Captcha check passed as 001.4 UAT015; owner accepted full 001.4 UAT. Do **not** re-test 001.3 UAT007/UAT008 “volunteer still stores”: superseded by 001.4 (mail-only, table dropped on preview). `002` unblocked (not started until the owner asks).

## CMS Captcha screen (001.3)

- [ ] UAT001 Local CMS `/cms-safehouse` → Impostazioni → **Captcha** (not inside Sportelli). URL `/cms-safehouse/captcha`
- [ ] UAT002 Screen has: on/off, public site key, secret (masked). Italian CMS copy
- [ ] UAT003 Save with the secret field **blank**: stored secret remains; public volunteer still challenges when toggle is on and both keys exist
- [ ] UAT004 Impostazioni → Sportelli: **no** captcha tab/fields; desks and email templates still save
- [ ] UAT005 Integrazioni helper no longer says captcha lives on Sportelli

## Public challenge (001.2 behaviour, now driven by Captcha screen)

- [ ] UAT006 With challenge **on** and both keys: widget on `/it/contact`, `/en/contact`, `/it/volunteers`, `/en/volunteers`
- [ ] UAT007 Same state: volunteer (and contact) submit without completing the widget stores **zero** new rows
- [ ] UAT008 Challenge **off** (or site key emptied): no widget; a valid volunteer still stores
- [ ] UAT009 Donate, cookie banner, and CMS login have **no** Turnstile widget
- [ ] UAT010 View-source on local contact/volunteer: public site key may appear; secret must not

## Framing and production (no live writes in this feature)

- [ ] UAT011 Agent did **not** paste keys into production CMS, reload production Caddy, or onboard `safehouse.community` as a Cloudflare DNS/proxy zone
- [ ] UAT012 Live `https://safehouse.community/it/contact` still has **no** widget until staff save the same keys on **production** CMS Captcha (preview and live DBs are independent)

## Optional leftover from 001.2 (skip if already accepted)

- [ ] UAT013 English donate empty email/phone copy stays English; Italian stays Italian
- [ ] UAT014 Public `<title>` ends with the translated suffix (`— Safe House` / Italian equivalent)
- [ ] UAT015 Stripe Dashboard live webhook URL was **not** changed in 001.3. CLI `stripe listen` `whsec_` is still local Integrations only, if you use listen

## Notes from agent inspect (2026-09-15)

- Production `https://safehouse.community/it/contact` HTTP 200. **Turnstile widget: no** (`cf-turnstile` / `challenges.cloudflare.com` absent). Inspect-only; no production CMS/`.env`/Caddy/DNS write.
- Public Blade (`contact-form-shell`, `volunteer-form-shell`) prints `siteKey()` only; no `secretKey()` / `secret_key`.
- PHPUnit: 335 passed, 2 skipped. Pint: 358 files PASS. Recorded in `.specify/progress/018-implement-001.3.md`.
- Local Turnstile rows from 2026-09-15 were **not** wiped by implement. Captcha CMS page is the single editor; Sportelli no longer writes `turnstile.*`.
