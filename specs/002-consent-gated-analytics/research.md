# Research: Consent-gated audience measurement (002)

**Date**: 2026-09-16

## Product and load path

- **Decision**: Google Analytics 4 is the staff dashboard. The public site loads **one** Google Tag Manager web container after analytics consent. GA4 Configuration / events live **inside** that container (owner-ops). Site JS does not also embed gtag.js as a second product ([GTM web](https://developers.google.com/tag-platform/tag-manager/web), [GTM account/container](https://support.google.com/tagmanager/answer/6103696)).
- **Rationale**: Spec lock 2026-09-16. Ad Grants can later import GA4 key events ([Ad Grants conversions](https://support.google.com/grants/answer/9841491)) without a second on-site tracker in 002.
- **Alternatives considered**: gtag-only (same law, worse later Grants/ops). Plausible/Matomo (still need Google for Grants). Dual Plausible+GA4 (two processors). Server-side GTM (new infra, YAGNI).

## Basic consent, not advanced pings

- **Decision**: **Do not** inject the GTM snippet (nor the noscript iframe) until the stored choice is analytics-accepted **and** `config('measurement.enabled')` is true **and** a valid `GTM-…` id is present. Before inject, set consent defaults to denied for `ad_storage`, `ad_user_data`, `ad_personalization`, and `analytics_storage`; on analytics accept, grant **only** `analytics_storage` ([Consent mode](https://developers.google.com/tag-platform/security/guides/consent)). On withdraw: update those keys to denied and stop pushing events; do not reload GTM for advertising.
- **Rationale**: Spec FR-001–FR-003, FR-008. Advanced consent mode still contacts Google before a choice; Garante requires default deny for non-technical trackers ([FAQ cookie](https://www.garanteprivacy.it/faq/cookie) Q7). Italian cookie rules do not treat this GA4 class as FAQ 4 exemption.
- **Alternatives considered**: Always-load GTM with denied defaults (advanced) — extra contact before consent. Cookie wall — forbidden (FAQ Q14). Scroll-consent — forbidden (FAQ Q13).

## Kill-switch and secrets

- **Decision**: `config/measurement.php` from env: `MEASUREMENT_ENABLED` (default false), `GTM_CONTAINER_ID` (empty). Enabled without a matching `^GTM-[A-Z0-9]+$` id behaves as off. IDs never in git ([Laravel configuration](https://laravel.com/docs/13.x/configuration)). phpunit uses `Config::set` in tests, not a real id in `phpunit.xml`.
- **Rationale**: FR-007, constitution VI.
- **Alternatives considered**: Hardcode a test GTM id in the repo (secret-shaped, useless). CMS field for the container id (YAGNI; env is enough).

## Preview and CMS

- **Decision**: Measurement boot is skipped on Filament (`/cms-safehouse` uses its own layout) and on public preview routes already flagged in `layouts/app.blade.php` (`pages.preview`, `articles.preview`, `editorial-articles.preview`).
- **Rationale**: Spec edge “preview must not pollute production reports”.
- **Alternatives considered**: robots noindex only (still would send hits).

## Conversion events

- **Decision**: Custom events `donate_success` (one-time thank-you), `donate_recurring_success` (recurring campaign thank-you), `volunteer_success`, `contact_success` ([GA4 custom events](https://support.google.com/analytics/answer/12229021)). Payload = event name only. No names, emails, phones, messages, amounts, payment ids. Donate marker from campaign `allows_recurring`; never both donate names on one view. Later Stripe subscription invoices without a new thank-you stay CRM-only. Volunteer/contact: marker only when session flash is **real** success — honeypot keeps the fake visitor success flash but MUST NOT set the measurement marker.
- **Rationale**: FR-006; owner 2026-09-16 must split site one-time vs recurring in Events. Two names beat a `donation_kind` parameter (needs a custom dimension) and beat URL-only filtering after query redaction. Honeypot currently shares volunteer/contact success flashes; counting those would invent conversions.
- **Alternatives considered**: One `donate_success` + page path (fragile if slug changes). Parameter `donation_kind` without amount. Recommended `purchase` with `value` (amount; rejected). Ads website tag (FR-013). Fire on every thank-you query including `donor_name` (PII leak).

## Cookie banner UX

- **Decision**: Keep one banner (accept all / essentials / preferences). Add an explicit dismiss control that stores **essential** (Garante FAQ Q7–Q8: closing keeps default). Preferences Close without Save does not accept analytics. Footer (and cookie-policy copy) gets a durable **reopen preferences** button ([FAQ Q9](https://www.garanteprivacy.it/faq/cookie)). Do not re-show the first-visit banner on every page once a choice exists (FAQ Q10).
- **Rationale**: FR-002, FR-004. Today `cookie-consent.js` hides forever with no reopen.
- **Alternatives considered**: Second CMP product. Marketing category (no ads tags in 002).

## Cookie and privacy texts (after the gate works)

- **Decision**: **Last implement slice**, after measurement can actually inject. Update `lang/*/site.php` banner strings from config (enabled vs globally off). Update `LegalPagesContent` Italian and English operational bodies: name Google Analytics 4 and Google Tag Manager, purpose (aggregated statistics + conversion events), consent gate, no raw IP on association servers, GA4 cookies `_ga` and `_ga_*` ([cookie usage](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage)), Google as recipient for this purpose, DPF/adequacy mentioned as **operational fact** not a contract. Add a Blade status line on legal cookie/privacy templates from config (live vs not loaded) so SC-004 is not a stale CMS paragraph. Sync with `php artisan site:sync-legal-pages --force` on preview ([Filament resources](https://filamentphp.com/docs/4.x/resources/overview) remain the editor). Do not publish `/ru`. Do not write DPA/SCC text. After owner UAT, owner shows IT+EN pages to counsel; corrections are a later spec.
- **Rationale**: Spec US5 / FR-009; owner 2026-09-16 “include cookie/privacy in the plan”; `S-GDPR`.
- **Alternatives considered**: Leave F-019 entirely to 006 (owner overrode). Write a lawyer DPA in implement (forbidden). Two full CMS bodies toggled by env (fragile; status line + honest “only after consent / off when switch is off” prose is enough).

## CSP / Caddy

- **Decision**: Document required public CSP hosts in `deploy/Caddyfile.snippet` and [owner-ops](./contracts/owner-ops.md): at least `https://www.googletagmanager.com`, `https://www.google-analytics.com`, `https://*.google-analytics.com`, `https://*.analytics.google.com` for script/img/connect as Google documents ([EU-focused data](https://support.google.com/analytics/answer/12017362), [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header)). **Do not** reload production Caddy in this feature unless the owner asks that turn. Local DDEV has no this CSP today — network UAT still works locally.
- **Rationale**: FR-011, `S-CSP`, constitution VI.
- **Alternatives considered**: PHP CSP middleware (forbidden). Apply Caddy from implement (forbidden unless asked).

## Owner GA4 admin

- **Decision**: Owner creates GA4 property + GTM web container; accepts Ads Data Processing Terms if the UI asks ([DPT](https://support.google.com/analytics/answer/3379636)); Google signals, ads personalization, and Google products-and-services sharing **off**; do not send user-provided data. Mark the three custom events as key events in GA4 when ready for Grants import (Ads UI import is ops, not 004 pixels).
- **Rationale**: Spec assumptions; Google’s EU IP statement is **their** processing, not a reason to skip consent ([EU IP](https://support.google.com/analytics/answer/12017362)).
- **Alternatives considered**: Agent logs into Google as the owner (no).

## Tests

- **Decision**: Feature tests ([Laravel testing](https://laravel.com/docs/13.x/testing)): kill-switch omits boot id; invalid/empty id omits inject hooks; one-time thank-you HTML has `data-measurement-event="donate_success"` (not recurring); recurring thank-you has `donate_recurring_success`; neither marker contains donor name; volunteer/contact honeypot success flash **without** measurement marker; real volunteer/contact success **with** marker; home still renders impact stats; cookie banner has dismiss + footer reopen; after LegalPagesContent sync, IT/EN cookie/privacy contain product names and do not claim “analytics not in use” while a test enables measurement. No CSS snapshots. No live Google calls. Pint on PHP ([Pint](https://laravel.com/docs/13.x/pint)).
- **Rationale**: Constitution V — consent gating and PII-on-events are real bugs. GA4 UI is owner UAT.
- **Alternatives considered**: Dusk for GTM inject (not in stack). Coverage %.

## Out of scope (confirmed)

- Google Ads / remarketing / conversion linker pixels (004/006 later)
- Linking GA4 to Ads (owner/ops)
- DPA, DPIA, counsel-authored clauses
- Applying production Caddy/HSTS
- Specs 003 SEO, 004 landings, 005 banners, 006 leftover GDPR inventory
- Public Russian locale
- Custom in-app analytics dashboard
