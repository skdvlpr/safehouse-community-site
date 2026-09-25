# Contract: Donations heading family

Shared pattern: Chi siamo heading — large title, vertical bar, tagline — **outside** the glass or form card. No red eyebrow above the title. Phone may wrap the tagline. Diventa socio, thank-you, and payment-privacy are excluded.

| Route (Italian) | Title | Tagline | Card below |
| :--- | :--- | :--- | :--- |
| `/it/donations` | Donazioni | Sostieni Safe House. 5 x 1000 o pagamenti digitali. | Featured 5 x 1000 + bank transfer on one wide row; campaigns unchanged |
| `/it/donations/5-per-thousand` | CMS heading | CMS lead | Body, tax code, instructions; no repeated large title |
| `/it/donations/{slug}` one-time | Donazione | Campaign name | Story, progress, donor fields, payment widget |
| `/it/donations/{slug}` recurring | Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente. | No portal-interrupt body sentence; red cancel panel stays |

English routes under `/en/…` use the English lines in [data-model.md](../data-model.md).

Payment Element stays mounted inside the form card. This contract does not change payment confirmation.
