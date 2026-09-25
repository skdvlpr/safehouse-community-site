# Contract: Donation form width and one-time heading

Supersedes `019` one-time heading row. Recurring heading and listing/5 x 1000 copy stay. Payment Element stays inside the form ([Payment Element](https://docs.stripe.com/payments/payment-element)).

| Route (Italian) | Title | Tagline | Card below |
| :--- | :--- | :--- | :--- |
| `/it/donations` | Donazioni | Sostieni Safe House. 5 x 1000 o pagamenti digitali. | Featured cards full width; heading `align=center` |
| `/it/donations/5-per-thousand` | CMS heading | CMS lead | Body, tax code, instructions; heading `align=center` |
| `/it/donations/{slug}` one-time | Campaign name | Sostieni Safe House con un dono. | `#donation-form` `max-w-2xl mx-auto` |
| `/it/donations/{slug}` recurring | Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente. | Same narrow form; red cancel panel stays |

English:

| Route | Title | Tagline |
| :--- | :--- | :--- |
| `/en/donations/{slug}` one-time | Campaign name | Support Safe House with a gift. |
| `/en/donations/{slug}` recurring | Recurring donation | Support Safe House every month with a recurring contribution. |
