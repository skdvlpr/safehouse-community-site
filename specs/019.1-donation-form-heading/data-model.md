# Data model: Donation form width and one-time heading

No new tables. Lang keys only.

| Key | Italian | English |
| :--- | :--- | :--- |
| `site.donations.campaign_tagline` | Sostieni Safe House con un dono. | Support Safe House with a gift. |
| `site.donations.recurring_heading` (unchanged) | Donazione ricorrente | Recurring donation |
| `site.donations.recurring_tagline` (unchanged) | Sostieni Safe House ogni mese con un contributo ricorrente. | Support Safe House every month with a recurring contribution. |
| `site.donations.campaign_heading` | Donazione | Donation |

`campaign_heading` is no longer the one-time H1. It may remain unused.

One-time `show` H1 = localized campaign `title`. Tagline = `campaign_tagline`.
