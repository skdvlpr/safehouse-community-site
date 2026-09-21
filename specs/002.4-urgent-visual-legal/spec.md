# Feature Specification: Urgent visual and legal correction

**Feature Branch**: `002.4-urgent-visual-legal`

**Created**: 2026-09-21

**Status**: Implemented locally (not pushed)

**Queue**: Owner hotfix interrupt (said «срочная коррекция») while S04 copy (`002.3`) is local-only. Finish this amendment before `003`. Does **not** start `007-public-visual-refresh` (later large look rewrite).

**Queue**: Owner hotfix interrupt (said «срочная коррекция») while S04 copy (`002.3`) is local-only. Finish this amendment before `003`. Does **not** start `007-public-visual-refresh` (later large look rewrite).

**Depends on**: Local commit `32fbf1e` (002.3 operational cookie/privacy copy). Production CMS legal HTML is still the old August text; production **does** already show the compact vertical cookie banner from `0cdb4e0`.

**Input**: Owner 2026-09-21: do **not** deploy anything without explicit approval. The compact vertical cookie banner is rejected (especially in light theme). Restore a **horizontal** banner of roughly the previous shape. Text sitting on the photo without a panel is unreadable. Replace the programmer-editor public typeface with a Google Fonts family. Repair the **landing** template (`diventa-socio`): first viewport must show the important message and buttons; stop the left-aligned text column with a huge empty right side. Bold header nav, larger logo, taller/wider header. Put the new legal seat on privacy, cookie, and Contatti. Contatti FAQ must be a **button**, not a raw URL. Intermediate analysis of landing + legal templates against current public-web practice; owner picks layout options in chat. Copy of membership landing and extra buttons stay owner-editable later. **No git push / no production** until the owner says so in a later message.

