# Research: Light-theme chrome, 5×1000 mark, slower Home count

## Decision: Exclude values + closing from the light glass-border remap

**Rationale**: Owner screenshots show two Chi siamo blocks with a red outline in dark and a plain grey glass border in light. `.template-about-values` uses `--safehouse-accent-panel-border` (primary mixed with glass). `.template-about-closing` uses `border-l-4 border-safehouse-primary`; Tailwind `border-safehouse-primary` sets [border-color](https://tailwindcss.com/docs/border-color) on all sides, so dark already draws a full red rounded box (thick left). The light-theme block at `html[data-theme='light'] .template-page--about .template-about-values` **and** `.template-about-closing` forces `border-color: var(--safehouse-glass-border)`, which is why the red disappears. Fix: drop those two selectors from the remap; set closing to `--color-safehouse-primary` and values to a stronger primary mix so light still reads as a red outline. Intro stays on the generic remap.

**Alternatives considered**: Raise `--safehouse-accent-panel-border` globally in light (would also change Servizi banners). Duplicate a new class on the Blade (unnecessary — CSS exception is enough).

## Decision: Rectangular transparent 5× mark; hover fills primary with white text

**Rationale**: Owner: make the logo rectangular, keep **5×**, transparent with red outline; hover must not hide the inscription. Current `.site-five__mark` is `rounded-full bg-safehouse-primary text-white`. Hover applies `text-safehouse-link` **and** `bg-safehouse-primary-hover` — red letters on a red fill, which vanishes on light. Restyle rest to [border-radius](https://tailwindcss.com/docs/border-radius) `rounded-sm`, `bg-transparent`, `border-safehouse-primary`, `text-safehouse-primary`. Hover: fill `--color-safehouse-primary`, text `#fff`, stronger link wash via `color-mix` with primary instead of `hover:bg-white/5` (light remaps that to `rgb(0 0 0 / 4%)`, which is the weak highlight).

**Alternatives considered**: Keep the circle and only fix hover text (rejected: owner asked for rectangle). Invert to white fill on hover (weaker brand). Change Blade to an SVG (unnecessary).

## Decision: Map `.news-cat-menu__summary` to the same light control tokens as date inputs

**Rationale**: Light already sets date inputs to `background-color: rgb(255 255 255 / 92%)`, `border-color: rgb(0 0 0 / 14%)`. The category summary still uses `bg-safehouse-page/35` + remapped `border-white/10`, so it looks greyer. Add the summary to that same rule. Dark already shares `border-white/10` + `bg-safehouse-page/35` with the dates.

**Alternatives considered**: Restyle dates down to match Categorie (rejected: owner said make Categorie like the others). New shared utility class (more churn than one selector).

## Decision: Count duration 2000 → 3500 ms, keep easeOutCubic

**Rationale**: Owner: “a bit slower” after the chrome work. Current `COUNT_DURATION_MS = 2000` in `impact-count.js`. 3500 ms is clearly longer without feeling stuck. Keep [requestAnimationFrame](https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame) and skip when [prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion) matches.

**Alternatives considered**: 3000 ms (too close to “a bit”). 5000 ms (too slow). Change easing (not asked).

## Decision: Tests assert markup classes, not pixels

**Rationale**: Theme and hover are owner UAT. PHPUnit asserts Chi siamo still has `template-about-values` / `template-about-closing` / `border-safehouse-primary` in CSS source via HTML class presence, banner still has `site-five__mark` and **5×**, news still has `news-cat-menu__summary` ([testing](https://laravel.com/docs/13.x/testing)). Duration is a named constant — no PHPUnit of rAF.
