# Contract: Mobile header drawer

Public UI only. No new HTTP routes.

## Phone header (`< md`)

- Hamburger is visible in the header, to the left of the logo, with `aria-label` from `site.nav.menu` and `aria-expanded`.
- Donate control is one link to `donations.index`. Visible text is `site.nav.donate_short` (**Dona ora** / **Donate now**). The long `site.nav.donate` string is in the markup for the wide breakpoint, not the visible phone label.
- Language / display prefs stay in the header.
- Tapping the hamburger opens a left-edge panel. The panel is transparent with a strong backdrop blur and lists every destination from the wide-screen header, including Altre Pagine children.
- Close: visible close control on the panel, hamburger, backdrop, Escape, or a drawer link.
- Reduced motion: panel still opens and closes; slide animation is off.

## Wide header (`md+`)

- Horizontal `<nav>` remains.
- Drawer and hamburger are not shown.
- Donate visible text is `site.nav.donate` (**Tutti i modi per donare** / **All ways to donate**).

## 5 x 1000 strip

- Same link and copy-codice behavior as today.
- Content is centered under the header menu on a wide screen and centered on a phone.

## Contatti heading

- Line after the bar is `site.pages.contact_lead` as in [data-model.md](../data-model.md).
- Desk names are not in that line.
- Desk explanations sit in the left column with the FAQ button (`site.pages.contact_desks_blurb`).

## Contatti form

- Fields include first name (`name` → Lead `firstName`) and surname (`last_name` → Lead `lastName`).
- Missing surname is rejected.
- Wide layout: left column stretches to the form column height. Form vertical spacing is tighter than today.

## Out of scope

- Diventa socio.
- `019-donations-row`.
- Header color / donate button chrome.
