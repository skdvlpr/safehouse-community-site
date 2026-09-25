# Research: Home stats order, side reveals, donation form columns

## Decision: Home order is hero → stats → quote

**Rationale**: Owner: put the numbers block above the quote, between the quote and the first Home block. The first block is the hero glass in `home.blade.php`. The quote is `home-manifesto-banner`. Stories and independence stay below.

**Alternatives considered**: Stats above the hero (rejected: hero stays first). Stats between quote and stories (rejected: owner said above the quote).

## Decision: Chi siamo and Donazioni use socio left/right; other pages keep current reveals

**Rationale**: Owner said the previous pass did not give them socio-style side slides on Chi siamo and Donazioni, and other pages may stay as they are. Diventa socio (`landing.blade.php`) is not edited.

**Alternatives considered**: Convert every public glass to left/right (rejected). Restyle Diventa socio (out of scope).

## Decision: Define `--landing-ease-out` on `.landing-reveal` and clip with `overflow: clip`

**Rationale**: Socio cards work because `.template-page--landing` sets `--landing-ease-out` and `.template-landing-cards` uses `overflow: clip`. [CSS overflow](https://drafts.csswg.org/css-overflow/#overflow-properties): mixed `overflow-x: clip` + `overflow-y: visible` computes to `auto`. Chi siamo grid uses `overflow-x-clip` only. `--landing-ease-out` is unset on `.template-page--about` and the donations layout, so `transform 0.9s var(--landing-ease-out)` is invalid and the ±100vw slide never runs — only a fade. Closing (Chi siamo) and listing/form cards still use `data-reveal-from="up"` (2rem). Fix: set the easing on `.landing-reveal`, switch those blocks to left/right, clip parents like socio. Observer stays in `landing-motion.js` ([Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API)).

**Alternatives considered**: Copy the socio card markup onto Chi siamo (unnecessary). New GSAP library (rejected).

## Decision: Donation form is `max-w-2xl` then `lg:max-w-5xl` with two field columns

**Rationale**: Owner: make the form wide on desktop without stretching fields — pull lower fields up into several columns; keep the current stacked narrow card on a small screen. [max-width](https://tailwindcss.com/docs/max-width): `max-w-2xl` = 42rem (today), `max-w-5xl` = 64rem (two columns). [grid-template-columns](https://tailwindcss.com/docs/grid-template-columns): `grid-cols-1 lg:grid-cols-2`. Identity (type, name, email, phone) in column 1; comment, amount, notices, ack, privacy in column 2. Payment Element and submit stay full-card under the columns ([Payment Element](https://docs.stripe.com/payments/payment-element) still `mount('#payment-element')`). Recurring cancel panel stays above the columns. Same Blade for one-time and recurring.

**Alternatives considered**: Stretch a single column to `max-w-5xl` (rejected: owner forbade stretching fields). Three columns (fields too narrow). Change Diventa socio (out of scope).

## Decision: Tests assert HTML order and classes, not pixel motion

**Rationale**: IntersectionObserver timing is owner UAT. PHPUnit checks Home stats-before-quote, Chi siamo / Donazioni `left`+`right` without `up` on those cards, and form `donation-form__columns` + `lg:max-w-5xl` ([testing](https://laravel.com/docs/13.x/testing)).
