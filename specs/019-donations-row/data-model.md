# Data model: Donations heading family

No new stored entities. Visible strings:

| Surface | Italian title | Italian tagline | English title | English tagline |
| :--- | :--- | :--- | :--- | :--- |
| Listing | Donazioni | Sostieni Safe House. 5 x 1000 o pagamenti digitali. | Donations | Support Safe House. 5 x 1000 or digital payments. |
| 5 x 1000 | CMS heading (example: Dona 5 x 1000) | CMS lead | CMS heading | CMS lead |
| One-time campaign | Donazione | Campaign public name | Donation | Campaign public name |
| Recurring campaign | Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente. | Recurring donation | Support Safe House every month with a recurring contribution. |

Recurring campaign **description** (HTML) MUST NOT contain the portal-interrupt sentence and MUST NOT repeat the tagline. If nothing remains, omit the body.

Constraints (verbatim from spec):

- Recurring body MUST NOT include *Puoi interrompere in qualsiasi momento tramite il portale Stripe dedicato ai donatori.*
- Recurring English body MUST NOT include the English equivalent about cancelling anytime via the Stripe donor portal.
- Recurring tagline MUST be exactly **Sostieni Safe House ogni mese con un contributo ricorrente.** / **Support Safe House every month with a recurring contribution.**
