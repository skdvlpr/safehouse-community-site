# Feature Specification: Consent-gated audience measurement

**Feature Branch**: `002-consent-gated-analytics`

**Created**: 2026-09-13

**Status**: Draft

**Queue**: S02a (split from constitution S02 — owner allowed split on 2026-09-13)

**Depends on**: S01 owner UAT complete (closed 2026-09-16). Must not start implement while a `001.K` UAT is open.

**Input**: Staff need aggregated visits, pages, devices, geography where the product provides it, traffic sources, engagement time, and a small set of conversion events, using a ready-made measurement product (not a custom analytics engine). Visitors who need the site must get it with **necessary tools only**, without a prior optional-consent click. Anything that Italian ePrivacy/cookie rules treat as non-technical (audience measurement of this product class) MUST wait for an **explicit** first-visit choice. The association MUST NOT store visitor IP or user-agent in the clear for measurement. Cookie banner copy, cookie policy, and privacy policy MUST be updated **after** the gated measurement actually works, so public texts describe live behaviour. CRM remains the system for cases and donations accounting, not for public-web traffic. Lawyer-grade DPA/DPIA wording stays out of this feature (`S-GDPR`).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Necessary tools run; optional measurement waits for an explicit first-visit choice (Priority: P1)

A first-time visitor sees a cookie choice on the first public page (home or any other). Necessary tools (session, security, remembering the cookie choice itself, and payment checkout when the visitor actually donates) run without an extra click. Analytics / audience-measurement tools stay off until the visitor clearly accepts that category (accept all, or preferences with analytics on). Closing the first-visit choice without accepting analytics MUST leave only necessary tools. Scrolling the page MUST NOT count as consent. Refusing analytics MUST still allow reading the site, donating, volunteering, and contacting.

**Why this priority**: Italian cookie rules require prior informed consent for this class of measurement, and they require a usable site without that consent. The owner asked for exactly this split: explicit consent for what needs it; the rest automatic.

**Independent Test**: With no stored choice, network tools show no measurement-product requests. After essentials-only (or dismiss without accept), the same. After accepting analytics, measurement requests may appear. Donate and volunteer still work on essentials-only.

**Acceptance Scenarios**:

1. **Given** no stored choice, **When** the visitor picks essentials only, **Then** public pages and donate/volunteer/contact journeys work and no measurement product loads.
2. **Given** no stored choice, **When** the visitor accepts analytics (accept all, or save preferences with analytics on), **Then** measurement may load on subsequent views in that visit.
3. **Given** no stored choice, **When** the visitor dismisses the first-visit choice without accepting analytics, **Then** only necessary tools run and measurement stays off.
4. **Given** the first-visit choice is visible, **When** the visitor only scrolls, **Then** that MUST NOT turn analytics on.

---

### User Story 2 - Staff can read aggregated traffic without building a custom dashboard (Priority: P1)

A designated staff member (and optionally an ads contractor as viewer) can open the locked measurement product's dashboard and see visits, unique visitors, pages, devices, country/city if the product provides them, sources, and session/engagement time. They MUST NOT be given a list of raw visitor IP addresses from this site's systems.

**Why this priority**: This is the reason to add measurement. Building charts in-house is out of scope.

**Independent Test**: After test traffic with analytics consent, staff can point to those numbers in the product dashboard (or an export). Association consent/audit records for the same visits still show hashed identifiers only.

**Acceptance Scenarios**:

1. **Given** consented test visits to home and donate, **When** staff open the dashboard within the product's normal delay, **Then** those pages appear in page reports.
2. **Given** measurement disabled by configuration, **When** anyone visits, **Then** no measurement scripts load even if cookie consent is "accept analytics".
3. **Given** consented traffic, **When** staff inspect association-side consent audit, **Then** they do not see raw IP or raw user-agent.

---

### User Story 3 - Conversion events for donate, volunteer, contact (Priority: P1)

Staff can see that a donation thank-you, a successful volunteer application, and a successful contact/desk message occurred, without sending names, emails, phones, messages, or donation amounts into the measurement product. One-time and recurring **site** checkouts MUST be two different event names so the Events report splits them (later Stripe invoices for an existing subscription are CRM, not this event).

**Why this priority**: Later Ad Grants work needs meaningful conversions. Events must exist here so ads landings (004) do not invent a second tracker. Google Ad Grants documents importing measurement key events as the preferred conversion path.

**Independent Test**: Complete each journey with analytics consent; dashboard shows the four conversion names and no personal fields in event payloads. Repeat without analytics consent: events are not sent.

**Acceptance Scenarios**:

