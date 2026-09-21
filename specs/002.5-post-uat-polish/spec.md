# Feature Specification: Post-UAT visual and legal polish

**Feature Branch**: `002.5-post-uat-polish`

**Created**: 2026-09-21

**Status**: Draft (specify + plan; implement waits for owner yes)

**Queue**: Owner UAT repair of `002.4`. Amendment `002.5` before `003`. Does **not** start S03 banners or `007`.

**Depends on**: Shipped `002.4` (`922a18e`). Production cookie/privacy **CMS HTML** is still the old August text; local DDEV has 21 September operational copy from `002.3`.

**Input**: Owner 2026-09-21 after phone+desktop UAT of Diventa socio: landing is accepted; remove ticker pause; on narrow phones add a swipe-down hint between hero and first card; fill the empty desktop Contattaci band with larger square social logos plus Statuto / FAQ / donate / volunteer links without spoiling the phone band; cookie and privacy desktop layout is a T (wide opaque hero, narrow paper) — even widths and matching translucency; footer Cookie preferences → English version; keep preferences only on the cookie page; IT/EN on cookie+privacy pages and as obvious buttons on the cookie banner; **next authorised push must publish the new cookie/privacy document to production**.

Vendor docs opened this specify: [Laravel Blade](https://laravel.com/docs/13.x/blade); [Laravel localization](https://laravel.com/docs/13.x/localization); [Tailwind max-width](https://tailwindcss.com/docs/max-width); [WCAG 2.2.2 Pause, Stop, Hide](https://www.w3.org/WAI/WCAG22/Understanding/pause-stop-hide.html).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Phone landing shows there is more below the hero (Priority: P1)

On a narrow phone, Diventa socio still looks like “only the first block exists” because cards paint after scroll. Between the hero (Diventa socio) and card 01 the visitor sees a short **Scorri in basso** (EN: Swipe down) hint with a downward arrow. The hint is **not** on laptop/desktop. The ticker has **no Pause button**. Cards may still slide in later.

**Why this priority**: Owner said mobile looks strong except this false “one block” impression and the pause control.

**Independent Test**: `/it/diventa-socio` at ~390px and at ~1400px.

**Acceptance Scenarios**:

1. **Given** a viewport under ~768px, **When** the membership landing loads, **Then** a swipe-down hint with an arrow sits between the hero and the first numbered card.
2. **Given** a laptop viewport, **When** the same page loads, **Then** that hint is absent.
3. **Given** any viewport, **When** the ticker is visible, **Then** there is no Pause/Riprendi control on the ticker.
4. **Given** the OS asks for reduced motion, **When** the ticker is shown, **Then** it does not auto-scroll (system preference still wins).

---

### User Story 2 - Desktop Contattaci is filled; phone Contattaci stays as today (Priority: P1)

On a wide screen the red Contattaci band is no longer empty. It carries **larger square social logos** (the same networks already in the footer) and a harmonious row of links: **Statuto**, **FAQ**, **Donazioni**, **Volontariato**. On a phone the band stays the compact stack already accepted (title, lead, Compila domanda, sede) — no extra emptiness, no crowded new grid.

**Why this priority**: Owner called the PC last block too empty and warned not to spoil the phone version.

**Independent Test**: Same URL, desktop vs ~390px, focus on the last band only.

**Acceptance Scenarios**:

1. **Given** a laptop, **When** the visitor reaches Contattaci, **Then** they see large square social marks and the four destination links, plus the existing form button and sede.
2. **Given** a phone, **When** they reach Contattaci, **Then** the band still looks like today’s compact block (form button + sede); the extra desktop filler is not a second stacked wall of content.
3. **Given** FAQ / donate / volunteer destinations that already exist, **When** those links are used, **Then** they open those existing public pages.

---

### User Story 3 - Cookie and privacy pages are one even column (Priority: P1)

On a laptop, cookie and privacy no longer form a **T** (wide header card, narrow paper). Hero and document share **one width**. The surface under the title and the cookie-preferences control uses the **same translucency** as the paper below (today the top is fully opaque). Phone layout may stay as-is if it already looks even.

**Why this priority**: Owner rejected the desktop legal chrome while accepting the phone look.

**Independent Test**: Local `/it/cookie-policy` and `/it/privacy-policy` at ~1400px and ~390px, both themes.

**Acceptance Scenarios**:

1. **Given** a laptop, **When** cookie or privacy is open, **Then** the header block and the document block align to the same column width (not a T).
2. **Given** those pages, **When** compared side by side, **Then** the header panel is not a solid opaque slab against a translucent paper — they match.
3. **Given** a phone, **When** the same pages open, **Then** they remain readable and not worse than today.

---

### User Story 4 - Language is obvious; cookie preferences live only on the cookie page (Priority: P1)

The footer no longer has **Preferenze cookie**. In its place is **English version** (on Italian pages) or **Versione italiana** (on English pages), sending the visitor to the same page in the other public locale. Cookie and privacy each also show that language control. The cookie **banner** shows clear **IT** and **EN** buttons (readable, not hidden in the gear menu). **Preferenze cookie** remains on the cookie policy page only.

**Why this priority**: First-visit English speakers currently have no obvious banner language switch; footer reopen is the wrong control.

**Independent Test**: Home footer, cookie page, privacy page, first-visit banner, Italian and English URLs.

**Acceptance Scenarios**:

1. **Given** any public Italian page, **When** the footer is visible, **Then** there is an English-version control and **no** footer cookie-preferences control.
2. **Given** the cookie policy page, **When** it is open, **Then** the reopen-preferences button is still there.
3. **Given** cookie and privacy, **When** the visitor chooses the other language, **Then** they land on that document in IT or EN.
4. **Given** the first-visit banner, **When** it is shown, **Then** IT and EN are two obvious buttons; choosing one shows the banner (and page) in that language.

---

### User Story 5 - Production cookie and privacy documents match the September operational text (Priority: P1)

The next authorised push **does** publish the current operational cookie/privacy bodies (Aruba VPS Italy, Google Workspace, Turnstile, Anzio seat, no advertising of name/phone/email). After that push, production pages are no longer the August HTML. The copy no longer tells people to reopen preferences from the footer.

**Why this priority**: Owner: the new document was not moved to production; next push must fix that.

**Independent Test**: After deploy, production cookie and privacy show 21 September (or later) operational text and Anzio seat.

**Acceptance Scenarios**:

1. **Given** production after this feature’s authorised deploy, **When** a visitor opens cookie or privacy, **Then** they see the operational September text (seat, processors, consent), not the old August body.
2. **Given** that text, **When** it mentions how to change cookie choice, **Then** it points to the cookie-page button, not a footer preferences link.

---

### Edge Cases

- Ticker with one or many values still has no pause control; reduced-motion users get a still ticker.
- If a social network has no URL in CMS, that square is omitted (same rule as the footer).
- If a Statuto CMS page does not exist, Statuto uses FAQ until a dedicated page or PDF URL exists.
- English bodies that are still empty keep the existing Italian fallback behaviour; the language button still switches the URL prefix.
- Banner language switch must not reset an already-saved cookie choice.
- Phone Contattaci must not grow a second “desktop” column when the phone is rotated to landscape under the desktop breakpoint.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Narrow viewports MUST show a swipe-down hint with an arrow between the membership hero and the first card. Viewports from the tablet/desktop breakpoint up MUST NOT show it.
- **FR-002**: The values ticker MUST NOT show an on-page pause/resume control. Auto-motion MUST still stop when the visitor’s system requests reduced motion ([WCAG 2.2.2](https://www.w3.org/WAI/WCAG22/Understanding/pause-stop-hide.html) residual: no on-page pause for other visitors — owner UAT).
- **FR-003**: From the desktop breakpoint, the Contattaci band MUST include larger square social logos and links to Statuto, FAQ, Donazioni, and Volontariato, without removing Compila domanda or sede legale.
- **FR-004**: Below the desktop breakpoint, the Contattaci band MUST keep today’s compact composition. Extra desktop filler MUST NOT become a tall extra stack on the phone.
- **FR-005**: Cookie and privacy desktop layout MUST be a single even column: header and document the same width, header surface the same translucency as the document. MUST NOT remain a T of opaque-wide over narrow-paper.
- **FR-006**: Footer MUST replace Preferenze cookie with a locale switch to the other public language (IT↔EN) for the current path. Cookie-page reopen MUST remain on the cookie page only.
- **FR-007**: Cookie and privacy pages MUST offer the same IT↔EN switch as a visible control.
- **FR-008**: The cookie banner MUST show obvious **IT** and **EN** buttons. Activating one MUST switch the public locale (banner copy follows). Consent already stored MUST remain.
- **FR-009**: No public `/ru`. CMS path stays `/cms-safehouse`. Consent gate (necessary always; analytics after Accetta tutti / Salva) MUST NOT change.
- **FR-010**: This feature’s authorised production deploy MUST run `site:sync-legal-pages --force` once so live cookie/privacy match `LegalPagesContent`. Future deploys MUST NOT keep overwriting CMS legal HTML unless the owner later asks. Copy MUST drop the “reopen from the footer” sentence.
- **FR-011**: MUST NOT start S03, volunteer→Lead Volontario, or `007` motion inventory. MUST NOT write `nonprofit-espocrm`. MUST NOT author a DPA.

### Key Entities

- **Swipe-down hint**: Narrow-viewport-only cue between membership hero and first card.
- **Contattaci filler**: Desktop-only social squares + four destination links.
- **Locale switch**: Same-path IT↔EN using the existing public locale prefix.
- **Legal column**: Cookie/privacy header + paper sharing width and translucency.
- **Production legal sync**: One-shot overwrite of privacy/cookie CMS bodies on live.

## Success Criteria *(mandatory)*

- **SC-001**: On a phone, a reviewer who only saw the first screen can tell there is more below without guessing (hint visible).
- **SC-002**: On a phone, Contattaci still matches the accepted compact band; a reviewer does not call it “ruined”.
- **SC-003**: On a laptop, Contattaci does not look empty: social squares and the four links are visible in the red band.
- **SC-004**: On a laptop, cookie and privacy are one even column, not a T, and the header is not a solid slab against a glass paper.
- **SC-005**: Footer has English/Italian version and no cookie-preferences control; cookie page still reopens preferences; banner IT/EN is readable without opening the header gear.
- **SC-006**: After the authorised push, production cookie and privacy show the September operational document (Anzio seat, named processors), not August HTML.

## Assumptions

- Owner “внеочередная коррекционная” = `002.5` amendment of S02/002, not a new SITE-queue row.
- Swipe copy: IT **Scorri in basso**, EN **Swipe down**, plus a downward arrow. Decorative for sighted users; cards remain in the accessibility tree.
- Ticker pause removal is an owner UAT override of the previous on-page WCAG control; reduced-motion still pauses the animation.
- Statuto has no CMS page today → link to the existing FAQ page until the owner supplies a PDF or a Statuto page. FAQ, donate, and volunteer already exist.
- Social squares reuse CMS-configured footer networks (Instagram, Facebook, WhatsApp, email, etc.), larger and square on desktop Contattaci only.
- Banner IT/EN switches the **site locale** (same helper as the header), not a banner-only translation overlay.
- Footer control on `/en/…` is “Versione italiana”; on `/it/…` is “English version”.
- Production legal sync is **once** for this feature (self-deleting once-script or equivalent), not every future deploy.
- Implement starts only after the owner says an explicit **yes** to the task list (and Launch vs Replace for any advanced model).

## Changelog

- 2026-09-21: Initial post-UAT polish specification after accepted `002.4` landing.