Vendor docs opened this specify: [Laravel Blade](https://laravel.com/docs/13.x/blade); [Tailwind font-family](https://tailwindcss.com/docs/font-family); [Tailwind max-width](https://tailwindcss.com/docs/max-width); [Google Fonts — Source Sans 3](https://fonts.google.com/specimen/Source+Sans+3); [WCAG 2.2 contrast minimum](https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html). Practice sources: [Haven membership site](https://gohaven.co/resources/membership-website-that-converts); [Apexure above-the-fold](https://www.apexure.com/blog/above-the-fold-landing-page-design); [Passiro cookie-banner design](https://passiro.com/cookie-compliance/cookie-banners/design/).

## Template analysis (this hotfix)

### Landing (`diventa-socio`, template `landing`)

**What it is today:** one full-width hero block; title; optional carousel; CMS HTML in a **narrow left column** (`max-w-2xl`); donate + contact buttons **under** that long HTML. The membership body is a long stack of headings, quotes, prices, and values. On a wide screen the right ~50% is empty. The visitor must scroll far to reach buttons.

**What current practice says:** first screen = headline + short promise + **one primary CTA visible without scrolling**; desktop often **two columns** (copy+CTA | visual); mobile stacks without the image pushing the CTA below the fold; left-aligned copy in a column, not a thin strip in an otherwise empty band. ([Haven](https://gohaven.co/resources/membership-website-that-converts), [Apexure](https://www.apexure.com/blog/above-the-fold-landing-page-design), [OneMinuteBranding two-column gravity](https://www.oneminutebranding.com/blog/landing-page-above-the-fold)).

**This hotfix:** rearrange the **template**, do not rewrite membership copy. **Locked 2026-09-21:** hybrid **A+C** — two-column hero (copy + CTAs | visual) plus a responsive card grid for the rest of the CMS body, both themes. Primary CTA **Compila domanda** opens a modal of the official admission form (Word: *Modulo Domanda di Ammissione Socio*). Public fields are §1–3 of that form. Board-only §4 stays off the website. Mail to `matteo.grossi@safehouse.community` is required (volunteer pattern). CRM Lead `contactType=MemberContact` is best-effort after mail. Extra CRM layout/PDF/convert work is a brief for the CRM agent; this repo does not change Espo schemas.

### Cookie + privacy (template `legal`)

**What it is today:** photo hero; eyebrow + lead sit on the photograph **without a panel** (unreadable in light theme); measurement status and cookie reopen control also sit on the photo; document is a glass card with **monospace** body. Production still shows August 2026 HTML; local DDEV has 21 September 2026 copy from `002.3` (seat still “Italia”).

**What current practice says:** legal pages use a readable proportional sans, comfortable line length (~60–75 characters), solid or near-opaque paper panel, dark-on-light or high-contrast dark-on-dark; optional sticky contents for long policies. Cookie banners use an **opaque** surface so text never competes with the page photo; horizontal bar or wide card; Accept and Refuse of similar weight; WCAG AA 4.5:1 for normal text ([Passiro](https://passiro.com/cookie-compliance/cookie-banners/design/), [WCAG contrast](https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html)).

**This hotfix:** opaque cookie banner; photo labels on a **solid panel**. Legal document: slightly translucent paper (keep current glass), proportional sans, **metadata mini-block** (document type + last updated) above the body.

### Cookie banner

**What it is today (prod + local):** narrow vertical card (`max-w-lg`), stacked buttons, glass/transparency. Light theme: pale translucent card over a busy photo. Owner: “super ugly”, “same shape as before but horizontal”.

**This hotfix:** **Locked 2026-09-21:** wide centered card ~1000–1100px, slightly above the bottom, actions in a row; **opaque** panel (not glass-on-photo).

### Header

Nav links are `text-sm font-medium`; wordmark `text-lg`/`sm:text-xl`; emblem ~40–48px; bar `py-3`/`sm:py-4`. Owner: bolder menu and type, larger logo, wider/taller header. Locked in this spec (not a clarification).

### Out of this hotfix

Home, donate, about, services full redesigns; motion system of `007`; Ad Grants copy of `004`; socio CRM form of backlog 039; production deploy.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Cookie banner is horizontal and readable (Priority: P1)

A visitor in light or dark theme sees a **horizontal** cookie choice near the bottom, similar in overall shape to the pre-compact banner. Every title, body line, and button label sits on an **opaque** panel. Accetta tutti, Solo necessari, Preferenze, and (when open) Salva remain. Analytics still do not load until Accetta tutti or Salva. The cookie-policy page button still reopens Preferenze.

**Why this priority**: Owner rejected the live compact card; unreadability is a first-visit defect.

**Independent Test**: Local home and cookie-policy, light and dark, with a busy photo behind. Banner is wide/horizontal. No label is transparent-on-photo.

**Acceptance Scenarios**:

1. **Given** light theme and a photographic page, **When** the banner is open, **Then** title, body, links, and buttons are readable without guessing (opaque panel, not glass-on-photo).
2. **Given** dark theme, **When** the same, **Then** the same readability holds.
3. **Given** a laptop viewport, **When** the banner shows, **Then** primary actions sit in a **row** (horizontal), not a tall narrow stack like today.
4. **Given** a phone, **When** the banner shows, **Then** actions may stack so they remain tappable, still on the opaque panel.
5. **Given** no analytics consent yet, **When** the banner is only visible, **Then** measurement still does not load.

---

### User Story 2 - Public typeface is a Google Fonts sans, not JetBrains (Priority: P1)

A visitor on any public Italian or English page reads **Nunito Sans** (Google Fonts, self-hosted). The programmer-editor family is gone from the public site. CMS `/cms-safehouse` look is unchanged.

**Why this priority**: Owner called the current family an urgent mistake. `007` already planned this later; this hotfix pulls **only the typeface** forward.

**Independent Test**: Home, landing, cookie, contact, donate — public text is the chosen Google family. CMS login is unchanged.

**Acceptance Scenarios**:

1. **Given** a public page, **When** a reviewer looks at headings and body, **Then** they are not JetBrains Sans / JetBrains Mono as the site sans.
2. **Given** `/cms-safehouse`, **When** staff browse, **Then** admin appearance is unchanged.

---

### User Story 3 - Landing first screen carries the message and the buttons (Priority: P1)

On `/it/diventa-socio` (and English of the same page) a laptop visitor sees heading, a short above-the-fold excerpt of the CMS body, and the template buttons **without scrolling**. The layout uses the width of the page (no huge empty right). Remaining CMS HTML can continue below the fold. Owner will still change copy later; this story is structure.

**Why this priority**: Owner: important info and buttons must be visible on open; page is wide.

**Independent Test**: Desktop ~1280–1440px and phone ~375px, light and dark. First screen has title + CTA. Body copy uses the width.

**Acceptance Scenarios**:

1. **Given** a laptop opening `/it/diventa-socio`, **When** they do not scroll, **Then** they see the title and the landing buttons.
2. **Given** the same, **When** they look at the first screen, **Then** the layout is not a thin left column with an empty right half.
3. **Given** a phone, **When** they open the page, **Then** the primary button is reachable without hunting through the full essay.
4. **Given** existing CMS HTML, **When** the template changes, **Then** the words themselves are not rewritten by this feature.

---

### User Story 4 - Legal and contact facts: seat, FAQ button, readable chrome (Priority: P1)

Privacy, cookie, and Contatti show the legal seat: **Via delleani 26, 00042 Anzio (RM)**; **RUNTS 156768**; **C.F. 96629270586**. Contatti “Domande frequenti” is a **button** to the existing FAQ page, not a pasted URL. Cookie/privacy labels on the photo are readable (panel or equivalent). Local `002.3` processor copy stays (Aruba VPS, Google Workspace, no advertising of name/phone/email). Seat line replaces “Italia” only.

**Why this priority**: Owner supplied the seat and the FAQ-as-button rule in the same hotfix.

**Independent Test**: Local IT+EN privacy, cookie, contact. Seat present. FAQ control is a button. No production overwrite.

**Acceptance Scenarios**:

1. **Given** local privacy and cookie pages, **When** a visitor reads the controller block, **Then** they see the Anzio street, RUNTS, and fiscal code (not only “Italia”).
2. **Given** Contatti, **When** they want FAQ, **Then** they use a button, not a raw `https://…` line.
3. **Given** cookie/privacy in light theme, **When** they read the hero/status/reopen lines, **Then** those lines are not transparent-on-photo.
4. **Given** this feature, **When** production is considered, **Then** nothing is pushed or synced live until the owner asks in a later message.

---

### User Story 5 - Header is heavier and larger (Priority: P2)

Header logo and wordmark are clearly larger than today; menu labels are bolder; the bar is taller/uses the width more generously. Donate and 5×1000 stay findable.

**Independent Test**: Home header vs current screenshots; light and dark.

**Acceptance Scenarios**:

1. **Given** the public header, **When** compared with today’s bar, **Then** logo/wordmark are larger and nav labels are bolder.
2. **Given** a laptop, **When** the header is shown, **Then** it still fits without overlapping the donate control.

---

### User Story 6 - Membership application modal matches the official form (Priority: P1)

On `/it/diventa-socio` the visitor uses **Compila domanda**. A modal collects the public fields of the official *Domanda di ammissione socio* (name, birth, codice fiscale, residence, phone, email, three statute/mission/quota declarations, newsletter yes/no). Submit emails staff and the applicant. When Espo is configured, the site creates a Lead with type Associato (`MemberContact`). Consiglio Direttivo / Libro Soci / tessera fields are not on the public form.

**Why this priority**: Owner sent the official Word form and asked for the same pattern as volunteer mail plus CRM Lead Associato.

**Independent Test**: Local membership landing, submit valid payload, staff+applicant mail, no new database table.

**Acceptance Scenarios**:

1. **Given** `/it/diventa-socio`, **When** the visitor opens Compila domanda, **Then** they see the official public fields and not the board-only block.
2. **Given** a valid submission and SMTP configured, **When** they send, **Then** staff and applicant receive mail and the page shows success.
3. **Given** Espo API is configured, **When** mail already succeeded, **Then** the site attempts Lead create with `contactType` Associato; mail success does not depend on CRM.
4. **Given** SMTP is missing, **When** they send, **Then** the form fails closed (no false success).

---

### Edge Cases

- Phone: banner actions stack; landing CTA still in the first screens; header may use the existing hamburger.
- Reduced motion: no new decorative motion required in this hotfix.
- Font file missing: system sans fallback, still not JetBrains as the primary name.
- English contact/privacy get the same seat; FAQ button uses the English FAQ URL when in `en`.
- Do not publish `/ru`.
- Do not author a DPA.
- Production already has the ugly banner: this feature **does not** silently revert live. Owner may later approve a deploy of the local result.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Cookie banner MUST be horizontal on desktop (actions in a row), roughly the previous wide shape, not the compact square card. Phone MAY stack actions.
- **FR-002**: Cookie banner, preference labels, and legal/cookie hero/status/reopen text MUST sit on an opaque or equivalent high-contrast surface in **both** themes. WCAG AA 4.5:1 for normal text against that surface. Glass-on-photo for those labels is forbidden.
- **FR-003**: Consent behaviour from `002`/`002.3` MUST stay: necessary tools always; analytics only after Accetta tutti or Salva; checkbox may start selected; cookie-page reopen remains.
- **FR-004**: Public site sans MUST be a Google Fonts family chosen in clarification, self-hosted for the public site so the look does not depend on a live Google CSS request. MUST NOT remain JetBrains Sans as the public family. CMS appearance MUST NOT change.
- **FR-005**: Landing template MUST put title + primary template buttons in the first viewport on a typical laptop, and MUST use the horizontal space (two-column or equivalent — see landing_layout). MUST NOT rewrite `diventa-socio` CMS essay in this feature.
- **FR-006**: Header logo/wordmark MUST be larger; nav labels MUST be bolder; header bar MUST be taller/more substantial than today.
- **FR-007**: Privacy, cookie, and Contatti MUST show sede legale **Via delleani 26, 00042 Anzio (RM)**, RUNTS **156768**, C.F. **96629270586**.
- **FR-008**: Contatti FAQ MUST be a button to the existing FAQ page (Italian and English), not a pasted URL.
- **FR-009**: Legal document body MUST use the public proportional sans (not a code mono) and a readable measure. Chrome choice: legal_chrome clarification.
- **FR-010**: Implement and preview **locally only**. MUST NOT `git push`, MUST NOT production `site:sync-legal-pages`, MUST NOT production deploy, unless the owner later writes an explicit deploy instruction.
- **FR-011**: This feature MUST NOT implement `007` motion/dimension inventory, S03 banners, or volunteer→Lead Volontario. Membership public form + best-effort Lead Associato ARE in scope after the owner sent the official Word form.
- **FR-012**: Membership POST MUST reuse the volunteer mail pattern (staff inbox, applicant copy, Turnstile, honeypot, rate limit). Public fields MUST match the official form §1–3. Lead payload MUST use existing Espo fields only (`MemberContact`, `taxCode`, `birthDate`, `birthPlace`, `birthProvince`, address*, phone, email). MUST NOT add a site `memberships` table. MUST NOT write `nonprofit-espocrm`.

### Key Entities

- **Cookie banner surface**: Opaque panel carrying consent copy.
- **Landing first screen**: Title, short excerpt, buttons, optional visual column.
- **Membership application**: Official form §1–3 in a modal; staff+applicant mail; best-effort Espo Lead Associato.
- **Legal seat**: Street, CAP, city, RUNTS, fiscal code.
- **Public typeface**: Nunito Sans (self-hosted Google Fonts).

## Success Criteria *(mandatory)*

- **SC-001**: In light theme on a photographic page, a reviewer can read every cookie-banner word without squinting or selecting the text.
- **SC-002**: On a laptop, the cookie banner is clearly wider than tall (horizontal), not a compact square stack.
- **SC-003**: On `/it/diventa-socio` at ~1400px width, title and landing buttons are visible without scrolling.
- **SC-004**: A reviewer who knew JetBrains can say the public site no longer uses that family after a 2-minute browse.
- **SC-005**: Privacy, cookie, and Contatti each show the Anzio seat; Contatti FAQ is a button.
- **SC-006**: After implement, `git status` is not pushed; production cookie HTML date remains the old live date until the owner asks to publish.
- **SC-007**: A reviewer can complete the membership modal with the official public fields and receive the applicant confirmation mail locally.

## Assumptions

- Owner hotfix language is enough to interrupt the queue; `007` stays later for the large visual pass. Typeface replacement here **consumes** `007` US1 so `007` must not swap fonts again.
- `002.3` processor inventory remains; this feature adds the Anzio seat and visual/legal chrome.
- Street spelling **Delleani** as on the official form (Via Delleani 26).
- Self-hosting a Google Fonts file is the privacy-friendly default (no extra third-party font CDN on every page).
- Locked 2026-09-21: banner **wide_card** (~1000–1100px, horizontal actions); labels **solid_panel**; typeface **Nunito Sans**; landing **A+C** plus Compila domanda modal; legal chrome **paper column slightly transparent**, metadata mini-block on top.
- Tests: copy/markup assertions, membership mail like volunteer; no screenshot tests.

## Changelog

- 2026-09-21: Initial hotfix specification. Layout/typeface/banner-shape clarifications open.
- 2026-09-21: Owner locked banner/typeface/landing/legal chrome. Official Word form pulled US6 into this hotfix. Volunteer Lead remains backlog.
