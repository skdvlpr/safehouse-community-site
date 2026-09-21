# Research: 002.4 urgent visual and legal

## Cookie banner shape

**Decision**: Wide card (`max-w-5xl`), slightly above the bottom, desktop actions in a row (`md:grid-cols-[auto_1fr_auto]`). Fully opaque `background-color: var(--color-safehouse-modal)`. Remove `safehouse-glass` from the banner card.

**Rationale**: Owner rejected the compact vertical card from `0cdb4e0`, especially in light theme. [Passiro cookie banners](https://passiro.com/cookie-compliance/cookie-banners/design/); [WCAG 2.2 contrast](https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html).

**Alternatives**: Keep glass (rejected); third-party CMP (already rejected in 002).

## Typeface

**Decision**: Self-host [Nunito Sans](https://fonts.google.com/specimen/Nunito+Sans) woff2 and set `--font-sans` ([Tailwind font-family](https://tailwindcss.com/docs/font-family)). No `fonts.googleapis.com`. CMS Filament Inter unchanged.

**Rationale**: Owner asked for a Google Fonts sans, not JetBrains. Self-host avoids a new third-party request on every page.

## Landing

**Decision**: Split CMS HTML on `<hr>` ([LandingBody](../../app/Support/LandingBody.php)). First chunk = hero intro; remaining chunks = card grid. Membership key `diventa-socio` gets Compila domanda + native [dialog](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dialog). Hide the visual column when the page has no carousel so the right half is not empty.

**Rationale**: Owner A+C hybrid; do not rewrite CMS essay.

## Membership form vs official Word module

**Decision**: Public modal = form §1–3 only (identity, residence, three declarations, newsletter radio). §4 Consiglio Direttivo stays off the site. POST `/membership-application` with `throttle:membership` 3/hour ([Laravel routing](https://laravel.com/docs/13.x/routing#rate-limiting)). Mail required like volunteer ([Laravel mail](https://laravel.com/docs/13.x/mail)). Espo `POST Lead` best-effort ([Espo API](https://docs.espocrm.com/development/api/)) with `contactType: ['MemberContact']`. Turnstile implicit + re-render on `showModal` ([Turnstile embed](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/)).

**Rationale**: Owner file *Modulo Domanda di Ammissione Socio*. CRM already has MemberContact / taxCode / birth* on Lead. Hidden `layoutAvailabilityList: []` is a CRM-agent layout job, not a site schema change.

**Alternatives**: New site table (rejected — volunteer table was dropped). Wait for CRM PDF (rejected for this hotfix — mail + Lead first).

## Legal seat and FAQ

**Decision**: Seat strings in `lang/*/site.php` (`site.org.*`) plus `LegalPagesContent` privacy/cookie bodies. Contatti FAQ is a primary button via `urlForKey('faq')`. Strip pasted production URL from `deploy-pages.php` contact body.

**Rationale**: Owner: button, not URL; Anzio / RUNTS / CF from the official form.
