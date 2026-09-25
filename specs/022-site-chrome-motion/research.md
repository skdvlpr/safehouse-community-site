# Research: Site chrome, motion, and ETS naming

## Decision 1: Drawer Home is markup-only, not a header config item

**Choice**: Insert a Home `<a href="{{ route('home', ['locale' => $locale]) }}">` at the top of `site-header-drawer__panel` before the `navigation.header` loop. Do not add Home to `config/navigation.php` `header`.

**Why**: Owner: Home only in the sliding menu, not in the desktop nav. Desktop loop stays as today.

**Rejected**: A `header_mobile` config duplicate of the whole menu.

## Decision 2: Drawer title key, Menu stays as aria-label

**Choice**: New `site.nav.drawer_title` = `Safe House ETS` (IT and EN). Panel visible title uses it. Hamburger and close `aria-label` stay `site.nav.menu` / `close_menu`.

**Why**: Accessible name “Menu” still describes the control. Visible branding is the ETS name.

## Decision 3: Title suffix is lang-only

**Choice**: Change `site.layout.title_suffix` to `— Safe House ETS`. `discovery-head.blade.php` already concatenates it. Do not append the suffix to `og:title`.

**Why**: Owner asked for the tab string. Open Graph stays the page name.

## Decision 4: Vertical center is the page-hero default

**Choice**: Default `align` on `page-hero` / `page-header` becomes `center`. CSS `.page-hero__headline` uses `lg:items-center`; drop `lg:pb-2` on the tagline. Cite [align-items](https://tailwindcss.com/docs/align-items).

**Why**: Owner wants this on every page. Passing `align=center` on each include is how `019.1` did donations; the remaining pages still default to `end`. Changing the default is smaller than touching every template.

## Decision 5: Reuse `.landing-reveal`, freeze Diventa socio

**Choice**: Wrap glass/card roots in `.landing-reveal` + inner `.landing-reveal__target` with alternating left/right (or `up` for single stacked blocks). Existing `landing-motion.js` already observes every `.landing-reveal`. Do not edit `resources/views/pages/templates/landing.blade.php`. Cite [Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API) and [prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion).

**Why**: Owner asked for the same animation as socio, and explicitly not to restyle Diventa socio.

**Rejected**: A second observer. CSS-only `@media` animation without the existing stagger.

## Decision 6: Menu uses title keys; Home CTAs keep All

**Choice**: Desktop Altre Pagine and drawer extra-pages group switch from `site.pages.news_all` / `editorial_all` to `site.pages.news_title` / `editorial_title` (Notizie / Articoli, News / Articles). `latest-stories.blade.php` buttons keep `news_all` / `editorial_all`.

**Why**: Listing H1 already uses the title keys. The leftover “all” wording is the dropdown/drawer.

## Decision 7: Count-up is client-only; HTML keeps the final value

**Choice**: New `resources/js/impact-count.js`. Markup: `data-count-to="{int}"` plus the formatted string as text. On boot, if motion is allowed, set text to 0 and ease to `data-count-to` over 2000ms with locale grouping (IT `.` thousands). `—` has no `data-count-to`. Reduced motion: no rewrite. Cite [requestAnimationFrame](https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame).

**Why**: PHPUnit Home tests assert `3.149` in the HTML. No-JS visitors still see totals.

## Decision 8: Banner tint is a second commit

**Choice**: After US1–US6 are committed, mix about 8% `--color-safehouse-primary` (`#dc2626`) into `.site-five` background via `color-mix`. Light and dark theme both get a light wash, not a solid red bar.

**Why**: Owner: commit first so a rejected tint is one revert.
