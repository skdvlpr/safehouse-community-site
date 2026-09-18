# Checklist: Owner GA4 + GTM setup (002)

**Purpose:** Revisit Analytics / Tag Manager admin after the site code exists. Not a DPA. Do **not** put `GTM-` / `G-` ids in this file.

**When:** (1) now, while creating accounts; (2) after `/speckit-implement` local UAT; (3) before production publish.

**Official pages:** [GTM container](https://support.google.com/tagmanager/answer/6103696), [GTM consent overview](https://support.google.com/tagmanager/answer/10718549), [Consent mode](https://support.google.com/analytics/answer/9976101) (we use **basic**: no tags until analytics accept — ignore Google’s “load tags in all cases”), [DPT](https://support.google.com/analytics/answer/3379636), [GA4 cookies](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage).

Record: 2026-09-16 (GA4 wizard finished — property Home shows 0, expected).

## Do not do (ever in 002)

- [ ] Paste GTM `<head>` / `noscript` snippets (or the Google tag) into Laravel, CMS, or Caddy
- [ ] Commit container / measurement ids
- [ ] Enable Google Ads / remarketing tags in GTM
- [ ] Turn on **Google products & services** account data sharing
- [ ] Import a CMP from GTM Community Gallery (Cookiebot, iubenda, …) — second banner / extra processor; out of spec 002

## Done in the wizard (2026-09-16)

- [x] GTM **web** container named for `safehouse.community` (id stays with the owner)
- [x] GA4 account sharing: **Google products & services = off**; Modeling, Technical support, Recommendations = on (accepted)
- [x] Business category: Other business activities (ETS / not a shop)
- [x] Business objectives: **Generate leads** + **Understand web and/or app traffic** (not Drive sales)
- [x] Web stream URL `https://safehouse.community`; Enhanced measurement left **on** (page views, scrolls, outbound clicks, …)
- [x] GA4 wizard completed; property **Safe House ETS** Home is open (0 users until the site loads GTM after consent)
- [x] **Redact data:** Email on; URL query keys `donor_name`, `phone`, `email` (GA4 still does not log those query values into page_location). Site/GTM will also strip them at implement.

## Still to do in Google Admin (before or during UAT)

### Tag Manager

- [ ] **Enable consent overview** (Admin → Container Settings) — UI only, not a banner ([consent overview](https://support.google.com/tagmanager/answer/10718549))
- [x] Google tag (named e.g. Safe House GA4) with stream `G-…` — trigger Initialization / All Pages
- [x] **Consent warning on Submit:** Additional Consent Checks = **No additional consent required** on Google tag + four event tags (green *All tags have been configured for consent*, 2026-09-17). Do **not** Get started / import CMP ([GTM consent](https://support.google.com/tagmanager/answer/10718549))
- [x] Four GA4 Event tags + Custom Event triggers: `donate_success`, `donate_recurring_success`, `volunteer_success`, `contact_success` (folders + Measurement ID constant). Confirm **Publish** of that workspace if Submit is still showing changes ([publish](https://support.google.com/tagmanager/answer/6107163))
- [ ] **Submit / Publish** the events version if Workspace still shows unpublished changes (Preview alone is not enough for Realtime on a published site)
- [ ] Add a second GTM admin (organisational Google account)

### Analytics property

- [ ] Copy Measurement ID (`G-…`) into the GTM tag only — not into git
- [ ] Admin → Data collection: **Google signals off**
- [ ] Ads personalization / Google advertising features **off**
- [ ] Do not collect user-provided data
- [ ] Accept **Data Processing Terms** if the UI still asks ([DPT](https://support.google.com/analytics/answer/3379636))
- [ ] Add a second GA4 admin
- [ ] After events fire: mark `donate_success`, `donate_recurring_success`, `volunteer_success`, `contact_success` as **key events** (donors are this, not “Drive sales”)
- [ ] Enhanced measurement gear: keep page view / scroll / outbound; confirm form events do not send field values
- [ ] **PII re-check in DebugView** after implement (redact keys already on; confirm thank-you URLs show `(redacted)` / no name)

## After site code (local, then production)

- [ ] Local `.env` only: `MEASUREMENT_ENABLED=true` + `GTM_CONTAINER_ID` (see [owner-ops](../contracts/owner-ops.md))
- [ ] Essentials-only / dismiss: no `googletagmanager.com`
- [ ] Analytics accepted: hit in GTM Preview or GA4 **Realtime**
- [ ] Four conversion names with consent; none without; honeypot not counted; one-time thank-you ≠ recurring thank-you
- [ ] Production: `.env` + **Caddy CSP** allowlist (owner apply) + cookie/privacy texts synced
- [ ] Ads import of key events = later Grants ops, not this feature
- [ ] Counsel review of IT/EN cookie + privacy **after** owner UAT