1. **Given** analytics consent and a completed **one-time** donation thank-you, **When** staff check conversions, **Then** `donate_success` exists without donor identity or amount, and `donate_recurring_success` is absent.
2. **Given** analytics consent and a completed **recurring** (site signup) thank-you, **When** staff check conversions, **Then** `donate_recurring_success` exists without donor identity or amount, and `donate_success` is absent.
3. **Given** essentials-only consent and either thank-you, **When** staff check, **Then** neither donate measurement event is sent.
4. **Given** analytics consent and a successful volunteer or contact submit, **When** staff check, **Then** the matching success event exists without the form body or contact fields.

---

### User Story 4 - Visitor can change or withdraw analytics consent later (Priority: P1)

After the first choice, the visitor can reopen preferences from a durable on-site control (not only by wiping the browser) and switch analytics off or on. The new choice is stored. If they turn analytics off, further measurement loads stop in that visit after the choice is saved. The first-visit banner MUST NOT reappear on every page once a choice exists, except in the cases Italian cookie FAQ already allows (choice gone, significant change of tools, or a long interval).

**Why this priority**: Garante cookie FAQ requires an easy later change of mind. Today's first-visit banner hides after a choice and has no public reopen control.

**Independent Test**: Accept analytics, confirm measurement can load, reopen preferences, switch to essentials only, confirm further measurement requests stop. Repeat the other direction.

**Acceptance Scenarios**:

1. **Given** analytics previously accepted, **When** the visitor switches to essentials only via the on-site control, **Then** further measurement loads stop after the choice is saved.
2. **Given** essentials only, **When** the visitor later accepts analytics via the same control, **Then** measurement may load without a second unrelated banner product.
3. **Given** a stored choice, **When** the visitor goes to another public page, **Then** the first-visit banner does not appear again.

---

### User Story 5 - Cookie and privacy texts match live tools after measurement works (Priority: P2)

Once gated measurement actually runs, Italian and English cookie-banner notes, cookie policy, and privacy policy MUST stop saying analytics are inactive. They name the measurement product and tag manager, the purpose (aggregated statistics and conversion events), that it runs only after analytics consent, that the association does not keep raw visitor IP for this purpose, and that advertising/remarketing tags are not part of this feature. Texts remain operational. This story ships **after** stories 1–4 so the pages describe reality, not a plan.

**Why this priority**: False "analytics not active" copy becomes a privacy defect the moment measurement is on. Full leftover GDPR inventory (other processors, later ads tags) stays in 006. This feature MUST NOT author a DPA.

**Independent Test**: With measurement enabled, Italian and English cookie and privacy pages no longer contain the inactive claim and they name the live product. With measurement disabled by configuration, those texts again say the tool is not loaded.

**Acceptance Scenarios**:

1. **Given** measurement enabled, **When** a visitor opens cookie information, **Then** the analytics category describes a live, consent-gated tool and lists it in the cookie table.
2. **Given** measurement enabled, **When** a visitor opens the privacy notice, **Then** audience measurement is described (product, purpose, consent, no raw IP on association servers) and Google is listed among recipients for this purpose.
3. **Given** measurement disabled by configuration, **When** a visitor opens cookie information, **Then** texts again say the tool is not loaded.
4. **Given** any of those pages, **When** looking for DPA/processor-agreement legal drafting, **Then** this feature has not added it.

---

### Edge Cases

