# Data model: Ads-ready public refresh

No new tables and no new columns.

## Latest story

Computed, not stored. A published news article or editorial article in the current locale.

| Field | Rules |
| :--- | :--- |
| Kind | News or article |
| Title | That locale's title. Skip the row if that locale has no title |
| URL | Existing public show URL |
| When | `published_at`, else `updated_at` |
| Order | Newest first |
| Limit | 4 |

## Header item

Existing `config/navigation.php` rows. No database menu.

| Item | Target |
| :--- | :--- |
| Who we are | page `about` |
| Services | page `services` |
| Become a member | page `diventa-socio` |
| Volunteering | route `volunteers.show` |
| Contact | page `contact` |
| Donate | existing header button, unchanged |

`diventa-socio` is added to `standard_page_keys` so it does not also appear under Other pages.

## 5×1000 banner

Not stored. Visible on public layouts except when the current page key is `diventa-socio`. Destination is the existing 5×1000 page.
