# Data model: Home stats order, side reveals, donation form columns

No CRM or MariaDB schema change.

## Home section order

| Position | Block | Blade |
| :--- | :--- | :--- |
| 1 | Hero glass | `resources/views/pages/templates/home.blade.php` first `landing-reveal` |
| 2 | CRM impact stats | `pages.partials.home-impact-stats` |
| 3 | Manifesto quote | `pages.partials.home-manifesto-banner` |
| 4 | Latest stories | `pages.partials.latest-stories` |
| 5 | Independence | `pages.partials.home-independence-banner` |

Stats count-up (`data-count-to`) is unchanged from `022`.

## Reveal direction

| Page | Blocks | `data-reveal-from` |
| :--- | :--- | :--- |
| Chi siamo | intro, values, closing | alternate `left` / `right` (no `up`) |
| Donazioni listing | 5 x 1000, bank, recurring, campaign cards | alternate `left` / `right` (no `up`) |
| 5 x 1000 page | glass article | `left` or `right` |
| Campaign `show` | form card | `left` or `right` |
| Diventa socio | cards | unchanged |
| Other public pages | existing | unchanged |

Parent clip: `overflow: clip` (not mixed `overflow-x: clip`). Easing: `--landing-ease-out` on `.landing-reveal`.

## Donation form layout

| Breakpoint | Card max-width | Fields |
| :--- | :--- | :--- |
| default / phone | `max-w-2xl` (42rem) | single stacked column, current order |
| `lg` and up | `max-w-5xl` (64rem) | two columns: identity \| gift; Payment Element + submit full width below |

No new translation keys. `#donation-form`, `#payment-element`, intent fetch, and `confirmPayment` stay.
