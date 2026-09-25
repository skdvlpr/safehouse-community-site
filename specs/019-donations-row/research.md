# Research: Donations heading family

## Decision: Reuse the Chi siamo heading partial

**Rationale**: Owner asked every listed donations surface, except Diventa socio, to use that title-and-tagline bar, with the title outside the card. [Laravel Blade](https://laravel.com/docs/13.x/blade) already has `pages.partials.page-header` → `page-hero`.

**Alternatives considered**: A new heading style (rejected). Duplicating hero markup per donations view (rejected).

## Decision: Listing two-up only for 5 x 1000 + bank transfer

**Rationale**: Owner asked those two featured cards on one wide-screen row. Online campaign cards stay stacked. [Tailwind grid-template-columns](https://tailwindcss.com/docs/grid-template-columns) `lg:grid-cols-2` with stacked default.

**Alternatives considered**: Always two columns (rejected on phones). Putting recurring and 5 x 1000 on the same row (owner named 5 x 1000 and bank transfer only).

## Decision: 5 x 1000 title and tagline reuse CMS heading and lead

**Rationale**: Owner said bring the page to the common look and pull the heading out of the card, without giving a new slogan. Moving the existing heading to the large title and the existing lead after the bar avoids inventing copy.

**Alternatives considered**: Hard-coding **5 x 1000 |** plus a new sentence (rejected; would ignore CMS). Leaving heading inside the glass (rejected; owner asked it out).

## Decision: One-time tagline is the campaign name

**Rationale**: Owner example **Donazione | Dona a Safe House**. Campaign public name is already stored.

**Alternatives considered**: A generic tagline for every one-time page (rejected).

## Decision: Recurring heading is fixed copy, not the stored title

**Rationale**: Owner gave the exact Italian title and tagline. Stored campaign title is already “Donazione ricorrente”; using it as the tagline would duplicate the title.

**Alternatives considered**: **Donazione |** plus stored title (rejected; owner named **Donazione ricorrente**).

## Decision: Drop the portal-interrupt sentence from recurring body only

**Rationale**: Owner called it redundant with the red cancel panel. Keep the panel, checkbox, and thank-you cancel copy.

**Alternatives considered**: Removing the red panel (rejected). Editing thank-you (out of scope).

## Decision: Do not change Stripe payment collection

**Rationale**: Constitution `S-STRIPE` locks native [Payment Element](https://docs.stripe.com/payments/payment-element). Official Stripe docs currently prefer Checkout Sessions; this spec only moves chrome around the existing form. MUST NOT switch APIs.

**Alternatives considered**: Checkout Sessions migration (forbidden here).