- Consent stored but measurement configuration missing: behave as "not loaded"; do not error the page.
- Thank-you or success pages opened without completing the journey: do not count as conversions unless the site already treats them as success.
- Preview/unpublished pages: do not pollute production reports as if they were public (exclude or mark).
- Advertising, remarketing, and Google Ads website-conversion pixels are **out of scope**. They MUST NOT ride along inside analytics consent. Linking the measurement property to Google Ads and importing key events is **owner/ops** after this feature; it is not a second on-site tracker.
- Membership conversion is out of scope until 004 confirms the existing socio landing (`/it/diventa-socio`).
- Public Russian locale remains absent (`S-I18N`). Do not publish `/ru` cookie or privacy pages.
- Closing preferences without saving MUST NOT silently accept analytics.
- Legitimate interest MUST NOT be used as the banner basis for measurement cookies (Garante cookie guidelines).
- 2022 Italian SA findings on Google Analytics concerned **transfers without Chapter V safeguards**. They do not license skipping cookie consent, and they are not treated here as a perpetual product ban when an adequacy decision covers a certified US organisation. Residual litigation around that adequacy decision is noted in Assumptions; it does not change this feature's consent rule.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Necessary tools MUST run without optional consent. Essentials-only MUST be sufficient to use the whole public site, including donate, volunteer, and contact.
- **FR-002**: On the first visit with no stored choice, the visitor MUST see an explicit choice (accept optional analytics, essentials only, and a preferences panel). Default MUST be analytics off. Scroll MUST NOT grant analytics consent. Dismissing the first-visit choice without accepting analytics MUST keep analytics off.
- **FR-003**: Audience measurement MUST load only after the visitor accepts the analytics category. After a later withdrawal, further measurement loads MUST stop in that visit once the choice is saved.
- **FR-004**: Visitors MUST be able to reopen the same category choice later from a durable public control (for example in the site footer) without contacting staff and without a second unrelated consent product.
- **FR-005**: Staff MUST have the locked ready-made dashboard (see Assumptions) showing visits, uniques, pages, devices, sources, engagement, and geography if the product offers it. Custom-built analytics is forbidden.
- **FR-006**: The site MUST record at least: page view; one-time donate thank-you (`donate_success`); recurring-campaign thank-you (`donate_recurring_success`); volunteer submit success; contact submit success. One thank-you MUST emit exactly one of the two donate names (campaign `allows_recurring`). Event properties MUST NOT include names, emails, phone numbers, messages, or donation amounts. Subsequent subscription invoices without a new thank-you MUST NOT invent extra GA4 events.
- **FR-007**: A configuration switch MUST allow turning measurement off globally without deploying a new product decision.
- **FR-008**: The measurement product is locked (Assumptions). It MUST support consent gating and a staff dashboard. Advertising storage / remarketing / ads personalization MUST stay off for this feature even when analytics is accepted.
- **FR-009**: After measurement can actually run, cookie-banner notes, cookie policy, and privacy policy in Italian and English MUST match live tools (product names, purpose, consent gate, association does not keep raw visitor IP for measurement). When measurement is globally off, those texts MUST revert to inactive. This feature MUST NOT author lawyer-grade DPA or DPIA language.
- **FR-010**: Measurement MUST NOT replace or mix into CRM impact counters on the home page.
- **FR-011**: New third parties MUST be listed in cookie information and MUST remain blocked by the site's edge security policy until explicitly allowed for consented loads.
- **FR-012**: Association systems MUST NOT persist raw visitor IP or raw user-agent for measurement. Cookie-consent audit MUST continue to store only hashed network identifiers plus choice type and time.
- **FR-013**: This feature MUST NOT install Google Ads / remarketing tags. Conversion **events** in the measurement product are in scope; Ads UI import is owner/ops.
- **FR-014**: Banner and policies MUST remain translatable; no hardcoded visitor strings. There is no public Russian locale.

### Key Entities

- **Consent level**: Essential (necessary only) vs analytics accepted (existing two-level choice, copy updated).
- **Measurement event**: Page view or named conversion without personal data.
- **Staff dashboard access**: Who may view reports (ops, not a public account on this site).
- **Operational policy page**: Cookie policy and privacy policy in Italian and English, updated after live measurement exists.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In a supervised test, 100% of essentials-only sessions (including dismiss-without-accept) produce zero measurement-product requests.
- **SC-002**: After 20 consented test page views, staff can identify those pages in the dashboard without exporting raw logs and without seeing visitor IPs in association systems.
- **SC-003**: Each conversion name (`donate_success`, `donate_recurring_success`, `volunteer_success`, `contact_success`) can be demonstrated once with analytics consent and once without (with = counted, without = not sent). One-time and recurring thank-you tests MUST NOT share the same donate event name.
- **SC-004**: Cookie information and privacy notice in Italian and English match whether measurement is currently on or off (no stale "inactive" / "active" mismatch).
- **SC-005**: Home CRM impact numbers remain visible and unchanged in meaning.
- **SC-006**: An essentials-only visitor can complete donate and volunteer without staff help.
- **SC-007**: A visitor who accepted analytics can withdraw it via the on-site control and stop further measurement loads without staff help.

## Assumptions

### Locked product decision (owner, 2026-09-16)

**Google Analytics 4** is the staff measurement product, loaded through **Google Tag Manager**, only after analytics consent.

This is the law-and-fit choice for Safe House ETS (not a hosting preference):

