# Contract: Home order, side reveals, donation form columns

Cite: [Blade](https://laravel.com/docs/13.x/blade), [grid-template-columns](https://tailwindcss.com/docs/grid-template-columns), [max-width](https://tailwindcss.com/docs/max-width), [Payment Element](https://docs.stripe.com/payments/payment-element), [Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API).

## Home HTML order

On `GET /it` and `GET /en`, the document body contains the impact stats heading (`Pasti distribuiti` / `Meals distributed` or the current `site.home.stats` labels) **after** the Home hero `h1` and **before** the manifesto quote string `NESSUN ESSERE UMANO È ILLEGALE`.

## Chi siamo reveal

`GET /it/about-us` HTML contains `data-reveal-from="left"` and `data-reveal-from="right"`. The intro, values, and closing wrappers do not use `data-reveal-from="up"`. `landing.blade.php` is not part of this contract.

## Donazioni reveal

`GET /it/donations` listing cards (featured, recurring when present, campaign list) use `left` or `right`, not `up`. `GET` 5 x 1000 and campaign `show` main glass/form use `left` or `right`.

## Donation form

`GET /it/donations/{slug}` (one-time and recurring) includes:

- `id="donation-form"`
- class `donation-form__columns`
- `max-w-2xl` (phone cap)
- `lg:max-w-5xl` (desktop card)
- `id="payment-element"` still present; JS still mounts Payment Element there

Diventa socio membership form is out of contract.
