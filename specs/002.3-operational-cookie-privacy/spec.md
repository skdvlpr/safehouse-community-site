# Feature Specification: Operational cookie and privacy pages

**Feature Branch**: `002.3-operational-cookie-privacy`

**Created**: 2026-09-21

**Status**: Draft

**Queue**: SITE **S04** (GDPR / cookie / privacy operational alignment). Owner reordered this **ahead of S03** (Satispay / 5×1000 banners) on 2026-09-21. Amendment of `002` leftover US5. Finish this amendment before `003`.

**Depends on**: Consent-gated measurement (`002`, `002.1`, `002.2`) is live. Banner layout, analytics preference default, and cookie-page reopen control already shipped (`0cdb4e0`). This feature updates **public cookie and privacy documents** so they match that live behaviour and the processors the site actually uses.

**Input**: Visitors and later counsel must read Italian (primary) and English cookie and privacy pages that describe the live site: hosting on an Aruba Cloud VPS in Italy; institutional mail on Google Workspace for Nonprofits; what the public site collects and where it goes; that names, phone numbers, and email addresses are **not** sent to advertising third parties; that analytics wait for Accetta tutti or Salva preferenze. Copy is production-ready. Do not label the pages as a demo, a draft, or “to approve”. Do not author a DPA, SCC, or DPIA (`S-GDPR`). Preview locally for owner review; overwrite production CMS only when the owner asks.