- Italian ETS have the same cookie and GDPR duties as any other site operator ([Garante Analytics communiqué, doc. 9782874](https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9782874)).
- Garante cookie guidelines and FAQ: analytics of this third-party class need prior consent; legitimate interest cannot justify trackers; banner on first access; X/dismiss keeps the default (technical only); no scroll-consent; no cookie wall without an equivalent path; later change of mind must be easy ([guidelines 10 June 2021, doc. 9677876](https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9677876); [FAQ cookie](https://www.garanteprivacy.it/faq/cookie)).
- GA4 in the usual configuration does **not** fit the FAQ 4 / guidelines §7.2 analytics exemption (third-party combination/enrichment — the 2022 findings). Consent stays mandatory even after transfer law changed.
- Transfers to a DPF-certified US organisation currently rest on Commission [Decision (EU) 2023/1795](https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX%3A32023D1795). Google documents Ads [Data Processing Terms](https://support.google.com/analytics/answer/3379636) for EEA customers. Google documents that it does not log or store IP addresses from EU/EEA (and CH/UK) users; IP is used only to derive coarse geo and then discarded ([EU-focused data and privacy](https://support.google.com/analytics/answer/12017362)). That is **Google's** processing statement, not a reason to skip consent, and not permission to store raw IP on association servers.
- Ad Grants conversion policy documents GA4 key events as the import path ([Set up conversion tracking](https://support.google.com/grants/answer/9841491)). One measurement product therefore serves both staff statistics and later Grants import, without a second on-site tracker in this feature.
- Rejected alternatives for this organisation: Plausible-only or Matomo-only would still need a Google tag later for Grants, creating two processors and two policy stories while the banner would still be required for Google. Direct Google tag without a tag manager is the same legal class and a worse later Grants/ops path. Dual Plausible+GA4 was rejected as extra processor surface for no consent saving.

Owner/ops (not this repo's PHP): create the GA4 property and Tag Manager container; accept Google Ads Data Processing Terms if the UI still asks; keep Google signals, ads personalization, and "Google products and services" sharing **off**; do not send user-provided data. Container and measurement IDs are secrets/config, never committed. Edge allowlist for consented third-party hosts is owner-ops in Caddy (`S-CSP`); this feature MUST NOT move CSP into PHP.

Residual: General Court [T-553/23](https://eur-lex.europa.eu/legal-content/en/TXT/?uri=CELEX%3A62023TJ0553) dismissed the DPF challenge (3 Sept 2025); appeal [C-703/25 P](https://eur-lex.europa.eu/eli/C/2025/6610/oj/eng) is pending. DPF remains in force at specification time. If adequacy is later invalidated, stop and specify a repair; do not silently switch products inside this feature.

### Compatibility and sequence

- **Reuse** the existing first-visit banner, two-level stored choice (essential vs analytics), hashed consent audit, and cookie/privacy CMS pages. Do not add a second banner product. Do not add a marketing/advertising category in this feature (no ads tags here).
- **Sequence inside this feature**: implement consent gate + measurement + withdraw control first; **then** update banner notes and cookie/privacy operational pages so they describe the live tool. Owner UAT includes both behaviour and texts.
- **Compatibility with 001**: S01 UAT is closed. Adopt any 001 findings bucketed to 002. Do not start `001.K` security repairs here unless the owner re-buckets.
- **Compatibility with 003–005**: SEO and ads landings consume these events; they do not install another tracker. Campaign banners are not conversion goals unless they land on existing donate/5 x 1000 success paths.
- **Compatibility with 006 (S04)**: 002 now owns the operational cookie **banner behaviour** (including reopen/withdraw) and the measurement-related cookie/privacy sentences. 006 still owns leftover GDPR operational alignment (full processor inventory beyond measurement, later marketing category if Ads tags are requested, reconcile). 006 MUST NOT reintroduce "analytics inactive" while 002 measurement is on, and MUST NOT author a DPA.
- Constitution `S-GDPR` remains closed.
- Visitor-facing banner strings remain translatable in Italian and English. Cookie and privacy bodies remain the existing CMS legal pages (staff-editable), not a new parallel policy site.
- Official stack pages opened this specify turn (cite only; load order belongs in plan): [Laravel localization](https://laravel.com/docs/13.x/localization), [Laravel Blade](https://laravel.com/docs/13.x/blade), [Filament overview](https://filamentphp.com/docs/4.x/introduction/overview).
- Google tag-platform consent defaults MUST stay denied for advertising until a later spec; analytics updates only after this feature's analytics accept ([Consent mode on websites](https://developers.google.com/tag-platform/security/guides/consent); [Tag Manager account/container](https://support.google.com/tagmanager/answer/6103696)).

## Changelog

- 2026-09-13: Initial specification (S02 split; measurement only).
- 2026-09-13: 001.1 remap — public locales it+en only (no Russian).
- 2026-09-16: Owner locked stack A (GA4 via Tag Manager). Pulled operational cookie/privacy adaptation and withdraw/reopen into this living spec (still no DPA). Product choice no longer deferred to plan. Hosting location is not a legal criterion; Italian/EU cookie + GDPR + transfer rules are.
- 2026-09-16: Owner needs site one-time vs recurring donate distinguishable in GA4 Events. Split to `donate_success` / `donate_recurring_success` (no amount/PII). Monthly Stripe invoices remain CRM-only.
- 2026-09-17: Doc sync before `/speckit-tasks`: plan tree lists owner GA4 checklist; measurement-boot adds page_location query strip; policy-copy names four conversion events. Product unchanged.
