# Contract: Site chrome, motion, and ETS naming

## Drawer (phone only)

| Element | Value |
| :--- | :--- |
| Visible panel title | Safe House ETS |
| First extra link | Home → `route('home')` |
| Hamburger aria-label | existing Menu |
| Desktop `navigation.header` | unchanged, no Home |

## Document title

`{discovery title} {site.layout.title_suffix}` with suffix `— Safe House ETS`.

`og:title` = discovery title only.

## Heading

`.page-hero__headline` vertical center by default (`items-center`). Tagline has no `lg:pb-2` drop.

## Motion

In-scope glass/card blocks: `.landing-reveal` + `.landing-reveal__target`. Frozen: `resources/views/pages/templates/landing.blade.php`.

## Listing names

| Surface | IT | EN |
| :--- | :--- | :--- |
| News listing H1 / Altre Pagine / drawer | Notizie | News |
| Articles listing H1 / Altre Pagine / drawer | Articoli | Articles |
| Home slider buttons | Tutte le notizie / Tutti gli articoli | All news / All articles |

## Counters

`[data-count-to]` on numeric Home stats. Duration ~2000ms. `—` has no attribute.

## Banner (after a commit of the rest)

`.site-five` light red wash ~8% brand primary. Isolated commit.
