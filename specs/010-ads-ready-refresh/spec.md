# Feature Specification: Ads-ready public refresh

**Feature Branch**: `010-ads-ready-refresh`

**Created**: 2026-09-24

**Status**: Closed 2026-09-25. Page-by-page heading and layout rework is **not** finished here. Owner decision: each public page is a separate later spec (`011` onward). Diventa socio stays unchanged. This spec keeps the home strip, short header, and 5×1000 strip already tried locally.

**Banner decision (2026-09-25)**: The header stays the dark, lightly see-through bar (`page` at 90% with a small blur). Do not restyle it when changing the strip. The 5×1000 strip under the header uses the footer color at 25% opacity plus a 96px blur ([Tailwind backdrop blur](https://tailwindcss.com/docs/backdrop-blur)) so text scrolling underneath cannot be read. The header stays above that strip (`z-20` on the header, `z-0` on the strip, [Tailwind z-index](https://tailwindcss.com/docs/z-index)) so the Altre Pagine menu is not covered. The footer slogan with dots stays in the footer. The community sentence stays on home under the title only.

**Queue**: Owner follow-up after `003` is on production. Does not operate the Google Ads account. `004` still owns the sitelink copy table.

**Input**: Owner 2026-09-24: rename and expand the menu spec into a public refresh that prepares the site for advertising. Keep the home address. Use Diventa socio as the visual reference without making every page that wide. Show the latest news and articles in small animated windows on home, with links to both sections. Widen the volunteer form on large screens. Modernize public pages and add motion. Remove 5×1000 from the header and replace it with a banner that is easy to see, on every public page except Diventa socio.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Home tells people what to do and what is new (Priority: P1)

A visitor opening the home page sees who Safe House is, one primary action (Become a member), secondary Donate and Become a volunteer, and a few small windows of the latest published news and articles. Those windows animate in as the page opens. Links open all news and all articles.

**Why this priority**: Home is the address ads and search will use. Hiding news in Other pages must not hide the stories.

**Independent Test**: Open home in Italian and English, phone and a wide screen. Membership is the primary button. The latest mixed items appear. Both section links work. The address is still the home address.

**Acceptance Scenarios**:

1. **Given** published news and articles, **When** home opens, **Then** a short mixed list of the most recently published items appears, news and articles together, newest first, in small windows that animate in.
2. **Given** that strip, **When** the visitor uses the two section links, **Then** one opens all news and the other opens all articles.
3. **Given** no published stories, **When** home opens, **Then** the strip is absent and the primary actions remain.
4. **Given** the first screen, **When** the visitor looks for an action, **Then** Become a member is primary and Donate and Become a volunteer are secondary. The full membership form stays on the membership page.

---

### User Story 2 - Header is short and 5×1000 stays visible (Priority: P1)

The header lists Who we are, Services, Become a member, Volunteering, and Contact, plus a Donate button. The logo opens home. There is no Home item, no news item, and no 5×1000 item. A clearly visible 5×1000 banner appears on public pages other than Diventa socio.

**Why this priority**: The owner is removing 5×1000 from the header and still needs people to find it.

**Independent Test**: From home, about, volunteer, news, and Diventa socio, read the header and look for 5×1000. The banner is present everywhere in that list except Diventa socio, and it opens the 5×1000 page.

**Acceptance Scenarios**:

1. **Given** any public page, **When** the visitor reads the header, **Then** they see Who we are, Services, Become a member, Volunteering, Contact, and a Donate button.
2. **Given** a public page that is not Diventa socio, **When** the page loads, **Then** a 5×1000 banner is visible without opening the menu.
3. **Given** Diventa socio, **When** the page loads, **Then** that banner is absent.
4. **Given** news and articles, **When** the visitor looks in the header, **Then** they are under Other pages, and home still links to both sections.
5. **Given** an Italian page with the cookie banner open, **When** the visitor chooses English inside that banner, **Then** the banner text becomes English and the page address and the rest of the page stay Italian. The footer language control still switches the whole page.

---

### User Story 3 - Public pages feel current, without becoming a wide landing (Priority: P2)

Volunteer, news, articles, about, services, contact, and donations borrow the membership page's motion and surfaces. On a large screen the content can be wider than today, but it does not stretch edge to edge the way Diventa socio does. The volunteer form is clearly wider on a large screen than on a phone.

**Why this priority**: The owner asked to modernize every public page and to prepare the site for ads, using membership as the sample.

**Independent Test**: Open volunteer on a phone and on a wide screen. The form is narrow on the phone and wider on the wide screen. Open a news article and about. They move and feel related to membership, and the text column does not span the full viewport.

**Acceptance Scenarios**:

1. **Given** a wide screen, **When** the visitor opens the volunteer page, **Then** the form is wider than the phone layout and remains one comfortable reading width, not a full-bleed landing.
2. **Given** news, articles, about, services, contact, and donations, **When** each opens, **Then** the page has the same kind of entrance motion as membership and a wider large-screen column than the old narrow stack.
3. **Given** a visitor who prefers reduced motion, **When** any of these pages open, **Then** the content is still there and the entrance animation does not run.
4. **Given** Diventa socio, **When** this feature ships, **Then** its address, form, and role as the visual sample stay. It is not rebuilt into a different page.

---

### Edge Cases

- A story with only one locale appears in the home strip only for that locale.
- The president video is not part of this feature.
- Legal pages stay readable. They may share the calmer width. They do not become a marketing landing.
- CMS screens are unchanged.
- There is no public Russian.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The home address MUST remain the home page. The membership page MUST remain its own address.
- **FR-002**: Home MUST show Become a member as the primary action, with Donate and Become a volunteer secondary.
- **FR-003**: Home MUST show up to four of the latest published news and articles together, newest first, in small windows that animate in on open, plus a link to all news and a link to all articles.
- **FR-004**: The header MUST list Who we are, Services, Become a member, Volunteering, and Contact, plus a Donate button. The logo MUST open home. The header MUST NOT list Home, news, articles, or 5×1000.
- **FR-005**: News and articles MUST remain under Other pages.
- **FR-006**: A visible 5×1000 banner MUST appear on public pages except Diventa socio and MUST open the existing 5×1000 page.
- **FR-007**: On a large screen the volunteer form MUST be wider than on a phone, without becoming a full-bleed page.
- **FR-008**: Public reading pages MUST use a wider large-screen column than today and MUST NOT stretch edge to edge like the membership landing.
- **FR-009**: Public pages MUST use restrained entrance motion in the membership style. Visitors who prefer reduced motion MUST see the content without that animation.
- **FR-010**: This feature MUST NOT add the president video, MUST NOT change the CMS look, and MUST NOT create the Google Ads account.
- **FR-011**: The language control inside the cookie banner MUST change only the banner's own text. It MUST NOT change the page address or the language of the rest of the site. The footer language control MUST still change the whole page.
- **FR-012**: Tables in public CMS text, including the cookie-policy table, MUST stay inside the screen on a phone. The visitor scrolls the table sideways. The rest of the page does not scroll sideways.
- **FR-013**: Every public template except Diventa socio MUST receive this refresh (width, motion, and the 5×1000 banner where FR-006 applies). The membership template is not restyled.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On home, a first-time visitor can start membership, donation, or volunteering without opening the menu, and can open the latest story or either section index from the same page.
- **SC-002**: 5×1000 is one click from every public page except Diventa socio, without a header item.
- **SC-003**: On a screen wider than a laptop, the volunteer form is visibly wider than on a phone, and a news article's text does not run to both screen edges.
- **SC-004**: With reduced motion requested, home and one inner page still show their content and do not play the entrance animation.

## Assumptions

- The banner sits on all public pages except Diventa socio, including home. That is the default so inner pages do not lose 5×1000 when it leaves the header.
- The home strip mixes both kinds of story by publish time and shows at most four. Empty means hidden.
- Diventa socio is the visual sample, not a layout to copy at full width.
- `007-public-visual-refresh` is not implemented separately. Its public typeface change already shipped. This feature takes the remaining public motion and layout.
- `004` still lists sitelink URLs and checks that those destinations match their labels. This feature makes the pages feel ready. It does not write ads.