Vendor docs opened this specify (constitution § Tech Stack): [Laravel localization](https://laravel.com/docs/13.x/localization); [Laravel Blade](https://laravel.com/docs/13.x/blade); [Filament resources](https://filamentphp.com/docs/4.x/resources/overview); [GA4 cookies](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage); [Turnstile widgets](https://developers.cloudflare.com/turnstile/concepts/widget/); [Google Workspace privacy/security](https://support.google.com/a/answer/60762); [Stripe Privacy Policy](https://stripe.com/privacy).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Privacy page matches live processing (Priority: P1)

A visitor opens Privacy policy in Italian or English and can answer: who the controller is; that the public site runs on a virtual server in Italy (Aruba Cloud); that association email uses Google Workspace for Nonprofits; which categories of data the public site collects (navigation, contact, volunteer, donations, cookie choice, optional audience measurement, bot protection); who receives what and why; that card data does not pass through association servers; that staff Google Calendar/Drive is a separate staff-only integration; how to exercise rights. The page states clearly that Safe House does not send names, phone numbers, or email addresses to advertising or remarketing companies, and that the association treats personal data as confidential for institutional work.

**Why this priority**: The live privacy HTML still names Aruba for **email** and still lists a retired contact conversion name. That is factually wrong and is the remaining 002 US5 / S04 work.

**Independent Test**: Open `/it/privacy-policy` and `/en/privacy-policy` after local sync. Confirm hosting, mail, Stripe, Turnstile, measurement, internal accounting, and the no-advertising-PII sentence. Confirm no EspoCRM hostname, no DPA title, no demo/draft wording.

**Acceptance Scenarios**:

1. **Given** the synced privacy page, **When** a visitor reads controller and hosting, **Then** they see Safe House ETS, fiscal code 96629270586, site https://safehouse.community, privacy mail info@safehouse.community, legal seat Italy, and hosting described as an Aruba Cloud VPS in Italy.
2. **Given** the same page, **When** they read processors, **Then** institutional outbound mail is Google Workspace for Nonprofits (not Aruba mail), payments are Stripe, bot protection on contact/volunteer is Cloudflare Turnstile, and audience measurement is Google Analytics 4 via Google Tag Manager after analytics consent.
3. **Given** the same page, **When** they look for advertising use of identity, **Then** the text states that name, phone, and email from forms and donations are not sent to advertising third parties.
4. **Given** either locale, **When** searching the HTML, **Then** there is no DPA/addendum drafting, no public `/ru` link as a live locale, and no wording that the document is a demo, a draft, or awaiting approval.

---

### User Story 2 - Cookie page matches live banner and tools (Priority: P1)

A visitor opens Cookie policy in Italian or English and understands: necessary cookies run without a click; analytics cookies are **proposed selected** in Preferenze but do not load until Accetta tutti or Salva preferenze; they can uncheck analytics and save; dismiss (X) and Solo necessari leave analytics off; the same panel reopens from the footer and from the button on the cookie page itself; the choice lasts until they change it or clear site cookies; marketing/ads cookies are not used. The cookie table lists session, CSRF, consent memory, Stripe on checkout, Turnstile on protected forms, and GA4 `_ga` / `_ga_*` after consent.

**Why this priority**: Banner UX already changed; the document must not still say “footer only” or imply analytics are unused.

**Independent Test**: Open `/it/cookie-policy` and `/en/cookie-policy`. Confirm table, consent actions, reopen from page + footer, Turnstile as necessary security, marketing not in use, GA4 names, no DPA.

**Acceptance Scenarios**:

1. **Given** the cookie page, **When** a visitor reads consent, **Then** Accetta tutti, Solo necessari, Preferenze, X = necessary only, and “no load until Accetta tutti or Salva” are all described.
2. **Given** Preferenze, **When** the visitor has not yet chosen essentials-only, **Then** the document states that analytics start selected and can be unchecked before save.
3. **Given** the cookie page, **When** they look for how to change later, **Then** they see both the on-page button and the footer control, and that clearing site cookies resets the choice.
4. **Given** the table, **When** measurement is globally off, **Then** the existing status line still says the measurement product is not loaded, and the table does **not** claim analytics cookies are unused as a product decision.

---

### User Story 3 - Local preview first, production overwrite only on owner request (Priority: P1)

Staff can read the new Italian and English pages on local preview after a one-command sync of CMS bodies. Production pages stay unchanged until the owner asks to overwrite. Filament remains able to edit the same pages later. Unused Russian CMS bodies stay unpublished (no public `/ru`).

**Why this priority**: Owner ordered local review before production; deploy already auto-syncs neither legal HTML nor a DPA.

**Independent Test**: After implement, local `/it/privacy-policy` and `/it/cookie-policy` show the new date and processors. Production is not overwritten in this feature unless the owner says so.

**Acceptance Scenarios**:

1. **Given** local database after forced legal sync, **When** the owner opens Italian cookie and privacy, **Then** they see the 21 September 2026 (or later implement date) texts.
2. **Given** this feature’s implement, **When** production deploy runs, **Then** it does not by itself overwrite live CMS legal HTML.
3. **Given** Filament Pages, **When** a super-admin opens privacy or cookie, **Then** they can still edit the body after sync.

---

### Edge Cases

- Measurement CMS toggle off: status line says the tool is not loaded; cookie/privacy still describe the consent-gated product that exists when the toggle is on.
- Visitor chose Solo necessari: Preferenze show analytics unchecked; document must not claim they cannot refuse.
- Contact flash key `contact_success` is a UI message, not a measurement event. Public pages MUST NOT list `contact_success` as an analytics event. Desk conversions are described in ordinary language (contact, sportello legale, sportello digitale), not as a developer catalogue unless needed for counsel later.
- Volunteer applications are emailed, not kept in a public “volunteers” table. Do not invent a volunteer database on the privacy page.
- Contact messages are stored for the desk, emailed to the desk inbox with a copy to the sender, and may be linked to internal case files. Describe that as internal association systems, **without** naming the CRM product or its hostname.
- Donation identity and amounts go to Stripe and to internal accounting. Card numbers do not.
- Legal seat stays “Italia” until the owner supplies a street address.
- Do not add a public Russian locale. Do not invite lawyers in the visitor-facing HTML.
- Do not mention server IPs, CMS path, container ids, or environment names on the public pages.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Italian and English privacy pages MUST describe the live public-site processing inventory in Assumptions (controller, hosting, mail, forms, donations, consent audit, measurement, Turnstile, staff Calendar/Drive, internal accounting/case files).
- **FR-002**: Privacy MUST state that Safe House does not send names, phone numbers, or email addresses to third parties for advertising or remarketing, and that personal data are used for institutional purposes and confidentiality is valued.
- **FR-003**: Privacy MUST name hosting as Aruba Cloud VPS in Italy for the public site (and the internal systems on the same server). Privacy MUST name institutional email as Google Workspace for Nonprofits. Privacy MUST NOT name Aruba as the mail processor.
- **FR-004**: Privacy MUST name Stripe for payments and state that payment-card data do not pass through association servers.
- **FR-005**: Privacy MUST name Cloudflare Turnstile as bot protection on contact and volunteer forms (security, not advertising).
- **FR-006**: Privacy MUST describe Google Analytics 4 via Google Tag Manager as consent-gated aggregated statistics and conversion events (donation thank-you, volunteer success, contact/desk success), not advertising. Association systems MUST NOT be described as storing raw visitor IP for this measurement. Cookie-consent audit remains hashed identifiers plus choice and time.
- **FR-007**: Conversion machine names on the privacy page, if any, MUST match live measurement (`donate_success`, `donate_recurring_success`, `volunteer_success`, `contact_generic_success`, `contact_slegale_success`, `contact_sdigitale_success`). They MUST NOT include `contact_success`. Ordinary-language descriptions are preferred; skip a raw event dump if the sentence already covers the journeys.
- **FR-008**: Italian and English cookie pages MUST describe the live banner actions, the analytics checkbox proposed selected, the right to uncheck, no analytics until Accetta tutti or Salva preferenze, reopen from footer and from the cookie page button, and persistence until the visitor changes the choice or clears cookies.
- **FR-009**: Cookie table MUST list necessary session, CSRF, consent memory, Stripe on checkout, Turnstile on protected forms, GA4 `_ga` and `_ga_*` after consent ([GA4 cookie usage](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage)), and marketing/ads as not in use.
- **FR-010**: Public legal HTML MUST NOT contain: DPA/DPIA/SCC drafting; EspoCRM or the CRM hostname; Laravel or other stack brand names; demo / draft / “to approve” / equivalent Italian; leftover “Aruba email”; leftover “analytics not in use” as the analytics **row** (the marketing row may still say not in use).
- **FR-011**: Public locales remain Italian primary and English second. No public `/ru`. Russian bodies in the seeder MUST NOT be published.
- **FR-012**: Source of truth for bodies remains the existing legal CMS pages. Preview applies a forced legal sync. Production overwrite waits for explicit owner request. Existing measurement status line on legal templates stays.
- **FR-013**: This feature MUST NOT change banner JavaScript, Caddy, measurement events, or CRM behaviour. Those already shipped.
- **FR-014**: This feature MUST NOT author lawyer-grade processor-agreement text. Operational transfer notes (Google / Stripe / Cloudflare may involve processing outside Italy, EU–US adequacy where those vendors publish it) are allowed as visitor information, not as a contract.

### Key Entities

- **Operational privacy page**: CMS legal body, Italian and English, describing controller, purposes, processors, rights.
- **Operational cookie page**: CMS legal body, Italian and English, describing consent UX and the cookie table.
- **Processor list**: Aruba Cloud (hosting), Google Workspace for Nonprofits (mail), Stripe (payments), Cloudflare Turnstile (bot protection), Google Analytics 4 / Tag Manager (consented measurement), Google Calendar/Drive (staff only), internal association systems (accounting and desk files).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: After local sync, a reviewer can open Italian privacy and, in one sitting, point to hosting in Italy (Aruba), Google Workspace mail, Stripe, Turnstile, consented Analytics, and the sentence that identity is not given to advertisers — without asking staff.
- **SC-002**: After local sync, Italian cookie policy describes Accetta tutti, Solo necessari, Preferenze (analytics proposed on, can be turned off), X, no measurement before save/accept, and reopen from both the page button and the footer.
- **SC-003**: Automated checks on seeded pages fail if EspoCRM, the CRM hostname, a DPA title, Aruba-as-mail, or `contact_success` as an analytics event appear in public privacy/cookie HTML.
- **SC-004**: English pages carry the same facts as Italian (not a shorter “see Italian” stub).
- **SC-005**: Production legal HTML is unchanged by this feature’s default deploy path; local preview shows the new date.
- **SC-006**: A visitor who refuses analytics can still read both legal pages, donate, volunteer, and contact.

## Assumptions

### Locked owner answers (2026-09-21)

- Legal seat: keep **Italia** until the owner supplies a street address.
- Mail processor: **Google Workspace for Nonprofits**.
- Turnstile: **always mention**; captcha is on and remains on.
- Banner preference: analytics checkbox starts selected; visitor may uncheck; Accetta tutti / Salva apply the selection; no analytics until then. Cookie page has a button that reopens the banner with Preferenze. After a choice, the control remains until cookies are cleared. Owner accepted the Garante Recital 32 tension of a pre-selected box; this spec documents the live UX, it does not re-open that product debate.
- Local review first; production CMS overwrite later, on request.
- No DPA.

### Processing inventory (from the live public site; for copy, not a dump of internals)

- **Navigation / security logs**: connection data as needed to serve the site and stop abuse. Raw IP is not kept for measurement. Cookie-consent audit stores hashed network identifiers, choice, and time.
- **Contact forms** (generic, legal desk, digital desk): name, email, message, chosen desk, consent timestamp. Stored for the association, emailed to the configured desk inbox, copy to the sender, and may be attached to internal desk case files. Hashed IP/UA on the submission record.
- **Volunteer form**: first name, last name, email, phone, message, consent. Sent to the staff volunteer inbox and a confirmation to the applicant. Not stored in a dedicated public volunteer table.
- **Donations**: donor name, individual/organisation, email and/or phone, optional comment, amount, campaign, one-time vs recurring. Card data handled by Stripe. Settlement metadata (including fees when present) recorded in internal accounting together with donor contact details needed for receipts and association books.
- **Audience measurement** (only after Accetta tutti or Salva with analytics on, and only if staff have measurement enabled): aggregated visits, pages, approximate geography, device/source, engagement, and conversion events without names, phones, emails, messages, or amounts. Ads storage / ads personalization remain denied.
- **Turnstile**: token verified with Cloudflare, including the request IP for that check, on contact and volunteer submits.
- **Staff Google Calendar / Drive**: OAuth for authorised staff only; Limited Use; not public measurement.
- **Same server**: public site and internal systems share the Aruba Cloud VPS in Italy.

### Out of scope

- S03 banners, Ad Grants landing pages, socio CRM Lead / PDF (backlog 039), cookieless analytics, changing banner code, Caddy, turning measurement off, lawyer-signed DPA.
- Publishing street address, phone of the seat, or extra mailboxes the owner did not confirm. Privacy contact remains info@safehouse.community.

### Compatibility

- `002` FR-009 / US5 is completed by this amendment (measurement-related sentences plus full operational processor list that S04 owned).
- `006` leftover GDPR inventory MUST NOT reintroduce Aruba mail or `contact_success` as analytics while this text is live.
- Constitution `S-I18N`, `S-GDPR`, `S-CSP` unchanged.

## Changelog

- 2026-09-21: Initial specification (S04 brought forward; local legal copy matching live processors and banner).
