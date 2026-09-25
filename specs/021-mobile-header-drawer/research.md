# Research: Mobile header drawer

## Decision: Left drawer plus hamburger, not a shorter Menu dropdown

**Rationale**: Owner asked to try a left-sliding menu with animation on a phone. The current `details.site-header__menu` sits after the donate button and is the control that disappeared. A hamburger on the left of the header cannot be covered by that button. [Tailwind translate](https://tailwindcss.com/docs/translate) plus [transition](https://tailwindcss.com/docs/transition-property) animate `-translate-x-full` to `translate-x-0`. [z-index](https://tailwindcss.com/docs/z-index) keeps the overlay above `.site-top`. [width](https://tailwindcss.com/docs/width) sizes the panel (`w-[min(20rem,85vw)]`).

**Alternatives considered**: Keep `details` and only shorten the donate label (rejected by owner). Off-canvas from the right (rejected: owner asked left). Alpine or Livewire (rejected: no new JS framework).

## Decision: Transparent panel with strong blur

**Rationale**: Owner asked for a transparent drawer with a strong blur. The 5 x 1000 strip already uses `backdrop-blur-[96px]` with a low-opacity fill ([backdrop-blur](https://tailwindcss.com/docs/backdrop-blur)). Reuse that strength on the drawer panel (`bg-safehouse-page/40` or similar) plus a dimmed full-screen backdrop.

**Alternatives considered**: Opaque `bg-safehouse-modal` like the current dropdown (rejected). Tailwind `backdrop-blur-3xl` at 64px (weaker than the existing strip).

## Decision: Two donate labels in one link

**Rationale**: Spec keeps the long label on a wide screen and uses **Dona ora** / **Donate now** on a phone. One `<a>` with two spans (`md:hidden` / `hidden md:inline`) avoids two URLs. New key `site.nav.donate_short`. Existing `site.nav.donate` stays for desktop. [Blade](https://laravel.com/docs/13.x/blade).

**Alternatives considered**: One short label on every width (rejected: owner wants the long text on desktop). CSS `text-overflow` on the long Italian sentence (illegible).

## Decision: Vanilla JS module, not `details`

**Rationale**: Backdrop click, Escape, `aria-expanded`, and a CSS slide need an open state. The site already ships small Vite modules (`resources/js/sportello-select.js`). A `header-drawer.js` imported from `resources/js/app.js` matches that pattern. Reduced motion uses Tailwind `motion-reduce:transition-none` so the panel still opens ([transition](https://tailwindcss.com/docs/transition-property)).

**Alternatives considered**: Checkbox hack with no JS (Escape and focus trap are worse). Native `<dialog>` (harder to keep the transparent blur panel flush left).

## Decision: Center the 5 x 1000 inner, do not restyle the header chrome

**Rationale**: Header bar is `justify-between` with the nav as the middle child, so centering the strip inside the same `.site-content` width places it under the menu on a wide screen. On a phone, center the link and the copy control. Do not change header color or donate button chrome.

**Alternatives considered**: A three-column grid that mirrors logo/nav/actions pixel-for-pixel (fragile when labels change).

## Decision: Shorten `site.pages.contact_lead` and move desk blurbs to the FAQ block

**Rationale**: Contatti heading uses that key as the line after the bar. Drop the desk list from the heading. Put Sportello digitale / legale / Richiesta generica in the left column next to Domande frequenti. Membership `site.membership.contact_lead` stays.

**Alternatives considered**: CSS line-clamp on the long sentence (hides meaning). Keep desks only in the form select (owner asked the descriptions next to the FAQ button).

## Decision: Surname is Lead `lastName`, no CRM repo edit

**Rationale**: Membership and volunteer already send `last_name` to EspoCRM `lastName`. Contact intake currently splits a single `name` string. Site adds `contact_submissions.last_name` → Lead `lastName`; `name` → Lead `firstName`. [Laravel validation](https://laravel.com/docs/13.x/validation), [migrations](https://laravel.com/docs/13.x/migrations).

**Alternatives considered**: Keep splitting one name field (rejected). Edit Espo entityDefs (forbidden).

## Decision: Phone home news is swipe-only

**Rationale**: Owner screenshot showed arrows and two listing buttons eating the preview. Hide `.story-slider__arrow` and `.story-slider__links` below `md`, enlarge the card, swipe on the viewport.

**Alternatives considered**: Keep arrows only (rejected). Remove listing links from HTML (rejected: they stay for wide screens).

## Decision: Cookie table scrolls only on a phone

**Rationale**: `.prose-table-scroll` already wraps CMS tables. `overflow-x-auto` only below `md`; from `md` the table is `w-full` so all columns show. [overflow](https://tailwindcss.com/docs/overflow).

**Alternatives considered**: Always scroll (rejected: owner wants a full table on wide).

## Decision: Visible close control on the drawer

**Rationale**: Owner required a way to close the menu. Backdrop, Escape, and hamburger stay; the open panel also shows a close control.

**Alternatives considered**: Close only via hamburger (easy to miss once the panel covers it).
